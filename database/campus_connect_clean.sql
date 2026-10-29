-- Campus Connect: clean fictional demo database
-- Select the campus_connect database in phpMyAdmin, then import this whole file.
-- This replaces the existing tables and all their data.
-- Demo login password for every account: 123@@ (stored as plain text to match this project).

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `tbl_attendance_detail`, `tbl_timetable`, `tbl_teachersubject`, `tbl_student`, `tbl_teacher`, `tbl_subject`, `tbl_semesterpromotion_log`, `tbl_semester`, `tbl_notice`, `tbl_designation`, `tbl_departmentperiods`, `tbl_department`, `tbl_course`, `tbl_complaint`, `tbl_class`, `tbl_attendance_master`, `tbl_admin`, `tbl_academicyear`;
SET FOREIGN_KEY_CHECKS=1;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 12:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_academicyear`
--

CREATE TABLE `tbl_academicyear` (
  `acyear_id` int(10) NOT NULL,
  `acyear_name` varchar(15) NOT NULL,
  `sem_type` enum('odd','even') NOT NULL DEFAULT 'odd',
  `is_current` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_academicyear`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_password` varchar(100) NOT NULL,
  `admin_photo` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admin`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_attendance_detail`
--

CREATE TABLE `tbl_attendance_detail` (
  `att_detail_id` int(11) NOT NULL,
  `att_master_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Present','Absent') DEFAULT 'Absent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_attendance_detail`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_attendance_master`
--

