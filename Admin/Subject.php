<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

if(!isset($_GET['course_id'])){
    echo "<script>alert('Invalid access');window.location='DepartmentList.php';</script>";
    exit;
}
$course_id = intval($_GET['course_id']);

// Fetch course details
$courseRes = $con->query("SELECT * FROM tbl_course WHERE course_id=".$course_id);
if($courseRes->num_rows == 0){
    echo "<script>alert('Course not found');window.location='DepartmentList.php';</script>";
    exit;
}
$course = $courseRes->fetch_assoc();
$total_semesters = intval($course['total_semesters']);

// Add Subject
if(isset($_POST['btn_add_subject'])){
    $subject_name = trim($_POST['txt_subject']);
    $semester_id = intval($_POST['sel_semester']);
    if($subject_name != "" && $semester_id > 0){
        $insertQry = "INSERT INTO tbl_subject(subject_name, course_id, semester_id)
                      VALUES('$subject_name', '$course_id', '$semester_id')";
        if($con->query($insertQry)){
            echo "<script>alert('Subject added successfully');window.location='Subject.php?course_id=$course_id';</script>";
            exit;
        } else {
            echo "<script>alert('Error adding subject');</script>";
        }
    }
}

// Delete Subject
if(isset($_GET['delID'])){
    $delID = intval($_GET['delID']);
    $con->query("DELETE FROM tbl_subject WHERE subject_id=".$delID);
    echo "<script>alert('Subject deleted successfully');window.location='Subject.php?course_id=$course_id';</script>";
    exit;
}

// Fetch subjects
$subjectRes = $con->query("
    SELECT s.*, sem.semester_name
    FROM tbl_subject s
    LEFT JOIN tbl_semester sem ON s.semester_id = sem.semester_id
    WHERE s.course_id = $course_id
    ORDER BY s.semester_id, s.subject_name ASC
");

$page_title = "Manage Subjects";
$breadcrumb = '<span>Courses</span> <i class="fas fa-chevron-right"></i> <span>Subjects</span>';
?>

<!-- INTERNAL STYLING FIX -->
<style>
/* Dropdown Fix for Semester Selection */
select.form-control {
  background: rgba(255,255,255,0.05) !important;
  color: var(--text-primary) !important;
  border: 1px solid var(--border-glass) !important;
  border-radius: 10px !important;
  padding: 0.6rem 1rem !important;
  font-size: 0.9rem !important;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  backdrop-filter: blur(6px);
}
select.form-control option {
  background: var(--secondary-bg);
  color: var(--text-primary);
  padding: 6px 8px;
}
select.form-control:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
  outline: none;
}
</style>

<!-- MAIN CONTENT -->
<div class="main-content">

  <!-- Page Header -->
  <div class="glass-card d-flex justify-between align-center">
    <div>
      <h2><?php echo htmlspecialchars($course['course_name']); ?> - Subjects</h2>
      <p style="color: var(--text-secondary);">
        <strong>Total Semesters:</strong> <?php echo $total_semesters; ?>
      </p>
    </div>
    <div class="d-flex gap-1">
      <a href="ClassList.php?course_id=<?php echo $course_id; ?>&department_id=<?php echo $course['department_id']; ?>" class="action-btn">
        <i class="fas fa-arrow-left"></i> Back
      </a>
      <button class="btn-primary" id="openAddSubject"><i class="fas fa-plus"></i> Add Subject</button>
    </div>
  </div>

  <!-- Subjects Table -->
  <div class="glass-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-book"></i> Subjects List</h3>
    </div>

    <table class="glass-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Subject Name</th>
          <th>Semester</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 0;
        while($subject = $subjectRes->fetch_assoc()){
          $i++;
        ?>
        <tr>
          <td><?php echo $i; ?></td>
          <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
          <td><?php echo htmlspecialchars($subject['semester_name'] ?? "Semester ".$subject['semester_id']); ?></td>
          <td>
            <a href="Subject.php?delID=<?php echo $subject['subject_id']; ?>&course_id=<?php echo $course_id; ?>" 
               class="action-btn delete"
               onclick="return confirm('Delete this subject?');">
               <i class="fas fa-trash-alt"></i> Delete
            </a>
          </td>
        </tr>
        <?php } 
        if($i==0){ ?>
        <tr><td colspan="4" class="empty-state-text text-center">No subjects added yet.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <h3 class="card-title"><i class="fas fa-plus-circle"></i> Add New Subject</h3>
    <form method="post" class="mt-2">
      <div class="form-group">
        <label class="form-label">Subject Name</label>
        <input type="text" name="txt_subject" class="form-control" placeholder="Enter Subject Name" required>
      </div>
      <div class="form-group">
        <label class="form-label">Select Semester</label>
        <select name="sel_semester" class="form-control" required>
          <option value="">-- Select Semester --</option>
          <?php for($i=1; $i<=$total_semesters; $i++){ ?>
            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
          <?php } ?>
        </select>
      </div>
      <button type="submit" name="btn_add_subject" class="btn-primary w-full mt-2">Add Subject</button>
      <button type="button" id="closeAddSubject" class="action-btn mt-2 w-full"><i class="fas fa-times"></i> Cancel</button>
    </form>
  </div>
</div>

<!-- Modal JS -->
<script>
const modal = document.getElementById('addSubjectModal');
document.getElementById('openAddSubject').addEventListener('click', () => {
  modal.style.display = 'flex';
  setTimeout(() => modal.style.opacity = '1', 10);
});
document.getElementById('closeAddSubject').addEventListener('click', () => {
  modal.style.opacity = '0';
  setTimeout(() => modal.style.display = 'none', 300);
});
</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
