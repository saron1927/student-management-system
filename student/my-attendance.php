<?php
// student/my-attendance.php
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

$sid = $student['id'];

// 1. Fetch attendance summary per course
$summary_query = "
    SELECT c.code, c.name as course_name, 
      COUNT(a.id) as total_days, 
      SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days, 
      SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days, 
      SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    LEFT JOIN attendance a ON (a.course_id = c.id AND a.student_id = e.student_id) 
    WHERE e.student_id = ? 
    GROUP BY c.id 
    ORDER BY c.name ASC
";
$stmt = $pdo->prepare($summary_query);
$stmt->execute([$sid]);
$summaries = $stmt->fetchAll();

// 2. Fetch detailed chronological attendance log
$log_query = "
    SELECT a.date, c.code, c.name as course_name, a.status 
    FROM attendance a 
    JOIN courses c ON a.course_id = c.id 
    WHERE a.student_id = ? 
    ORDER BY a.date DESC 
    LIMIT 50
";
$stmt = $pdo->prepare($log_query);
$stmt->execute([$sid]);
$logs = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">My Attendance</h1>
            <p style="color: var(--text-muted);">Track your daily roll call presence and attendance rates</p>
        </div>
    </header>

    <!-- Attendance Summaries Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Course Summaries</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th style="text-align: center;">Total Days</th>
                        <th style="text-align: center; color: #065f46;">Present</th>
                        <th style="text-align: center; color: #b45309;">Late</th>
                        <th style="text-align: center; color: #991b1b;">Absent</th>
                        <th style="text-align: center;">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($summaries) > 0): ?>
                        <?php foreach ($summaries as $sum): ?>
                        <?php 
                            $total = intval($sum['total_days']);
                            $present = intval($sum['present_days']);
                            $late = intval($sum['late_days']);
                            $absent = intval($sum['absent_days']);
                            
                            // Attendance rate counts present and late as attended
                            $attended = $present + $late;
                            $rate = ($total > 0) ? round(($attended / $total) * 100) : 100;
                            
                            // Styling classes for rates
                            $rate_color = "#059669"; // Green
                            if ($rate < 75) {
                                $rate_color = "#dc2626"; // Red
                            } elseif ($rate < 85) {
                                $rate_color = "#d97706"; // Amber
                            }
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($sum['code']) ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($sum['course_name']) ?></td>
                            <td style="text-align: center; font-weight: 600;"><?= $total ?></td>
                            <td style="text-align: center; color: #059669; font-weight: 600;"><?= $present ?></td>
                            <td style="text-align: center; color: #d97706; font-weight: 600;"><?= $late ?></td>
                            <td style="text-align: center; color: #dc2626; font-weight: 600;"><?= $absent ?></td>
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                    <div style="width: 50px; background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden; display: inline-block;">
                                        <div style="background: <?= $rate_color ?>; width: <?= $rate ?>%; height: 100%;"></div>
                                    </div>
                                    <span style="font-weight: 700; color: <?= $rate_color ?>;"><?= $rate ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">You are not enrolled in any courses yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chronological Log Card -->
    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Daily Attendance Log</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= date('l, M d, Y', strtotime($log['date'])) ?></td>
                            <td><?= htmlspecialchars($log['course_name']) ?> (<?= htmlspecialchars($log['code']) ?>)</td>
                            <td>
                                <?php if ($log['status'] === 'present'): ?>
                                    <span class="badge" style="background: #d1fae5; color: #065f46;">Present</span>
                                <?php elseif ($log['status'] === 'late'): ?>
                                    <span class="badge" style="background: #fef3c7; color: #b45309;">Late</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">Absent</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">No attendance entries recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
