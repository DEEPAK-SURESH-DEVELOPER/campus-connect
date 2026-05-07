<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
if(!isset($_GET['teacher_id'])){
    echo "<script>alert('Invalid teacher'); window.location='AdminTeacherList.php';</script>";
    exit;
}

$teacher_id = (int)$_GET['teacher_id'];

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $con->query("DELETE FROM tbl_teachersubject WHERE teacher_id='$teacher_id' AND subject_id='$delete_id'");
    echo "<script>alert('Subject removed successfully'); window.location='AssignSubjects.php?teacher_id=$teacher_id';</script>";
    exit;
}

$teacherQry = "SELECT t.*, d.designation_name
               FROM tbl_teacher t
               JOIN tbl_designation d ON t.designation_id = d.designation_id
               WHERE t.teacher_id='$teacher_id'";
$teacherRes = mysqli_query($con, $teacherQry);
if(!$teacherRes || mysqli_num_rows($teacherRes)==0){
    echo "<script>alert('Teacher not found'); window.location='AdminTeacherList.php';</script>";
    exit;
}
$teacher = mysqli_fetch_assoc($teacherRes);

$photo = !empty($teacher['teacher_photo']) ? $teacher['teacher_photo'] : 'default.png';
$photo_path = "../Assets/Files/Teacher/".$photo;
if(!file_exists($photo_path)){
    $photo_path = "../Assets/Files/Teacher/default.png";
}

$err = '';
$success = '';

if(isset($_POST['btn_assign'])){
    if(isset($_POST['course_id'], $_POST['semester_id'], $_POST['subject_ids'])){
        $course_id = (int)$_POST['course_id'];
        $semester_id = (int)$_POST['semester_id'];
        $subject_ids = $_POST['subject_ids'];

        $deleteQry = "DELETE ts FROM tbl_teachersubject ts
                      JOIN tbl_subject s ON ts.subject_id=s.subject_id
                      WHERE ts.teacher_id='$teacher_id' AND s.course_id='$course_id' AND s.semester_id='$semester_id'";
        mysqli_query($con, $deleteQry);

        foreach($subject_ids as $sub_id){
            $sub_id = (int)$sub_id;
            $con->query("INSERT INTO tbl_teachersubject (teacher_id, subject_id) VALUES ('$teacher_id', '$sub_id')");
        }
        $success = "Subjects assigned successfully!";
    } else {
        $err = "Please select course, semester, and at least one subject.";
    }
}

$dept_id = (int)$teacher['department_id'];
$courseQry = "SELECT * FROM tbl_course WHERE department_id = '$dept_id'";
$courseRes = mysqli_query($con, $courseQry);

$assignedQry = "SELECT s.subject_id, s.subject_name, c.course_name, s.semester_id
                FROM tbl_teachersubject ts
                JOIN tbl_subject s ON ts.subject_id = s.subject_id
                JOIN tbl_course c ON s.course_id = c.course_id
                WHERE ts.teacher_id='$teacher_id'
                ORDER BY c.course_name, s.semester_id, s.subject_name";
$assignedRes = mysqli_query($con, $assignedQry);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Subjects - Admin</title>
<style>
/* ------------------------------
   PAGE WRAPPER
------------------------------- */
.page-wrapper {
    padding: 2rem;
    margin-left: 260px; /* sidebar adjustment */
    color: var(--text-primary);
}

/* ------------------------------
   TOP BAR
------------------------------- */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.8rem;
}
.page-title {
    font-size: 1.6rem;
    font-weight: 700;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    color: transparent;
}
.back-btn {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    padding: 0.55rem 1.2rem;
    border-radius: 8px;
    color: #fff;
    font-weight: 600;
    display: inline-flex;
    gap: 6px;
    align-items: center;
    box-shadow: 0 0 15px rgba(59,130,246,0.4);
}
.back-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 25px rgba(59,130,246,0.8);
}

/* ------------------------------
   TEACHER CARD
------------------------------- */
.teacher-card {
    display: flex;
    align-items: center;
    gap: 1.4rem;
    padding: 1.5rem;
    border-radius: 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    backdrop-filter: blur(10px);
    margin-bottom: 2rem;
}

.teacher-photo-wrap {
    position: relative;
    width: 110px;
    height: 110px;
}
.teacher-photo {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    z-index: 2;
    position: relative;
}
.photo-glow {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(59,130,246,0.6), transparent);
    filter: blur(25px);
    z-index: 1;
}
.teacher-details h3 {
    font-size: 1.3rem;
    font-weight: 700;
}
.designation {
    color: var(--text-secondary);
}

/* ------------------------------
   ALERTS
------------------------------- */
.alert {
    padding: 1rem;
    margin-bottom: 1.4rem;
    border-radius: 10px;
    font-weight: 600;
}
.alert.error {
    background: rgba(239,68,68,0.2);
    border: 1px solid rgba(239,68,68,0.4);
    color: #f87171;
}
.alert.success {
    background: rgba(16,185,129,0.2);
    border: 1px solid rgba(16,185,129,0.4);
    color: #4ade80;
}

/* ------------------------------
   FORM
------------------------------- */
.assign-form {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 1.5rem;
    border-radius: 16px;
    backdrop-filter: blur(10px);
    margin-bottom: 2rem;
}
.row {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1rem;
}
.form-group {
    width: 100%;
}
.form-group.half {
    width: 50%;
}
label {
    display: block;
    margin-bottom: 0.4rem;
    color: var(--text-secondary);
    font-weight: 600;
}
select, input {
    width: 100%;
    padding: 0.65rem;
    border-radius: 10px;
    font-size: 0.95rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border-glass);
    color: var(--text-primary);
}

