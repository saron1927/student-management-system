<?php
// admin/teacher-edit.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$id = intval($_GET['id'] ?? 0); // This is teachers.id
if ($id <= 0) {
    header("Location: teachers.php?error=" . urlencode("Invalid teacher ID!"));
    exit();
}

$error = "";

// Fetch teacher and user details together
$stmt = $pdo->prepare("
    SELECT t.id as teacher_row_id, t.qualification, t.phone, t.dept_id, u.* 
    FROM teachers t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.id = ?
");
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: teachers.php?error=" . urlencode("Teacher not found!"));
    exit();
}

// Fetch all departments for selection
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $dept_id = 1;
    $qualification = trim($_POST['qualification'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $user_id = $teacher['id']; // users.id

    if (empty($full_name) || empty($email) || empty($username)) {
        $error = "All primary fields (Full Name, Username, Email) are required!";
    } else {
        try {
            // Check if username or email already exists in OTHER users
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Another user with this username or email already exists!";
            } else {
                $pdo->beginTransaction();

                // 1. Update users table
                if (!empty($password)) {
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $hashed_password, $full_name, $email, $user_id]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $full_name, $email, $user_id]);
                }

                // 2. Update teachers table
                $stmt = $pdo->prepare("UPDATE teachers SET dept_id = ?, qualification = ?, phone = ? WHERE id = ?");
                $stmt->execute([$dept_id, $qualification, $phone, $id]);

                $pdo->commit();

                header("Location: teachers.php?success=" . urlencode("Teacher updated successfully!"));
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Error updating teacher: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Edit Teacher</h1>
            <p style="color: var(--text-muted);"><a href="teachers.php" style="color: var(--accent); text-decoration: none;">Teachers</a> &raquo; Edit</p>
        </div>
        <a href="teachers.php" class="btn" style="background: #f1f5f9; color: var(--text-main); text-decoration: none; width: auto;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </header>

    <div style="max-width: 700px; margin: 0 auto;">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 1.125rem;">Edit Teacher Profile & Credentials</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST">
                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-bottom: 1rem; color: var(--accent);">Personal Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="full_name" style="font-weight: 600;">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($teacher['full_name']) ?>" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="phone" style="font-weight: 600;">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($teacher['phone'] ?? '') ?>" placeholder="e.g. +1 555-0199">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">

                        <div class="form-group">
                            <label for="qualification" style="font-weight: 600;">Qualification / Title</label>
                            <input type="text" id="qualification" name="qualification" class="form-control" value="<?= htmlspecialchars($teacher['qualification'] ?? '') ?>" placeholder="e.g. PhD in Computer Science">
                        </div>
                    </div>

                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem; color: var(--accent);">Authentication Credentials</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="username" style="font-weight: 600;">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($teacher['username']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email" style="font-weight: 600;">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" style="font-weight: 600;">Account Password (Leave blank to keep current)</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
