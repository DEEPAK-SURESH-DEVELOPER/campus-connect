<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

if(isset($_GET['del_id'])){
    $del_id = (int)$_GET['del_id'];
    mysqli_query($con, "DELETE FROM tbl_teacher WHERE teacher_id='$del_id'");
    echo "<script>alert('Teacher deleted successfully!'); window.location='AdminTeacherList.php';</script>";
    exit;
}

// Fetch Departments
$deptQry = "SELECT department_id, department_name FROM tbl_department ORDER BY department_name ASC";
$deptRes = mysqli_query($con, $deptQry);
$selected_dept = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;

// Fetch Teachers
$teacherQry = "
    SELECT t.teacher_id, t.teacher_name, t.teacher_photo, d.designation_name, dep.department_name
    FROM tbl_teacher t
    JOIN tbl_designation d ON t.designation_id = d.designation_id
    JOIN tbl_department dep ON t.department_id = dep.department_id";
if($selected_dept > 0){
    $teacherQry .= " WHERE t.department_id='$selected_dept'";
}
$teacherQry .= " ORDER BY dep.department_name, t.teacher_name";
$teacherRes = mysqli_query($con, $teacherQry);

$page_title = "Teacher Management";
$breadcrumb = '<span>Administration</span> <i class="fas fa-chevron-right"></i> <span>Teachers</span>';
?>

<!-- INTERNAL STYLING -->
<style>
.main-content {
  padding: 1.5rem;
}

/* Header */
.page-header {
  margin-bottom: 1.5rem;
}
.page-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
}
.page-header p {
  color: var(--text-secondary);
  margin-top: 0.3rem;
}

/* Filter Dropdown */
.filter-container {
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}
.filter-container label {
  font-weight: 600;
  color: var(--text-secondary);
}
.filter-container select {
  background: rgba(255,255,255,0.08);
  border: 1px solid var(--border-glass);
  border-radius: 8px;
  color: var(--text-primary);
  padding: 0.6rem 1rem;
  outline: none;
  font-size: 0.9rem;
  backdrop-filter: blur(6px);
}
.filter-container select option {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

/* Table Card */
.table-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1.5rem 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
  color: var(--text-primary);
}
.table-card h3 {
  color: var(--gradient-1);
  margin-bottom: 1rem;
}

.glass-table {
  width: 100%;
  border-collapse: collapse;
}
.glass-table th, .glass-table td {
  padding: 0.9rem;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  color: var(--text-primary);
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
.no-data {
  text-align: center;
  color: var(--text-secondary);
  padding: 1rem;
}

/* Teacher Photos */
.teacher-photo {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--gradient-1);
  box-shadow: 0 0 10px rgba(102,126,234,0.3);
  transition: transform 0.3s ease;
}
.teacher-photo:hover {
  transform: scale(1.1);
}

/* Action Buttons */
.action-link {
  margin: 0 5px;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.85rem;
  transition: 0.3s ease;
}
.action-link:hover {
  opacity: 0.85;
}
.action-link.view {
  background: rgba(255,255,255,0.08);
  color: var(--gradient-1);
  border: 1px solid var(--border-glass);
}
.action-link.delete-link {
  background: rgba(239,68,68,0.1);
  color: #f87171;
  border: 1px solid rgba(239,68,68,0.3);
}
.action-link.delete-link:hover {
  background: rgba(239,68,68,0.15);
}

/* Responsive Adjustments */
@media(max-width:768px){
  .filter-container {
    flex-direction: column;
    align-items: flex-start;
  }
  .glass-table th, .glass-table td {
    font-size: 0.8rem;
    padding: 0.6rem;
  }
  .teacher-photo {
    width: 50px;
    height: 50px;
  }
}
</style>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="page-header">
    <h2><i class="fas fa-chalkboard-teacher"></i> All Teachers</h2>
    <p>Manage and view all registered teachers in the system</p>
  </div>

  <!-- Filter by Department -->
  <div class="filter-container">
    <form method="GET" action="">
      <label for="department">Select Department:</label>
      <select name="department_id" id="department" onchange="this.form.submit()">
        <option value="0">All Departments</option>
        <?php
        if($deptRes && mysqli_num_rows($deptRes) > 0){
            while($dept = mysqli_fetch_assoc($deptRes)){
                $selected = ($selected_dept == $dept['department_id']) ? "selected" : "";
                echo "<option value='{$dept['department_id']}' $selected>".htmlspecialchars($dept['department_name'])."</option>";
            }
        }
        ?>
      </select>
    </form>
  </div>

  <!-- Teacher Table -->
  <div class="table-card">
    <h3><i class="fas fa-users"></i> Teachers List</h3>
    <table class="glass-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Photo</th>
          <th>Name</th>
          <th>Designation</th>
          <th>Department</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $count = 1;
        if($teacherRes && mysqli_num_rows($teacherRes) > 0){
            while($row = mysqli_fetch_assoc($teacherRes)){
                $tid = $row['teacher_id'];
                $photo = !empty($row['teacher_photo']) ? $row['teacher_photo'] : 'default.png';
                $name = htmlspecialchars($row['teacher_name']);
                $designation = htmlspecialchars($row['designation_name']);
                $dept_name = htmlspecialchars($row['department_name']);
                echo "
                <tr>
                    <td>$count</td>
                    <td><img src='../Assets/Files/Teacher/".htmlspecialchars($photo)."' class='teacher-photo' alt='Photo'></td>
                    <td>$name</td>
                    <td>$designation</td>
                    <td>$dept_name</td>
                    <td>
                        <a href='TeacherProfileAdmin.php?teacher_id=$tid' class='action-link view'><i class='fas fa-id-card'></i> Profile</a>
                        <a href='AssignSubjects.php?teacher_id=$tid' class='action-link view'><i class='fas fa-book'></i> Assign</a>
                        <a href='AdminTeacherList.php?del_id=$tid' class='action-link delete-link' onclick='return confirm(\"Are you sure you want to delete this teacher?\");'><i class='fas fa-trash'></i> Delete</a>
                    </td>
                </tr>";
                $count++;
            }
        } else {
            echo "<tr><td colspan='6' class='no-data'>No teachers found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
