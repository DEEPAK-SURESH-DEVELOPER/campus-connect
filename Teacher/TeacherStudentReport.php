<?php
include("../Includes/TeacherHeader.php");

if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = (int)$_SESSION['teacher_id'];
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id === 0) {
    echo "<p style='padding:30px;font-family:Arial;'>Invalid student access.</p>";
    exit;
}

/* --- Fetch student details --- */
$qStudent = "
SELECT s.student_id, s.student_name, s.student_photo, s.class_id,
       c.class_name, c.semester_id, c.course_id,
       sem.semester_name, co.course_name
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
$class_id    = $student['class_id'];
$semester_id = $student['semester_id'];

/* --- Verify class teacher ownership --- */
$check = $con->query("SELECT teacher_id FROM tbl_class WHERE class_id='$class_id' AND teacher_id='$teacher_id'");
if ($check->num_rows == 0) {
    echo "<script>alert('Access denied. You are not the class teacher of this student.'); window.location='TeacherHome.php';</script>";
    exit;
}

/* --- Determine student photo --- */
$photo = !empty($student['student_photo']) ? $student['student_photo'] : 'default.png';
$photo_path = "../Assets/Files/Student/" . $photo;
if (!file_exists($photo_path)) {
    $photo_path = "../Assets/Files/Student/default.png";
}

/* --- Fetch subjects from active timetable --- */
$subRes = $con->query("
    SELECT DISTINCT s.subject_id, s.subject_name
    FROM tbl_timetable tt
    INNER JOIN tbl_subject s ON tt.subject_id = s.subject_id
    INNER JOIN tbl_class c ON tt.class_id = c.class_id
    WHERE tt.class_id = '$class_id'
      AND tt.semester_id = c.semester_id
    ORDER BY s.subject_name
");

$subjects = [];
while ($row = $subRes->fetch_assoc()) {
    $subjects[] = $row;
}

/* --- Compute attendance per subject --- */
$report = [];
foreach ($subjects as $sub) {
    $sub_id = (int)$sub['subject_id'];

    $totalRes = $con->query("
        SELECT COUNT(DISTINCT att_master_id) AS total_classes
        FROM tbl_attendance_master
        WHERE class_id='$class_id' AND subject_id='$sub_id'
    ");
    $total_classes = ($totalRes->fetch_assoc())['total_classes'] ?? 0;

    $presentRes = $con->query("
        SELECT COUNT(DISTINCT ad.att_detail_id) AS present_count
        FROM tbl_attendance_detail ad
        INNER JOIN tbl_attendance_master am ON ad.att_master_id = am.att_master_id
        WHERE ad.student_id='$student_id'
          AND am.class_id='$class_id'
          AND am.subject_id='$sub_id'
          AND ad.status='Present'
    ");
    $present_count = ($presentRes->fetch_assoc())['present_count'] ?? 0;

    $percentage = ($total_classes > 0)
        ? round(($present_count / $total_classes) * 100, 2)
        : 0;

    $report[] = [
        'subject_name' => $sub['subject_name'],
        'total' => $total_classes,
        'present' => $present_count,
        'absent' => max(0, $total_classes - $present_count),
        'percentage' => $percentage
    ];
}

$page_title = "Student Attendance Report";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Attendance Report</span>';

?>

<div class="main-content">
    <div class="glass-card slide-up">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Student Attendance Report</h3>
            <a href="StudentList.php?class_id=<?= $class_id ?>" class="btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- Student Attendance Container -->
        <div class="teacher-attendance-container">

            <!-- Student Info Section -->
            <div class="tac-student-profile">
                <div class="tac-student-photo">
                    <img src="<?= htmlspecialchars($photo_path) ?>" alt="Student Photo">
                </div>
                <div class="tac-student-info">
                    <h2 class="tac-student-name"><?= htmlspecialchars($student['student_name']) ?></h2>
                    <p><strong>Class:</strong> <?= htmlspecialchars($student['class_name']) ?></p>
                    <p><strong>Semester:</strong> <?= htmlspecialchars($student['semester_name']) ?></p>
                    <p><strong>Course:</strong> <?= htmlspecialchars($student['course_name']) ?></p>
                </div>
            </div>

            <!-- Attendance Table or Empty State -->
            <?php
            if (empty($report)) {
                ?>
                <div class="tac-empty-state">
                    <div class="empty-state-icon"><i class="fas fa-info-circle"></i></div>
                    <div class="empty-state-title">No Subjects Found</div>
                    <div class="empty-state-text">
                        No active subjects found for this student’s current semester.
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="tac-table-wrap">
                    <table class="tac-table" id="attendanceTable">
                        <thead>
                            <tr>
                                <th class="col-subject">Subject</th>
                                <th class="col-total">Total Sessions</th>
                                <th class="col-present">Present</th>
                                <th class="col-absent">Absent</th>
                                <th class="col-percent">Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($report as $r) {
                                $barColor = ($r['percentage'] < 75) ? '#ef4444' : '#10b981';
                                ?>
                                <tr>
                                    <td class="td-subject"><?= htmlspecialchars($r['subject_name']) ?></td>
                                    <td class="td-total"><?= (int)$r['total'] ?></td>
                                    <td class="td-present">
                                        <span class="tac-badge tac-present"><?= (int)$r['present'] ?></span>
                                    </td>
                                    <td class="td-absent">
                                        <span class="tac-badge tac-absent"><?= (int)$r['absent'] ?></span>
                                    </td>
                                    <td class="td-percent">
                                        <div class="tac-progress">
                                            <div class="tac-fill" style="width:<?= $r['percentage'] ?>%; background:<?= $barColor ?>;"></div>
                                        </div>
                                        <div class="tac-percent-text"><?= htmlspecialchars($r['percentage']) ?>%</div>
                                    </td>
                                </tr>
                                <?php
                            } // end foreach
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
            } // end if
            ?>
        </div>
        <!-- End .teacher-attendance-container -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>

<style>
/* ===== Minimal Scoped CSS for Teacher Attendance Page ===== */
.teacher-attendance-container {
    margin-top: 1.5rem;
    background: rgba(21, 25, 50, 0.55);
    border: 1px solid var(--border-glass);
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(10,14,40,0.35);
    padding: 1.5rem;
}

/* ---------- Student Info Header ---------- */
.tac-student-profile {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-glass);
}

.tac-student-photo {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--gradient-1);
    box-shadow: 0 0 15px rgba(102,126,234,0.25);
}

.tac-student-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tac-student-info h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.tac-student-info p {
    margin: 0.15rem 0;
    color: var(--text-secondary);
    font-size: 0.95rem;
}

/* ---------- Attendance Table ---------- */
.tac-table-wrap {
    overflow-x: auto;
}

.tac-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    table-layout: fixed;
}

