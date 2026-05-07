<?php
include("../Connection/Connection.php");

if(isset($_GET['course_id']) && $_GET['course_id'] != "")
{
    $course_id = $_GET['course_id'];
    $selClass = $con->query("SELECT * FROM tbl_class WHERE course_id='".$course_id."' AND is_completed = 0");
    echo '<option value="">Select Class</option>';
    while($class = $selClass->fetch_assoc())
    {
        echo '<option value="'.$class['class_id'].'">'.$class['class_name'].'</option>';
    }
}
?>
