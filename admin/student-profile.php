<?php
// admin/student-profile.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$student_id = intval($_GET['id'] ?? 0); // This is students.id
if ($student_id <= 0) {
    header("Location: students.php?error=" . urlencode("Invalid student ID!"));
    exit();
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// 1. Fetch Student and User Details
$stmt = $pdo->prepare("
    SELECT s.id as student_row_id, s.student_id_no, s.phone, s.dob, s.address, u.full_name, u.email, d.name as dept_name, d.id as dept_id 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    JOIN departments d ON s.dept_id = d.id 
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: students.php?error=" . urlencode("Student not found!"));
    exit();
}

$dept_id = $student['dept_id'];

// --- Post Actions Handle ---

// A. Enroll Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_enroll'])) {
    $course_id = intval($_POST['course_id'] ?? 0);
    if ($course_id > 0) {
        try {
            // Verify not already enrolled
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$student_id, $course_id]);
            if ($stmt->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                $stmt->execute([$student_id, $course_id]);
                header("Location: student-profile.php?id=$student_id&success=" . urlencode("Student enrolled in course successfully!"));
                exit();
            } else {
                $error = "Student is already enrolled in this course.";
            }
        } catch (PDOException $e) {
            $error = "Error enrolling student: " . $e->getMessage();
        }
    } else {
        $error = "Please select a valid course.";
    }
}

