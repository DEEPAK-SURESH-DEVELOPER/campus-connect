<?php
include("../Includes/HODHeader.php");

if(!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 

if(!isset($_GET['teacher_id'])){
    echo "<script>alert('Invalid teacher'); window.location='TeacherList.php';</script>";
    exit;
}

$teacher_id = (int)$_GET['teacher_id'];
$hod_department_id = (int)$_SESSION['hod_department_id'];

// --- Handle delete ---
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $con->query("DELETE FROM tbl_teachersubject WHERE teacher_id='$teacher_id' AND subject_id='$delete_id'");
    echo "<script>alert('Subject removed successfully'); window.location='AssignSubjects.php?teacher_id=$teacher_id';</script>";
    exit;
}

// --- Fetch teacher info ---
$teacherQry = "SELECT t.teacher_id, t.teacher_name, t.teacher_photo, d.designation_name 
               FROM tbl_teacher t
               JOIN tbl_designation d ON t.designation_id = d.designation_id
               WHERE t.teacher_id='$teacher_id'";
$teacherRes = mysqli_query($con, $teacherQry);
if(!$teacherRes || mysqli_num_rows($teacherRes)==0){
    echo "<script>alert('Teacher not found'); window.location='TeacherList.php';</script>";
    exit;
}
$teacher = mysqli_fetch_assoc($teacherRes);

// --- Safe teacher photo path ---
$photo = $teacher['teacher_photo'] ?? '';
$photoPath = "../Assets/Files/Teacher/" . $photo;
if(empty($photo) || !file_exists($photoPath)) {
    $photoPath = "../Assets/Images/default.png";
}

$err = '';
$success = '';

// --- Handle form submission (Assign subjects) ---
if(isset($_POST['btn_assign'])){
    if(isset($_POST['course_id'], $_POST['semester_id'], $_POST['subject_ids'])){
        $course_id = (int)$_POST['course_id'];
        $semester_id = (int)$_POST['semester_id'];
        $subject_ids = $_POST['subject_ids'];

        // Delete previous assignments for this teacher in this course & semester
        $deleteQry = "DELETE ts FROM tbl_teachersubject ts
                      JOIN tbl_subject s ON ts.subject_id=s.subject_id
                      WHERE ts.teacher_id='$teacher_id' AND s.course_id='$course_id' AND s.semester_id='$semester_id'";
        mysqli_query($con, $deleteQry);

        // Insert new assignments
        $stmt = $con->prepare("INSERT INTO tbl_teachersubject (teacher_id, subject_id) VALUES (?, ?)");
        foreach($subject_ids as $sub_id){
            $sub_id = (int)$sub_id;
            $stmt->bind_param("ii", $teacher_id, $sub_id);
            $stmt->execute();
        }
        $stmt->close();
        $success = "Subjects assigned successfully!";
    } else {
        $err = "Please select course, semester, and at least one subject.";
    }
}

// --- Fetch courses in HOD's department ---
$courseQry = "SELECT * FROM tbl_course WHERE department_id='$hod_department_id'";
$courseRes = mysqli_query($con, $courseQry);

// --- Fetch assigned subjects for display ---
$assignedQry = "SELECT s.subject_id, s.subject_name, c.course_name, s.semester_id
                FROM tbl_teachersubject ts
                JOIN tbl_subject s ON ts.subject_id = s.subject_id
                JOIN tbl_course c ON s.course_id = c.course_id
                WHERE ts.teacher_id='$teacher_id'
                ORDER BY c.course_name, s.semester_id, s.subject_name";
$assignedRes = mysqli_query($con, $assignedQry);

// --- Page meta ---
$page_title = "Assign Subjects";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> 
               <a href="TeacherList.php">Teacher List</a> 
               <i class="fas fa-chevron-right"></i> <span>Assign Subjects</span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?> - Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<!-- JQuery -->
<script src="../Assets/JQ/JQuery.js"></script>
<style>

