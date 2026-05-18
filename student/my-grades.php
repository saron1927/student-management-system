<?php
// student/my-grades.php
require_once '../includes/header.php';
checkRole('student');
require_once '../includes/sidebar.php';

// Get student details
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    die("Student profile not found.");
}

// Fetch enrolled courses and grades
$query = "
    SELECT c.code, c.name as course_name, c.credits, g.marks, g.grade_letter 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    LEFT JOIN grades g ON (g.course_id = c.id AND g.student_id = e.student_id) 
    WHERE e.student_id = ? 
    ORDER BY c.name ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$student['id']]);
$grades = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">My Grades</h1>
            <p style="color: var(--text-muted);">View your academic performance reports and course marks</p>
        </div>
    </header>

    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Semester Grade Report</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th style="text-align: center;">Numeric Score (0-100)</th>
                        <th style="text-align: center;">Letter Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($grades) > 0): ?>
                        <?php foreach ($grades as $g): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($g['code']) ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($g['course_name']) ?></td>
                            <td><?= htmlspecialchars($g['credits']) ?></td>
                            <td style="text-align: center; font-weight: 600;">
                                <?= ($g['marks'] !== null) ? htmlspecialchars($g['marks']) : '<span style="color: var(--text-muted); font-weight: normal; font-style: italic;">—</span>' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($g['grade_letter'] !== null): ?>
                                    <span class="badge" style="background: #d1fae5; color: #065f46; font-weight: 700; font-size: 0.9rem; padding: 0.35rem 0.75rem;">
                                        <?= htmlspecialchars($g['grade_letter']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: #f1f5f9; color: var(--text-muted); font-weight: normal;">
                                        Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">You are not enrolled in any courses yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
