<?php
// admin/department-create.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = strtoupper(trim($_POST['code'] ?? ''));

    if (empty($name) || empty($code)) {
        $error = "All fields are required!";
    } else {
        try {
            // Check if department name or code already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE name = ? OR code = ?");
            $stmt->execute([$name, $code]);
            if ($stmt->fetchColumn() > 0) {
                $error = "A department with this name or code already exists!";
            } else {
                // Insert new department
                $stmt = $pdo->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
                $stmt->execute([$name, $code]);
                header("Location: departments.php?success=" . urlencode("Department created successfully!"));
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error adding department: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Add New Class and Section</h1>
            <p style="color: var(--text-muted);"><a href="departments.php" style="color: var(--accent); text-decoration: none;">Classes and Sections</a> &raquo; Add New</p>
        </div>
        <a href="departments.php" class="btn" style="background: #f1f5f9; color: var(--text-main); text-decoration: none; width: auto;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </header>

    <div style="max-width: 600px; margin: 0 auto;">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 1.125rem;">Class and Section Details</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST">
                    <div class="form-group">
                        <label for="name" style="font-weight: 600; color: var(--text-main);">Class and Section Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Grade 1A" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="code" style="font-weight: 600; color: var(--text-main);">Class and Section Code</label>
                        <input type="text" id="code" name="code" class="form-control" placeholder="e.g. G1A" required maxlength="10">
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Usually a short abbreviation (e.g. G1A, G2B).</p>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-save"></i> Save Class and Section
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
