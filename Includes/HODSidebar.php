<?php
// HODSidebar.php
if(!isset($_SESSION)) session_start();

$navigation = [
    ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'HODHome.php'],
    ['icon' => 'chalkboard-teacher', 'label' => 'Teachers', 'url' => 'TeacherList.php'],
    ['icon' => 'user-graduate', 'label' => 'Students', 'url' => 'ManageStudents.php'],
    ['icon' => 'users', 'label' => 'Classes', 'url' => 'HODClasses.php'],
    ['icon' => 'clock', 'label' => 'Timetable', 'url' => 'HODTimetable.php'],
    ['icon' => 'calendar-check', 'label' => 'Mark Attendance', 'url' => 'MarkAttendance.php'],
    ['icon' => 'bullhorn', 'label' => 'Send Notices', 'url' => 'HODNotice.php'],
    ['icon' => 'exclamation-circle', 'label' => 'Complaints', 'url' => 'HODHandleComplaints.php'],
    ['icon' => 'book', 'label' => 'View Courses', 'url' => 'HODViewCourses.php'],
    ['icon' => 'chart-bar', 'label' => 'View Notices', 'url' => 'HODViewNotice.php'],
    ['icon' => 'user-circle', 'label' => 'My Profile', 'url' => 'HODProfile.php'],
];

$current_page = basename($_SERVER['PHP_SELF']);
include("SidebarLayout.php");
?>
