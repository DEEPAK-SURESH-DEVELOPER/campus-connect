<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
date_default_timezone_set('Asia/Kolkata');

$hod_id = $_SESSION['hod_id'];
$department_id = $_SESSION['hod_department_id'];

// --- Get selected class ---
if (!isset($_GET['class_id'])) {
    echo "<script>alert('Invalid request.'); window.location='HODViewCourses.php';</script>";
    exit;
}
$class_id = intval($_GET['class_id']);

// --- Get class details ---
$classQry = "
    SELECT c.class_name, c.semester_id, co.course_id, co.course_name
    FROM tbl_class c
    INNER JOIN tbl_course co ON c.course_id = co.course_id
    WHERE c.class_id = '$class_id'
";
$classRes = $con->query($classQry);
if ($classRes->num_rows == 0) {
    echo "<p style='padding:30px;font-family:Arial;'>Class details not found.</p>";
    exit;
}
$classRow = $classRes->fetch_assoc();
$semester_id = $classRow['semester_id'];
$course_id = $classRow['course_id'];
$course_name = $classRow['course_name'];
$class_name = $classRow['class_name'];

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
    INNER JOIN tbl_class c ON c.class_id = tt.class_id
    INNER JOIN tbl_departmentperiods dp ON tt.period_id = dp.period_id
    LEFT JOIN tbl_subject s ON tt.subject_id = s.subject_id
    LEFT JOIN tbl_teacher t ON tt.teacher_id = t.teacher_id
    WHERE tt.class_id = '$class_id'
      AND c.is_completed = 0
      AND tt.semester_id = c.semester_id
";

$ttRes = $con->query($ttQry);

// Normalize weekdays
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

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$today = date('l');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Class Timetable - <?= htmlspecialchars($class_name) ?> | Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal CSS -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* === HOD Timetable - Universal Theme === */
.timetable-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  background:rgba(255,255,255,0.08);
  padding:20px;
  border-radius:14px;
  margin-bottom:25px;
  backdrop-filter:blur(10px);
  box-shadow:0 4px 15px rgba(0,0,0,0.3);
}
.timetable-header h1 {
  color:var(--text-primary);
  font-size:1.4rem;
}
.timetable-header p {
  color:var(--text-secondary);
  margin:5px 0 0;
}

.timetable-wrapper {
  overflow-x:auto;
  margin-top:20px;
}

.week-table {
  width:100%;
  border-collapse:collapse;
  background:rgba(255,255,255,0.06);
  border-radius:12px;
  overflow:hidden;
  box-shadow:0 4px 15px rgba(0,0,0,0.2);
  backdrop-filter:blur(8px);
}
.week-table th, .week-table td {
  padding:12px;
  text-align:center;
  border-bottom:1px solid rgba(255,255,255,0.1);
}
.week-table th {
  background:linear-gradient(135deg,var(--gradient-1,#6366f1),var(--gradient-2,#8b5cf6));
  color:#fff;
  font-weight:600;
}
.week-table tr:nth-child(even) td {
  background:rgba(255,255,255,0.03);
}
.week-table td {
  color:var(--text-primary);
  font-size:.95rem;
}

.cell {
  padding:10px;
  border-radius:10px;
}
.cell.break {
  background:rgba(250,204,21,0.1);
  color:#facc15;
  font-weight:600;
}
.cell.filled {
  background:rgba(37,99,235,0.12);
  border:1px solid rgba(37,99,235,0.4);
}
.cell.empty {
  color:var(--text-secondary);
}
.sub {
  font-weight:600;
  color:#fff;
}
.tch {
  color:#93c5fd;
  font-size:.85rem;
}
.today {
  background:rgba(59,130,246,0.05);
  border-left:4px solid #3b82f6;
}
.no-data {
  text-align:center;
  color:var(--text-secondary);
  padding:20px;
  font-style:italic;
}
</style>
</head>

<body>
<main class="main-content">
  <div class="glass-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-calendar"></i> Class Timetable</h3>
    </div>

    <div class="timetable-header">
      <div>
        <h1><?= htmlspecialchars($class_name) ?> — <?= htmlspecialchars($course_name) ?></h1>
        <p>Semester <?= htmlspecialchars($semester_id) ?></p>
      </div>
      <a href="HODViewClasses.php?course_id=<?= $course_id ?>" class="btn-primary">
        <i class="fas fa-arrow-left"></i> Back to Classes
      </a>
    </div>

    <?php if (empty($periods)): ?>
      <div class="no-data">No periods configured for your department.</div>
    <?php else: ?>
      <div class="timetable-wrapper">
        <table class="week-table">
          <thead>
            <tr>
              <th>Day / Period</th>
              <?php foreach ($periods as $p): ?>
                <th>
                  <div>P<?= $p['period_no'] ?></div>
                  <div style="font-size:.8rem;opacity:.8;">
                    <?= date('H:i', strtotime($p['start_time'])) ?> - <?= date('H:i', strtotime($p['end_time'])) ?>
                  </div>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($days as $day): ?>
              <tr class="<?= ($day == $today) ? 'today' : '' ?>">
                <td><strong><?= $day ?></strong></td>
                <?php foreach ($periods as $p):
                  $pno = $p['period_no'];
                  if ($p['is_break'] == 1) {
                      echo "<td class='cell break'>Break</td>";
                      continue;
                  }
                  $cell = $timetable[$day][$pno] ?? null;
                  if ($cell && $cell['subject']) {
                      echo "<td class='cell filled'>
                              <div class='sub'>".htmlspecialchars($cell['subject'])."</div>
                              <div class='tch'>".htmlspecialchars($cell['teacher'])."</div>
                            </td>";
                  } else {
                      echo "<td class='cell empty'>-</td>";
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

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
