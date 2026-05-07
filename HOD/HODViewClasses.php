<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$hod_id = $_SESSION['hod_id'];
$department_id = $_SESSION['hod_department_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

/* === Handle class teacher update === */
if (isset($_POST['btn_update'])) {
    $class_id = intval($_POST['class_id']);
    $new_teacher_id = intval($_POST['new_teacher_id']);

    if ($new_teacher_id > 0 && $class_id > 0) {
        $updateQry = "UPDATE tbl_class SET teacher_id = $new_teacher_id WHERE class_id = $class_id";
        if ($con->query($updateQry)) {
            echo "<script>alert('Class teacher updated successfully!'); window.location='HODViewClasses.php?course_id=$course_id';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating class teacher.');</script>";
        }
    } else {
        echo "<script>alert('Please select a teacher.');</script>";
    }
}

/* === Course Info === */
$courseQry = "SELECT course_name FROM tbl_course WHERE course_id = $course_id";
$courseRes = $con->query($courseQry);
if ($courseRes->num_rows == 0) {
    echo "<script>alert('Invalid course selected.'); window.location='HODViewCourses.php';</script>";
    exit;
}
$courseData = $courseRes->fetch_assoc();
$course_name = $courseData['course_name'];

/* === HOD Info === */
$hodQry = "SELECT teacher_name, teacher_photo FROM tbl_teacher WHERE teacher_id = $hod_id";
$hodRes = $con->query($hodQry);
$hodData = $hodRes->fetch_assoc();
$hod_name = $hodData['teacher_name'];
$hod_photo = $hodData['teacher_photo'] ?? 'default.png';

/* === Active Classes === */
$classQry = "SELECT * FROM tbl_class WHERE course_id = $course_id AND is_completed = 0";
$classRes = $con->query($classQry);

/* === Eligible Teachers === */
$eligibleTeachers = [];
$tQry = "
    SELECT teacher_id, teacher_name 
    FROM tbl_teacher 
    WHERE department_id = $department_id 
      AND teacher_id NOT IN (SELECT teacher_id FROM tbl_class WHERE is_completed = 0)
";
$tRes = $con->query($tQry);
if ($tRes && $tRes->num_rows > 0) {
    while ($row = $tRes->fetch_assoc()) {
        $eligibleTeachers[$row['teacher_id']] = $row['teacher_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Classes - <?php echo htmlspecialchars($course_name); ?> | Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* === Universal Styled View Classes Page === */
.hod-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  background:rgba(255,255,255,0.08);
  padding:20px;
  border-radius:14px;
  backdrop-filter:blur(10px);
  box-shadow:0 4px 15px rgba(0,0,0,0.3);
  margin-bottom:25px;
}
.hod-info {
  display:flex;
  align-items:center;
  gap:15px;
}
.hod-info img {
  width:70px;
  height:70px;
  border-radius:50%;
  border:3px solid var(--gradient-1,#6366f1);
  object-fit:cover;
}
.hod-details h1 {
  margin:0;
  font-size:1.4rem;
  color:var(--text-primary);
}
.hod-details p {
  color:var(--text-secondary);
  margin:2px 0 0;
}
.btn-primary {
  background:linear-gradient(135deg,#3b82f6,#2563eb);
  color:#fff;
  padding:10px 16px;
  border-radius:8px;
  text-decoration:none;
  font-weight:600;
  transition:all .3s ease;
}
.btn-primary:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(59,130,246,.35); }

/* === Back Button === */
.back-btn {
  background: rgba(255,255,255,0.1);
  color: #fff;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all .3s ease;
}
.back-btn:hover {
  background: rgba(255,255,255,0.2);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(255,255,255,0.15);
}

/* === Classes Table === */
.classes-section h2 {
  color:var(--text-primary);
  font-size:1.3rem;
  margin-bottom:15px;
}
.classes-table {
  width:100%;
  border-collapse:collapse;
  border-radius:12px;
  overflow:hidden;
  background:rgba(255,255,255,0.06);
  backdrop-filter:blur(8px);
  box-shadow:0 4px 15px rgba(0,0,0,0.2);
}
.classes-table th, .classes-table td {
  padding:12px 15px;
  text-align:center;
}
.classes-table th {
  background:linear-gradient(135deg,var(--gradient-1,#6366f1),var(--gradient-2,#8b5cf6));
  color:#fff;
  font-weight:600;
}
.classes-table tr:nth-child(even) {
  background:rgba(255,255,255,0.04);
}
.classes-table td {
  color:var(--text-primary);
  font-size:.95rem;
}
.teacher-dropdown {
  padding:6px 10px;
  border-radius:8px;
  border:none;
  background:rgba(255,255,255,0.1);
  color:#fff;
  font-size:.9rem;
}
.teacher-dropdown option {
  background-color:#1e1e2f;
  color:#ffffff;
}
.teacher-dropdown:focus {
  outline:2px solid var(--gradient-1,#6366f1);
  background:rgba(255,255,255,0.15);
}
.btn.small-btn {
  background:linear-gradient(135deg,#10b981,#059669);
  color:#fff;
  border:none;
  padding:6px 10px;
  border-radius:6px;
  font-size:.85rem;
  cursor:pointer;
  transition:all .3s ease;
}
.btn.small-btn:hover {
  transform:translateY(-2px);
  box-shadow:0 4px 12px rgba(16,185,129,.3);
}
.empty-msg {
  color:var(--text-secondary);
  text-align:center;
  font-style:italic;
  margin-top:1rem;
}
.action-buttons {
  display:flex;
  justify-content:center;
  align-items:center;
  gap:6px;
}
</style>
</head>

<body>
<main class="main-content">
  <div class="glass-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 class="card-title"><i class="fas fa-chalkboard"></i> Classes in <?php echo htmlspecialchars($course_name); ?></h3>
      <a href="HODViewCourses.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Courses</a>
    </div>

    <header class="hod-header">
      <div class="hod-info">
        <img src="../Assets/Files/Teacher/<?php echo htmlspecialchars($hod_photo); ?>" alt="HOD Photo">
        <div class="hod-details">
          <h1><?php echo htmlspecialchars($hod_name); ?></h1>
          <p>Head of Department</p>
        </div>
      </div>
      <a href="HODCreateSubjects.php?course_id=<?php echo $course_id; ?>" class="btn-primary">
        <i class="fas fa-plus"></i> Create New Subjects
      </a>
    </header>

    <section class="classes-section">
      <h2>Active Classes</h2>
      <?php
      if ($classRes && $classRes->num_rows > 0) {
          echo '<table class="classes-table">';
          echo '<thead><tr>
                  <th>Class Name</th>
                  <th>Semester</th>
                  <th>Total Strength</th>
                  <th>Current Class Teacher</th>
                  <th>Change Class Teacher</th>
                  <th>Actions</th>
                </tr></thead><tbody>';
          while ($row = $classRes->fetch_assoc()) {
              $class_id = $row['class_id'];
              $teacher_id = $row['teacher_id'];

              $tQry = "SELECT teacher_name FROM tbl_teacher WHERE teacher_id = $teacher_id";
              $tRes = $con->query($tQry);
              $tName = $tRes && $tRes->num_rows > 0 ? $tRes->fetch_assoc()['teacher_name'] : 'N/A';

              $sQry = "SELECT COUNT(*) AS strength FROM tbl_student WHERE class_id = $class_id";
              $sRes = $con->query($sQry);
              $strength = $sRes ? $sRes->fetch_assoc()['strength'] : 0;

              echo '<tr>';
              echo '<td>' . htmlspecialchars($row['class_name']) . '</td>';
              echo '<td>' . htmlspecialchars($row['semester_id']) . '</td>';
              echo '<td>' . $strength . '</td>';
              echo '<td>' . htmlspecialchars($tName) . '</td>';

              echo '<td>
                      <form method="post" style="display:flex;align-items:center;justify-content:center;gap:8px;">
                        <input type="hidden" name="class_id" value="' . $class_id . '">
                        <select class="teacher-dropdown" name="new_teacher_id" required>
                          <option value="">Select Teacher</option>';
                            foreach ($eligibleTeachers as $tid => $tname) {
                                echo '<option value="' . $tid . '">' . htmlspecialchars($tname) . '</option>';
                            }
              echo '      </select>
                        <button type="submit" name="btn_update" class="btn small-btn">Update</button>
                      </form>
                    </td>';

              echo '<td class="action-buttons">
                      <a href="HODViewTimetable.php?class_id=' . $class_id . '" class="btn small-btn">
                        <i class="fas fa-eye"></i> View Timetable
                      </a>
                    </td>';
              echo '</tr>';
          }
          echo '</tbody></table>';
      } else {
          echo '<p class="empty-msg">No active classes found for this course.</p>';
      }
      ?>
    </section>
  </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
