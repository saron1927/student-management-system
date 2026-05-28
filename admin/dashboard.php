<?php
// admin/dashboard.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Fetch stats
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_teachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// Fetch recent students
$recent_students = $pdo->query("
    SELECT s.*, u.full_name 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY u.created_at DESC LIMIT 5
")->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Dashboard Overview</h1>
            <p style="color: var(--text-muted);"><?= date('F j, Y') ?></p>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="text-align: right;">
                <p style="font-weight: 600;"><?= $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User' ?></p>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Administrator</p>
            </div>
            <img src="../assets/images/default.png" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--accent);">
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--accent);">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-info">
                <h3>Total Students</h3>
                <p><?= number_format($total_students) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success);">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-info">
                <h3>Total Teachers</h3>
                <p><?= number_format($total_teachers) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning);">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3>Total Subjects</h3>
                <p><?= number_format($total_courses) ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Recent Student Registrations</h2>
            <button class="btn" style="background: #f1f5f9; font-size: 0.875rem;">View All</button>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Enrollment Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_students as $student): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= $student['student_id_no'] ?></td>
                        <td><?= $student['full_name'] ?></td>
                        <td><?= date('M d, Y') ?></td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="#" style="color: var(--accent);"><i class="fas fa-eye"></i></a>
                                <a href="#" style="color: var(--info);"><i class="fas fa-edit"></i></a>
                                <a href="#" style="color: var(--danger);"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
