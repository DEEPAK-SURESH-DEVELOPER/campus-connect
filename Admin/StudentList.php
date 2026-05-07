<?php 
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

$page_title = "Student Management";
$breadcrumb = '<span>Administration</span> <i class="fas fa-chevron-right"></i> <span>Students</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student List - Admin</title>
<script src="../Assets/JQ/jQuery.js"></script>

<!-- INTERNAL FIXED CSS -->
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

/* Filter section */
.filter-container {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1rem 1.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.filter-container label {
  display: block;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.3rem;
}
.filter-container select {
  width: 220px;
  background: rgba(255,255,255,0.08);
  border: 1px solid var(--border-glass);
  border-radius: 8px;
  color: var(--text-primary);
  padding: 0.65rem 0.9rem;
  outline: none;
  font-size: 0.9rem;
  backdrop-filter: blur(6px);
  transition: 0.3s;
}
.filter-container select:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 5px rgba(102,126,234,0.4);
}
.filter-container select option {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

/* Table container (AJAX content area) */
#studentTable {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1.5rem 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.25);
  min-height: 100px;
  transition: all 0.3s ease;
  overflow-x: auto;
}
#studentTable table {
  width: 100%;
  border-collapse: collapse;
}
#studentTable th, #studentTable td {
  padding: 0.8rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  text-align: center;
  color: var(--text-primary);
  vertical-align: middle;
}
#studentTable th {
  background: rgba(255,255,255,0.08);
  color: var(--gradient-1);
  font-weight: 600;
}
#studentTable tr:hover {
  background: rgba(255,255,255,0.05);
  transition: 0.3s ease;
}

/* Student photo fix */
#studentTable img,
.student-photo {
  width: 60px !important;
  height: 60px !important;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid var(--gradient-1);
  box-shadow: 0 0 10px rgba(102,126,234,0.3);
  transition: transform 0.3s ease;
}
#studentTable img:hover {
  transform: scale(1.1);
}

/* 🌈 Standardized Action Buttons */
.action-buttons {
  display: flex;
  justify-content: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.action-btn {
  display: inline-block;
  padding: 0.45rem 0.9rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  text-decoration: none;
  letter-spacing: 0.3px;
  transition: all 0.3s ease;
  border: 1px solid var(--border-glass);
  backdrop-filter: blur(6px);
}

/* Profile (Blue) */
.action-btn.profile {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: #fff;
}
.action-btn.profile:hover {
  opacity: 0.9;
  box-shadow: 0 0 10px rgba(99,102,241,0.6);
}

/* Report (Green) */
.action-btn.report {
  background: linear-gradient(135deg, #22c55e, #10b981);
  color: #fff;
}
.action-btn.report:hover {
  opacity: 0.9;
  box-shadow: 0 0 10px rgba(34,197,94,0.6);
}

/* Delete (Red) */
.action-btn.delete {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
}
.action-btn.delete:hover {
  opacity: 0.9;
  box-shadow: 0 0 10px rgba(239,68,68,0.6);
}

/* Responsive tweaks */
@media(max-width:768px){
  .filter-container {
    flex-direction: column;
  }
  .filter-container select {
    width: 100%;
  }
  #studentTable img {
    width: 50px !important;
    height: 50px !important;
  }
}

</style>
</head>

<body>
<div class="main-content">
  <div class="page-header">
    <h2><i class="fas fa-user-graduate"></i> Student Management</h2>
    <p>View and manage all students department-wise</p>
  </div>

  <!-- Filter Dropdowns -->
  <div class="filter-container">
    <div>
      <label for="sel_department">Department:</label>
      <select name="sel_department" id="sel_department" onchange="getCourses(this.value)">
        <option value="">Select Department</option>
        <?php
        $resDept = $con->query("SELECT * FROM tbl_department ORDER BY department_name ASC");
        while($dept = $resDept->fetch_assoc()){
            echo "<option value='".$dept['department_id']."'>".$dept['department_name']."</option>";
        }
        ?>
      </select>
    </div>

    <div>
      <label for="sel_course">Course:</label>
      <select name="sel_course" id="sel_course" onchange="getClasses(this.value)">
        <option value="">Select Course</option>
      </select>
    </div>

    <div>
      <label for="sel_class">Class:</label>
      <select name="sel_class" id="sel_class" onchange="getStudents(this.value)">
        <option value="">Select Class</option>
      </select>
    </div>
  </div>

  <!-- Student Table (AJAX content) -->
  <div id="studentTable">
    <p style="text-align:center; color:var(--text-secondary);">Select a class to view students.</p>
  </div>
</div>

<script>
// Get courses for selected department
function getCourses(deptId){
    $.ajax({
        url: "../Assets/AjaxPages/AjaxCourse.php",
        method: "GET",
        data: { department_id: deptId },
        success: function(data){
            $("#sel_course").html(data);
            $("#sel_class").html('<option value="">Select Class</option>'); 
            $("#studentTable").html('<p style="text-align:center;color:var(--text-secondary);">Select a class to view students.</p>');
        }
    });
}

// Get classes for selected course
function getClasses(courseId){
    $.ajax({
        url: "../Assets/AjaxPages/AjaxClass.php",
        method: "GET",
        data: { course_id: courseId },
        success: function(data){
            $("#sel_class").html(data);
            $("#studentTable").html('<p style="text-align:center;color:var(--text-secondary);">Select a class to view students.</p>');
        }
    });
}

// Get students for selected class
function getStudents(classId){
    if(classId == ""){
        $("#studentTable").html('<p style="text-align:center;color:var(--text-secondary);">Select a class to view students.</p>');
        return;
    }
    $.ajax({
        url: "../Assets/AjaxPages/AjaxStudentsByClass.php",
        method: "GET",
        data: { class_id: classId },
        success: function(data){
            $("#studentTable").html(data);
        }
    });
}

// Delete student
function deleteStudent(id){
    if(confirm("Are you sure you want to delete this student?")){
        $.ajax({
            url: "../Assets/AjaxPages/AjaxStudentsByClass.php",
            method: "GET",
            data: { class_id: $("#sel_class").val(), delID: id },
            success: function(data){
                $("#studentTable").html(data);
            }
        });
    }
}
</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
