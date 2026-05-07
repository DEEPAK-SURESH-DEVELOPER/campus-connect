<?php
include("../Includes/StudentHeader.php");
date_default_timezone_set('Asia/Kolkata');
if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}

include("../Includes/StudentSidebar.php");
$student_id = $_SESSION['student_id'];

/* --- Get student's class, course, semester, and department --- */
$studentQry = "
    SELECT c.class_id, c.semester_id, co.course_id, co.department_id
    FROM tbl_student s
    INNER JOIN tbl_class c ON s.class_id = c.class_id
    INNER JOIN tbl_course co ON c.course_id = co.course_id
    WHERE s.student_id = '$student_id'
";
$studentRes = $con->query($studentQry);
if ($studentRes->num_rows == 0) {
    echo "<p style='padding:30px;font-family:Arial;'>Student class details not found.</p>";
    exit;
}
$studentRow = $studentRes->fetch_assoc();
$class_id = $studentRow['class_id'];
$semester_id = $studentRow['semester_id'];
$department_id = $studentRow['department_id'];

/* --- Get department periods --- */
$periods = [];
$pRes = $con->query("SELECT * FROM tbl_departmentperiods WHERE department_id='$department_id' ORDER BY period_no");
while ($pRow = $pRes->fetch_assoc()) {
    $periods[] = $pRow;
}

/* --- Fetch timetable data --- */
$timetable = [];
$ttQry = "
    SELECT tt.weekday, dp.period_no, dp.is_break, s.subject_name, t.teacher_name
    FROM tbl_timetable tt
    INNER JOIN tbl_departmentperiods dp ON tt.period_id = dp.period_id
    LEFT JOIN tbl_subject s ON tt.subject_id = s.subject_id
    LEFT JOIN tbl_teacher t ON tt.teacher_id = t.teacher_id
    WHERE tt.class_id='$class_id' AND tt.semester_id='$semester_id'
";
$ttRes = $con->query($ttQry);

// Normalize weekdays and build structured timetable
while ($row = $ttRes->fetch_assoc()) {
    $weekday = ucfirst(strtolower(trim($row['weekday'])));
    if (is_numeric($weekday)) {
        $map = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
        $weekday = $map[(int)$weekday] ?? 'Monday';
    }

    $pno = (int)$row['period_no'];

    $timetable[$weekday][$pno] = [
        'subject' => $row['subject_name'] ?? '',
        'teacher' => $row['teacher_name'] ?? '',
        'is_break' => (int)$row['is_break']
    ];
}

/* --- Days of week --- */
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$today = date('l');

/* --- Universal Theme Page Setup --- */
$page_title = "My Timetable";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Timetable</span>';

?>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="glass-card fade-in">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-week"></i> Weekly Timetable
            </h3>
        </div>

        <?php if (empty($periods)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-info-circle"></i></div>
                <div class="empty-state-title">No Periods Found</div>
                <div class="empty-state-text">Your department has not configured any class periods yet.</div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="glass-table" id="timetableTable">
                    <thead>
                        <tr>
                            <th>Day / Period</th>
                            <?php foreach ($periods as $p): ?>
                                <th>
                                    <div class="text-gradient">P<?= $p['period_no'] ?></div>
                                    <small><?= date('H:i', strtotime($p['start_time'])) ?> - <?= date('H:i', strtotime($p['end_time'])) ?></small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($days as $day): ?>
                            <tr class="<?= ($day == $today) ? 'highlight-row' : '' ?>">
                                <td style="font-weight:600;"><?= $day ?></td>
                                <?php foreach ($periods as $p):
                                    $pno = $p['period_no'];
                                    if ($p['is_break'] == 1): ?>
                                        <td class="badge badge-info text-center">Break</td>
                                    <?php else:
                                        $cell = $timetable[$day][$pno] ?? null;
                                        if ($cell && $cell['subject']): ?>
                                            <td>
                                                <div class="text-gradient"><?= $cell['subject'] ?></div>
                                                <div style="color: var(--text-muted); font-size: 0.85rem;"><?= $cell['teacher'] ?></div>
                                            </td>
                                        <?php else: ?>
                                            <td style="color: var(--text-muted);">-</td>
                                        <?php endif; ?>
                                    <?php endif;
                                endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- INTERNAL STYLES FOR ENHANCEMENT -->
<style>
.table-responsive {
    overflow-x: auto;
}
.highlight-row {
    background: rgba(102, 126, 234, 0.08);
    box-shadow: inset 0 0 10px rgba(102, 126, 234, 0.3);
}
.glass-table th, .glass-table td {
    text-align: center;
    vertical-align: middle;
    padding: 1rem;
}
.glass-table th {
    font-size: 0.9rem;
    color: var(--text-primary);
    text-transform: uppercase;
}
.glass-table td {
    font-size: 0.95rem;
    transition: all 0.3s ease;
}
.glass-table td:hover {
    background: rgba(102, 126, 234, 0.08);
    transform: scale(1.03);
}
</style>

<!-- UNIVERSAL JS -->
<script src="../Assets/JS/universal.js"></script>

</body>
</html>
