<?php
include("../Includes/TeacherHeader.php");
date_default_timezone_set('Asia/Kolkata');

if(!isset($_SESSION['teacher_id'])){
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}

include("../Includes/TeacherSidebar.php");

$teacher_id = (int)$_SESSION['teacher_id'];

$qry = "
    SELECT DISTINCT 
        c.class_id, c.class_name,
        c.semester_id, sem.semester_name,
        s.subject_id, s.subject_name
    FROM tbl_timetable t
    INNER JOIN tbl_class c ON t.class_id = c.class_id
    INNER JOIN tbl_semester sem ON c.semester_id = sem.semester_id
    INNER JOIN tbl_subject s ON t.subject_id = s.subject_id
    WHERE t.teacher_id = '$teacher_id'
      AND t.semester_id = c.semester_id
      AND c.is_completed = 0
    ORDER BY c.class_name, sem.semester_name, s.subject_name
";

$res = $con->query($qry);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Classes</title>

<link rel="stylesheet" href="../Assets/CSS/universal.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* PAGE WRAPPER */
.page-wrapper {
    padding: 2rem;
    min-height: calc(100vh - 120px);
}

/* CARD */
.myclass-card {
    max-width: 1200px;
    margin: 0 auto;
    background: var(--card-bg);
    border: 1px solid var(--border-glass);
    border-radius: 20px;
    padding: 1.8rem;
    backdrop-filter: blur(20px);
}

/* TITLE */
.myclass-title {
    font-size: 1.6rem;
    font-weight: 700;
    display:flex;
    align-items:center;
    gap:0.7rem;
    margin-bottom:1.5rem;
}
.myclass-title i {
    background: linear-gradient(135deg,var(--gradient-1),var(--gradient-2));
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

/* FIX TABLE — remove universal.css interference */
.myclass-table {
    width:100%;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
}

.myclass-table thead th {
    text-align:left;
    padding:12px 10px;
    background:rgba(255,255,255,0.06);
    color:var(--text-primary);
    font-weight:600;
    border-bottom:1px solid var(--border-glass);
}

.myclass-table tbody tr {
    background: rgba(255,255,255,0.04);
    transition:0.3s;
}
.myclass-table tbody tr:hover {
    background: rgba(255,255,255,0.10);
}

.myclass-table tbody td {
    padding:12px 10px;
    color:var(--text-secondary);
    border-bottom:1px solid rgba(255,255,255,0.05);
}

/* ACTION BUTTON */
.btn-view {
    padding: 0.45rem 0.9rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color:#fff;
    border-radius:10px;
    font-weight:600;
    text-decoration:none;
    transition:0.3s;
}
.btn-view:hover {
    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(102,126,234,0.3);
}

/* BACK BUTTON */
.back-btn { margin-top:1.5rem; }
.btn-back {
    padding: 0.6rem 1.2rem;
    border:2px solid var(--gradient-1);
    border-radius:12px;
    font-weight:600;
    color:var(--gradient-1);
    text-decoration:none;
}
.btn-back:hover {
    background:var(--gradient-1);
    color:#fff;
}

/* EMPTY STATE */
.no-data-wrapper {
    padding:3rem 0;
    text-align:center;
}
.no-data-icon {
    font-size:4rem;
    background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    opacity:0.7;
}
.no-data-title {
    margin-top:0.7rem;
    font-size:1.4rem;
    font-weight:700;
    color:var(--text-primary);
}
.no-data-text {
    font-size:1rem;
    color:var(--text-muted);
}
</style>

</head>
<body>
<main class="main-content">
    <div class="page-wrapper">
        <div class="myclass-card">

            <!-- TITLE -->
            <div class="myclass-title">
                <i class="fas fa-book-open"></i>
                My Classes
            </div>

            <!-- TABLE OR EMPTY STATE -->
            <?php if(!$res || $res->num_rows == 0): ?>

                <div class="no-data-wrapper">
                    <div class="no-data-icon"><i class="fas fa-calendar-times"></i></div>
                    <div class="no-data-title">No classes assigned</div>
                    <div class="no-data-text">You have no active timetable entries.</div>
                </div>

            <?php else: ?>
                <table class="myclass-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Class</th>
                            <th>Semester</th>
                            <th>Subject</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php 
                        $i = 0;
                        while($row = $res->fetch_assoc()):
                            $i++;
                        ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td><?= htmlspecialchars($row['semester_name']) ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td>
                                <a class="btn-view"
                                   href="TeacherClassAttendance.php?class_id=<?= $row['class_id'] ?>&subject_id=<?= $row['subject_id'] ?>&semester_id=<?= $row['semester_id'] ?>">
                                   <i class="fas fa-eye"></i> View Attendance
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </div>
</main>
<script src="../Assets/JS/universal.js"></script>
</body>
</html>