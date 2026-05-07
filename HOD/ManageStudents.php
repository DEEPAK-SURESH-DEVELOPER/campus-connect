<?php 
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$hod_id = $_SESSION['hod_id'];
$department_id = $_SESSION['hod_department_id'];

// --- Fetch department name ---
$deptQry = $con->query("SELECT department_name FROM tbl_department WHERE department_id = '$department_id'");
$deptRow = $deptQry->fetch_assoc();
$department_name = $deptRow['department_name'] ?? 'Department';

// --- Page Meta ---
$page_title = "Manage Students";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> 
               <span>Manage Students</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?> - Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<!-- JQuery -->
<script src="../Assets/JQ/jQuery.js"></script>
</head>
<body>
<main class="main-content">

    <!-- Header Section -->
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Manage Students</h3>
        </div>

        <!-- Filter Controls -->
        <div class="filter-container" style="display:flex;flex-wrap:wrap;gap:1.5rem;align-items:flex-end;margin-top:1rem;">
            
            <div class="form-group">
                <label class="form-label">Department:</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars($department_name); ?>" readonly>
            </div>

            <div class="form-group">
                <label class="form-label" for="sel_course">Course:</label>
                <select name="sel_course" id="sel_course" class="form-control" onchange="getClasses(this.value)">
                    <option value="">Select Course</option>
                    <?php
                    $resCourse = $con->query("SELECT * FROM tbl_course WHERE department_id = '$department_id'");
                    while($course = $resCourse->fetch_assoc()){
                        echo "<option value='".$course['course_id']."'>".htmlspecialchars($course['course_name'])."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="sel_class">Class:</label>
                <select name="sel_class" id="sel_class" class="form-control" onchange="getStudents(this.value)">
                    <option value="">Select Class</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Student Table Section -->
    <div id="studentTable" class="glass-card mt-3">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-users"></i></div>
            <div class="empty-state-title">No Data Loaded</div>
            <div class="empty-state-text">Select a course and class to view students.</div>
        </div>
    </div>

</main>
<style>
/* === Inline Styling for Filter Dropdowns & Inputs === */
.form-control {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #e5e7eb;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.95rem;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    width: 230px;
    outline: none;
}

/* On focus or hover */
.form-control:focus, 
.form-control:hover {
    border-color: var(--gradient-1, #6366f1);
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
    background: rgba(255, 255, 255, 0.15);
}

/* Dropdown arrow and text color fix */
.form-control option {
    background-color: #1f2937;
    color: #f9fafb;
}

/* Label style */
.form-label {
    color: var(--text-primary, #f9fafb);
    font-weight: 500;
    margin-bottom: 6px;
    display: block;
}

/* Readonly Department Input */
input[readonly].form-control {
    color: #cbd5e1;
    font-weight: 500;
    background: rgba(255, 255, 255, 0.05);
    cursor: not-allowed;
}

/* Adjust spacing and flex alignment for filters */
.filter-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    align-items: flex-end;
    margin-top: 1rem;
}
</style>


<!-- Universal JS -->
<script src="../Assets/JS/universal.js"></script>

<!-- AJAX Functions -->
<script>
function getClasses(courseId){
    $("#sel_class").html('<option value="">Select Class</option>');
    $("#studentTable").html('<div class="loader">Loading classes...</div>');
    if(courseId !== ""){
        $.ajax({
            url: "../Assets/AjaxPages/AjaxClass.php",
            method: "GET",
            data: { course_id: courseId },
            success: function(data){
                $("#sel_class").html(data);
                $("#studentTable").html('<div class="empty-state"><div class="empty-state-icon"><i class="fas fa-users"></i></div><div class="empty-state-title">Select a class to view students.</div></div>');
            }
        });
    } else {
        $("#studentTable").html('<div class="empty-state"><div class="empty-state-icon"><i class="fas fa-info-circle"></i></div><div class="empty-state-title">Please select a course.</div></div>');
    }
}

function getStudents(classId){
    if(classId === ""){
        $("#studentTable").html('<div class="empty-state"><div class="empty-state-icon"><i class="fas fa-users"></i></div><div class="empty-state-title">Select a class to view students.</div></div>');
        return;
    }
    $("#studentTable").html('<div class="loader">Loading students...</div>');
    $.ajax({
        url: "../Assets/AjaxPages/AjaxStudents.php",
        method: "GET",
        data: { class_id: classId },
        success: function(data){
            $("#studentTable").html(data);
        }
    });
}

function deleteStudent(id){
    if(confirm("Are you sure you want to delete this student?")){
        $.ajax({
            url: "../Assets/AjaxPages/AjaxStudents.php",
            method: "GET",
            data: { class_id: $("#sel_class").val(), delID: id },
            success: function(data){
                $("#studentTable").html(data);
            }
        });
    }
}
</script>

</body>
</html>
