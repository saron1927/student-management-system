<?php
// admin/teacher-create.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$error = "";

// Fetch all departments for selection
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $dept_id = intval($_POST['dept_id'] ?? 0);
    $qualification = trim($_POST['qualification'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($full_name) || empty($email) || empty($username) || empty($password) || $dept_id <= 0) {
        $error = "All primary fields (Full Name, Username, Email, Password, Department) are required!";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $username_exists = $stmt->fetchColumn() > 0;

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $email_exists = $stmt->fetchColumn() > 0;

            if ($username_exists) {
                $error = "The username '<strong>" . htmlspecialchars($username) . "</strong>' is already taken! Please choose a different username.";
            } elseif ($email_exists) {
                $error = "The email address '<strong>" . htmlspecialchars($email) . "</strong>' is already registered! Please use a different email.";
            } else {
                // Wrap in transaction for safety
                $pdo->beginTransaction();

                // 1. Insert into users
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, 'teacher', ?, ?)");
                $stmt->execute([$username, $hashed_password, $full_name, $email]);
                $user_id = $pdo->lastInsertId();

                // 2. Insert into teachers
                $stmt = $pdo->prepare("INSERT INTO teachers (user_id, dept_id, qualification, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $dept_id, $qualification, $phone]);

                $pdo->commit();

                header("Location: teachers.php?success=" . urlencode("Teacher added successfully!"));
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Error adding teacher: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Add New Teacher</h1>
            <p style="color: var(--text-muted);"><a href="teachers.php" style="color: var(--accent); text-decoration: none;">Teachers</a> &raquo; Add New</p>
        </div>
        <a href="teachers.php" class="btn" style="background: #f1f5f9; color: var(--text-main); text-decoration: none; width: auto;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </header>

    <div style="max-width: 700px; margin: 0 auto;">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (count($departments) === 0): ?>
            <div style="background: #fef3c7; color: #d97706; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-triangle"></i> You must <a href="department-create.php" style="color: #b45309; font-weight: 700;">create a department</a> before adding a teacher!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 1.125rem;">Teacher Profile & Credentials</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST">
                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-bottom: 1rem; color: var(--accent);">Personal Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="full_name" style="font-weight: 600;">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Dr. Jane Doe" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="phone" style="font-weight: 600;">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. +1 555-0199">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="dept_id" style="font-weight: 600;">Department Assignment</label>
                            <select id="dept_id" name="dept_id" class="form-control" required>
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="qualification" style="font-weight: 600;">Qualification / Title</label>
                            <input type="text" id="qualification" name="qualification" class="form-control" placeholder="e.g. PhD in Computer Science">
                        </div>
                    </div>

                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem; color: var(--accent);">Authentication Credentials</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="username" style="font-weight: 600;">Username</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="e.g. janedoe" required>
                        </div>
                        <div class="form-group">
                            <label for="email" style="font-weight: 600;">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="e.g. jane@unitrack.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" style="font-weight: 600;">Account Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;" <?= (count($departments) === 0) ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Save Teacher Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
