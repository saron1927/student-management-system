<?php
// admin/course-create.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$error = "";

// Fetch all departments for selection
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

// Fetch all teachers for selection
$teachers = $pdo->query("
    SELECT t.id, u.full_name 
    FROM teachers t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY u.full_name ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $credits = intval($_POST['credits'] ?? 0);
    $dept_id = intval($_POST['dept_id'] ?? 0);
    $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    if (empty($name) || empty($code) || $credits <= 0 || $dept_id <= 0) {
        $error = "All primary fields are required! Credits must be greater than 0.";
    } else {
        try {
            // Check if course code already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE code = ?");
            $stmt->execute([$code]);
            if ($stmt->fetchColumn() > 0) {
                $error = "A course with this code already exists!";
            } else {
                // Insert new course
                $stmt = $pdo->prepare("INSERT INTO courses (dept_id, teacher_id, name, code, credits) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$dept_id, $teacher_id, $name, $code, $credits]);
                header("Location: courses.php?success=" . urlencode("Course created successfully!"));
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error adding course: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Add New Subject</h1>
            <p style="color: var(--text-muted);"><a href="courses.php" style="color: var(--accent); text-decoration: none;">Subjects</a> &raquo; Add New</p>
        </div>
        <a href="courses.php" class="btn" style="background: #f1f5f9; color: var(--text-main); text-decoration: none; width: auto;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </header>

    <div style="max-width: 650px; margin: 0 auto;">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (count($departments) === 0): ?>
            <div style="background: #fef3c7; color: #d97706; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-triangle"></i> You must <a href="department-create.php" style="color: #b45309; font-weight: 700;">create a class and section</a> before adding a subject!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 1.125rem;">Subject Details</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="name" style="font-weight: 600;">Subject Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Mathematics" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="code" style="font-weight: 600;">Subject Code</label>
                            <input type="text" id="code" name="code" class="form-control" placeholder="e.g. MATH1" required maxlength="10">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="dept_id" style="font-weight: 600;">Class and Section</label>
                            <select id="dept_id" name="dept_id" class="form-control" required>
                                <option value="">-- Select Class and Section --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="credits" style="font-weight: 600;">Credits</label>
                            <input type="number" id="credits" name="credits" class="form-control" min="1" max="10" placeholder="e.g. 3" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="teacher_id" style="font-weight: 600;">Assigned Teacher (Optional)</label>
                        <select id="teacher_id" name="teacher_id" class="form-control">
                            <option value="">-- Unassigned / Select Teacher --</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;" <?= (count($departments) === 0) ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Save Subject
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
