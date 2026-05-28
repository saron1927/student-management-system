<?php
// admin/students.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Handle search query
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $query = "
        SELECT s.*, u.full_name, u.email, d.name as dept_name 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        JOIN departments d ON s.dept_id = d.id 
        WHERE u.full_name LIKE ? OR u.username LIKE ? OR s.student_id_no LIKE ? OR d.name LIKE ?
        ORDER BY u.full_name ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $students = $stmt->fetchAll();
} else {
    $query = "
        SELECT s.*, u.full_name, u.email, d.name as dept_name 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        JOIN departments d ON s.dept_id = d.id 
        ORDER BY u.full_name ASC
    ";
    $students = $pdo->query($query)->fetchAll();
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Manage Students</h1>
            <p style="color: var(--text-muted);">View and manage all enrolled students</p>
        </div>
        <a href="student-create.php" class="btn btn-primary" style="width: auto; text-decoration: none;">
            <i class="fas fa-plus"></i> Add New Student
        </a>
    </header>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <form method="GET" style="display: flex; gap: 1rem; flex: 1; max-width: 400px;">
                <div style="position: relative; flex: 1;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search students..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 2.5rem;">
                </div>
                <button type="submit" class="btn" style="background: #e2e8f0; color: var(--text-main); font-size: 0.875rem;">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="students.php" class="btn" style="background: #fee2e2; color: var(--danger); font-size: 0.875rem; text-decoration: none;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date of Birth</th>
                        <th>Status</th>
                        <th style="width: 150px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($student['student_id_no']) ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; border: 1px solid #bae6fd;">
                                        <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                                    </div>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($student['full_name']) ?></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></td>
                            <td><?= $student['dob'] ? date('M d, Y', strtotime($student['dob'])) : 'N/A' ?></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="student-profile.php?id=<?= $student['id'] ?>" class="btn" style="padding: 0.5rem; background: #d1fae5; color: #065f46;" title="Academic Record & Enrollments"><i class="fas fa-graduation-cap"></i></a>
                                    <a href="student-edit.php?id=<?= $student['id'] ?>" class="btn" style="padding: 0.5rem; background: #e0f2fe; color: var(--accent);"><i class="fas fa-edit"></i></a>
                                    <!-- We delete by user_id to cascade delete the students record -->
                                    <a href="student-delete.php?id=<?= $student['user_id'] ?>" class="btn" style="padding: 0.5rem; background: #fee2e2; color: var(--danger);" onclick="return confirm('Are you sure you want to delete this student? This will also remove their user account, grades, attendance, and enrollments.')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
