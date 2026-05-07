<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$teacher_id = $_SESSION['hod_id'];
$today = date('l');

// --- Fetch department periods ---
$qPeriod = "SELECT * FROM tbl_departmentperiods 
            WHERE department_id=(SELECT department_id FROM tbl_teacher WHERE teacher_id='$teacher_id')
            ORDER BY period_no";
$resPeriod = $con->query($qPeriod);

$periods = [];
while($row = $resPeriod->fetch_assoc()){
    $periods[] = $row;
}

// --- Days of the week ---
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// --- Build timetable structure ---
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

// --- Page metadata for universal header ---
$page_title = "My Weekly Timetable";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Timetable</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?> - Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Styles -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">
</head>
<body>
<main class="main-content">

    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-alt"></i> My Weekly Timetable</h3>
        </div>

        <?php if(empty($periods)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="empty-state-title">No Periods Configured</div>
                <div class="empty-state-text">Your department doesn’t have any period setup yet.</div>
            </div>
        <?php else: ?>
        <div class="timetable-wrapper" style="overflow-x:auto;margin-top:1rem;">
            <table class="glass-table" id="timetableTable">
                <thead>
                    <tr>
                        <th>Day / Period</th>
                        <?php foreach($periods as $p): ?>
                            <th>
                                <div style="font-weight:600;">P<?= $p['period_no'] ?></div>
                                <small style="color:var(--text-secondary);">
                                    <?= date('H:i', strtotime($p['start_time'])) ?> - <?= date('H:i', strtotime($p['end_time'])) ?>
                                </small>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($days as $day): ?>
                        <tr class="<?php echo ($day == $today) ? 'highlight-today' : ''; ?>">
                            <td style="font-weight:600;color:var(--text-primary);"><?= $day ?></td>
                            <?php foreach($periods as $p): 
                                $pno = $p['period_no'];
                                if($p['is_break'] == 1){
                                    echo "<td style='text-align:center;color:var(--warning);font-weight:600;'>Break</td>";
                                    continue;
                                }
                                $cell = $timetable[$day][$pno] ?? null;
                                if($cell){
                                    echo "<td style='text-align:center;'>
                                            <div style='font-weight:600;color:var(--text-primary);'>{$cell['subject']}</div>
                                            <div style='color:var(--text-muted);font-size:0.9rem;'>{$cell['class']}</div>
                                          </td>";
                                } else {
                                    echo "<td style='text-align:center;color:var(--text-muted);'>—</td>";
                                }
                            endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- Universal JS -->
<script src="../Assets/JS/universal.js"></script>

<!-- Extra Table Enhancement (Highlight today row) -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const todayRow = document.querySelector(".highlight-today");
    if(todayRow){
        todayRow.style.background = "rgba(102,126,234,0.08)";
        todayRow.style.boxShadow = "0 0 15px rgba(102,126,234,0.2)";
        todayRow.style.transition = "background 0.5s ease";
    }
});
</script>

</body>
</html>
