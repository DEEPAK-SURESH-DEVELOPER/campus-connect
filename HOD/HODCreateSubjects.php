<?php
include("../Includes/HODHeader.php");

if (!isset($_SESSION['hod_id']) || !isset($_SESSION['hod_department_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php");
$hod_id = $_SESSION['hod_id'];
$department_id = $_SESSION['hod_department_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

/* === Fetch Course Info === */
$courseQry = "SELECT course_name, total_semesters FROM tbl_course WHERE course_id = $course_id";
$courseRes = $con->query($courseQry);
if ($courseRes->num_rows == 0) {
    echo "<script>alert('Invalid course selected.'); window.location='HODViewCourses.php';</script>";
    exit;
}
$courseData = $courseRes->fetch_assoc();
$course_name = $courseData['course_name'];
$total_semesters = $courseData['total_semesters'];

/* === HOD Info === */
$hodQry = "SELECT teacher_name, teacher_photo FROM tbl_teacher WHERE teacher_id = $hod_id";
$hodRes = $con->query($hodQry);
$hodData = $hodRes->fetch_assoc();
$hod_name = $hodData['teacher_name'];
$hod_photo = $hodData['teacher_photo'] ?? 'default.png';

/* === Handle Form Submission === */
if (isset($_POST['btn_submit'])) {
    $subject_name = trim($_POST['txt_subject_name']);
    $semester = intval($_POST['ddl_semester']);

    if ($subject_name != "" && $semester > 0) {
        $insertQry = "INSERT INTO tbl_subject (subject_name, course_id, semester_id) 
                      VALUES ('$subject_name', $course_id, $semester)";
        if ($con->query($insertQry)) {
            echo "<script>alert('Subject added successfully!'); window.location='HODCreateSubjects.php?course_id=$course_id';</script>";
        } else {
            echo "<script>alert('Error adding subject. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Please fill all fields.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Subjects - <?php echo htmlspecialchars($course_name); ?> | Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* === Universal Themed HOD Create Subjects === */
.hod-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  background:rgba(255,255,255,0.08);
  padding:20px;
  border-radius:14px;
  margin-bottom:25px;
  backdrop-filter:blur(10px);
  box-shadow:0 4px 15px rgba(0,0,0,0.3);
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

.course-title h2 {
  color:var(--text-primary);
  font-size:1.2rem;
  font-weight:600;
}

/* === Form Styling === */
.form-section {
  background:rgba(255,255,255,0.06);
  border-radius:14px;
  padding:20px;
  backdrop-filter:blur(8px);
  box-shadow:0 4px 15px rgba(0,0,0,0.2);
  margin-bottom:25px;
}
.subject-form {
  display:flex;
  flex-wrap:wrap;
  gap:20px;
}
.form-group {
  flex:1 1 45%;
  display:flex;
  flex-direction:column;
}
.form-group label {
  color:var(--text-secondary);
  margin-bottom:6px;
  font-weight:500;
}
.form-group input, .form-group select {
  padding:10px;
  border-radius:8px;
  border:none;
  background:rgba(255,255,255,0.1);
  color:#fff;
  font-size:.95rem;
}
/* ✅ Dropdown Fix for dark theme */
.form-group select option {
  background-color:#1e1e2f;
  color:#ffffff;
}
.form-group input:focus, .form-group select:focus {
  outline:2px solid var(--gradient-1,#6366f1);
  background:rgba(255,255,255,0.15);
}
.form-actions {
  display:flex;
  align-items:center;
  gap:12px;
  margin-top:15px;
}
.btn-primary {
  background:linear-gradient(135deg,#3b82f6,#2563eb);
  color:#fff;
  padding:10px 16px;
  border:none;
  border-radius:8px;
  font-weight:600;
  cursor:pointer;
  transition:all .3s ease;
}
.btn-primary:hover {
  transform:translateY(-2px);
  box-shadow:0 4px 15px rgba(59,130,246,.35);
}
.btn-secondary {
  background:rgba(255,255,255,0.1);
  color:#fff;
  padding:10px 16px;
  border-radius:8px;
  font-weight:500;
  text-decoration:none;
  transition:all .3s ease;
}
.btn-secondary:hover {
  background:rgba(255,255,255,0.2);
}

/* === Subject Table === */
.subject-list h3 {
  color:var(--text-primary);
  font-size:1.2rem;
  margin-bottom:15px;
}
.subject-table {
  width:100%;
  border-collapse:collapse;
  border-radius:12px;
  overflow:hidden;
  background:rgba(255,255,255,0.06);
  backdrop-filter:blur(8px);
  box-shadow:0 4px 15px rgba(0,0,0,0.2);
}
.subject-table th, .subject-table td {
  padding:12px 15px;
  text-align:center;
}
.subject-table th {
  background:linear-gradient(135deg,var(--gradient-1,#6366f1),var(--gradient-2,#8b5cf6));
  color:#fff;
  font-weight:600;
}
.subject-table tr:nth-child(even) {
  background:rgba(255,255,255,0.04);
}
.subject-table td {
  color:var(--text-primary);
  font-size:.95rem;
}
.empty-msg {
  text-align:center;
  color:var(--text-secondary);
  font-style:italic;
}
</style>
</head>

<body>

<main class="main-content">
  <div class="glass-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-book"></i> Create Subjects - <?php echo htmlspecialchars($course_name); ?></h3>
    </div>

    <header class="hod-header">
      <div class="hod-info">
        <img src="../Assets/Files/Teacher/<?php echo htmlspecialchars($hod_photo); ?>" alt="HOD Photo">
        <div class="hod-details">
          <h1><?php echo htmlspecialchars($hod_name); ?></h1>
          <p>Head of Department</p>
        </div>
      </div>
      <div class="course-title">
        <h2>Create Subjects for <?php echo htmlspecialchars($course_name); ?></h2>
      </div>
    </header>

    <section class="form-section">
      <form method="post" class="subject-form">
        <div class="form-group">
          <label for="txt_subject_name">Subject Name</label>
          <input type="text" name="txt_subject_name" id="txt_subject_name" placeholder="Enter Subject Name" required>
        </div>

        <div class="form-group">
          <label for="ddl_semester">Semester</label>
          <select name="ddl_semester" id="ddl_semester" required>
            <option value="">-- Select Semester --</option>
            <?php
            for ($i = 1; $i <= $total_semesters; $i++) {
                echo "<option value='$i'>Semester $i</option>";
            }
            ?>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" name="btn_submit" class="btn-primary">
            <i class="fas fa-plus"></i> Add Subject
          </button>
          <a href="HODViewClasses.php?course_id=<?php echo $course_id; ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Classes
          </a>
        </div>
      </form>
    </section>

    <section class="subject-list">
      <h3>Existing Subjects for <?php echo htmlspecialchars($course_name); ?></h3>
      <?php
      $subQry = "SELECT * FROM tbl_subject WHERE course_id = $course_id ORDER BY semester_id";
      $subRes = $con->query($subQry);
      if ($subRes->num_rows > 0) {
          echo "<table class='subject-table'>
                  <thead>
                    <tr><th>Subject Name</th><th>Semester</th></tr>
                  </thead><tbody>";
          while ($row = $subRes->fetch_assoc()) {
              echo "<tr>
                      <td>" . htmlspecialchars($row['subject_name']) . "</td>
                      <td>Semester " . htmlspecialchars($row['semester_id']) . "</td>
                    </tr>";
          }
          echo "</tbody></table>";
      } else {
          echo "<p class='empty-msg'>No subjects added yet.</p>";
      }
      ?>
    </section>
  </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
