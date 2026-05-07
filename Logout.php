<?php
session_start();

// Destroy the correct session based on who is logged in
if (isset($_SESSION['admin_id'])) {
    session_unset();
    session_destroy();
    header("Location: Guest/Login.php");
    exit;

} elseif (isset($_SESSION['hod_id'])) {
    session_unset();
    session_destroy();
    header("Location:  Guest/Login.php");
    exit;

} elseif (isset($_SESSION['teacher_id'])) {
    session_unset();
    session_destroy();
    header("Location:  Guest/Login.php");
    exit;

} elseif (isset($_SESSION['student_id'])) {
    session_unset();
    session_destroy();
    header("Location:  Guest/Login.php");
    exit;

} else {
    // If somehow no one is logged in
    header("Location: Guest/Login.php");
    exit;
}
?>
