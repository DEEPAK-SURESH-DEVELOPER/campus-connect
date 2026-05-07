<?php
include("../Includes/StudentHeader.php");

if (!isset($_SESSION['student_id'])) {
    header("location:../Guest/Login.php");
    exit;
}
include("../Includes/StudentSidebar.php");
$student_id = $_SESSION['student_id'];
$subject_id = $_GET['sid'];

// Student’s class & semester
$studentQry = $con->query("
    SELECT s.class_id, c.semester_id 
    FROM tbl_student s 
    INNER JOIN tbl_class c ON s.class_id = c.class_id 
    WHERE s.student_id = '$student_id'
");
$student = $studentQry->fetch_assoc();
$class_id = $student['class_id'];
$semester_id = $student['semester_id'];

// Subject name
$sub = $con->query("SELECT subject_name FROM tbl_subject WHERE subject_id='$subject_id'")
          ->fetch_assoc()['subject_name'];

// Fetch attendance records
$attQry = $con->query("
    SELECT am.attendance_date, ad.status, p.period_no, p.start_time, p.end_time
    FROM tbl_attendance_master am
    INNER JOIN tbl_attendance_detail ad ON ad.att_master_id = am.att_master_id
    INNER JOIN tbl_departmentperiods p ON p.period_id = am.period_id
    WHERE am.subject_id = '$subject_id' 
      AND am.class_id = '$class_id' 
      AND ad.student_id = '$student_id'
    ORDER BY am.attendance_date DESC
");

// Attendance summary
$summaryQry = $con->query("
    SELECT COUNT(*) AS total,
           SUM(CASE WHEN ad.status='Present' THEN 1 ELSE 0 END) AS present
    FROM tbl_attendance_master am
    INNER JOIN tbl_attendance_detail ad ON ad.att_master_id = am.att_master_id
    WHERE am.subject_id = '$subject_id' 
      AND am.class_id = '$class_id' 
      AND ad.student_id = '$student_id'
");
$s = $summaryQry->fetch_assoc();
$present = $s['present'] ?? 0;
$total = $s['total'] ?? 0;
$percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

// Page Configuration
$page_title = "$sub Attendance";
$breadcrumb = '<span>Attendance</span> <i class="fas fa-chevron-right"></i> <span>' . htmlspecialchars($sub) . '</span>';
?>

<style>
.main-content {
    margin-left: 280px;
    margin-top: 80px;
    padding: 2rem;
    min-height: calc(100vh - 80px);
}

/* Attendance Container */
.attendance-container {
    background: var(--card-bg);
    backdrop-filter: blur(25px);
    border-radius: 20px;
    border: 1px solid var(--border-glass);
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    animation: fadeIn 0.5s ease;
}

/* Header */
.attendance-header {
    text-align: center;
    margin-bottom: 2rem;
}
.attendance-header h1 {
    font-size: 1.6rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.5rem;
}
.attendance-header p {
    color: var(--text-secondary);
    font-size: 1rem;
}

/* Improved Glass Table (matches dashboard) */
.attendance-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.6rem;
    margin-top: 1rem;
}
.attendance-table thead th {
    background: rgba(102, 126, 234, 0.15);
    color: var(--text-primary);
    font-weight: 600;
    padding: 1rem;
    text-align: center;
    font-size: 0.95rem;
    text-transform: uppercase;
    border: none;
    border-bottom: 2px solid var(--border-glass);
    letter-spacing: 0.5px;
}
.attendance-table tbody tr {
    background: rgba(255, 255, 255, 0.04);
    border-radius: 12px;
    transition: all 0.3s ease;
}
.attendance-table tbody tr:hover {
    background: rgba(102, 126, 234, 0.1);
    transform: scale(1.01);
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.25);
}
.attendance-table td {
    padding: 1rem;
    text-align: center;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--border-glass);
    font-size: 0.95rem;
}
.attendance-table td:first-child {
    border-radius: 12px 0 0 12px;
}
.attendance-table td:last-child {
    border-radius: 0 12px 12px 0;
}
.attendance-table td strong {
    color: var(--text-primary);
}

/* Status badges */
.status-badge {
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
    min-width: 100px;
}
.status-badge.present {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.4);
}
.status-badge.absent {
    background: rgba(239, 68, 68, 0.15);
    color: var(--error);
    border: 1px solid rgba(239, 68, 68, 0.4);
}
.status-badge.late {
    background: rgba(245, 158, 11, 0.15);
    color: var(--warning);
    border: 1px solid rgba(245, 158, 11, 0.4);
}

/* No Data */
.no-data {
    color: var(--text-muted);
    text-align: center;
    padding: 1.5rem;
    font-size: 1rem;
}

/* Back button */
.back-button-container {
    text-align: center;
    margin-top: 2rem;
}
.back-button {
    display: inline-block;
    padding: 0.8rem 2rem;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: white;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 5px 15px var(--glow-primary);
    transition: 0.3s;
}
.back-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px var(--glow-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .main-content { margin-left: 0; padding: 1.5rem; }
    .attendance-header h1 { font-size: 1.3rem; }
    .attendance-table th, .attendance-table td { font-size: 0.85rem; padding: 0.6rem; }
}
</style>

<div class="main-content">
  <div class="attendance-container">
    <div class="attendance-header">
      <h1><i class="fas fa-calendar-check"></i> <?php echo htmlspecialchars($sub); ?> Attendance</h1>
      <p>You are <strong><?php echo $percentage; ?>%</strong> present in this subject.</p>
    </div>

    <table class="attendance-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Period</th>
          <th>Time</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($attQry->num_rows > 0) {
            while ($row = $attQry->fetch_assoc()) {
                $status = strtolower($row['status']);
                $statusBadge = "<span class='status-badge {$status}'>" . htmlspecialchars($row['status']) . "</span>";
                echo "<tr>";
                echo "<td>" . date("d M Y", strtotime($row['attendance_date'])) . "</td>";
                echo "<td><strong>Period " . $row['period_no'] . "</strong></td>";
                echo "<td>" . date("H:i", strtotime($row['start_time'])) . " - " . date("H:i", strtotime($row['end_time'])) . "</td>";
                echo "<td>$statusBadge</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='no-data'>No attendance records available.</td></tr>";
        }
        ?>
      </tbody>
    </table>

    <div class="back-button-container">
      <a href="ViewAttendance.php" class="back-button">← Back to Subjects</a>
    </div>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
