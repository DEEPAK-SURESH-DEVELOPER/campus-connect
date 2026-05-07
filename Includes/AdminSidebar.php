<?php
// AdminSidebar.php
if(!isset($_SESSION)) session_start();

$navigation = [
    ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'AdminHome.php'],
    ['icon' => 'building', 'label' => 'Departments', 'url' => 'DepartmentList.php'],
    ['icon' => 'award', 'label' => 'Designations', 'url' => 'Designation.php'],
    ['icon' => 'chalkboard-teacher', 'label' => 'Teachers', 'url' => 'AdminTeacherList.php'],
    ['icon' => 'user-graduate', 'label' => 'Students', 'url' => 'StudentList.php'],

    ['icon' => 'calendar-alt', 'label' => 'Academic Year', 'url' => 'ManageAcademicYear.php'],
    ['icon' => 'bullhorn', 'label' => 'Notices', 'url' => 'AdminNotice.php'],
    ['icon' => 'exclamation-circle', 'label' => 'Complaints', 'url' => 'AdminHandleComplaints.php'],

    ['icon' => 'clock', 'label' => 'Teacher Registration', 'url' => 'TeacherRegistration.php'],
    ['icon' => 'users', 'label' => 'Promote Semester', 'url' => 'SemesterPromotion.php'],
    ['icon' => 'cog', 'label' => 'Profile', 'url' => 'AdminProfile.php'],
];

$current_page = basename($_SERVER['PHP_SELF']);
include("SidebarLayout.php");
?>
