<?php
include("../Includes/TeacherHeader.php");

if(!isset($_SESSION['teacher_id'])){
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = $_SESSION['teacher_id'];
$today = date('l');

// Fetch periods of teacher’s department
$qPeriod = "SELECT * FROM tbl_departmentperiods 
            WHERE department_id=(SELECT department_id FROM tbl_teacher WHERE teacher_id='$teacher_id')
            ORDER BY period_no";
$resPeriod = $con->query($qPeriod);

$periods = [];
while($row = $resPeriod->fetch_assoc()){
    $periods[] = $row;
}

// Days
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// Build timetable
$timetable = [];
foreach($days as $day){
    foreach($periods as $p){
        $pid = $p['period_id'];
        $q = "SELECT tt.*, c.class_name, s.subject_name, sem.semester_name
      FROM tbl_timetable tt
      INNER JOIN tbl_class c ON c.class_id = tt.class_id
      INNER JOIN tbl_subject s ON s.subject_id = tt.subject_id
      INNER JOIN tbl_semester sem ON sem.semester_id = c.semester_id
      WHERE tt.teacher_id = '$teacher_id'
        AND tt.weekday = '$day'
        AND tt.period_id = '$pid'
        AND tt.semester_id = c.semester_id
        AND c.is_completed = 0";

        $res = $con->query($q);
        if($res->num_rows > 0){
            $row = $res->fetch_assoc();
            $timetable[$day][$p['period_no']] = [
                'subject' => $row['subject_name'],
                'class' => $row['class_name']
            ];
        }
    }
}

// Page settings
$page_title = "My Weekly Timetable";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Timetable</span>';

?>

<div class="main-content">
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-week"></i> My Weekly Timetable
            </h3>
        </div>

        <?php if(empty($periods)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-info-circle"></i></div>
                <div class="empty-state-title">No Periods Configured</div>
                <div class="empty-state-text">Your department doesn’t have any defined periods yet.</div>
            </div>
        <?php else: ?>
            <div class="glass-card" style="overflow-x:auto;">
                <table class="glass-table" style="min-width:900px;">
                    <thead>
                        <tr>
                            <th>Day / Period</th>
                            <?php foreach($periods as $p): ?>
                                <th>
                                    <div class="text-gradient">P<?= $p['period_no'] ?></div>
                                    <small style="color:var(--text-muted);">
                                        <?= date('h:i A', strtotime($p['start_time'])) ?> - <?= date('h:i A', strtotime($p['end_time'])) ?>
                                    </small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($days as $day): ?>
                            <tr class="<?= ($day == $today) ? 'highlight-row' : '' ?>">
                                <td><strong><?= $day ?></strong></td>
                                <?php foreach($periods as $p): 
                                    $pno = $p['period_no'];
                                    if($p['is_break'] == 1){
                                        echo "<td><span class='badge badge-warning'>Break</span></td>";
                                        continue;
                                    }
                                    $cell = $timetable[$day][$pno] ?? null;
                                    if($cell){
                                        echo "<td>
                                                <div style='font-weight:600; color:var(--text-primary);'>{$cell['subject']}</div>
                                                <div style='color:var(--text-secondary); font-size:0.9rem;'>{$cell['class']}</div>
                                              </td>";
                                    } else {
                                        echo "<td style='color:var(--text-muted); text-align:center;'>—</td>";
                                    }
                                endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Highlight effect for today's row -->
<style>
.highlight-row {
    background: rgba(102, 126, 234, 0.1) !important;
    box-shadow: 0 0 10px rgba(102,126,234,0.2);
    transition: all 0.3s ease;
}
.highlight-row td:first-child strong {
    color: var(--gradient-1);
}
.glass-table td, .glass-table th {
    text-align: center;
    vertical-align: middle;
}
</style>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>
</body>
</html>
