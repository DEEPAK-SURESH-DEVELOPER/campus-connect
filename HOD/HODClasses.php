<?php
include("../Includes/HODHeader.php"); 

// --- Robust HOD login check ---
if (!isset($_SESSION['hod_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$teacher_id = (int)$_SESSION['hod_id'];
$page_title = "My Classes";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?> | Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
.table-wrapper { overflow-x:auto; margin-top:1rem; }
.glass-table { width:100%; border-collapse:collapse; border-radius:12px; overflow:hidden; }
.glass-table th, .glass-table td { padding:12px 14px; border-bottom:1px solid rgba(255,255,255,0.06); }
.glass-table th {
  background:linear-gradient(135deg,var(--gradient-1,#6366f1),var(--gradient-2,#8b5cf6));
  color:#fff;font-weight:600;text-align:center;
}
.glass-table td { text-align:center; color:var(--text-primary,#e6eef8); }
.btn-view {
  background:linear-gradient(135deg,#10b981,#059669);
  color:#fff;padding:6px 14px;border-radius:8px;
  text-decoration:none;font-weight:500;display:inline-block;transition:.3s;
}
.btn-view:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(16,185,129,0.25); }
.no-data { text-align:center; color:var(--text-muted,#cbd5e1); padding:1.5rem; }
.back-link {
  text-decoration:none; background:rgba(255,255,255,0.1);
  padding:8px 14px; border-radius:8px; color:#fff; font-size:0.9rem; transition:.3s;
}
.back-link:hover { background:rgba(255,255,255,0.2); }
.debug { margin-top:1rem; color:#f87171; font-family:monospace; background:rgba(248,113,113,0.06); padding:10px; border-radius:8px; }
</style>
</head>

<body>

<main class="main-content">
  <div class="glass-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 class="card-title"><i class="fas fa-chalkboard-teacher"></i> My Classes</h3>
      <a href="HODHome.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php
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
    if(!$res){
        echo "<div class='debug'>SQL ERROR: " . htmlspecialchars($con->error) . "</div>";
    } elseif($res->num_rows == 0){
        echo "<div class='no-data'>No classes assigned to you in the timetable.</div>";
    } else {
        echo '<div class="table-wrapper">';
        echo '<table class="glass-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Semester</th>
                    <th>Subject</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>';
        $i=0;
        while($row=$res->fetch_assoc()){
            echo '<tr>
                    <td>'.(++$i).'</td>
                    <td>'.htmlspecialchars($row['class_name']).'</td>
                    <td>'.htmlspecialchars($row['semester_name']).'</td>
                    <td>'.htmlspecialchars($row['subject_name']).'</td>
                    <td><a href="HODClassAttendance.php?class_id='.$row['class_id'].'&subject_id='.$row['subject_id'].'&semester_id='.$row['semester_id'].'" class="btn-view"><i class="fas fa-eye"></i> View Attendance</a></td>
                  </tr>';
        }
        echo '</tbody></table></div>';
    }
    ?>
  </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
