<?php
// admin/student-create.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$error = "";

// Fetch all departments for selection
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

// Suggest next Student ID
$current_year = date('Y');
$suggested_id = "UNIT-" . $current_year . "-" . sprintf("%03d", rand(1, 999));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $student_id_no = strtoupper(trim($_POST['student_id_no'] ?? ''));
    $dept_id = 1;
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($student_id_no)) {
        $error = "All primary fields (Full Name, Username, Email, Password, and Student ID No) are required!";
    } else {
        try {
            // Check if username, email or student_id_no already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $user_exists = $stmt->fetchColumn() > 0;

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id_no = ?");
            $stmt->execute([$student_id_no]);
            $student_id_exists = $stmt->fetchColumn() > 0;

            if ($user_exists) {
                $error = "A user with this username or email already exists!";
            } elseif ($student_id_exists) {
                $error = "A student with this Student ID Number already exists!";
            } else {
                $pdo->beginTransaction();

                // 1. Insert into users
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, 'student', ?, ?)");
                $stmt->execute([$username, $hashed_password, $full_name, $email]);
                $user_id = $pdo->lastInsertId();

                // 2. Insert into students
                $stmt = $pdo->prepare("INSERT INTO students (user_id, dept_id, student_id_no, phone, address, dob) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $dept_id, $student_id_no, $phone, !empty($address) ? $address : null, !empty($dob) ? $dob : null]);

                $pdo->commit();

                header("Location: students.php?success=" . urlencode("Student registered successfully!"));
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Error registering student: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Add New Student</h1>
            <p style="color: var(--text-muted);"><a href="students.php" style="color: var(--accent); text-decoration: none;">Students</a> &raquo; Register</p>
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
                <h2 style="font-size: 1.125rem;">Student Profile & Credentials</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST">
                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-bottom: 1rem; color: var(--accent);">Personal & Academic Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="full_name" style="font-weight: 600;">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Alice Johnson" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="student_id_no" style="font-weight: 600;">Student ID Number</label>
                            <input type="text" id="student_id_no" name="student_id_no" class="form-control" value="<?= $suggested_id ?>" required placeholder="e.g. UNIT-2026-001">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">

                        <div class="form-group">
                            <label for="phone" style="font-weight: 600;">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. +1 555-0155">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="dob" style="font-weight: 600;">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address" style="font-weight: 600;">Home Address</label>
                        <textarea id="address" name="address" class="form-control" rows="2" placeholder="e.g. 123 University Drive, Cityville"></textarea>
                    </div>

                    <h3 style="font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem; color: var(--accent);">Authentication Credentials</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="username" style="font-weight: 600;">Username</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="e.g. alicej" required>
                        </div>
                        <div class="form-group">
                            <label for="email" style="font-weight: 600;">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="e.g. alice@unitrack.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" style="font-weight: 600;">Account Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-save"></i> Save Student Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
