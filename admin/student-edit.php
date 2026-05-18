<?php
// admin/student-edit.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$id = intval($_GET['id'] ?? 0); // This is students.id
if ($id <= 0) {
    header("Location: students.php?error=" . urlencode("Invalid student ID!"));
    exit();
}

$error = "";

// Fetch student and user details together
$stmt = $pdo->prepare("
    SELECT s.id as student_row_id, s.student_id_no, s.phone, s.dept_id, s.dob, s.address, u.* 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: students.php?error=" . urlencode("Student not found!"));
    exit();
}

// Fetch all departments for selection
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $student_id_no = strtoupper(trim($_POST['student_id_no'] ?? ''));
    $dept_id = intval($_POST['dept_id'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $user_id = $student['id']; // users.id

    if (empty($full_name) || empty($email) || empty($username) || empty($student_id_no) || $dept_id <= 0) {
        $error = "All primary fields (Full Name, Username, Email, Student ID No, Department) are required!";
    } else {
        try {
            // Check if username or email already exists in OTHER users
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            $user_exists = $stmt->fetchColumn() > 0;

            // Check if student_id_no already exists in OTHER students
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id_no = ? AND id != ?");
            $stmt->execute([$student_id_no, $id]);
            $student_id_exists = $stmt->fetchColumn() > 0;

            if ($user_exists) {
                $error = "Another user with this username or email already exists!";
            } elseif ($student_id_exists) {
                $error = "Another student with this Student ID Number already exists!";
            } else {
                $pdo->beginTransaction();

                // 1. Update users table
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $hashed_password, $full_name, $email, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $full_name, $email, $user_id]);
                }

                // 2. Update students table
                $stmt = $pdo->prepare("UPDATE students SET dept_id = ?, student_id_no = ?, phone = ?, address = ?, dob = ? WHERE id = ?");
                $stmt->execute([$dept_id, $student_id_no, $phone, !empty($address) ? $address : null, !empty($dob) ? $dob : null, $id]);

                $pdo->commit();

                header("Location: students.php?success=" . urlencode("Student profile updated successfully!"));
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Error updating student: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Edit Student</h1>
            <p style="color: var(--text-muted);"><a href="students.php" style="color: var(--accent); text-decoration: none;">Students</a> &raquo; Edit</p>
        </div>
        <a href="students.php" class="btn" style="background: #f1f5f9; color: var(--text-main); text-decoration: none; width: auto;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </header>

    <div style="max-width: 800px; margin: 0 auto;">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 1.125rem;">Edit Student Profile & Credentials</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST">
                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-bottom: 1rem; color: var(--accent);">Personal & Academic Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="full_name" style="font-weight: 600;">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($student['full_name']) ?>" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="student_id_no" style="font-weight: 600;">Student ID Number</label>
                            <input type="text" id="student_id_no" name="student_id_no" class="form-control" value="<?= htmlspecialchars($student['student_id_no']) ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="dept_id" style="font-weight: 600;">Department / Stream</label>
                            <select id="dept_id" name="dept_id" class="form-control" required>
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= ($student['dept_id'] == $dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone" style="font-weight: 600;">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" placeholder="e.g. +1 555-0155">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="dob" style="font-weight: 600;">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="form-control" value="<?= $student['dob'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address" style="font-weight: 600;">Home Address</label>
                        <textarea id="address" name="address" class="form-control" rows="2" placeholder="e.g. 123 University Drive, Cityville"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                    </div>

                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem; color: var(--accent);">Authentication Credentials</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="username" style="font-weight: 600;">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($student['username']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email" style="font-weight: 600;">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
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