CREATE TABLE `tbl_attendance_master` (
  `att_master_id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `created_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_attendance_master`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_class`
--

CREATE TABLE `tbl_class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `acyear_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_class`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_complaint`
--

CREATE TABLE `tbl_complaint` (
  `complaint_id` int(11) NOT NULL,
  `sender_type` enum('Student','Teacher') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `target_type` enum('Class Teacher','HOD','Admin') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `complaint_subject` varchar(150) NOT NULL,
  `complaint_details` text NOT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `complaint_date` datetime DEFAULT current_timestamp(),
  `complaint_status` enum('Pending','Reviewed','Resolved') DEFAULT 'Pending',
  `is_viewed` tinyint(1) DEFAULT 0,
  `reply` text DEFAULT NULL,
  `reply_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_complaint`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_course`
--

CREATE TABLE `tbl_course` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL,
  `course_years` int(11) NOT NULL,
  `total_semesters` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_course`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_department`
--

CREATE TABLE `tbl_department` (
  `department_id` int(10) NOT NULL,
  `department_name` varchar(25) NOT NULL,
  `hod_teacher_id` int(11) DEFAULT NULL,
  `department_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_department`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_departmentperiods`
--

CREATE TABLE `tbl_departmentperiods` (
  `period_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `period_no` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_break` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_departmentperiods`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_designation`
--

CREATE TABLE `tbl_designation` (
  `designation_id` int(10) NOT NULL,
  `designation_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_designation`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_notice`
--

CREATE TABLE `tbl_notice` (
  `notice_id` int(11) NOT NULL,
  `sender_type` enum('Admin','HOD','Teacher') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `target_type` enum('AllUsers','AllTeachers','AllHODs','DepartmentUsers','DepartmentTeachers','DepartmentAll','ClassStudents') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `notice_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_notice`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_semester`
--

CREATE TABLE `tbl_semester` (
  `semester_id` int(10) NOT NULL,
  `semester_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_semester`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_semesterpromotion_log`
--

CREATE TABLE `tbl_semesterpromotion_log` (
  `log_id` int(11) NOT NULL,
  `promoted_by` varchar(100) NOT NULL,
  `promoted_on` datetime NOT NULL,
  `from_year` varchar(15) NOT NULL,
  `to_year` varchar(15) DEFAULT NULL,
  `from_sem` enum('odd','even') NOT NULL,
  `to_sem` enum('odd','even') DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_semesterpromotion_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_student`
--

CREATE TABLE `tbl_student` (
  `student_id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `student_contact` varchar(15) DEFAULT NULL,
  `student_address` text DEFAULT NULL,
  `student_photo` varchar(255) DEFAULT NULL,
  `student_gender` enum('Male','Female') NOT NULL,
  `student_password` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_student`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_subject`
--

CREATE TABLE `tbl_subject` (
  `subject_id` int(10) NOT NULL,
  `subject_name` varchar(50) NOT NULL,
  `course_id` int(15) NOT NULL,
  `semester_id` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_subject`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_teacher`
--

CREATE TABLE `tbl_teacher` (
  `teacher_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `designation_id` int(11) NOT NULL,
  `teacher_name` varchar(100) NOT NULL,
  `teacher_gender` enum('Male','Female','Other') NOT NULL,
  `teacher_address` text NOT NULL,
  `teacher_contact` varchar(15) NOT NULL,
  `teacher_email` varchar(100) NOT NULL,
  `teacher_password` varchar(255) NOT NULL,
  `teacher_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_teacher`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_teachersubject`
--

CREATE TABLE `tbl_teachersubject` (
  `teachersubject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_teachersubject`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_timetable`
--

CREATE TABLE `tbl_timetable` (
  `timetable_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `semester_id` int(11) NOT NULL,
  `period_id` int(11) DEFAULT NULL,
  `weekday` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_timetable`
--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_academicyear`
--
ALTER TABLE `tbl_academicyear`
  ADD PRIMARY KEY (`acyear_id`);

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `tbl_attendance_detail`
--
ALTER TABLE `tbl_attendance_detail`
  ADD PRIMARY KEY (`att_detail_id`),
  ADD KEY `att_master_id` (`att_master_id`);

--
-- Indexes for table `tbl_attendance_master`
--
ALTER TABLE `tbl_attendance_master`
  ADD PRIMARY KEY (`att_master_id`),
  ADD UNIQUE KEY `timetable_id` (`timetable_id`,`attendance_date`);

--
-- Indexes for table `tbl_class`
--
ALTER TABLE `tbl_class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `tbl_complaint`
--
ALTER TABLE `tbl_complaint`
  ADD PRIMARY KEY (`complaint_id`);

--
-- Indexes for table `tbl_course`
--
ALTER TABLE `tbl_course`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `tbl_department`
--
ALTER TABLE `tbl_department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `tbl_departmentperiods`
--
ALTER TABLE `tbl_departmentperiods`
  ADD PRIMARY KEY (`period_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `tbl_designation`
--
ALTER TABLE `tbl_designation`
  ADD PRIMARY KEY (`designation_id`);

--
-- Indexes for table `tbl_notice`
--
ALTER TABLE `tbl_notice`
  ADD PRIMARY KEY (`notice_id`);

--
-- Indexes for table `tbl_semester`
--
ALTER TABLE `tbl_semester`
  ADD PRIMARY KEY (`semester_id`);

--
-- Indexes for table `tbl_semesterpromotion_log`
--
ALTER TABLE `tbl_semesterpromotion_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `tbl_student`
--
ALTER TABLE `tbl_student`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `tbl_subject`
--
ALTER TABLE `tbl_subject`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `tbl_teacher`
--
ALTER TABLE `tbl_teacher`
  ADD PRIMARY KEY (`teacher_id`);

--
-- Indexes for table `tbl_teachersubject`
--
ALTER TABLE `tbl_teachersubject`
  ADD PRIMARY KEY (`teachersubject_id`);

--
-- Indexes for table `tbl_timetable`
--
ALTER TABLE `tbl_timetable`
  ADD PRIMARY KEY (`timetable_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_academicyear`
--
ALTER TABLE `tbl_academicyear`
  MODIFY `acyear_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_attendance_detail`
--
ALTER TABLE `tbl_attendance_detail`
  MODIFY `att_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=954454;

--
-- AUTO_INCREMENT for table `tbl_attendance_master`
--
ALTER TABLE `tbl_attendance_master`
  MODIFY `att_master_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80262;

--
-- AUTO_INCREMENT for table `tbl_class`
--
ALTER TABLE `tbl_class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_complaint`
--
ALTER TABLE `tbl_complaint`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_course`
--
ALTER TABLE `tbl_course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_department`
--
ALTER TABLE `tbl_department`
  MODIFY `department_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_departmentperiods`
--
ALTER TABLE `tbl_departmentperiods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_designation`
--
ALTER TABLE `tbl_designation`
  MODIFY `designation_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_notice`
--
ALTER TABLE `tbl_notice`
  MODIFY `notice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_semester`
--
ALTER TABLE `tbl_semester`
  MODIFY `semester_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_semesterpromotion_log`
--
ALTER TABLE `tbl_semesterpromotion_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_student`
--
ALTER TABLE `tbl_student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `tbl_subject`
--
ALTER TABLE `tbl_subject`
  MODIFY `subject_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=449;

--
-- AUTO_INCREMENT for table `tbl_teacher`
--
ALTER TABLE `tbl_teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_teachersubject`
--
ALTER TABLE `tbl_teachersubject`
  MODIFY `teachersubject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tbl_timetable`
--
ALTER TABLE `tbl_timetable`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_attendance_detail`
--
ALTER TABLE `tbl_attendance_detail`
  ADD CONSTRAINT `tbl_attendance_detail_ibfk_1` FOREIGN KEY (`att_master_id`) REFERENCES `tbl_attendance_master` (`att_master_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- Fictional demo records. Profile columns use the shared default image or NULL.
INSERT INTO `tbl_academicyear` (`acyear_id`, `acyear_name`, `sem_type`, `is_current`) VALUES
(1, '2026-2027', 'odd', 1);

INSERT INTO `tbl_admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `admin_photo`) VALUES
(1, 'Campus Demo Admin', 'admin@campusconnect.example.test', '123@@', 'default.png');

INSERT INTO `tbl_semester` (`semester_id`, `semester_name`) VALUES
(1, 'First Semester'), (2, 'Second Semester'), (3, 'Third Semester'), (4, 'Fourth Semester'),
(5, 'Fifth Semester'), (6, 'Sixth Semester'), (7, 'Seventh Semester'), (8, 'Eighth Semester');

INSERT INTO `tbl_designation` (`designation_id`, `designation_name`) VALUES
(1, 'Professor'), (2, 'Assistant Professor'), (3, 'Lecturer');

INSERT INTO `tbl_teacher` (`teacher_id`, `department_id`, `designation_id`, `teacher_name`, `teacher_gender`, `teacher_address`, `teacher_contact`, `teacher_email`, `teacher_password`, `teacher_photo`) VALUES
(1, 1, 1, 'Mira Nair', 'Female', 'Demo Campus, Computer Science', '5550101001', 'mira.nair@example.test', '123@@', NULL),
(2, 1, 2, 'Arun Dev', 'Male', 'Demo Campus, Computer Science', '5550101002', 'arun.dev@example.test', '123@@', NULL),
(3, 2, 1, 'Leela Thomas', 'Female', 'Demo Campus, Commerce', '5550101003', 'leela.thomas@example.test', '123@@', NULL),
(4, 2, 2, 'Vivek Das', 'Male', 'Demo Campus, Commerce', '5550101004', 'vivek.das@example.test', '123@@', NULL),
(5, 3, 1, 'Ravi Menon', 'Male', 'Demo Campus, Physical Education', '5550101005', 'ravi.menon@example.test', '123@@', NULL),
(6, 3, 3, 'Neha Joseph', 'Female', 'Demo Campus, Physical Education', '5550101006', 'neha.joseph@example.test', '123@@', NULL);

INSERT INTO `tbl_department` (`department_id`, `department_name`, `hod_teacher_id`, `department_code`) VALUES
(1, 'Computer Science', 1, 'CS'), (2, 'Commerce', 3, 'COM'), (3, 'Physical Education', 5, 'PE');

INSERT INTO `tbl_course` (`course_id`, `course_name`, `department_id`, `course_years`, `total_semesters`) VALUES
(1, 'Bachelor of Computer Applications', 1, 3, 6),
(2, 'Bachelor of Business Administration', 2, 3, 6),
(3, 'Bachelor of Physical Education', 3, 4, 8);

INSERT INTO `tbl_departmentperiods` (`period_id`, `department_id`, `period_no`, `start_time`, `end_time`, `is_break`) VALUES
(1, 1, 1, '09:00:00', '09:50:00', 0), (2, 1, 2, '10:00:00', '10:50:00', 0),
(3, 1, 3, '11:00:00', '11:50:00', 0), (4, 1, 4, '13:00:00', '13:50:00', 0), (5, 1, 5, '14:00:00', '14:50:00', 0),
(6, 2, 1, '09:00:00', '09:50:00', 0), (7, 2, 2, '10:00:00', '10:50:00', 0),
(8, 2, 3, '11:00:00', '11:50:00', 0), (9, 2, 4, '13:00:00', '13:50:00', 0), (10, 2, 5, '14:00:00', '14:50:00', 0),
(11, 3, 1, '09:00:00', '09:50:00', 0), (12, 3, 2, '10:00:00', '10:50:00', 0),
(13, 3, 3, '11:00:00', '11:50:00', 0), (14, 3, 4, '13:00:00', '13:50:00', 0), (15, 3, 5, '14:00:00', '14:50:00', 0);

INSERT INTO `tbl_subject` (`subject_id`, `subject_name`, `course_id`, `semester_id`) VALUES
(1, 'Programming Fundamentals', 1, 1), (2, 'Computer Systems', 1, 1), (3, 'Mathematics for Computing', 1, 1), (4, 'Web Technology', 1, 1), (5, 'Communication Skills', 1, 1),
(6, 'Principles of Management', 2, 1), (7, 'Business Communication', 2, 1), (8, 'Financial Accounting', 2, 1), (9, 'Business Mathematics', 2, 1), (10, 'Microeconomics', 2, 1),
(11, 'Foundations of Physical Education', 3, 1), (12, 'Anatomy and Physiology', 3, 1), (13, 'Track and Field', 3, 1), (14, 'Sports Psychology', 3, 1), (15, 'Yoga and Wellness', 3, 1);

INSERT INTO `tbl_class` (`class_id`, `class_name`, `course_id`, `semester_id`, `acyear_id`, `teacher_id`, `is_completed`) VALUES
(1, 'BCA 2026-2029 Demo', 1, 1, 1, 1, 0),
(2, 'BBA 2026-2029 Demo', 2, 1, 1, 3, 0),
(3, 'BPEd 2026-2030 Demo', 3, 1, 1, 5, 0);

INSERT INTO `tbl_student` (`student_id`, `student_name`, `student_email`, `student_contact`, `student_address`, `student_photo`, `student_gender`, `student_password`, `class_id`, `is_active`) VALUES
(1, 'Aarav Menon', 'aarav.menon@example.test', '5550201001', 'Demo address 1', NULL, 'Male', '123@@', 1, 1),
(2, 'Diya Nair', 'diya.nair@example.test', '5550201002', 'Demo address 2', NULL, 'Female', '123@@', 1, 1),
(3, 'Rohan Das', 'rohan.das@example.test', '5550201003', 'Demo address 3', NULL, 'Male', '123@@', 1, 1),
(4, 'Anika Rao', 'anika.rao@example.test', '5550201004', 'Demo address 4', NULL, 'Female', '123@@', 1, 1),
(5, 'Ishan Pillai', 'ishan.pillai@example.test', '5550201005', 'Demo address 5', NULL, 'Male', '123@@', 1, 1),
(6, 'Meera George', 'meera.george@example.test', '5550201006', 'Demo address 6', NULL, 'Female', '123@@', 2, 1),
(7, 'Kabir Shah', 'kabir.shah@example.test', '5550201007', 'Demo address 7', NULL, 'Male', '123@@', 2, 1),
(8, 'Tara Menon', 'tara.menon@example.test', '5550201008', 'Demo address 8', NULL, 'Female', '123@@', 2, 1),
(9, 'Neil Francis', 'neil.francis@example.test', '5550201009', 'Demo address 9', NULL, 'Male', '123@@', 2, 1),
(10, 'Sara Mathew', 'sara.mathew@example.test', '5550201010', 'Demo address 10', NULL, 'Female', '123@@', 2, 1),
(11, 'Dev Kumar', 'dev.kumar@example.test', '5550201011', 'Demo address 11', NULL, 'Male', '123@@', 3, 1),
(12, 'Sana Joseph', 'sana.joseph@example.test', '5550201012', 'Demo address 12', NULL, 'Female', '123@@', 3, 1),
(13, 'Kiran Paul', 'kiran.paul@example.test', '5550201013', 'Demo address 13', NULL, 'Male', '123@@', 3, 1),
(14, 'Tia George', 'tia.george@example.test', '5550201014', 'Demo address 14', NULL, 'Female', '123@@', 3, 1),
(15, 'Nikhil Varma', 'nikhil.varma@example.test', '5550201015', 'Demo address 15', NULL, 'Male', '123@@', 3, 1);

INSERT INTO `tbl_teachersubject` (`teachersubject_id`, `teacher_id`, `subject_id`) VALUES
(1, 1, 1), (2, 1, 2), (3, 2, 3), (4, 2, 4), (5, 2, 5),
(6, 3, 6), (7, 3, 7), (8, 4, 8), (9, 4, 9), (10, 4, 10),
(11, 5, 11), (12, 5, 12), (13, 6, 13), (14, 6, 14), (15, 6, 15);

-- 25 weekday sessions for each class; period, course subject and teacher agree.
INSERT INTO `tbl_timetable` (`timetable_id`, `class_id`, `semester_id`, `period_id`, `weekday`, `subject_id`, `teacher_id`)
SELECT 1 + days.day_no * 5 + slots.slot_no, 1, 1, 1 + slots.slot_no,
       days.weekday, 1 + slots.slot_no,
       CASE WHEN slots.slot_no < 2 THEN 1 ELSE 2 END
FROM (
  SELECT 0 AS day_no, 'Monday' AS weekday UNION ALL SELECT 1, 'Tuesday' UNION ALL
  SELECT 2, 'Wednesday' UNION ALL SELECT 3, 'Thursday' UNION ALL SELECT 4, 'Friday'
) AS days
CROSS JOIN (SELECT 0 AS slot_no UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) AS slots;

INSERT INTO `tbl_timetable` (`timetable_id`, `class_id`, `semester_id`, `period_id`, `weekday`, `subject_id`, `teacher_id`)
SELECT 26 + days.day_no * 5 + slots.slot_no, 2, 1, 6 + slots.slot_no,
       days.weekday, 6 + slots.slot_no,
       CASE WHEN slots.slot_no < 2 THEN 3 ELSE 4 END
FROM (
  SELECT 0 AS day_no, 'Monday' AS weekday UNION ALL SELECT 1, 'Tuesday' UNION ALL
  SELECT 2, 'Wednesday' UNION ALL SELECT 3, 'Thursday' UNION ALL SELECT 4, 'Friday'
) AS days
CROSS JOIN (SELECT 0 AS slot_no UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) AS slots;

INSERT INTO `tbl_timetable` (`timetable_id`, `class_id`, `semester_id`, `period_id`, `weekday`, `subject_id`, `teacher_id`)
SELECT 51 + days.day_no * 5 + slots.slot_no, 3, 1, 11 + slots.slot_no,
       days.weekday, 11 + slots.slot_no,
       CASE WHEN slots.slot_no < 2 THEN 5 ELSE 6 END
FROM (
  SELECT 0 AS day_no, 'Monday' AS weekday UNION ALL SELECT 1, 'Tuesday' UNION ALL
  SELECT 2, 'Wednesday' UNION ALL SELECT 3, 'Thursday' UNION ALL SELECT 4, 'Friday'
) AS days
CROSS JOIN (SELECT 0 AS slot_no UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) AS slots;

INSERT INTO `tbl_attendance_master` (`att_master_id`, `timetable_id`, `teacher_id`, `class_id`, `subject_id`, `period_id`, `attendance_date`, `created_time`) VALUES
(1, 11, 1, 1, 1, 1, '2026-10-28', '2026-10-28 09:00:00'),
(2, 36, 3, 2, 6, 6, '2026-10-28', '2026-10-28 09:00:00'),
(3, 61, 5, 3, 11, 11, '2026-10-28', '2026-10-28 09:00:00');

INSERT INTO `tbl_attendance_detail` (`att_detail_id`, `att_master_id`, `student_id`, `status`) VALUES
(1, 1, 1, 'Present'), (2, 1, 2, 'Present'), (3, 1, 3, 'Absent'), (4, 1, 4, 'Present'), (5, 1, 5, 'Present'),
(6, 2, 6, 'Present'), (7, 2, 7, 'Absent'), (8, 2, 8, 'Present'), (9, 2, 9, 'Present'), (10, 2, 10, 'Present'),
(11, 3, 11, 'Present'), (12, 3, 12, 'Present'), (13, 3, 13, 'Present'), (14, 3, 14, 'Absent'), (15, 3, 15, 'Present');

INSERT INTO `tbl_notice` (`notice_id`, `sender_type`, `sender_id`, `target_type`, `department_id`, `class_id`, `title`, `content`, `file_name`, `file_path`, `notice_date`) VALUES
(1, 'Admin', 1, 'AllUsers', NULL, NULL, 'Welcome to Campus Connect', 'Welcome to the new demo academic year. Check your dashboard for class updates.', NULL, NULL, '2026-10-27 09:00:00'),
(2, 'Admin', 1, 'ClassStudents', NULL, 1, 'BCA Lab Orientation', 'The BCA first-semester lab orientation will take place in the computer lab on Friday.', NULL, NULL, '2026-10-27 10:00:00'),
(3, 'Teacher', 6, 'ClassStudents', NULL, 3, 'Athletics Practical', 'Bring suitable sports clothing and shoes for the athletics practical session.', NULL, NULL, '2026-10-28 08:30:00');

INSERT INTO `tbl_complaint` (`complaint_id`, `sender_type`, `sender_id`, `target_type`, `target_id`, `department_id`, `class_id`, `complaint_subject`, `complaint_details`, `is_anonymous`, `complaint_date`, `complaint_status`, `is_viewed`, `reply`, `reply_date`) VALUES
(1, 'Student', 1, 'HOD', 1, 1, 1, 'Lab computer access', 'Could the lab opening hours be posted on the student notice board?', 0, '2026-10-27 10:15:00', 'Pending', 0, NULL, NULL),
(2, 'Student', 6, 'Class Teacher', 3, 2, 2, 'Lecture room projector', 'The projector in the lecture room did not connect during class.', 0, '2026-10-27 11:30:00', 'Reviewed', 1, 'The connection will be checked before the next class.', '2026-10-28 09:00:00'),
(3, 'Student', 11, 'HOD', 5, 3, 3, 'Equipment schedule', 'Please share the weekly equipment checkout schedule.', 1, '2026-10-28 12:00:00', 'Resolved', 1, 'The schedule has been posted in the department notice.', '2026-10-28 14:00:00');