/* FINAL DARK DROPDOWN FIX (AssignSubjects.php) */
.form-control,
#course_id,
#semester_id {
    background: rgba(15, 17, 35, 0.95) !important;
    color: #fff !important;
    border: 1px solid rgba(102,126,234,0.35) !important;
    padding: 0.75rem 1rem !important;
    border-radius: 10px !important;
    backdrop-filter: blur(6px) !important;
}

/* Dropdown options */
.form-control option,
#course_id option,
#semester_id option {
    background: rgba(10, 12, 32, 0.95) !important;
    color: #ffffff !important;
    padding: 8px !important;
}

/* On focus */
.form-control:focus,
#course_id:focus,
#semester_id:focus {
    border-color: var(--gradient-1) !important;
    box-shadow: 0 0 0 3px rgba(102,126,234,.3) !important;
}

/* Fix for AJAX-loaded subject checkboxes dropdown (data inside containers) */
#subject-list select {
    background: rgba(15, 17, 35, 0.95) !important;
    color: #fff !important;
    border: 1px solid rgba(102,126,234,0.35) !important;
    border-radius: 10px !important;
}
#subject-list option {
    background: rgba(10, 12, 32, 0.95) !important;
    color: white !important;
}

</style>

</head>
<body>
<main class="main-content">

    <!-- Teacher Overview -->
    <div class="glass-card" style="display:flex;align-items:center;gap:1.5rem;">
        <div class="profile-avatar" style="width:90px;height:90px;border:3px solid var(--gradient-1);border-radius:50%;overflow:hidden;">
            <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Teacher Photo" style="width:100%;height:100%;object-fit:cover;">
        </div>
        <div>
            <h2 style="margin:0;"><?php echo htmlspecialchars($teacher['teacher_name']); ?></h2>
            <p style="color:var(--text-secondary);margin:0.25rem 0;">
                <strong>Designation:</strong> <?php echo htmlspecialchars($teacher['designation_name']); ?>
            </p>
        </div>
    </div>

    <!-- Alerts -->
    <?php if($err): ?>
        <div class="toast-notification error"><i class="fas fa-exclamation-circle"></i> <span><?php echo htmlspecialchars($err); ?></span></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="toast-notification success"><i class="fas fa-check-circle"></i> <span><?php echo htmlspecialchars($success); ?></span></div>
    <?php endif; ?>

    <!-- Assign Form -->
    <div class="glass-card mt-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tasks"></i> Assign Subjects</h3>
        </div>

        <form method="post" id="assignForm">
            <div class="form-group">
                <label class="form-label">Course:</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <option value="">Select Course</option>
                    <?php while($course = mysqli_fetch_assoc($courseRes)): ?>
                        <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Semester:</label>
                <select name="semester_id" id="semester_id" class="form-control" required>
                    <option value="">Select Semester</option>
                </select>
            </div>

            <div class="form-group" id="subject-list">
                <!-- Subjects checkboxes loaded dynamically -->
            </div>

            <button type="submit" name="btn_assign" class="btn-primary mt-2">
                <i class="fas fa-check-circle"></i> Assign Subjects
            </button>
        </form>
    </div>

    <!-- Assigned Subjects Table -->
    <div class="glass-card mt-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book-open"></i> Currently Assigned Subjects</h3>
        </div>

        <table class="glass-table">
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
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['semester_id']); ?></td>
                        <td>
                            <a href="AssignSubjects.php?teacher_id=<?php echo $teacher_id; ?>&delete_id=<?php echo $row['subject_id']; ?>"
                               class="action-btn delete"
                               onclick="return confirm('Are you sure you want to delete this assignment?');">
                               <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" class="text-center">No subjects assigned yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<!-- Universal JS -->
<script src="../Assets/JS/universal.js"></script>

<!-- Dynamic AJAX Logic -->
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
        var teacher_id = <?php echo $teacher_id; ?>;
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
