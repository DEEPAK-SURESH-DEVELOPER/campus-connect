<?php 
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

$class_id = intval($_GET['class_id']);
$department_id = intval($_GET['department_id']);
$returnUrl = "ViewStudents.php?class_id=$class_id&department_id=$department_id";

if(isset($_GET['del_id'])){
    $del_id = intval($_GET['del_id']);
    if($del_id > 0){
        $con->query("DELETE FROM tbl_student WHERE student_id=".$del_id);
        echo "<script>alert('Student deleted successfully'); window.location='ViewStudents.php?class_id=".$class_id."&department_id=".$department_id."';</script>";
        exit;
    }
}

$classRes = $con->query("SELECT * FROM tbl_class WHERE class_id=".$class_id);
$class = $classRes->fetch_assoc();

$page_title = "View Students";
$breadcrumb = '<span>Classes</span> <i class="fas fa-chevron-right"></i> <span>Students</span>';
?>

<!-- INTERNAL PAGE STYLE -->
<style>
.main-content {
  padding: 1.5rem;
}

.student-photo {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border-glass);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.student-photo:hover {
  transform: scale(1.1);
  box-shadow: 0 0 15px rgba(102,126,234,0.5);
}

.glass-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  overflow: hidden;
  backdrop-filter: blur(10px);
}
.glass-table th, .glass-table td {
  padding: 0.9rem 1rem;
  text-align: center;
  color: var(--text-primary);
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
.glass-table th {
  background: rgba(255,255,255,0.08);
  color: var(--gradient-1);
  font-weight: 600;
}
.glass-table tr:hover {
  background: rgba(255,255,255,0.05);
  transition: 0.3s ease;
}

.btn-group {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.page-header h2 {
  color: var(--text-primary);
  font-size: 1.4rem;
  font-weight: 600;
}
.empty-row {
  text-align: center;
  color: var(--text-secondary);
  padding: 1.5rem;
}
</style>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="glass-card">
    <div class="page-header">
      <div>
        <h2><i class="fas fa-users"></i> Students of <?php echo htmlspecialchars($class['class_name']); ?></h2>
      </div>
      <div class="d-flex gap-1">
        <a href="ClassList.php?course_id=<?php echo $class['course_id']; ?>&department_id=<?php echo $department_id; ?>" class="action-btn">
          <i class="fas fa-arrow-left"></i> Back to Class
        </a>
        <a href="AddStudent.php?class_id=<?php echo $class_id; ?>&department_id=<?php echo $department_id; ?>" class="btn-primary">
          <i class="fas fa-user-plus"></i> Add Student
        </a>
      </div>
    </div>

    <table class="glass-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Photo</th>
          <th>Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $i = 0;
      $studentRes = $con->query("SELECT * FROM tbl_student WHERE class_id=".$class_id);
      if($studentRes->num_rows > 0){
          while($student = $studentRes->fetch_assoc()){
              $i++;
      ?>
        <tr>
          <td><?php echo $i; ?></td>
          <td><img src="../Assets/Files/Student/<?php echo htmlspecialchars($student['student_photo']); ?>" class="student-photo" alt="Photo"></td>
          <td><?php echo htmlspecialchars($student['student_name']); ?></td>
          <td>
            <div class="btn-group">
              <a href="StudentProfileAdmin.php?student_id=<?php echo $student['student_id']; ?>&department_id=<?php echo $department_id; ?>&return_url=<?php echo urlencode($returnUrl); ?>" class="action-btn view">
                <i class="fas fa-id-card"></i> Profile
              </a>
              <a href="ViewStudents.php?del_id=<?php echo $student['student_id']; ?>&class_id=<?php echo $class_id; ?>&department_id=<?php echo $department_id; ?>" 
                 class="action-btn delete"
                 onclick="return confirm('Are you sure you want to delete this student?');">
                <i class="fas fa-trash-alt"></i> Delete
              </a>
            </div>
          </td>
        </tr>
      <?php
          }
      } else {
          echo '<tr><td colspan="4" class="empty-row">No students found in this class.</td></tr>';
      }
      ?>
      </tbody>
    </table>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
