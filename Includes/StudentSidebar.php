<?php
// StudentSidebar.php
if(!isset($_SESSION)) session_start();

$navigation = [
    ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'StudentHome.php'],
    ['icon' => 'calendar-alt', 'label' => 'My Timetable', 'url' => 'StudentViewTimetable.php'],
    ['icon' => 'calendar-check', 'label' => 'My Attendance', 'url' => 'ViewAttendance.php'],
    ['icon' => 'bullhorn', 'label' => 'Notices', 'url' => 'StudentViewNotice.php'],
    ['icon' => 'exclamation-circle', 'label' => 'Submit Complaint', 'url' => 'StudentSendComplaint.php'],
    ['icon' => 'history', 'label' => 'My Complaints', 'url' => 'StudentViewComplaint.php'],
    ['icon' => 'user-circle', 'label' => 'My Profile', 'url' => 'StudentProfile.php'],
];

$current_page = basename($_SERVER['PHP_SELF']);
include("SidebarLayout.php");
?>
