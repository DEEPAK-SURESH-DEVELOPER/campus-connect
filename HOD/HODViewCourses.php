<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$hod_id = $_SESSION['hod_id'];
$department_id = $_SESSION['hod_department_id'];

// --- HOD info ---
$hodQry = "SELECT teacher_name, teacher_photo FROM tbl_teacher WHERE teacher_id = $hod_id";
$hodRes = $con->query($hodQry);
$hodData = $hodRes->fetch_assoc();
$hod_name = $hodData['teacher_name'];
$hod_photo = $hodData['teacher_photo'] ?? 'default.png';

// --- Department info ---
$deptQry = "SELECT department_name FROM tbl_department WHERE department_id = $department_id";
$deptRes = $con->query($deptQry);
$deptData = $deptRes->fetch_assoc();
$dept_name = $deptData['department_name'];

// --- Courses ---
$courseQry = "SELECT * FROM tbl_course WHERE department_id = $department_id";
$courseRes = $con->query($courseQry);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Courses - HOD | Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* === HOD View Courses Styling (Universal Match) === */
.hod-header {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.08);
  padding: 20px 25px;
  border-radius: 14px;
  margin-bottom: 25px;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.hod-info {
  display: flex;
  align-items: center;
  gap: 20px;
}
.hod-info img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 3px solid var(--gradient-1, #6366f1);
  object-fit: cover;
}
.hod-details h1 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.5rem;
}
.hod-details p {
  color: var(--text-secondary);
  margin: 4px 0 0;
}

/* === Courses Section === */
.courses-section h2 {
  color: var(--text-primary);
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 15px;
}
.course-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 15px;
}

.course-card {
  background: rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  transition: all .3s ease;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 25px rgba(0,0,0,0.35);
}
.course-card h3 {
  color: var(--text-primary);
  font-size: 1.2rem;
  margin-bottom: 10px;
}
.course-card p {
  color: var(--text-secondary);
  margin-bottom: 15px;
}
.view-btn {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #fff;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.3s ease;
}
.view-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(37,99,235,0.35);
}

.empty-msg {
  text-align: center;
  color: var(--text-secondary);
  font-style: italic;
  margin-top: 20px;
}
</style>
</head>

<body>
<main class="main-content">
  <div class="glass-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-book-open"></i> Courses Under Your Department</h3>
    </div>

    <header class="hod-header">
      <div class="hod-info">
        <img src="../Assets/Files/Teacher/<?php echo htmlspecialchars($hod_photo); ?>" alt="HOD Photo">
        <div class="hod-details">
          <h1><?php echo htmlspecialchars($hod_name); ?></h1>
          <p>Head of Department — <?php echo htmlspecialchars($dept_name); ?></p>
        </div>
      </div>
    </header>

    <section class="courses-section">
      <div class="course-grid">
        <?php
        if ($courseRes && $courseRes->num_rows > 0) {
          while ($row = $courseRes->fetch_assoc()) {
            echo '<div class="course-card">';
            echo '<h3>' . htmlspecialchars($row['course_name']) . '</h3>';
            echo '<p><strong>Total Semesters:</strong> ' . htmlspecialchars($row['total_semesters']) . '</p>';
            echo '<a href="HODViewClasses.php?course_id=' . $row['course_id'] . '" class="view-btn"><i class="fas fa-eye"></i> View Classes</a>';
            echo '</div>';
          }
        } else {
          echo '<p class="empty-msg">No courses found in your department.</p>';
        }
        ?>
      </div>
    </section>
  </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
