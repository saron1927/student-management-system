<?php
// admin/class-schedule.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

// Filters
$search_teacher  = trim($_GET['teacher']  ?? '');
$search_class    = trim($_GET['class']    ?? '');
$search_subject  = trim($_GET['subject']  ?? '');

$where   = [];
$params  = [];

if ($search_teacher !== '') {
    $where[]  = 'u.full_name LIKE ?';
    $params[] = "%$search_teacher%";
}
if ($search_class !== '') {
    $where[]  = 'd.name LIKE ?';
    $params[] = "%$search_class%";
}
if ($search_subject !== '') {
    $where[]  = 'c.name LIKE ?';
    $params[] = "%$search_subject%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT
        cs.id,
        cs.section,
        cs.date,
        cs.time_start,
        cs.time_end,
        u.full_name   AS teacher_name,
        u.email       AS teacher_email,
        u.profile_pic,
        t.phone       AS teacher_phone,
        t.qualification,
        c.name        AS subject_name,
        d.name        AS class_name,
        -- derive gender from qualification prefix (simple heuristic; real projects store gender)
        CASE WHEN u.full_name LIKE 'Mrs.%' OR u.full_name LIKE 'Ms.%' THEN 'Female'
             WHEN u.full_name LIKE 'Mr.%'  THEN 'Male'
             ELSE 'N/A' END AS gender
    FROM class_schedule cs
    JOIN teachers    t  ON cs.teacher_id = t.id
    JOIN users       u  ON t.user_id     = u.id
    JOIN courses     c  ON cs.course_id  = c.id
    JOIN departments d  ON cs.dept_id    = d.id
    $whereSQL
    ORDER BY cs.date DESC, cs.time_start ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedules = $stmt->fetchAll();
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;">All Class Schedule</h1>
            <p style="color:var(--text-muted);">View and manage all class timetable entries</p>
        </div>
        <a href="class-schedule-create.php" class="btn btn-primary" style="width:auto;text-decoration:none;">
            <i class="fas fa-plus"></i> Add New Schedule
        </a>
    </header>

    <!-- Flash messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="sms-alert sms-alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="sms-alert sms-alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <!-- Filter bar -->
        <div class="card-header" style="flex-wrap:wrap;gap:1rem;align-items:flex-end;">
            <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;flex:1;">
                <div style="position:relative;min-width:160px;flex:1;">
                    <i class="fas fa-user-tie" style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;"></i>
                    <input type="text" name="teacher" id="filter_teacher" class="form-control"
                           placeholder="# Teacher name..."
                           value="<?= htmlspecialchars($search_teacher) ?>"
                           style="padding-left:2.2rem;font-size:.85rem;">
                </div>
                <div style="position:relative;min-width:140px;flex:1;">
                    <i class="fas fa-school" style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;"></i>
                    <input type="text" name="class" id="filter_class" class="form-control"
                           placeholder="Type Class..."
                           value="<?= htmlspecialchars($search_class) ?>"
                           style="padding-left:2.2rem;font-size:.85rem;">
                </div>
                <div style="position:relative;min-width:140px;flex:1;">
                    <i class="fas fa-book" style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;"></i>
                    <input type="text" name="subject" id="filter_subject" class="form-control"
                           placeholder="Subject..."
                           value="<?= htmlspecialchars($search_subject) ?>"
                           style="padding-left:2.2rem;font-size:.85rem;">
                </div>
                <button type="submit" id="btn_search_schedule" class="btn btn-primary" style="width:auto;padding:.65rem 1.25rem;font-size:.85rem;">
                    <i class="fas fa-search"></i> SEARCH
                </button>
                <?php if ($search_teacher || $search_class || $search_subject): ?>
                    <a href="class-schedule.php" class="btn" id="btn_clear_schedule"
                       style="background:#fee2e2;color:var(--danger);font-size:.85rem;text-decoration:none;padding:.65rem 1rem;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table id="scheduleTable">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="chk_all" title="Select all"></th>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Teacher Name</th>
                        <th>Gender</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Mobile No</th>
                        <th>E-mail</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($schedules) > 0): ?>
                        <?php foreach ($schedules as $i => $row): ?>
                        <tr>
                            <td><input type="checkbox" class="row-chk" name="selected[]" value="<?= $row['id'] ?>"></td>
                            <td style="font-weight:600;color:var(--accent);">#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <?php
                                    $pic = $row['profile_pic'] ?? 'default.png';
                                    $picPath = '../uploads/' . $pic;
                                    $initials = strtoupper(substr($row['teacher_name'], 0, 2));
                                    $colors = ['#820F36','#0284c7','#059669','#d97706','#7c3aed','#db2777'];
                                    $color  = $colors[$row['id'] % count($colors)];
                                ?>
                                <?php if (file_exists($picPath) && $pic !== 'default.png'): ?>
                                    <img src="<?= htmlspecialchars($picPath) ?>" alt="Photo"
                                         style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                                <?php else: ?>
                                    <div style="width:34px;height:34px;border-radius:50%;background:<?= $color ?>;color:#fff;
                                                display:flex;align-items:center;justify-content:center;
                                                font-size:.7rem;font-weight:700;border:2px solid rgba(0,0,0,.08);">
                                        <?= $initials ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:600;"><?= htmlspecialchars($row['teacher_name']) ?></td>
                            <td>
                                <?php $g = $row['gender']; ?>
                                <span class="badge" style="background:<?= $g==='Female'?'#fce7f3':'#e0f2fe' ?>;
                                      color:<?= $g==='Female'?'#be185d':'#0369a1' ?>;">
                                    <?= htmlspecialchars($g) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td>
                                <span class="badge" style="background:#f0fdf4;color:#166534;font-weight:700;">
                                    <?= htmlspecialchars($row['section']) ?>
                                </span>
                            </td>
                            <td><?= date('m/d/Y', strtotime($row['date'])) ?></td>
                            <td style="white-space:nowrap;font-size:.8rem;">
                                <?= date('h:i a', strtotime($row['time_start'])) ?> –
                                <?= date('h:i a', strtotime($row['time_end'])) ?>
                            </td>
                            <td style="font-size:.8rem;"><?= htmlspecialchars($row['teacher_phone'] ?? 'N/A') ?></td>
                            <td style="font-size:.8rem;"><?= htmlspecialchars($row['teacher_email']) ?></td>
                            <td>
                                <div style="display:flex;gap:.35rem;justify-content:flex-end;">
                                    <a href="class-schedule-view.php?id=<?= $row['id'] ?>"
                                       class="btn sched-action-btn" title="View"
                                       style="background:#e0f2fe;color:#0369a1;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="class-schedule-edit.php?id=<?= $row['id'] ?>"
                                       class="btn sched-action-btn" title="Edit"
                                       style="background:#fef9c3;color:#a16207;">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="class-schedule-delete.php?id=<?= $row['id'] ?>"
                                       class="btn sched-action-btn" title="Delete"
                                       style="background:#fee2e2;color:var(--danger);"
                                       onclick="return confirm('Delete this schedule entry?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" style="text-align:center;color:var(--text-muted);padding:2.5rem;">
                                <i class="fas fa-calendar-times" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                                No class schedule entries found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer row count -->
        <div style="padding:.75rem 1.5rem;font-size:.8rem;color:var(--text-muted);border-top:1px solid #e2e8f0;">
            Showing <strong><?= count($schedules) ?></strong> schedule entr<?= count($schedules) === 1 ? 'y' : 'ies' ?>.
        </div>
    </div>
</main>

<style>
.sched-action-btn { padding:.4rem .55rem; font-size:.8rem; }
.sms-alert { padding:.9rem 1rem; border-radius:.5rem; margin-bottom:1.25rem; font-size:.875rem; }
.sms-alert-success { background:#d1fae5; color:#065f46; }
.sms-alert-danger  { background:#fee2e2; color:#991b1b; }
</style>

<script>
// Select-all checkbox
document.getElementById('chk_all').addEventListener('change', function () {
    document.querySelectorAll('.row-chk').forEach(cb => cb.checked = this.checked);
});
</script>

<?php require_once '../includes/footer.php'; ?>
