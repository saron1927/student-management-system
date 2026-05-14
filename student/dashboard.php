<?php
// student/dashboard.php
require_once '../includes/header.php';
checkRole('student');
require_once '../includes/sidebar.php';

// Get student details
$stmt = $pdo->prepare("SELECT s.*, d.name as dept_name FROM students s JOIN departments d ON s.dept_id = d.id WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

// Get enrolled courses
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as teacher_name 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    LEFT JOIN teachers t ON c.teacher_id = t.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE e.student_id = ?
");
$stmt->execute([$student['id']]);
$courses = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Student Dashboard</h1>
            <p style="color: var(--text-muted);">Welcome, <?= $_SESSION['full_name'] ?></p>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--accent);">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-info">
                <h3>My GPA</h3>
                <p>3.85</p> <!-- Mock data -->
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info);">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-info">
                <h3>Enrolled Courses</h3>
                <p><?= count($courses) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3>Attendance</h3>
                <p>94%</p> <!-- Mock data -->
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">My Enrolled Courses</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Instructor</th>
                        <th>Credits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= $course['code'] ?></td>
                            <td style="font-weight: 600;"><?= $course['name'] ?></td>
                            <td><?= $course['teacher_name'] ?? 'Not Assigned' ?></td>
                            <td><?= $course['credits'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">You are not enrolled in any courses yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