/* ------------------------------
   SUBJECT LIST
------------------------------- */
.subject-list {
    padding: 1rem;
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.08);
}

/* ------------------------------
   SUBMIT BUTTON
------------------------------- */
.btn-assign {
    background: linear-gradient(135deg,#3b82f6,#60a5fa);
    border: none;
    color: #fff;
    padding: 0.65rem 1.4rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    box-shadow: 0 0 18px rgba(59,130,246,0.5);
}
.btn-assign:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 26px rgba(59,130,246,0.9);
}

/* ------------------------------
   ASSIGNED SUBJECTS TABLE
------------------------------- */
.assigned-title {
    font-size: 1.3rem;
    margin-bottom: 1rem;
}
.table-wrap {
    background: rgba(255,255,255,0.04);
    padding: 1rem;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.08);
    backdrop-filter: blur(10px);
}
.styled-table {
    width: 100%;
    border-collapse: collapse;
    color: var(--text-primary);
}
.styled-table th {
    background: rgba(255,255,255,0.08);
    padding: 0.75rem;
    text-align: left;
    color: var(--gradient-1);
}
.styled-table td {
    padding: 0.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.btn-delete {
    background: linear-gradient(135deg,#ef4444,#dc2626);
    padding: 0.45rem 1rem;
    border-radius: 8px;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 0 12px rgba(239,68,68,0.5);
}
.btn-delete:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(239,68,68,0.9);
}
/* UNIVERSAL DARK DROPDOWN FIX */
select,
select option {
    background-color: var(--primary-bg, #0a0e27) !important;
    color: var(--text-primary, #ffffff) !important;
}

/* Closed dropdown appearance */
select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid var(--border-glass);
    padding: 0.65rem !important;
    border-radius: 10px;
    backdrop-filter: blur(8px);
    color: var(--text-primary) !important;
}

/* Options inside dropdown list */
select option {
    background: rgba(15, 23, 42, 0.95) !important; /* deep dark blue */
    color: #fff !important;
    padding: 10px !important;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

/* Highlighted / hovered option */
select option:hover,
select option:checked {
    background: rgba(59,130,246,0.3) !important; /* neon blue tint */
    color: #fff !important;
}

/* Focus ring */
select:focus {
    border-color: var(--gradient-1);
    box-shadow: 0 0 0 3px rgba(102,126,234,0.3) !important;
}

</style>

<script src="../Assets/JQ/JQuery.js"></script>
</head>
<body>
<div class="page-wrapper">
  <div class="assign-container">

    <div class="top-bar">
      <h2 class="page-title">Assign Subjects</h2>
      <a href="AdminTeacherList.php" class="back-btn">Back to Teacher List</a>
    </div>

    <div class="teacher-card">
      <div class="teacher-photo-wrap">
        <img src="<?= $photo_path ?>" alt="Teacher Photo" class="teacher-photo" loading="lazy">
        <div class="photo-glow"></div>
      </div>
      <div class="teacher-details">
        <h3><?= htmlspecialchars($teacher['teacher_name']) ?></h3>
        <p class="designation"><strong>Designation:</strong> <?= htmlspecialchars($teacher['designation_name']) ?></p>
      </div>
    </div>

    <?php if($err): ?><div class="alert error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="post" id="assignForm" class="assign-form">
      <div class="row">
        <div class="form-group half">
          <label for="course_id">Course</label>
          <select name="course_id" id="course_id" required>
            <option value="">Select Course</option>
            <?php while($course = mysqli_fetch_assoc($courseRes)): ?>
              <option value="<?=$course['course_id']?>"><?=htmlspecialchars($course['course_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group half">
          <label for="semester_id">Semester</label>
          <select name="semester_id" id="semester_id" required>
            <option value="">Select Semester</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Subjects</label>
        <div id="subject-list" class="subject-list">
          <!-- AJAX-inserted checkbox list -->
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" name="btn_assign" class="btn-assign">Assign Subjects</button>
      </div>
    </form>

    <h3 class="assigned-title">Currently Assigned Subjects</h3>
    <div class="table-wrap">
      <table class="styled-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if($assignedRes && mysqli_num_rows($assignedRes)>0): 
            while($row = mysqli_fetch_assoc($assignedRes)): ?>
            <tr>
              <td><?= htmlspecialchars($row['subject_name']) ?></td>
              <td><?= htmlspecialchars($row['course_name']) ?></td>
              <td><?= htmlspecialchars($row['semester_id']) ?></td>
              <td>
                <a href="AssignSubjects.php?teacher_id=<?=$teacher_id?>&delete_id=<?=$row['subject_id']?>" 
                   class="btn-delete"
                   onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</a>
              </td>
            </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="4">No subjects assigned yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
$(document).ready(function(){
    $('#course_id').change(function(){
        var course_id = $(this).val();
        $('#semester_id').html('<option value="">Select Semester</option>');
        $('#subject-list').html('');
        if(course_id != ''){
            $.get('../Assets/AjaxPages/AjaxSemester.php', {course_id: course_id}, function(data){
                $('#semester_id').append(data);
            });
        }
    });

    $('#semester_id').change(function(){
        var course_id = $('#course_id').val();
        var semester = $(this).val();
        var teacher_id = <?=$teacher_id?>;
        if(course_id != '' && semester != ''){
            $.get('../Assets/AjaxPages/AjaxSubjectByCourseSemester.php', 
                  {course_id: course_id, semester: semester, teacher_id: teacher_id}, 
                  function(data){
                      $('#subject-list').html(data);
                  });
        } else {
            $('#subject-list').html('');
        }
    });
});
</script>

</body>
</html>
