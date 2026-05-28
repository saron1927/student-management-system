<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-graduation-cap fa-2x"></i>
        <h2 style="font-size: 1.25rem;">ST THRESA SMS</h2>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="sidebar-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </li>
        
        <?php if ($role == 'admin'): ?>
        <li>
            <a href="students.php" class="sidebar-link <?= ($current_page == 'students.php') ? 'active' : '' ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
        </li>
        <li>
            <a href="teachers.php" class="sidebar-link <?= ($current_page == 'teachers.php') ? 'active' : '' ?>">
                <i class="fas fa-chalkboard-teacher"></i> Teachers
            </a>
        </li>

        <li>
            <a href="courses.php" class="sidebar-link <?= ($current_page == 'courses.php') ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Subjects
            </a>
        </li>
        <li>
            <a href="class-schedule.php" class="sidebar-link <?= in_array($current_page, ['class-schedule.php','class-schedule-create.php','class-schedule-edit.php','class-schedule-view.php']) ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i> Class Schedule
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role == 'teacher'): ?>
        <li>
            <a href="my-courses.php" class="sidebar-link <?= ($current_page == 'my-courses.php') ? 'active' : '' ?>">
                <i class="fas fa-book"></i> My Subjects
            </a>
        </li>
        <li>
            <a href="attendance.php" class="sidebar-link <?= ($current_page == 'attendance.php') ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
        </li>
        <li>
            <a href="grades.php" class="sidebar-link <?= ($current_page == 'grades.php') ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Grades
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role == 'student'): ?>
        <li>
            <a href="enrolled-courses.php" class="sidebar-link <?= ($current_page == 'enrolled-courses.php') ? 'active' : '' ?>">
                <i class="fas fa-book-reader"></i> Enrolled Subjects
            </a>
        </li>
        <li>
            <a href="my-attendance.php" class="sidebar-link <?= ($current_page == 'my-attendance.php') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i> My Attendance
            </a>
        </li>
        <li>
            <a href="my-grades.php" class="sidebar-link <?= ($current_page == 'my-grades.php') ? 'active' : '' ?>">
                <i class="fas fa-poll-h"></i> My Grades
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="../logout.php" class="sidebar-link" style="color: var(--danger);">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</aside>
