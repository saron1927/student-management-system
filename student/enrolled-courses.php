<?php
// student/enrolled-courses.php
require_once '../includes/header.php';
checkRole('student');
require_once '../includes/sidebar.php';

// Get student details
$stmt = $pdo->prepare("SELECT s.*, d.name as dept_name FROM students s JOIN departments d ON s.dept_id = d.id WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    die("Student profile not found.");
}

// Fetch all enrolled courses with instructor details
$query = "
    SELECT c.*, u.full_name as teacher_name, u.email as teacher_email, d.name as dept_name 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    JOIN departments d ON c.dept_id = d.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE e.student_id = ?
    ORDER BY c.name ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$student['id']]);
$courses = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">My Enrolled Courses</h1>
            <p style="color: var(--text-muted);">View active enrollments, course credits, and assigned instructors</p>
        </div>
    </header>

    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Academic Schedule</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Department</th>
                        <th>Credits</th>
                        <th>Assigned Instructor</th>
                        <th style="width: 250px; text-align: right;">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($course['code']) ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($course['name']) ?></td>
                            <td><?= htmlspecialchars($course['dept_name']) ?></td>
                            <td>
                                <span class="badge" style="background: #fef3c7; color: #d97706;">
                                    <?= htmlspecialchars($course['credits']) ?> Credits
                                </span>
                            </td>
                            <td>
                                <?php if ($course['teacher_name']): ?>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($course['teacher_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><i class="far fa-envelope"></i> <?= htmlspecialchars($course['teacher_email']) ?></div>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-style: italic;">Not Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="my-attendance.php" class="btn" style="padding: 0.5rem 1rem; background: #e0f2fe; color: var(--accent); font-size: 0.8rem; text-decoration: none;">
                                        <i class="fas fa-calendar-check"></i> Attendance
                                    </a>
                                    <a href="my-grades.php" class="btn" style="padding: 0.5rem 1rem; background: #d1fae5; color: #065f46; font-size: 0.8rem; text-decoration: none;">
                                        <i class="fas fa-poll-h"></i> Grades
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                                <i class="fas fa-book-reader fa-3x" style="color: #cbd5e1; margin-bottom: 1rem;"></i>
                                <p>You are not currently enrolled in any courses yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
