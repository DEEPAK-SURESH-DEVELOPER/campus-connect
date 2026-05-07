<?php
include("../Connection/Connection.php");

if(isset($_GET['course_id']))
{
    $course_id = $_GET['course_id'];

    $qry = "SELECT subject_id, subject_name FROM tbl_subject WHERE course_id='".$course_id."' ORDER BY subject_name ASC";
    $res = $con->query($qry);

    echo '<option value="">Select Subjects</option>';
    while($row = $res->fetch_assoc())
    {
        echo '<option value="'.$row['subject_id'].'">'.$row['subject_name'].'</option>';
    }
}
?>
