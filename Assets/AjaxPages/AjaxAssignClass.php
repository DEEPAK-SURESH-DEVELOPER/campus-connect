<?php
session_start();
include("../Connection/Connection.php");
header('Content-Type: application/json');

// optional admin check
if(!isset($_SESSION['admin_id'])){
    echo json_encode(['success'=>false,'error'=>'Not authorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['success'=>false,'error'=>'Invalid request method']);
    exit;
}

$class_id = isset($_POST['class_id']) ? $con->real_escape_string($_POST['class_id']) : '';
$teacher_id = isset($_POST['teacher_id']) ? $con->real_escape_string($_POST['teacher_id']) : '';
$acyear_id = isset($_POST['acyear_id']) ? $con->real_escape_string($_POST['acyear_id']) : '';

if(!$class_id || !$teacher_id || !$acyear_id){
    echo json_encode(['success'=>false,'error'=>'Missing parameters']);
    exit;
}

// Verify class belongs to academic year
$chk = $con->query("SELECT teacher_id FROM tbl_class WHERE class_id='$class_id' AND acyear_id='$acyear_id'");
if($chk->num_rows == 0){
    echo json_encode(['success'=>false,'error'=>'Class not found for selected academic year']);
    exit;
}

$row = $chk->fetch_assoc();
if(!empty($row['teacher_id'])){
    echo json_encode(['success'=>false,'error'=>'This class already has a teacher. Reassignment via this quick action is disabled.']);
    exit;
}

// perform update
$upd = $con->query("UPDATE tbl_class SET teacher_id='".$teacher_id."' WHERE class_id='".$class_id."'");
if($upd){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>$con->error]);
}
?>
