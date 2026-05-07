<?php
include("../Includes/HODHeader.php"); 

if(!isset($_SESSION['hod_id'])){
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php");
$teacher_id = (int)$_SESSION['hod_id'];
$class_id   = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$semester_id= isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : 0;

if(!$class_id || !$subject_id || !$semester_id){
    echo "<p style='padding:30px;font-family:Arial;'>Invalid access. Missing parameters.</p>";
    exit;
}

/* --- Fetch class/subject/semester names --- */
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
$info = $infoRes->fetch_assoc();
$class_name   = $info['class_name'];
$subject_name = $info['subject_name'];
$semester_name= $info['semester_name'];

/* --- Attendance Summary --- */
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

/* --- Fetch Students --- */
$students = [];
$stuRes = $con->query("SELECT student_id, student_name FROM tbl_student WHERE class_id = '$class_id' ORDER BY student_name");
while($s = $stuRes->fetch_assoc()){
    $students[] = $s;
}

/* --- Fetch Present Counts --- */
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
<title>Attendance - <?= htmlspecialchars($class_name) ?> | Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* === Attendance Page Styling === */
.info-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  background: rgba(255,255,255,0.05);
  padding: 1rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
}
.info-bar p { margin: 0; color: var(--text-primary,#e5e7eb); }

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



.att-table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 12px;
  overflow: hidden;
  background: rgba(255,255,255,0.06);
  backdrop-filter: blur(8px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.att-table th, .att-table td {
  padding: 12px 14px;
  text-align: center;
  font-size: 0.95rem;
}
.att-table th {
  background: linear-gradient(135deg, var(--gradient-1,#6366f1), var(--gradient-2,#8b5cf6));
  color: #fff;
  font-weight: 600;
}
.att-table tr:nth-child(even) {
  background: rgba(255,255,255,0.04);
}
.att-table td { color: var(--text-primary,#f1f5f9); }
.present { color: #22c55e; font-weight: 600; }
.absent { color: #ef4444; font-weight: 600; }
.low { color: #fbbf24; font-weight: 600; }
.ok { color: #38bdf8; font-weight: 600; }

.no-data {
  text-align: center;
  color: var(--text-secondary);
  font-style: italic;
  margin-top: 1rem;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255,255,255,0.1);
  color: #fff;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
}
.btn-back:hover {
  background: rgba(255,255,255,0.2);
  transform: translateY(-2px);
}
.back-btn {
  text-align: right;
  margin-top: 1.5rem;
}
.page-title {
  color: #fff;
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.2rem;
}
</style>
</head>

<body>

<main class="main-content">
  <div class="glass-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Attendance Report</h3>
      <a href="HODClasses.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Classes</a>
    </div>

    <div class="info-bar">
      <p><strong>Class:</strong> <?= htmlspecialchars($class_name) ?></p>
      <p><strong>Subject:</strong> <?= htmlspecialchars($subject_name) ?></p>
      <p><strong>Semester:</strong> <?= htmlspecialchars($semester_name) ?></p>
      <p><strong>Total Sessions:</strong> <?= $total_classes ?></p>
    </div>

    <?php if(empty($students)): ?>
      <div class="no-data">No students found for this class.</div>
    <?php else: ?>
      <table class="att-table">
        <thead>
          <tr>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Total Sessions</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Attendance %</th>
          </tr>
        </thead>
        <tbody>
        <?php 
        $i = 0;
        foreach($students as $st):
          $sid = (int)$st['student_id'];
          $present = isset($presentCounts[$sid]) ? (int)$presentCounts[$sid] : 0;
          $total = $total_classes;
          $absent = max(0, $total - $present);
          $percentage = ($total > 0) ? round(($present / $total) * 100, 2) : 0;
        ?>
          <tr>
            <td><?= ++$i; ?></td>
            <td><?= htmlspecialchars($st['student_name']) ?></td>
            <td><?= $total ?></td>
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
  </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