// B. Unenroll Course
if (isset($_GET['action_unenroll'])) {
    $enrollment_id = intval($_GET['action_unenroll']);
    if ($enrollment_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ? AND student_id = ?");
            $stmt->execute([$enrollment_id, $student_id]);
            header("Location: student-profile.php?id=$student_id&success=" . urlencode("Student unenrolled from course successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error unenrolling student: " . $e->getMessage();
        }
    }
}

// C. Save Grades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_grade'])) {
    $course_id = intval($_POST['course_id'] ?? 0);
    $marks_raw = trim($_POST['marks'] ?? '');
    $grade_letter = strtoupper(trim($_POST['grade_letter'] ?? ''));

    $marks_val = ($marks_raw !== '') ? floatval($marks_raw) : null;
    $grade_letter_val = ($grade_letter !== '') ? substr($grade_letter, 0, 2) : null;

    if ($course_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$student_id, $course_id]);
            $entry = $stmt->fetch();

            if ($entry) {
                $stmt = $pdo->prepare("UPDATE grades SET marks = ?, grade_letter = ? WHERE id = ?");
                $stmt->execute([$marks_val, $grade_letter_val, $entry['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO grades (student_id, course_id, marks, grade_letter) VALUES (?, ?, ?, ?)");
                $stmt->execute([$student_id, $course_id, $marks_val, $grade_letter_val]);
            }
            header("Location: student-profile.php?id=$student_id&success=" . urlencode("Academic grade saved successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error saving grade: " . $e->getMessage();
        }
    }
}

// D. Save Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_attendance'])) {
    $course_id = intval($_POST['course_id'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $status = trim($_POST['status'] ?? 'present');

    if ($course_id > 0 && !empty($date)) {
        try {
            // Check if entry exists for this course and date
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND course_id = ? AND date = ?");
            $stmt->execute([$student_id, $course_id, $date]);
            $entry = $stmt->fetch();

            if ($entry) {
                $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE id = ?");
                $stmt->execute([$status, $entry['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO attendance (student_id, course_id, status, date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$student_id, $course_id, $status, $date]);
            }
            header("Location: student-profile.php?id=$student_id&success=" . urlencode("Attendance record saved successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error saving attendance: " . $e->getMessage();
        }
    } else {
        $error = "Please specify both course and date.";
    }
}

// --- Fetch Academic Data ---

// 2. Fetch Current Enrolled Courses
$stmt = $pdo->prepare("
    SELECT c.*, e.id as enrollment_id 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.student_id = ? 
    ORDER BY c.name ASC
");
$stmt->execute([$student_id]);
$enrolled_courses = $stmt->fetchAll();

// 3. Fetch Available Courses for Enrollment (not already enrolled)
$stmt = $pdo->prepare("
    SELECT c.*, d.name as dept_name 
    FROM courses c 
    JOIN departments d ON c.dept_id = d.id 
    WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?) 
    ORDER BY c.name ASC
");
$stmt->execute([$student_id]);
$available_courses = $stmt->fetchAll();

// 4. Fetch Attendance History
$stmt = $pdo->prepare("
    SELECT a.id as attendance_id, a.date, a.status, c.name as course_name, c.code 
    FROM attendance a 
    JOIN courses c ON a.course_id = c.id 
    WHERE a.student_id = ? 
    ORDER BY a.date DESC
");
$stmt->execute([$student_id]);
$attendance_logs = $stmt->fetchAll();

// 5. Fetch Grades Record
$stmt = $pdo->prepare("
    SELECT g.id as grade_row_id, g.marks, g.grade_letter, c.id as course_id, c.name as course_name, c.code 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN grades g ON (g.course_id = c.id AND g.student_id = e.student_id)
    WHERE e.student_id = ?
    ORDER BY c.name ASC
");
$stmt->execute([$student_id]);
$student_grades = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Student Academic Profile</h1>
            <p style="color: var(--text-muted);"><a href="students.php" style="color: var(--accent); text-decoration: none;">Students</a> &raquo; Academic Profile</p>
        </div>
        <a href="students.php" class="btn" style="background: #f1f5f9; color: var(--text-main); text-decoration: none; width: auto;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </header>

    <?php if ($success): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Student Header Summary Grid -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0)); border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; border: 1px solid #bae6fd;">
                    <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($student['full_name']) ?></h2>
                    <span style="font-family: monospace; font-size: 0.875rem; color: var(--accent); font-weight: 700;"><?= htmlspecialchars($student['student_id_no']) ?></span>
                </div>
            </div>
            <div style="display: flex; gap: 2rem; font-size: 0.875rem;">
                <div>
                    <span style="color: var(--text-muted); display: block;">Department</span>
                    <strong><?= htmlspecialchars($student['dept_name']) ?></strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block;">Email</span>
                    <strong><?= htmlspecialchars($student['email']) ?></strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block;">Phone</span>
                    <strong><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Triple Module Academic View Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start; margin-bottom: 2rem;">
        
        <!-- Module A: Course Enrollments -->
        <div class="card">
            <div class="card-header" style="background: rgba(2, 132, 199, 0.05);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0284c7;"><i class="fas fa-book-open"></i> Course Enrollments</h3>
            </div>
            <div style="padding: 1.5rem;">
                <!-- New Enrollment Form -->
                <form method="POST" style="margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1.5rem;">
                    <label style="font-weight: 600; font-size: 0.875rem; display: block; margin-bottom: 0.5rem;">Enroll In New Course</label>
                    <div style="display: flex; gap: 0.75rem;">
                        <select name="course_id" class="form-control" required style="flex: 1; margin: 0;">
                            <option value="">-- Select Course --</option>
                            <?php foreach ($available_courses as $ac): ?>
                                <option value="<?= $ac['id'] ?>"><?= htmlspecialchars($ac['name']) ?> (<?= htmlspecialchars($ac['code']) ?>) - [<?= htmlspecialchars($ac['dept_name']) ?>]</option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="action_enroll" class="btn btn-primary" style="width: auto; height: 42px;">
                            Enroll
                        </button>
                    </div>
                </form>

                <!-- Current Enrolled List -->
                <h4 style="font-size: 0.875rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-muted);">Enrolled Classes</h4>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Course Name</th>
                                <th style="width: 80px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($enrolled_courses) > 0): ?>
                                <?php foreach ($enrolled_courses as $ec): ?>
                                <tr>
                                    <td style="font-weight: 600; font-size: 0.85rem; color: var(--accent);"><?= htmlspecialchars($ec['code']) ?></td>
                                    <td style="font-weight: 500; font-size: 0.85rem;"><?= htmlspecialchars($ec['name']) ?></td>
                                    <td>
                                        <div style="display: flex; justify-content: flex-end;">
                                            <a href="student-profile.php?id=<?= $student_id ?>&action_unenroll=<?= $ec['enrollment_id'] ?>" class="btn" style="padding: 0.25rem 0.5rem; background: #fee2e2; color: var(--danger); font-size: 0.75rem;" onclick="return confirm('Unenroll student from this course? This will also delete their grades and attendance records.')" title="Unenroll"><i class="fas fa-times"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">Not enrolled in any courses.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Module B: Grades Management -->
        <div class="card">
            <div class="card-header" style="background: rgba(16, 185, 129, 0.05);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #10b981;"><i class="fas fa-award"></i> Grades Record</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th style="width: 100px; text-align: center;">Score</th>
                                <th style="width: 90px; text-align: center;">Letter</th>
                                <th style="width: 80px; text-align: right;">Save</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($student_grades) > 0): ?>
                                <?php foreach ($student_grades as $sg): ?>
                                <form method="POST">
                                    <input type="hidden" name="course_id" value="<?= $sg['course_id'] ?>">
                                    <tr>
                                        <td>
                                            <strong style="display: block; font-size: 0.85rem;"><?= htmlspecialchars($sg['code']) ?></strong>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($sg['course_name']) ?></span>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" name="marks" class="form-control" value="<?= htmlspecialchars($sg['marks'] ?? '') ?>" placeholder="Marks" style="padding: 0.35rem; font-size: 0.85rem; text-align: center; margin: 0;">
                                        </td>
                                        <td>
                                            <input type="text" maxlength="2" name="grade_letter" class="form-control" value="<?= htmlspecialchars($sg['grade_letter'] ?? '') ?>" placeholder="Letter" style="padding: 0.35rem; font-size: 0.85rem; text-align: center; text-transform: uppercase; margin: 0;">
                                        </td>
                                        <td>
                                            <div style="display: flex; justify-content: flex-end;">
                                                <button type="submit" name="action_save_grade" class="btn" style="padding: 0.4rem 0.6rem; background: #d1fae5; color: #065f46;" title="Save Grade"><i class="fas fa-save"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </form>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">No courses available for grading. Enroll student first.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Module C: Detailed Attendance Log & Creation -->
    <div class="card">
        <div class="card-header" style="background: rgba(245, 158, 11, 0.05);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #f59e0b;"><i class="fas fa-calendar-check"></i> Attendance Record</h3>
        </div>
        <div style="padding: 1.5rem; display: grid; grid-template-columns: 350px 1fr; gap: 2rem;">
            
            <!-- Quick Roll Call Insert Form -->
            <div style="border-right: 1px solid #f1f5f9; padding-right: 2rem;">
                <h4 style="font-size: 0.875rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main);">Add or Modify Attendance Roll</h4>
                <form method="POST">
                    <div class="form-group">
                        <label for="att_course" style="font-weight: 600;">Course Selection</label>
                        <select id="att_course" name="course_id" class="form-control" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($enrolled_courses as $ec): ?>
                                <option value="<?= $ec['id'] ?>"><?= htmlspecialchars($ec['name']) ?> (<?= htmlspecialchars($ec['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="att_date" style="font-weight: 600;">Date Picker</label>
                        <input type="date" id="att_date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Roll Call Status</label>
                        <div style="display: flex; gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                <input type="radio" name="status" value="present" checked style="accent-color: var(--success);"> Present
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                <input type="radio" name="status" value="absent" style="accent-color: var(--danger);"> Absent
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                <input type="radio" name="status" value="late" style="accent-color: var(--warning);"> Late
                            </label>
                        </div>
                    </div>
                    <button type="submit" name="action_save_attendance" class="btn btn-primary" style="margin-top: 1rem;" <?= (count($enrolled_courses) === 0) ? 'disabled' : '' ?>>
                        Save Roll Entry
                    </button>
                </form>
            </div>

            <!-- Attendance History timeline -->
            <div>
                <h4 style="font-size: 0.875rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main);">Historical Attendance Timeline</h4>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Course</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($attendance_logs) > 0): ?>
                                <?php foreach ($attendance_logs as $al): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= date('l, M d, Y', strtotime($al['date'])) ?></td>
                                    <td><?= htmlspecialchars($al['course_name']) ?> (<?= htmlspecialchars($al['code']) ?>)</td>
                                    <td>
                                        <?php if ($al['status'] === 'present'): ?>
                                            <span class="badge" style="background: #d1fae5; color: #065f46;">Present</span>
                                        <?php elseif ($al['status'] === 'late'): ?>
                                            <span class="badge" style="background: #fef3c7; color: #b45309;">Late</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #fee2e2; color: #991b1b;">Absent</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--text-muted);">No attendance recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
