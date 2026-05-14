<?php
// admin/students.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Fetch all students
$query = "
    SELECT s.*, u.full_name, u.email, d.name as dept_name 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    JOIN departments d ON s.dept_id = d.id 
    ORDER BY u.full_name ASC
";
$students = $pdo->query($query)->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Manage Students</h1>
            <p style="color: var(--text-muted);">View and manage all enrolled students</p>
        </div>
        <button class="btn btn-primary" style="width: auto;">
            <i class="fas fa-plus"></i> Add New Student
        </button>
    </header>

    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 1rem; flex: 1;">
                <div style="position: relative; flex: 1; max-width: 300px;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" class="form-control" placeholder="Search students..." style="padding-left: 2.5rem;">
                </div>
                <select class="form-control" style="width: auto;">
                    <option>All Departments</option>
                    <option>Computer Science</option>
                    <option>Information Technology</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= $student['student_id_no'] ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                                    <?= substr($student['full_name'], 0, 1) ?>
                                </div>
                                <?= $student['full_name'] ?>
                            </div>
                        </td>
                        <td><?= $student['email'] ?></td>
                        <td><?= $student['dept_name'] ?></td>
                        <td><?= $student['phone'] ?? 'N/A' ?></td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="#" class="btn" style="padding: 0.5rem; background: #f1f5f9; color: var(--accent);"><i class="fas fa-edit"></i></a>
                                <a href="delete_student.php?id=<?= $student['id'] ?>" class="btn" style="padding: 0.5rem; background: #fee2e2; color: var(--danger);" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
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
