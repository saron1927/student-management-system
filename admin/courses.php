<?php
// admin/courses.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Handle search query
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $query = "
        SELECT c.*, d.name as dept_name, u.full_name as teacher_name 
        FROM courses c 
        LEFT JOIN departments d ON c.dept_id = d.id 
        LEFT JOIN teachers t ON c.teacher_id = t.id 
        LEFT JOIN users u ON t.user_id = u.id 
        WHERE c.name LIKE ? OR c.code LIKE ? OR d.name LIKE ? OR u.full_name LIKE ?
        ORDER BY c.name ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $courses = $stmt->fetchAll();
} else {
    $query = "
        SELECT c.*, d.name as dept_name, u.full_name as teacher_name 
        FROM courses c 
        LEFT JOIN departments d ON c.dept_id = d.id 
        LEFT JOIN teachers t ON c.teacher_id = t.id 
        LEFT JOIN users u ON t.user_id = u.id 
        ORDER BY c.name ASC
    ";
    $courses = $pdo->query($query)->fetchAll();
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Manage Subjects</h1>
            <p style="color: var(--text-muted);">Configure active subjects, modules, and academic hours</p>
        </div>
        <a href="course-create.php" class="btn btn-primary" style="width: auto; text-decoration: none;">
            <i class="fas fa-plus"></i> Add New Subject
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
                    <input type="text" name="search" class="form-control" placeholder="Search by name, code, class..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 2.5rem;">
                </div>
                <button type="submit" class="btn" style="background: #e2e8f0; color: var(--text-main); font-size: 0.875rem;">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="courses.php" class="btn" style="background: #fee2e2; color: var(--danger); font-size: 0.875rem; text-decoration: none;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Credits</th>
                        <th>Assigned Teacher</th>
                        <th style="width: 150px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($course['code']) ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($course['name']) ?></td>
                            <td>
                                <span class="badge" style="background: #fef3c7; color: #d97706; font-weight: 700;">
                                    <?= htmlspecialchars($course['credits']) ?> Credits
                                </span>
                            </td>
                            <td>
                                <?php if ($course['teacher_name']): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas fa-user-tie" style="color: var(--text-muted);"></i>
                                        <?= htmlspecialchars($course['teacher_name']) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-style: italic;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="course-edit.php?id=<?= $course['id'] ?>" class="btn" style="padding: 0.5rem; background: #e0f2fe; color: var(--accent);"><i class="fas fa-edit"></i></a>
                                    <a href="course-delete.php?id=<?= $course['id'] ?>" class="btn" style="padding: 0.5rem; background: #fee2e2; color: var(--danger);" onclick="return confirm('Are you sure you want to delete this subject?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No subjects found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
