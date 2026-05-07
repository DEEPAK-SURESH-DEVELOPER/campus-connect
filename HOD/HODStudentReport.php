 <?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$hod_id = (int)$_SESSION['hod_id'];
$department_id = $_SESSION['hod_department_id'];
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id === 0) {
    echo "<p style='padding:30px;font-family:Arial;'>Invalid student access.</p>";
    exit;
}

// --- Fetch Student Info ---
$qStudent = "
SELECT s.student_name, s.class_id, c.class_name, c.semester_id, sem.semester_name, 
       co.course_name, co.department_id
FROM tbl_student s
INNER JOIN tbl_class c ON s.class_id = c.class_id
INNER JOIN tbl_semester sem ON c.semester_id = sem.semester_id
INNER JOIN tbl_course co ON c.course_id = co.course_id
WHERE s.student_id = '$student_id'
";
$resStudent = $con->query($qStudent);
if (!$resStudent || $resStudent->num_rows == 0) {
    echo "<p style='padding:30px;font-family:Arial;'>Student not found.</p>";
    exit;
}
$student = $resStudent->fetch_assoc();

if ($student['department_id'] != $department_id) {
    echo "<script>alert('Access denied. This student is not under your department.'); window.location='ManageStudents.php';</script>";
    exit;
}

$class_id = $student['class_id'];
$semester_id = $student['semester_id'];

// --- Fetch Subjects ---
$subRes = $con->query("
    SELECT DISTINCT s.subject_id, s.subject_name
    FROM tbl_timetable tt
    INNER JOIN tbl_subject s ON tt.subject_id = s.subject_id
    INNER JOIN tbl_class c ON c.class_id = tt.class_id
    WHERE tt.class_id = '$class_id'
      AND tt.semester_id = c.semester_id
      AND c.is_completed = 0
");

$subjects = [];
while ($s = $subRes->fetch_assoc()) {
    $subjects[] = $s;
}

// --- Compute Attendance ---
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

// Page Meta
$page_title = "Student Attendance Report";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> 
               <a href="ManageStudents.php">Manage Students</a> 
               <i class="fas fa-chevron-right"></i> <span>Student Report</span>';
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

<!-- Universal CSS -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* === Inline Styling for Report Page === */
.student-info {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
}
.student-info p {
    margin: 0;
    font-size: 1rem;
}
.report-summary {
    text-align: right;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}
.glass-table td.low {
    color: #f87171; /* red for low attendance */
    font-weight: 600;
}
.glass-table td.ok {
    color: #34d399; /* green for good attendance */
    font-weight: 600;
}
.no-data {
    text-align: center;
    color: var(--text-muted);
    padding: 1rem;
}
</style>
</head>

<body>
<main class="main-content">

    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Student Attendance Report</h3>
            <button class="btn-outline" onclick="window.location.href='ManageStudents.php'">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['student_name']); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name']); ?></p>
            <p><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester_name']); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course_name']); ?></p>
        </div>

        <?php if (empty($report)): ?>
            <div class="no-data">No subjects found for this semester.</div>
        <?php else: ?>
        <table class="glass-table">
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
                <?php 
                $overall_total = 0; 
                $overall_present = 0;
                foreach ($report as $r): 
                    $overall_total += $r['total'];
                    $overall_present += $r['present'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['subject_name']); ?></td>
                    <td><?php echo $r['total']; ?></td>
                    <td class="present"><?php echo $r['present']; ?></td>
                    <td class="absent"><?php echo $r['absent']; ?></td>
                    <td class="<?php echo ($r['percentage'] < 75 ? 'low' : 'ok'); ?>">
                        <?php echo $r['percentage']; ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="report-summary">
            <?php 
            $overall_percentage = ($overall_total > 0) ? round(($overall_present / $overall_total) * 100, 2) : 0;
            ?>
            <p><strong>Overall Attendance:</strong> 
                <span style="color:<?php echo ($overall_percentage < 75) ? '#f14040ff' : '#34d399'; ?>">
                    <?php echo $overall_percentage; ?>%
                </span>
            </p>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
