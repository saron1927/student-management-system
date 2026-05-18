<?php
// teacher/grades.php
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

// Get selected course
$selected_course_id = intval($_GET['course_id'] ?? ($_POST['course_id'] ?? 0));

$students = [];
$existing_grades = [];
$success_msg = "";
$error_msg = "";

// If a course is selected, fetch students and existing grades
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

        // Fetch existing grades for this course
        $stmt = $pdo->prepare("SELECT student_id, marks, grade_letter FROM grades WHERE course_id = ?");
        $stmt->execute([$selected_course_id]);
        $grades_data = $stmt->fetchAll();
        
        // Map to an array indexed by student_id
        foreach ($grades_data as $g) {
            $existing_grades[$g['student_id']] = [
                'marks' => $g['marks'],
                'grade_letter' => $g['grade_letter']
            ];
        }
    } else {
        $error_msg = "Unauthorized course access.";
        $selected_course_id = 0;
    }
}

// Handle Form Submission (Saving Grades)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grades'])) {
    if ($selected_course_id > 0 && !empty($students)) {
        try {
            $pdo->beginTransaction();
            $marks_inputs = $_POST['marks'] ?? [];
            $grade_inputs = $_POST['grade_letter'] ?? [];

            foreach ($students as $student) {
                $sid = $student['student_row_id'];
                $marks_raw = trim($marks_inputs[$sid] ?? '');
                $grade_letter = strtoupper(trim($grade_inputs[$sid] ?? ''));

                $marks_val = ($marks_raw !== '') ? floatval($marks_raw) : null;
                $grade_letter_val = ($grade_letter !== '') ? substr($grade_letter, 0, 2) : null;

                // Check if grade entry already exists
                $stmt = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
                $stmt->execute([$sid, $selected_course_id]);
                $entry = $stmt->fetch();

                if ($entry) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE grades SET marks = ?, grade_letter = ? WHERE id = ?");
                    $stmt->execute([$marks_val, $grade_letter_val, $entry['id']]);
                } else {
                    // Insert (only if at least one value is entered)
                    if ($marks_raw !== '' || $grade_letter !== '') {
                        $stmt = $pdo->prepare("INSERT INTO grades (student_id, course_id, marks, grade_letter) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$sid, $selected_course_id, $marks_val, $grade_letter_val]);
                    }
                }
                
                // Update local mapped state for view
                $existing_grades[$sid] = [
                    'marks' => $marks_val,
                    'grade_letter' => $grade_letter_val
                ];
            }

            $pdo->commit();
            $success_msg = "Academic marks and grades saved successfully!";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_msg = "Database error saving grades: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Student Grades</h1>
            <p style="color: var(--text-muted);">Manage student performance scores and letter grades</p>
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

    <!-- Course Selection Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Select Course to Grade</h2>
        </div>
        <div style="padding: 1.5rem;">
            <form method="GET" style="display: grid; grid-template-columns: 1fr 120px; gap: 1rem; align-items: flex-end;">
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
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px;">
                    Load List
                </button>
            </form>
        </div>
    </div>

    <!-- Grades Entry List -->
    <?php if ($selected_course_id > 0): ?>
        <div class="card">
            <div class="card-header" style="flex-wrap: wrap; gap: 0.5rem;">
                <h2 style="font-size: 1.125rem;">Academic Performance sheet</h2>
                <span style="font-weight: 600; font-size: 0.875rem; color: var(--text-muted);">
                    Course: <?= htmlspecialchars($course_verify['name']) ?> (<?= htmlspecialchars($course_verify['code']) ?>)
                </span>
            </div>
            
            <?php if (count($students) > 0): ?>
                <form method="POST">
                    <input type="hidden" name="course_id" value="<?= $selected_course_id ?>">
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Full Name</th>
                                    <th style="width: 160px; text-align: center;">Numeric Marks (0-100)</th>
                                    <th style="width: 140px; text-align: center;">Letter Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <?php 
                                    $sid = $student['student_row_id'];
                                    $student_marks = $existing_grades[$sid]['marks'] ?? '';
                                    $student_grade = $existing_grades[$sid]['grade_letter'] ?? '';
                                ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($student['student_id_no']) ?></td>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" name="marks[<?= $sid ?>]" class="form-control" value="<?= htmlspecialchars($student_marks) ?>" placeholder="e.g. 92.50" style="text-align: center; font-weight: 600;">
                                    </td>
                                    <td>
                                        <input type="text" maxlength="2" name="grade_letter[<?= $sid ?>]" class="form-control" value="<?= htmlspecialchars($student_grade) ?>" placeholder="e.g. A+" style="text-align: center; text-transform: uppercase; font-weight: 600;">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="padding: 1.5rem; display: flex; justify-content: flex-end;">
                        <button type="submit" name="save_grades" class="btn btn-primary" style="width: auto;">
                            <i class="fas fa-save"></i> Save Grades & Marks
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
            <i class="fas fa-award fa-3x" style="color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3>Please Select a Course Above</h3>
            <p>Select one of your assigned courses and click "Load List" to upload academic grades and remarks.</p>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>
