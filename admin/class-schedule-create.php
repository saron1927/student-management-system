<?php
// admin/class-schedule-create.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Load teachers & courses & departments for dropdowns
$teachers    = $pdo->query("SELECT t.id, u.full_name FROM teachers t JOIN users u ON t.user_id = u.id ORDER BY u.full_name")->fetchAll();
$courses     = $pdo->query("SELECT id, name, code FROM courses ORDER BY name")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $course_id  = (int)($_POST['course_id']  ?? 0);
    $dept_id    = (int)($_POST['dept_id']    ?? 0);
    $section    = trim($_POST['section']     ?? '');
    $date       = trim($_POST['date']        ?? '');
    $time_start = trim($_POST['time_start']  ?? '');
    $time_end   = trim($_POST['time_end']    ?? '');

    if (!$teacher_id) $errors[] = 'Please select a teacher.';
    if (!$course_id)  $errors[] = 'Please select a subject.';
    if (!$dept_id)    $errors[] = 'Please select a class.';
    if (!$section)    $errors[] = 'Section is required.';
    if (!$date)       $errors[] = 'Date is required.';
    if (!$time_start) $errors[] = 'Start time is required.';
    if (!$time_end)   $errors[] = 'End time is required.';
    if ($time_start && $time_end && $time_end <= $time_start)
        $errors[] = 'End time must be after start time.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO class_schedule (teacher_id, course_id, dept_id, section, date, time_start, time_end)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$teacher_id, $course_id, $dept_id, $section, $date, $time_start, $time_end]);
        header('Location: class-schedule.php?success=' . urlencode('Schedule entry created successfully.'));
        exit;
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;">Add New Class Schedule</h1>
            <p style="color:var(--text-muted);">Create a new timetable entry</p>
        </div>
        <a href="class-schedule.php" class="btn" style="width:auto;text-decoration:none;background:#e2e8f0;color:var(--text-main);">
            <i class="fas fa-arrow-left"></i> Back to Schedule
        </a>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="sms-alert sms-alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin:.5rem 0 0 1.25rem;padding:0;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width:760px;">
        <div class="card-header">
            <span style="font-weight:600;"><i class="fas fa-calendar-plus" style="margin-right:.5rem;color:var(--accent);"></i>Schedule Details</span>
        </div>
        <form method="POST" style="padding:1.75rem;display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

            <!-- Teacher -->
            <div class="form-group" style="grid-column:1/3;">
                <label for="teacher_id">Teacher <span style="color:var(--danger);">*</span></label>
                <select name="teacher_id" id="teacher_id" class="form-control" required>
                    <option value="">— Select Teacher —</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= (($old['teacher_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Subject -->
            <div class="form-group">
                <label for="course_id">Subject <span style="color:var(--danger);">*</span></label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <option value="">— Select Subject —</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($old['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['code'] . ' – ' . $c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Class -->
            <div class="form-group">
                <label for="dept_id">Class <span style="color:var(--danger);">*</span></label>
                <select name="dept_id" id="dept_id" class="form-control" required>
                    <option value="">— Select Class —</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= (($old['dept_id'] ?? '') == $d['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Section -->
            <div class="form-group">
                <label for="section">Section <span style="color:var(--danger);">*</span></label>
                <select name="section" id="section" class="form-control" required>
                    <?php foreach (['A','B','C','D','E'] as $sec): ?>
                        <option value="<?= $sec ?>" <?= (($old['section'] ?? 'A') === $sec) ? 'selected' : '' ?>><?= $sec ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date -->
            <div class="form-group">
                <label for="date">Date <span style="color:var(--danger);">*</span></label>
                <input type="date" name="date" id="date" class="form-control"
                       value="<?= htmlspecialchars($old['date'] ?? '') ?>" required>
            </div>

            <!-- Time Start -->
            <div class="form-group">
                <label for="time_start">Start Time <span style="color:var(--danger);">*</span></label>
                <input type="time" name="time_start" id="time_start" class="form-control"
                       value="<?= htmlspecialchars($old['time_start'] ?? '') ?>" required>
            </div>

            <!-- Time End -->
            <div class="form-group">
                <label for="time_end">End Time <span style="color:var(--danger);">*</span></label>
                <input type="time" name="time_end" id="time_end" class="form-control"
                       value="<?= htmlspecialchars($old['time_end'] ?? '') ?>" required>
            </div>

            <!-- Buttons -->
            <div style="grid-column:1/3;display:flex;gap:1rem;justify-content:flex-end;margin-top:.5rem;">
                <a href="class-schedule.php" class="btn"
                   style="background:#e2e8f0;color:var(--text-main);text-decoration:none;">Cancel</a>
                <button type="submit" id="btn_save_schedule" class="btn btn-primary" style="width:auto;">
                    <i class="fas fa-save"></i> Save Schedule
                </button>
            </div>
        </form>
    </div>
</main>

<style>
.sms-alert { padding:.9rem 1rem; border-radius:.5rem; margin-bottom:1.25rem; font-size:.875rem; }
.sms-alert-danger { background:#fee2e2; color:#991b1b; }
</style>

<?php require_once '../includes/footer.php'; ?>
