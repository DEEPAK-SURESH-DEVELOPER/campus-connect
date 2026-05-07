<?php
include("../Includes/TeacherHeader.php");

// --- Check if teacher is logged in and is a class teacher ---
if(!isset($_SESSION['teacher_id']) || empty($_SESSION['is_class_teacher']) || !$_SESSION['is_class_teacher']){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = $_SESSION['teacher_id'];
$class_id = $_SESSION['class_id'] ?? null;

// --- Fetch class name ---
$classRes = $con->query("SELECT class_name FROM tbl_class WHERE class_id='$class_id'");
$classRow = $classRes ? $classRes->fetch_assoc() : null;
$class_name = $classRow ? $classRow['class_name'] : '';

// --- Handle deletion ---
if(isset($_GET['delID'])){
    $delID = (int)$_GET['delID'];
    $check = $con->query("SELECT student_id FROM tbl_student WHERE student_id='$delID' AND class_id='$class_id'");
    if($check && $check->num_rows > 0){
        $con->query("DELETE FROM tbl_student WHERE student_id='$delID'");
        echo "<script>alert('Student deleted successfully'); window.location='StudentList.php';</script>";
        exit;
    } else {
        echo "<script>alert('Unauthorized deletion attempt'); window.location='StudentList.php';</script>";
        exit;
    }
}

$page_title = "Student List";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Student List</span>';

?>

<div class="main-content">
    <div class="glass-card fade-in">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> Student List - <?= htmlspecialchars($class_name) ?>
            </h3>
            <a href="TeacherHome.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>

        <div class="glass-card" style="overflow-x:auto;">
            <table class="glass-table" id="studentTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $resStudents = $con->query("SELECT * FROM tbl_student WHERE class_id='$class_id' ORDER BY student_name ASC");
                $count = 1;
                if($resStudents && $resStudents->num_rows > 0){
                    while($stu = $resStudents->fetch_assoc()){
                        $photo = $stu['student_photo'] && file_exists("../Assets/Files/Student/".$stu['student_photo'])
                                ? "../Assets/Files/Student/".$stu['student_photo']
                                : "../Assets/Images/default.png";
                ?>
                    <tr>
                        <td><?= $count ?></td>
                        <td><img src="<?= $photo ?>" alt="Photo" class="student-photo"></td>
                        <td><strong><?= htmlspecialchars($stu['student_name']) ?></strong></td>
                        <td>
                            <div class="action-buttons">
                                <a href="../Teacher/TeacherStudentProfile.php?student_id=<?= $stu['student_id'] ?>" class="btn-outline small-btn">
                                    <i class="fas fa-id-card"></i> Profile
                                </a>
                                <a href="../Teacher/TeacherStudentReport.php?student_id=<?= $stu['student_id'] ?>" class="btn-primary small-btn">
                                    <i class="fas fa-chart-line"></i> Report
                                </a>
                                <a href="StudentList.php?delID=<?= $stu['student_id'] ?>" 
                                   class="btn-outline small-btn delete" 
                                   onclick="return confirm('Are you sure to delete this student?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php
                        $count++;
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; color:var(--text-secondary);'>No students found in this class.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>

<!-- Page Specific Styles -->
<style>
.student-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--gradient-1);
    box-shadow: 0 0 10px rgba(102, 126, 234, 0.4);
    transition: transform 0.3s ease;
}
.student-photo:hover {
    transform: scale(1.1);
}
#studentTable th, #studentTable td {
    text-align: center;
    vertical-align: middle;
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
}
.small-btn {
    font-size: 0.8rem;
    padding: 0.5rem 0.8rem;
    border-radius: 10px;
}
.btn-outline.delete {
    border-color: var(--error);
    color: var(--error);
}
.btn-outline.delete:hover {
    background: var(--error);
    color: #fff;
}
</style>
</body>
</html>
