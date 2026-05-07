<?php
include("../Connection/Connection.php");

if(isset($_GET['department_id']) && $_GET['department_id'] != "")
{
    $dept_id = $_GET['department_id'];
    $selCourse = $con->query("SELECT * FROM tbl_course WHERE department_id='".$dept_id."'");
    echo '<option value="">Select Course</option>';
    while($course = $selCourse->fetch_assoc())
    {
        echo '<option value="'.$course['course_id'].'">'.$course['course_name'].'</option>';
    }
}
?>
