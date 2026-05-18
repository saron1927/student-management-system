<?php
// teacher/attendance.php
require_once '../includes/header.php';
checkRole('teacher');
require_once '../includes/sidebar.php';

// Get teacher details
$stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();

if (!$teacher) {
    die("Teacher profile not found.");
}

// Fetch assigned courses for the dropdown selection
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? ORDER BY name ASC");
$stmt->execute([$teacher['id']]);
$my_courses = $stmt->fetchAll();

// Get selected course and date
$selected_course_id = intval($_GET['course_id'] ?? ($_POST['course_id'] ?? 0));
$selected_date = $_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d'));

$students = [];
$existing_attendance = [];
$success_msg = "";
$error_msg = "";

// If a course is selected, fetch students and existing attendance
if ($selected_course_id > 0) {
    // Verify course is assigned to this teacher
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$selected_course_id, $teacher['id']]);
    $course_verify = $stmt->fetch();

    if ($course_verify) {
        // Fetch all enrolled students
        $query = "
            SELECT s.id as student_row_id, s.student_id_no, u.full_name 
            FROM enrollments e 
            JOIN students s ON e.student_id = s.id 
            JOIN users u ON s.user_id = u.id 
            WHERE e.course_id = ? 
            ORDER BY u.full_name ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$selected_course_id]);
        $students = $stmt->fetchAll();

        // Fetch existing attendance for this course and date
        $stmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE course_id = ? AND date = ?");
        $stmt->execute([$selected_course_id, $selected_date]);
        $attendance_data = $stmt->fetchAll();
        
        // Map to an array indexed by student_id
        foreach ($attendance_data as $att) {
            $existing_attendance[$att['student_id']] = $att['status'];
        }
    } else {
        $error_msg = "Unauthorized course access.";
        $selected_course_id = 0;
    }
}

// Handle Form Submission (Saving Attendance)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    if ($selected_course_id > 0 && !empty($students)) {
        try {
            $pdo->beginTransaction();
            $status_data = $_POST['status'] ?? [];

            foreach ($students as $student) {
                $sid = $student['student_row_id'];
                $status = $status_data[$sid] ?? 'present'; // Default to present if not specified

                // Check if attendance entry already exists
                $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND course_id = ? AND date = ?");
                $stmt->execute([$sid, $selected_course_id, $selected_date]);
                $entry = $stmt->fetch();

                if ($entry) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $entry['id']]);
                } else {
                    // Insert
                    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, course_id, status, date) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$sid, $selected_course_id, $status, $selected_date]);
                }
                
                // Update local mapped state for view
                $existing_attendance[$sid] = $status;
            }

            $pdo->commit();
            $success_msg = "Attendance saved successfully for date " . date('M d, Y', strtotime($selected_date)) . "!";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_msg = "Database error saving attendance: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Student Attendance</h1>
            <p style="color: var(--text-muted);">Manage daily roll call and attendance logs</p>
        </div>
    </header>

    <?php if ($success_msg): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <!-- Course & Date Selection Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Select Course & Date</h2>
        </div>
        <div style="padding: 1.5rem;">
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 100px; gap: 1rem; align-items: flex-end;">
                <div class="form-group" style="margin: 0;">
                    <label for="course_id" style="font-weight: 600;">Course</label>
                    <select id="course_id" name="course_id" class="form-control" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach ($my_courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($selected_course_id == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="date" style="font-weight: 600;">Date</label>
                    <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" required max="<?= date('Y-m-d') ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px;">
                    Load
                </button>
            </form>
        </div>
    </div>

    <!-- Attendance Entry Grid -->
    <?php if ($selected_course_id > 0): ?>
        <div class="card">
            <div class="card-header" style="flex-wrap: wrap; gap: 0.5rem;">
                <h2 style="font-size: 1.125rem;">Roll Call List</h2>
                <span style="font-weight: 600; font-size: 0.875rem; color: var(--text-muted);">
                    Date: <?= date('F j, Y', strtotime($selected_date)) ?>
                </span>
            </div>
            
            <?php if (count($students) > 0): ?>
                <form method="POST">
                    <input type="hidden" name="course_id" value="<?= $selected_course_id ?>">
                    <input type="hidden" name="date" value="<?= $selected_date ?>">
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Full Name</th>
                                    <th style="width: 350px; text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <?php 
                                    $sid = $student['student_row_id'];
                                    $status = $existing_attendance[$sid] ?? 'present'; // Default to present
                                ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($student['student_id_no']) ?></td>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td>
                                        <div style="display: flex; justify-content: center; gap: 1.5rem;">
                                            <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-weight: 500;">
                                                <input type="radio" name="status[<?= $sid ?>]" value="present" <?= ($status === 'present') ? 'checked' : '' ?> style="accent-color: var(--success); width: 1.1rem; height: 1.1rem;"> 
                                                <span style="color: #065f46;">Present</span>
                                            </label>
                                            <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-weight: 500;">
                                                <input type="radio" name="status[<?= $sid ?>]" value="absent" <?= ($status === 'absent') ? 'checked' : '' ?> style="accent-color: var(--danger); width: 1.1rem; height: 1.1rem;"> 
                                                <span style="color: #991b1b;">Absent</span>
                                            </label>
                                            <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-weight: 500;">
                                                <input type="radio" name="status[<?= $sid ?>]" value="late" <?= ($status === 'late') ? 'checked' : '' ?> style="accent-color: var(--warning); width: 1.1rem; height: 1.1rem;"> 
                                                <span style="color: #b45309;">Late</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="padding: 1.5rem; display: flex; justify-content: flex-end;">
                        <button type="submit" name="save_attendance" class="btn btn-primary" style="width: auto;">
                            <i class="fas fa-save"></i> Save Attendance Records
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div style="padding: 2.5rem; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-user-minus fa-2x" style="margin-bottom: 0.75rem; color: #cbd5e1;"></i>
                    <p>No students are currently enrolled in this course.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card" style="padding: 3rem; text-align: center; color: var(--text-muted);">
            <i class="fas fa-chalkboard-teacher fa-3x" style="color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3>Please Select a Course & Date Above</h3>
            <p>Select one of your assigned courses and click "Load" to manage its student attendance sheet.</p>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>
