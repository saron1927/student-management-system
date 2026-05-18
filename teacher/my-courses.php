<?php
// teacher/my-courses.php
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

// Fetch assigned courses along with the number of enrolled students
$query = "
    SELECT c.*, COUNT(e.id) as student_count 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    WHERE c.teacher_id = ? 
    GROUP BY c.id 
    ORDER BY c.name ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$teacher['id']]);
$courses = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">My Courses</h1>
            <p style="color: var(--text-muted);">View all classes and courses assigned to you</p>
        </div>
    </header>

    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Assigned Curriculum</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Enrolled Students</th>
                        <th style="width: 300px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($course['code']) ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($course['name']) ?></td>
                            <td>
                                <span class="badge" style="background: #fef3c7; color: #d97706;">
                                    <?= htmlspecialchars($course['credits']) ?> Credits
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                                    <i class="fas fa-users" style="color: var(--text-muted);"></i>
                                    <?= htmlspecialchars($course['student_count']) ?> Students
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="attendance.php?course_id=<?= $course['id'] ?>" class="btn" style="padding: 0.5rem 1rem; background: #e0f2fe; color: var(--accent); font-size: 0.8rem; text-decoration: none;">
                                        <i class="fas fa-calendar-check"></i> Attendance
                                    </a>
                                    <a href="grades.php?course_id=<?= $course['id'] ?>" class="btn" style="padding: 0.5rem 1rem; background: #d1fae5; color: #065f46; font-size: 0.8rem; text-decoration: none;">
                                        <i class="fas fa-star"></i> Grades
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">You are not currently assigned to any courses.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
