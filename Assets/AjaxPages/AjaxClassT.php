<?php
include("../Connection/Connection.php");

$course_id = isset($_GET['course_id']) ? $con->real_escape_string($_GET['course_id']) : '';
$acyear_id = isset($_GET['acyear_id']) ? $con->real_escape_string($_GET['acyear_id']) : '';

// Basic response
echo '<option value="">Select Class</option>';

if($course_id != '' && $acyear_id != '')
{
    $res = $con->query("SELECT cl.class_id, cl.class_name, cl.teacher_id, t.teacher_name
                        FROM tbl_class cl
                        LEFT JOIN tbl_teacher t ON cl.teacher_id = t.teacher_id
                        WHERE cl.course_id='".$course_id."' AND cl.acyear_id='".$acyear_id."'
                        ORDER BY cl.class_name");
    while($r = $res->fetch_assoc()){
        if(!empty($r['teacher_id'])){
            // disabled option for assigned classes
            $label = $r['class_name'] . ' (Assigned: ' . htmlspecialchars($r['teacher_name']) . ')';
            echo '<option value="'.$r['class_id'].'" disabled>'.$label.'</option>';
        } else {
            echo '<option value="'.$r['class_id'].'">'.htmlspecialchars($r['class_name']).'</option>';
        }
    }
}
?>
