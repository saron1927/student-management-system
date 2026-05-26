<?php
// admin/departments.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Handle search query
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE name LIKE ? OR code LIKE ? ORDER BY name ASC");
    $stmt->execute(["%$search%", "%$search%"]);
    $departments = $stmt->fetchAll();
} else {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Manage Classes and Sections</h1>
            <p style="color: var(--text-muted);">Configure class grades and sections</p>
        </div>
        <a href="department-create.php" class="btn btn-primary" style="width: auto; text-decoration: none;">
            <i class="fas fa-plus"></i> Add New Class and Section
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
                    <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 2.5rem;">
                </div>
                <button type="submit" class="btn" style="background: #e2e8f0; color: var(--text-main); font-size: 0.875rem;">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="departments.php" class="btn" style="background: #fee2e2; color: var(--danger); font-size: 0.875rem; text-decoration: none;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Class and Section Name</th>
                        <th>Code</th>
                        <th>Created At</th>
                        <th style="width: 150px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($departments) > 0): ?>
                        <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= $dept['id'] ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($dept['name']) ?></td>
                            <td><span class="badge" style="background: #e0f2fe; color: #0369a1;"><?= htmlspecialchars($dept['code']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($dept['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="department-edit.php?id=<?= $dept['id'] ?>" class="btn" style="padding: 0.5rem; background: #e0f2fe; color: var(--accent);"><i class="fas fa-edit"></i></a>
                                    <a href="department-delete.php?id=<?= $dept['id'] ?>" class="btn" style="padding: 0.5rem; background: #fee2e2; color: var(--danger);" onclick="return confirm('Are you sure you want to delete this class and section? Any subjects, teachers or students linked to this class and section may be impacted.')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No classes and sections found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