/* Header */
.tac-table thead th {
    background: rgba(102,126,234,0.12);
    color: var(--text-primary);
    text-transform: uppercase;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 1rem;
    text-align: center;
}

/* Subject column specifically left aligned */
.tac-table thead th.col-subject,
.tac-table .td-subject {
    text-align: left !important;
    padding-left: 1.2rem !important;
    color: var(--text-primary);
}

/* Data rows */
.tac-table tbody tr {
    background: rgba(255,255,255,0.03);
    transition: 0.2s ease;
}

.tac-table tbody tr:hover {
    background: rgba(102,126,234,0.08);
}

/* Table cells */
.tac-table td {
    padding: 1rem;
    font-size: 0.95rem;
    color: var(--text-secondary);
    text-align: center;
}

/* ---------- Badges ---------- */
.tac-badge {
    display: inline-block;
    min-width: 45px;
    padding: 0.35rem 0.6rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
}

.tac-present { background: rgba(16,185,129,0.15); color: var(--success); }
.tac-absent  { background: rgba(239,68,68,0.15);  color: var(--error);  }

/* ---------- Progress Bar ---------- */
.tac-progress {
    height: 10px;
    background: rgba(255,255,255,0.08);
    border-radius: 10px;
    overflow: hidden;
    margin-top: 4px;
}

.tac-fill {
    height: 100%;
    transition: width 0.6s ease;
    border-radius: 10px;
}

.tac-percent-text {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

/* ---------- Empty State ---------- */
.tac-empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

</style>
</body>
</html>
