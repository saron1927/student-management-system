<?php
// admin/class-schedule-view.php
require_once '../includes/header.php';
checkRole('admin');
require_once '../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: class-schedule.php?error=' . urlencode('Invalid schedule ID.'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        cs.id, cs.section, cs.date, cs.time_start, cs.time_end, cs.created_at,
        u.full_name  AS teacher_name,
        u.email      AS teacher_email,
        u.profile_pic,
        t.phone      AS teacher_phone,
        t.qualification,
        c.name       AS subject_name,
        c.code       AS subject_code,
        c.credits,
        d.name       AS class_name,
        d.code       AS class_code
    FROM class_schedule cs
    JOIN teachers    t  ON cs.teacher_id = t.id
    JOIN users       u  ON t.user_id     = u.id
    JOIN courses     c  ON cs.course_id  = c.id
    JOIN departments d  ON cs.dept_id    = d.id
    WHERE cs.id = ?
");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: class-schedule.php?error=' . urlencode('Schedule entry not found.'));
    exit;
}

$colors   = ['#820F36','#0284c7','#059669','#d97706','#7c3aed','#db2777'];
$color    = $colors[$row['id'] % count($colors)];
$initials = strtoupper(substr($row['teacher_name'], 0, 2));
$picPath  = '../uploads/' . ($row['profile_pic'] ?? 'default.png');
?>

<main class="main-content">
    <header class="top-nav">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;">Schedule Detail</h1>
            <p style="color:var(--text-muted);">Entry #<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></p>
        </div>
        <div style="display:flex;gap:.75rem;">
            <a href="class-schedule-edit.php?id=<?= $id ?>" class="btn btn-primary" style="width:auto;text-decoration:none;">
                <i class="fas fa-pen"></i> Edit
            </a>
            <a href="class-schedule.php" class="btn" style="width:auto;text-decoration:none;background:#e2e8f0;color:var(--text-main);">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </header>

    <div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start;">

        <!-- Teacher card -->
        <div class="card" style="padding:2rem;text-align:center;">
            <?php if (file_exists($picPath) && ($row['profile_pic'] ?? 'default.png') !== 'default.png'): ?>
                <img src="<?= htmlspecialchars($picPath) ?>" alt="Photo"
                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--accent);margin:0 auto 1rem;">
            <?php else: ?>
                <div style="width:90px;height:90px;border-radius:50%;background:<?= $color ?>;color:#fff;
                            display:flex;align-items:center;justify-content:center;
                            font-size:1.75rem;font-weight:700;margin:0 auto 1rem;
                            border:3px solid rgba(0,0,0,.08);">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.25rem;"><?= htmlspecialchars($row['teacher_name']) ?></h2>
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;"><?= htmlspecialchars($row['qualification'] ?? 'N/A') ?></p>
            <div style="display:flex;flex-direction:column;gap:.6rem;text-align:left;font-size:.85rem;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <i class="fas fa-envelope" style="color:var(--accent);width:16px;"></i>
                    <span style="word-break:break-all;"><?= htmlspecialchars($row['teacher_email']) ?></span>
                </div>
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <i class="fas fa-phone" style="color:var(--accent);width:16px;"></i>
                    <span><?= htmlspecialchars($row['teacher_phone'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>

        <!-- Details grid -->
        <div class="card">
            <div class="card-header">
                <span style="font-weight:600;"><i class="fas fa-info-circle" style="margin-right:.5rem;color:var(--accent);"></i>Schedule Information</span>
            </div>
            <div style="padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

                <?php
                $detail = function(string $label, string $icon, string $value, string $bg = '#f8f5f4') {
                    echo "
                    <div style='background:$bg;border-radius:.75rem;padding:1rem 1.25rem;'>
                        <div style='font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.35rem;'>
                            <i class='fas fa-$icon' style='margin-right:.35rem;'></i>$label
                        </div>
                        <div style='font-weight:600;font-size:.95rem;'>$value</div>
                    </div>";
                };
                ?>

                <?php $detail('Subject', 'book', htmlspecialchars($row['subject_name']) . ' <span style="font-size:.8rem;opacity:.6;">(' . htmlspecialchars($row['subject_code']) . ')</span>') ?>
                <?php $detail('Credits', 'star', htmlspecialchars($row['credits']) . ' Credits') ?>
                <?php $detail('Class', 'school', htmlspecialchars($row['class_name'])) ?>
                <?php $detail('Section', 'layer-group', '<span style="background:#f0fdf4;color:#166534;padding:.2rem .65rem;border-radius:9999px;font-size:.85rem;">' . htmlspecialchars($row['section']) . '</span>') ?>
                <?php $detail('Date', 'calendar-day', date('l, F j, Y', strtotime($row['date']))) ?>
                <?php $detail('Time', 'clock', date('h:i a', strtotime($row['time_start'])) . ' &ndash; ' . date('h:i a', strtotime($row['time_end']))) ?>

                <div style="grid-column:1/3;background:#f8f5f4;border-radius:.75rem;padding:1rem 1.25rem;">
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.35rem;">
                        <i class="fas fa-clock" style="margin-right:.35rem;"></i>Duration
                    </div>
                    <?php
                        $s = new DateTime($row['time_start']);
                        $e = new DateTime($row['time_end']);
                        $diff = $s->diff($e);
                        $dur  = ($diff->h ? $diff->h . 'h ' : '') . ($diff->i ? $diff->i . 'min' : '');
                    ?>
                    <div style="font-weight:600;font-size:.95rem;"><?= $dur ?: '0min' ?></div>
                </div>

                <div style="grid-column:1/3;background:#f8f5f4;border-radius:.75rem;padding:1rem 1.25rem;">
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.35rem;">
                        <i class="fas fa-calendar-plus" style="margin-right:.35rem;"></i>Created At
                    </div>
                    <div style="font-weight:600;font-size:.95rem;"><?= date('M j, Y – h:i a', strtotime($row['created_at'])) ?></div>
                </div>
            </div>

            <div style="padding:1rem 1.5rem;border-top:1px solid #e2e8f0;display:flex;gap:.75rem;justify-content:flex-end;">
                <a href="class-schedule-edit.php?id=<?= $id ?>"
                   class="btn" style="background:#fef9c3;color:#a16207;width:auto;text-decoration:none;">
                    <i class="fas fa-pen"></i> Edit Entry
                </a>
                <a href="class-schedule-delete.php?id=<?= $id ?>"
                   class="btn" style="background:#fee2e2;color:var(--danger);width:auto;text-decoration:none;"
                   onclick="return confirm('Are you sure you want to delete this schedule entry?')">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
