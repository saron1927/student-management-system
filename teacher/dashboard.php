<?php
// teacher/dashboard.php
require_once '../includes/header.php';
checkRole('teacher');
require_once '../includes/sidebar.php';

// Get teacher details
$stmt = $pdo->prepare("SELECT t.*, d.name as dept_name FROM teachers t JOIN departments d ON t.dept_id = d.id WHERE t.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();

// Get assigned courses
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher['id']]);
$courses = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Teacher Dashboard</h1>
            <p style="color: var(--text-muted);">Welcome back, <?= $_SESSION['full_name'] ?></p>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info);">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3>My Courses</h3>
                <p><?= count($courses) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Total Students</h3>
                <p>124</p> <!-- Mock data for now -->
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">My Assigned Courses</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= $course['code'] ?></td>
                        <td><?= $course['name'] ?></td>
                        <td><?= $course['credits'] ?></td>
                        <td>
                            <a href="attendance.php?course_id=<?= $course['id'] ?>" class="btn" style="padding: 0.5rem; background: #f1f5f9; color: var(--accent); font-size: 0.75rem;">
                                Mark Attendance
                            </a>
                            <a href="grades.php?course_id=<?= $course['id'] ?>" class="btn" style="padding: 0.5rem; background: #f1f5f9; color: var(--info); font-size: 0.75rem;">
                                Upload Grades
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
