<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id === 0) {
    echo "<p style='padding:30px;font-family:Arial;'>Invalid student access.</p>";
    exit;
}

/* --- 1. Fetch student details --- */
$qStudent = "
SELECT s.student_name, s.class_id, c.class_name, c.semester_id, sem.semester_name, 
       co.course_name, d.department_name
FROM tbl_student s
INNER JOIN tbl_class c ON s.class_id = c.class_id
INNER JOIN tbl_semester sem ON c.semester_id = sem.semester_id
INNER JOIN tbl_course co ON c.course_id = co.course_id
INNER JOIN tbl_department d ON co.department_id = d.department_id
WHERE s.student_id = '$student_id'
";
$resStudent = $con->query($qStudent);
if (!$resStudent || $resStudent->num_rows == 0) {
    echo "<p style='padding:30px;font-family:Arial;'>Student not found.</p>";
    exit;
}
$student = $resStudent->fetch_assoc();

$class_id    = $student['class_id'];
$semester_id = $student['semester_id'];

/* --- 2. Fetch all subjects for this semester --- */
$subRes = $con->query("SELECT subject_id, subject_name FROM tbl_subject WHERE semester_id='$semester_id'");
$subjects = [];
while ($s = $subRes->fetch_assoc()) {
    $subjects[] = $s;
}

/* --- 3. Compute attendance for each subject --- */
$report = [];
foreach ($subjects as $sub) {
    $sub_id = (int)$sub['subject_id'];

    $totalRes = $con->query("
        SELECT COUNT(*) AS total_classes
        FROM tbl_attendance_master
        WHERE class_id='$class_id' AND subject_id='$sub_id'
    ");
    $total_classes = ($totalRes->fetch_assoc())['total_classes'] ?? 0;

    $presentRes = $con->query("
        SELECT COUNT(*) AS present_count
        FROM tbl_attendance_detail ad
        INNER JOIN tbl_attendance_master am ON ad.att_master_id = am.att_master_id
        WHERE ad.student_id='$student_id'
          AND am.class_id='$class_id'
          AND am.subject_id='$sub_id'
          AND ad.status='Present'
    ");
    $present_count = ($presentRes->fetch_assoc())['present_count'] ?? 0;

    $percentage = ($total_classes > 0) ? round(($present_count / $total_classes) * 100, 2) : 0;

    $report[] = [
        'subject_name' => $sub['subject_name'],
        'total' => $total_classes,
        'present' => $present_count,
        'absent' => max(0, $total_classes - $present_count),
        'percentage' => $percentage
    ];
}

$page_title = "Student Attendance Report";
$breadcrumb = '<span>Reports</span> <i class="fas fa-chevron-right"></i> <span>Student Attendance</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Attendance Report</title>

<!-- INTERNAL CSS -->
<style>
.main-content {
  padding: 1.5rem;
}

.report-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  color: var(--text-primary);
  max-width: 900px;
  margin: 0 auto;
}

.report-title {
  text-align: center;
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--gradient-1);
  margin-bottom: 1.5rem;
}

.student-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.6rem;
  margin-bottom: 1.5rem;
  color: var(--text-secondary);
}
.student-info p {
  margin: 0;
  background: rgba(255,255,255,0.06);
  padding: 0.6rem 0.9rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
}

.report-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}
.report-table th, .report-table td {
  padding: 0.8rem;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  color: var(--text-primary);
}
.report-table th {
  background: rgba(255,255,255,0.08);
  color: var(--gradient-1);
  font-weight: 600;
}
.report-table tr:hover {
  background: rgba(255,255,255,0.05);
  transition: 0.3s ease;
}

/* Attendance color indicators */
.present {
  color: #22c55e;
  font-weight: 600;
}
.absent {
  color: #ef4444;
  font-weight: 600;
}
.ok {
  color: #38bdf8;
  font-weight: 600;
}
.low {
  color: #f87171;
  font-weight: 700;
  animation: blink 1.5s infinite alternate;
}
@keyframes blink {
  0% { opacity: 1; }
  100% { opacity: 0.5; }
}

/* No data */
.no-data {
  text-align: center;
  color: var(--text-secondary);
  margin-top: 2rem;
  padding: 1rem;
  font-size: 1rem;
  border-radius: 10px;
  background: rgba(255,255,255,0.05);
}

/* Back Button */
.back-btn {
  text-align: center;
  margin-top: 2rem;
}
.btn-back {
  display: inline-block;
  padding: 0.7rem 1.5rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--gradient-1);
  text-decoration: none;
  font-weight: 600;
  transition: 0.3s ease;
}
.btn-back:hover {
  background: rgba(255,255,255,0.15);
  box-shadow: 0 0 10px rgba(99,102,241,0.4);
}

/* Responsive */
@media(max-width:768px){
  .report-card {
    padding: 1.2rem;
  }
  .report-title {
    font-size: 1.4rem;
  }
}
</style>
</head>

<body>
<div class="main-content">
  <div class="report-card">
    <h1 class="report-title"><i class="fas fa-clipboard-list"></i> Student Attendance Report</h1>

    <div class="student-info">
      <p><strong>Name:</strong> <?= htmlspecialchars($student['student_name']) ?></p>
      <p><strong>Class:</strong> <?= htmlspecialchars($student['class_name']) ?></p>
      <p><strong>Semester:</strong> <?= htmlspecialchars($student['semester_name']) ?></p>
      <p><strong>Course:</strong> <?= htmlspecialchars($student['course_name']) ?></p>
      <p><strong>Department:</strong> <?= htmlspecialchars($student['department_name']) ?></p>
    </div>

    <?php if (empty($report)): ?>
      <div class="no-data">No subjects found for this semester.</div>
    <?php else: ?>
      <table class="report-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Total Sessions</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Attendance %</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($report as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['subject_name']) ?></td>
            <td><?= $r['total'] ?></td>
            <td class="present"><?= $r['present'] ?></td>
            <td class="absent"><?= $r['absent'] ?></td>
            <td class="<?= ($r['percentage'] < 75 ? 'low' : 'ok') ?>">
              <?= $r['percentage'] ?>%
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div class="back-btn">
      <a href="StudentListAdmin.php" class="btn-back">← Back to Student List</a>
    </div>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
