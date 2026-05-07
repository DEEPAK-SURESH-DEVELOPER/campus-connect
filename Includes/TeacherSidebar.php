<?php
// TeacherSidebar.php
if(!isset($_SESSION)) session_start();

$is_class_teacher = isset($_SESSION['is_class_teacher']) && $_SESSION['is_class_teacher'];

$navigation = [
    ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'TeacherHome.php'],
    ['icon' => 'calendar-alt', 'label' => 'My Timetable', 'url' => 'TeacherTimetable.php'],
    ['icon' => 'calendar-check', 'label' => 'Mark Attendance', 'url' => 'MarkAttendance.php'],
    ['icon' => 'clipboard-list', 'label' => 'My Classes', 'url' => 'TeacherClasses.php'],
    ['icon' => 'book', 'label' => 'Send Complaint', 'url' => 'TeacherSendComplaint.php'],
    ['icon' => 'tasks', 'label' => 'View My Complaints', 'url' => 'TeacherViewComplaint.php'],
    ['icon' => 'bullhorn', 'label' => 'Send Notices', 'url' => 'TeacherClassNotice.php'],
    ['icon' => 'bullhorn', 'label' => 'View Notices', 'url' => 'TeacherViewNotice.php'],
];

if($is_class_teacher) {
    $navigation[] = ['icon' => 'users-cog', 'label' => 'Register Student', 'url' => 'StudentRegistration.php'];
    $navigation[] = ['icon' => 'exclamation-circle', 'label' => 'Class Complaints', 'url' => 'ClassTeacherHandleComplaints.php'];
    $navigation[] = ['icon' => 'user-graduate', 'label' => 'Students', 'url' => 'StudentList.php'];
}

$navigation[] = ['icon' => 'user-circle', 'label' => 'My Profile', 'url' => 'TeacherProfile.php'];

$current_page = basename($_SERVER['PHP_SELF']);
include("SidebarLayout.php");
?>
