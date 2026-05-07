<?php
include("../Includes/TeacherHeader.php");
if(!isset($_SESSION['teacher_id'])){
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id  = (int)$_SESSION['teacher_id'];
$class_id    = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$subject_id  = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$semester_id = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : 0;

if(!$class_id || !$subject_id || !$semester_id){
    echo "<p style='padding:30px;font-family:Arial;'>Invalid access. Missing parameters.</p>";
    exit;
}

/* Fetch class/subject/semester names */
$infoQry = "
    SELECT c.class_name, s.subject_name, sem.semester_name
    FROM tbl_class c
    LEFT JOIN tbl_subject s ON s.subject_id = '$subject_id'
    LEFT JOIN tbl_semester sem ON sem.semester_id = '$semester_id'
    WHERE c.class_id = '$class_id'
";
$infoRes = $con->query($infoQry);

if(!$infoRes || $infoRes->num_rows == 0){
    echo "<p style='padding:30px;font-family:Arial;'>Class or subject details not found.</p>";
    exit;
}

$info           = $infoRes->fetch_assoc();
$class_name     = $info['class_name'];
$subject_name   = $info['subject_name'];
$semester_name  = $info['semester_name'];

/* Attendance Master IDs */
$masterIds = [];
$masterRes = $con->query("
    SELECT att_master_id
    FROM tbl_attendance_master
    WHERE class_id = '$class_id'
      AND subject_id = '$subject_id'
      AND teacher_id = '$teacher_id'
");

while($m = $masterRes->fetch_assoc()){
    $masterIds[] = (int)$m['att_master_id'];
}

$total_classes = count($masterIds);
$masterListForIn = $total_classes ? implode(',', $masterIds) : '0';

/* Students */
$students = [];
$stuRes = $con->query("SELECT student_id, student_name FROM tbl_student WHERE class_id = '$class_id' ORDER BY student_name");
while($s = $stuRes->fetch_assoc()){
    $students[] = $s;
}

/* Present Count */
$presentCounts = [];
if($total_classes > 0){
    $q = "
      SELECT ad.student_id, COUNT(DISTINCT ad.att_master_id) AS present_count
      FROM tbl_attendance_detail ad
      WHERE ad.att_master_id IN ($masterListForIn)
        AND ad.status = 'Present'
      GROUP BY ad.student_id
    ";
    $pr = $con->query($q);
    while($row = $pr->fetch_assoc()){
        $presentCounts[(int)$row['student_id']] = (int)$row['present_count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance - <?= htmlspecialchars($class_name) ?></title>

<link rel="stylesheet" href="../Assets/CSS/universal.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* PAGE WRAP */
.attendance-page {
    padding: 2rem;
    min-height: calc(100vh - 120px);
}

/* CARD */
.attendance-card {
    background: var(--card-bg);
    border: 1px solid var(--border-glass);
    border-radius: 20px;
    padding: 1.8rem;
    max-width: 1250px;
    margin: 0 auto;
    backdrop-filter: blur(20px);
}

/* TITLE */
.att-title {
    font-size: 1.6rem;
    font-weight: 700;
    display:flex;
    align-items:center;
    gap:0.8rem;
    margin-bottom:1rem;
}
.att-title i {
    font-size: 1.3rem;
    background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

/* INFO BAR */
.att-info-bar {
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
    gap:1rem;
    background:rgba(255,255,255,0.05);
    padding:1rem;
    border-radius:14px;
    border:1px solid var(--border-glass);
    margin-bottom:1.4rem;
}
.att-info-bar p {
    margin:0;
    color:var(--text-secondary);
    font-weight:600;
}

/* TABLE */
.att-table-custom {
    width:100%;
    border-collapse:collapse !important;
    margin-top:1rem;
}
.att-table-custom thead th {
    text-align:left;
    padding:12px;
    background:rgba(255,255,255,0.07);
    border-bottom:1px solid var(--border-glass);
    color:var(--text-primary);
    font-weight:600;
}
.att-table-custom tbody td {
    padding:12px;
    color:var(--text-secondary);
    border-bottom:1px solid rgba(255,255,255,0.05);
}
.att-table-custom tbody tr {
    transition:0.3s;
}
.att-table-custom tbody tr:hover {
    background:rgba(255,255,255,0.08);
}

/* STATUS COLORS */
.present { color: var(--success); font-weight:600; }
.absent { color: var(--error); font-weight:600; }
.low    { color: var(--error); font-weight:700; }
.ok     { color: var(--success); font-weight:700; }

/* EMPTY STATE */
.no-data-box {
    text-align:center;
    padding:3rem 0;
}
.no-data-box .icon {
    font-size:4rem;
    background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    opacity:0.8;
    margin-bottom:1rem;
}
.no-data-box .title {
    color:var(--text-primary);
    font-size:1.4rem;
    font-weight:700;
}
.no-data-box .subtitle {
    color:var(--text-muted);
}

/* BACK BUTTON */
.btn-back {
    margin-top:1.5rem;
    padding:0.7rem 1.4rem;
    border-radius:12px;
    border:2px solid var(--gradient-1);
    color:var(--gradient-1);
    background:transparent;
    text-decoration:none;
    font-weight:600;
    display:inline-block;
}
.btn-back:hover {
    background:var(--gradient-1);
    color:#fff;
}

.present { 
  color: #22c55e !important; 
  font-weight: 600; 
}

.absent { 
  color: #ef4444 !important; 
  font-weight: 600; 
}

/* Circle + percentage centered and side-by-side */
.circle-status {
  display: flex;
  align-items: center;    
  justify-content: center; 
  gap: 8px;               
  font-weight: 700;
  width: 100%;
}

.circle-good, .circle-bad {
  width: 16px;
  height: 16px;
  border-radius: 50%;
}

.circle-good { background: #22c55e; }
.circle-bad  { background: #ef4444; }

</style>

</head>
<body>

<main class="main-content">
<div class="attendance-page">

    <div class="attendance-card">

        <!-- TITLE -->
        <div class="att-title">
            <i class="fas fa-user-check"></i>
            Attendance Report
        </div>

        <!-- INFO BAR -->
        <div class="att-info-bar">
            <p><strong>Class:</strong> <?= htmlspecialchars($class_name) ?></p>
            <p><strong>Subject:</strong> <?= htmlspecialchars($subject_name) ?></p>
            <p><strong>Semester:</strong> <?= htmlspecialchars($semester_name) ?></p>
            <p><strong>Total Sessions:</strong> <?= $total_classes ?></p>
        </div>

        <!-- TABLE / NO DATA -->
        <?php if(empty($students)): ?>

            <div class="no-data-box">
                <div class="icon"><i class="fas fa-user-slash"></i></div>
                <div class="title">No students found</div>
                <div class="subtitle">This class has no registered students.</div>
            </div>

        <?php else: ?>

            <table class="att-table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Total Sessions</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i=0;
                    foreach($students as $st):
                        $sid = (int)$st['student_id'];
                        $present = $presentCounts[$sid] ?? 0;
                        $absent = max(0, $total_classes - $present);
                        $percentage = ($total_classes > 0)
                            ? round(($present/$total_classes)*100,2)
                            : 0;
                    ?>
          <tr>
            <td><?= ++$i; ?></td>
            <td><?= htmlspecialchars($st['student_name']) ?></td>
            <td><?= $total_classes ?></td>
            <td class="present"><?= $present ?></td>
            <td class="absent"><?= $absent ?></td>
            <td>
              <div class="circle-status">
                <span class="<?= $percentage < 75 ? 'circle-bad' : 'circle-good' ?>"></span>
                <span><?= $percentage ?>%</span>
              </div>
            </td>
           </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

        <!-- BACK BUTTON -->
        <a href="TeacherClasses.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Class List
        </a>  

    </div>

</div>
</main>

<script src="../Assets/JS/universal.js"></script>

</body>
</html>
