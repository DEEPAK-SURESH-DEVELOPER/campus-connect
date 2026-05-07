<?php
include("../Includes/StudentHeader.php");
if (!isset($_SESSION['student_id'])) {
    header("location:../Guest/Login.php");
    exit;
}

include("../Includes/StudentSidebar.php");
$student_id = $_SESSION['student_id'];

/* --- Get class and semester --- */
$studentQry = $con->query("
    SELECT s.class_id, c.semester_id 
    FROM tbl_student s 
    INNER JOIN tbl_class c ON s.class_id = c.class_id 
    WHERE s.student_id = '$student_id'
");
$student = $studentQry->fetch_assoc();
$class_id = $student['class_id'];
$semester_id = $student['semester_id'];

/* --- Get subjects with per-subject attendance --- */
$subjectQry = $con->query("
    SELECT DISTINCT su.subject_id, su.subject_name
    FROM tbl_timetable t
    INNER JOIN tbl_subject su ON su.subject_id = t.subject_id
    WHERE t.class_id = '$class_id' AND t.semester_id = '$semester_id'
");

$total_present = 0;
$total_classes = 0;
$subjects = [];

while ($row = $subjectQry->fetch_assoc()) {
    $sid = $row['subject_id'];
    $attQry = $con->query("
        SELECT COUNT(*) AS total,
               SUM(CASE WHEN ad.status='Present' THEN 1 ELSE 0 END) AS present
        FROM tbl_attendance_master am
        INNER JOIN tbl_attendance_detail ad ON ad.att_master_id = am.att_master_id
        WHERE am.subject_id = '$sid' 
          AND am.class_id = '$class_id' 
          AND ad.student_id = '$student_id'
    ");
    $att = $attQry->fetch_assoc();
    $present = $att['present'] ?? 0;
    $total = $att['total'] ?? 0;
    $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

    $subjects[] = [
        'id' => $sid,
        'name' => $row['subject_name'],
        'present' => $present,
        'total' => $total,
        'percentage' => $percentage
    ];

    $total_present += $present;
    $total_classes += $total;
}

$overall_percentage = $total_classes > 0 ? round(($total_present / $total_classes) * 100, 2) : 0;

/* --- Page setup for universal header --- */
$page_title = "Attendance Overview";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Attendance</span>';
?>

<!-- MAIN CONTENT -->
<div class="main-content">
    <!-- Overall Summary -->
    <div class="glass-card text-center fade-in">
        <h2 class="text-gradient mb-1"><i class="fas fa-book-open"></i> Attendance Summary</h2>
        <p style="color: var(--text-secondary);">Your overall attendance across all subjects</p>

        <div class="stat-card mt-3" style="max-width:350px; margin:2rem auto;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
            <div class="stat-card-value"><?php echo $overall_percentage; ?>%</div>
            <div class="stat-card-label">Overall Attendance</div>
            <div class="progress-bar mt-2">
                <div class="progress-fill" style="width:<?php echo $overall_percentage; ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- Subject-wise Attendance -->
    <div class="glass-card slide-up">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list-check"></i> Subject-wise Attendance
            </h3>
        </div>

        <?php if (count($subjects) > 0): ?>
        <div class="dashboard-cards">
            <?php foreach ($subjects as $s): 
                $badge = $s['percentage'] >= 75 ? 'badge-success' : ($s['percentage'] >= 50 ? 'badge-warning' : 'badge-error'); ?>
                <div class="stat-card" onclick="window.location='ViewAttendanceDetailed.php?sid=<?php echo $s['id']; ?>'">
                    <div class="stat-card-header">
                        <div class="stat-card-icon"><i class="fas fa-book"></i></div>
                        <span class="badge <?php echo $badge; ?>"><?php echo $s['percentage']; ?>%</span>
                    </div>
                    <div style="font-size:1.5rem;"><?php echo $s['name']; ?></div>
                    <div class="stat-card-label"><?php echo $s['present'].' / '.$s['total']; ?> Classes Present</div>
                    <div class="progress-bar mt-2">
                        <div class="progress-fill" style="width:<?php echo $s['percentage']; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="empty-state-title">No Subjects Found</div>
                <div class="empty-state-text">Your attendance details are not available yet.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="StudentHome.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
</div>

<!-- UNIVERSAL JS -->
<script src="../Assets/JS/universal.js"></script>
</body>
</html>
