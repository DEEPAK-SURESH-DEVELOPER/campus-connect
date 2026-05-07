<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$course_id     = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($department_id <= 0 || $course_id <= 0) {
    echo "<script>alert('Missing department or course.'); window.location='CourseList.php';</script>";
    exit;
}

// Current academic year
$currentAYRes = $con->query("SELECT * FROM tbl_academicyear WHERE is_current=1 LIMIT 1");
$currentAY = ($currentAYRes && $currentAYRes->num_rows) ? $currentAYRes->fetch_assoc() : null;
$current_acyear_id = intval($currentAY['acyear_id'] ?? 0);
$current_sem_type = $currentAY['sem_type'] ?? 'odd';

// Course & Department
$courseRes = $con->query("SELECT * FROM tbl_course WHERE course_id = {$course_id}");
$course = $courseRes->fetch_assoc();

$deptRes = $con->query("SELECT * FROM tbl_department WHERE department_id = {$department_id}");
$department = $deptRes->fetch_assoc();

// Teachers list
$teacherRes = $con->query("SELECT teacher_id, teacher_name FROM tbl_teacher WHERE department_id={$department_id}");
$teachers = [];
while ($t = $teacherRes->fetch_assoc()) $teachers[] = $t;

// Add new class
if (isset($_POST['btn_add_class'])) {
    $class_name = $con->real_escape_string($_POST['txt_class']);
    $sem_id = intval($_POST['sel_semester']);
    if ($class_name != '' && $sem_id > 0) {
        $con->query("INSERT INTO tbl_class(class_name, course_id, semester_id, acyear_id, is_completed)
                     VALUES('$class_name', $course_id, $sem_id, $current_acyear_id, 0)");
        echo "<script>window.location='ClassList.php?course_id={$course_id}&department_id={$department_id}';</script>";
        exit;
    }
}

// Assign teacher
if (isset($_POST['btn_assign_teacher'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $class_id = intval($_POST['class_id']);

    if ($teacher_id > 0 && $class_id > 0) {
        $check = $con->query("
            SELECT class_id FROM tbl_class 
            WHERE teacher_id = $teacher_id AND is_completed = 0 AND class_id != $class_id LIMIT 1
        ");
        if ($check && $check->num_rows > 0) {
            echo "<script>alert('This teacher is already a class teacher for another active class.'); 
                  window.location='ClassList.php?course_id={$course_id}&department_id={$department_id}';</script>";
            exit;
        }
        $con->query("UPDATE tbl_class SET teacher_id = $teacher_id WHERE class_id = $class_id");
    }

    echo "<script>window.location='ClassList.php?course_id={$course_id}&department_id={$department_id}';</script>";
    exit;
}

// Delete class
if (isset($_POST['confirm_delete'])) {
    $del = intval($_POST['del_class_id']);
    $con->query("DELETE FROM tbl_class WHERE class_id=$del");
    echo "<script>window.location='ClassList.php?course_id={$course_id}&department_id={$department_id}';</script>";
    exit;
}

// View toggle
$view_completed = isset($_GET['view_completed']) ? 1 : 0;

// Fetch classes
$classQry = "
SELECT c.*, s.semester_name,
       (SELECT COUNT(*) FROM tbl_student st WHERE st.class_id=c.class_id) AS total_strength,
       t.teacher_name
FROM tbl_class c
JOIN tbl_semester s ON c.semester_id=s.semester_id
LEFT JOIN tbl_teacher t ON t.teacher_id=c.teacher_id
WHERE c.course_id=$course_id AND c.is_completed=$view_completed
ORDER BY s.semester_id, c.class_name";
$classRes = $con->query($classQry);

$page_title = "Manage Classes";
$breadcrumb = '<span>Courses</span> <i class="fas fa-chevron-right"></i> <span>Classes</span>';
?>

<!-- INTERNAL STYLE FIX -->
<style>
/* ---------- SELECT DROPDOWNS ---------- */
select.teacher-select, select.form-control {
  background: rgba(255,255,255,0.05) !important;
  color: var(--text-primary) !important;
  border: 1px solid var(--border-glass);
  border-radius: 10px;
  padding: 0.6rem 1rem;
  font-size: 0.9rem;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  backdrop-filter: blur(6px);
}
select.teacher-select option {
  background: var(--secondary-bg);
  color: var(--text-primary);
  padding: 6px 8px;
}
select.teacher-select:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
  outline: none;
}
.edit-form.hidden { display: none; }


/* -------------------------------------------------------
   UNIVERSAL BUTTON SIZE (same size for blue/green/red)
------------------------------------------------------- */
.btn-small,
.action-btn.view,
.action-btn.delete,
.btn-glow-blue,
.btn-glow-green,
.btn-glow-red {
    padding: 0.45rem 1rem !important;
    font-size: 0.82rem !important;
    border-radius: 8px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    min-width: 115px;       /* same width */
    height: 38px;           /* same height */
    font-weight: 600 !important;
    border: none !important;
    cursor: pointer;
    transition: 0.3s ease;
}


/* -------------------------------------------------------
   BRIGHT BLUE BUTTON (Timetable, Students, View Completed)
------------------------------------------------------- */
.btn-glow-blue,
.action-btn.view {
    background: linear-gradient(135deg, #3b82f6, #60a5fa) !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(59,130,246,0.55);
}
.btn-glow-blue:hover,
.action-btn.view:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(59,130,246,0.9);
    background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
}


/* -------------------------------------------------------
   BRIGHT GREEN BUTTON (Assign/Edit Teacher)
------------------------------------------------------- */
.btn-glow-green {
    background: linear-gradient(135deg, #34d399, #10b981) !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(16,185,129,0.55);
}
.btn-glow-green:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(16,185,129,0.9);
}


/* -------------------------------------------------------
   BRIGHT RED BUTTON (Delete)
------------------------------------------------------- */
.btn-glow-red,
.action-btn.delete {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(239,68,68,0.55);
}
.btn-glow-red:hover,
.action-btn.delete:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(239,68,68,0.9);
}
.class-btn-group {
    display: flex;
    justify-content: space-between;
    gap: 0.7rem;
    width: 100%;
}

.class-btn-group a,
.class-btn-group button {
    flex: 1; /* all buttons same width */
    text-align: center;
    width: 100%;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const selects = document.querySelectorAll('select.teacher-select');
  selects.forEach(sel => {
    for (let i = 0; i < sel.options.length; i++) {
      sel.options[i].style.background = getComputedStyle(document.body).getPropertyValue('--primary-bg');
      sel.options[i].style.color = getComputedStyle(document.body).getPropertyValue('--text-primary');
    }
  });
});
</script>

<!-- MAIN CONTENT -->
<div class="main-content">

  <!-- Header Section -->
  <div class="glass-card d-flex justify-between align-center">
    <div>
      <h2><?php echo htmlspecialchars($course['course_name']); ?> - Class Management</h2>
      <p style="color: var(--text-secondary);">
        <strong>Department:</strong> <?php echo htmlspecialchars($department['department_name']); ?> |
        <strong>Academic Year:</strong> <?php echo htmlspecialchars($currentAY['acyear_name']); ?>
      </p>
    </div>
    <div class="d-flex gap-1">
      <a href="CourseList.php?department_id=<?php echo $department_id; ?>" class="action-btn">
        <i class="fas fa-arrow-left"></i> Back
      </a>
      <?php if ($view_completed): ?>
        <a href="ClassList.php?course_id=<?php echo $course_id; ?>&department_id=<?php echo $department_id; ?>" class="action-btn view">
          <i class="fas fa-eye"></i> View Active
        </a>
      <?php else: ?>
        <a href="ClassList.php?course_id=<?php echo $course_id; ?>&department_id=<?php echo $department_id; ?>&view_completed=1" class="action-btn view">
          <i class="fas fa-check-circle"></i> View Completed
        </a>
      <?php endif; ?>
      <a href="Subject.php?course_id=<?php echo $course_id; ?>" class="action-btn view">
        <i class="fas fa-book"></i> Manage Subjects
      </a>
      <button class="btn-primary" id="openAddClass"><i class="fas fa-plus"></i> Add Class</button>
    </div>
  </div>

  <!-- Classes Grid -->
  <div class="dashboard-cards">
    <?php if ($classRes->num_rows > 0): ?>
      <?php while ($row = $classRes->fetch_assoc()): ?>
      <div class="stat-card">
        <div class="stat-card-icon"><i class="fas fa-users"></i></div>
        <div class="stat-card-label">
          <strong><?php echo htmlspecialchars($row['class_name']); ?></strong> 
          <span style="color: var(--text-muted); font-size:0.9rem;">(<?php echo htmlspecialchars($row['semester_name']); ?>)</span>
        </div>

        <p class="mt-2" style="color: var(--text-secondary);">
          <strong>Class Teacher:</strong> 
          <?php echo $row['teacher_name'] ? htmlspecialchars($row['teacher_name']) : "<span style='color:var(--error)'>Not Assigned</span>"; ?>
        </p>

        <?php if (!$view_completed): ?>
        <form method="post" id="editForm_<?php echo $row['class_id']; ?>" class="d-flex gap-1 mt-2 edit-form hidden">
          <input type="hidden" name="class_id" value="<?php echo $row['class_id']; ?>">
          <select name="teacher_id" class="teacher-select">
            <option value="">Select Teacher</option>
            <?php foreach ($teachers as $t): ?>
              <option value="<?php echo $t['teacher_id']; ?>" <?php if ($t['teacher_id'] == $row['teacher_id']) echo "selected"; ?>>
                <?php echo htmlspecialchars($t['teacher_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" name="btn_assign_teacher" class="btn-primary small"><i class="fas fa-save"></i> Save</button>
        </form>

        <button type="button" class="action-btn view mt-2" onclick="toggleEdit('<?php echo $row['class_id']; ?>')">
          <i class="fas fa-edit"></i> Assign/Edit Teacher
        </button>
        <?php endif; ?>

        <p class="mt-2"><strong>Students:</strong> <?php echo intval($row['total_strength']); ?></p>

        <div class="d-flex gap-1 mt-3">
          <?php if (!$view_completed): ?>
            <a href="CreateTimetable.php?class_id=<?php echo $row['class_id']; ?>&department_id=<?php echo $department_id; ?>" class="action-btn view"><i class="fas fa-calendar"></i> Timetable</a>
            <a href="ViewStudents.php?class_id=<?php echo $row['class_id']; ?>&department_id=<?php echo $department_id; ?>" class="action-btn view"><i class="fas fa-user-graduate"></i> Students</a>
            <form method="post" onsubmit="return confirm('Delete this class?');">
              <input type="hidden" name="del_class_id" value="<?php echo $row['class_id']; ?>">
              <button type="submit" name="confirm_delete" class="action-btn delete"><i class="fas fa-trash"></i> Delete</button>
            </form>
          <?php else: ?>
            <a href="ViewStudents.php?class_id=<?php echo $row['class_id']; ?>&department_id=<?php echo $department_id; ?>" class="action-btn view"><i class="fas fa-user-graduate"></i> Students</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
        <h3 class="empty-state-title">No Classes Found</h3>
        <p class="empty-state-text">Click “Add Class” above to create your first class.</p>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Add Class Modal -->
<div id="addClassModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <h3 class="card-title"><i class="fas fa-plus-circle"></i> Add New Class</h3>
    <form method="post" class="mt-2">
      <div class="form-group">
        <label class="form-label">Class Name</label>
        <input type="text" name="txt_class" class="form-control" placeholder="Enter Class Name" required>
      </div>
      <div class="form-group">
        <label class="form-label">Semester</label>
        <select name="sel_semester" class="form-control" required>
          <option value="">Select Semester</option>
          <?php
            for ($i = 1; $i <= $course['total_semesters']; $i++) {
              $is_odd = $i % 2 == 1;
              if (($current_sem_type === 'odd' && $is_odd) || ($current_sem_type === 'even' && !$is_odd)) {
                echo "<option value='$i'>Semester $i</option>";
              }
            }
          ?>
        </select>
      </div>
      <button type="submit" name="btn_add_class" class="btn-primary w-full mt-2">Add Class</button>
      <button type="button" id="closeAddClass" class="action-btn mt-2 w-full"><i class="fas fa-times"></i> Cancel</button>
    </form>
  </div>
</div>

<script>
function toggleEdit(id) {
  const form = document.getElementById('editForm_' + id);
  form.classList.toggle('hidden');
}

const modal = document.getElementById('addClassModal');
document.getElementById('openAddClass').addEventListener('click', () => {
  modal.style.display = 'flex';
  setTimeout(() => modal.style.opacity = '1', 10);
});
document.getElementById('closeAddClass').addEventListener('click', () => {
  modal.style.opacity = '0';
  setTimeout(() => modal.style.display = 'none', 300);
});
</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
