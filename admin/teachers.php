<?php
// admin/teachers.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Handle search query
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $query = "
        SELECT t.id as teacher_row_id, t.qualification, t.phone, u.*, d.name as dept_name 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN departments d ON t.dept_id = d.id 
        WHERE u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR d.name LIKE ?
        ORDER BY u.full_name ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $teachers = $stmt->fetchAll();
} else {
    $query = "
        SELECT t.id as teacher_row_id, t.qualification, t.phone, u.*, d.name as dept_name 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN departments d ON t.dept_id = d.id 
        ORDER BY u.full_name ASC
    ";
    $teachers = $pdo->query($query)->fetchAll();
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Manage Teachers</h1>
            <p style="color: var(--text-muted);">View and manage your academic staff and instructors</p>
        </div>
        <a href="teacher-create.php" class="btn btn-primary" style="width: auto; text-decoration: none;">
            <i class="fas fa-plus"></i> Add New Teacher
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
                    <input type="text" name="search" class="form-control" placeholder="Search teachers..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 2.5rem;">
                </div>
                <button type="submit" class="btn" style="background: #e2e8f0; color: var(--text-main); font-size: 0.875rem;">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="teachers.php" class="btn" style="background: #fee2e2; color: var(--danger); font-size: 0.875rem; text-decoration: none;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Class and Section</th>
                        <th>Qualification</th>
                        <th>Phone</th>
                        <th style="width: 150px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($teachers) > 0): ?>
                        <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; border: 1px solid #bae6fd;">
                                        <?= strtoupper(substr($teacher['full_name'], 0, 1)) ?>
                                    </div>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($teacher['full_name']) ?></div>
                                </div>
                            </td>
                            <td style="font-family: monospace; font-weight: 600;"><?= htmlspecialchars($teacher['username']) ?></td>
                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                            <td><?= htmlspecialchars($teacher['dept_name'] ?? 'Unassigned') ?></td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($teacher['qualification'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($teacher['phone'] ?? 'N/A') ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="teacher-edit.php?id=<?= $teacher['teacher_row_id'] ?>" class="btn" style="padding: 0.5rem; background: #e0f2fe; color: var(--accent);"><i class="fas fa-edit"></i></a>
                                    <!-- We delete the user account to automatically cascade delete the teacher profile record -->
                                    <a href="teacher-delete.php?id=<?= $teacher['id'] ?>" class="btn" style="padding: 0.5rem; background: #fee2e2; color: var(--danger);" onclick="return confirm('Are you sure you want to delete this teacher? This will also remove their user account, courses link, and attendance/grade entries.')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No teachers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
