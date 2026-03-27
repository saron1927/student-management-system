<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">🎓 Student App</a>
            
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="list_students.php">View List</a></li>
                <li><a href="create_student.php">Add Student</a></li>
                
                <?php if (isset($_SESSION['username'])): ?>
                    <li style="margin-left: 20px; color: #bdc3c7; font-size: 0.9rem;">
                        Hi, <?= htmlspecialchars($_SESSION['username']) ?>
                    </li>
                    <li>
                        <a href="logout.php" style="color: #ff7675; font-weight: bold;">Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>