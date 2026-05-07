<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
if (!isset($_GET['department_id'])) {
    echo "<script>alert('No department selected!'); window.location='DepartmentList.php';</script>";
    exit;
}

$department_id = intval($_GET['department_id']);
$depRes = $con->query("SELECT * FROM tbl_department WHERE department_id=$department_id");

if ($depRes->num_rows == 0) {
    echo "<script>alert('Department not found!'); window.location='DepartmentList.php';</script>";
    exit;
}

$department = $depRes->fetch_assoc();

// Current Academic Year
$curYearRes = $con->query("SELECT * FROM tbl_academicyear WHERE is_current=1 LIMIT 1");
$curYear = $curYearRes->num_rows > 0 ? $curYearRes->fetch_assoc() : null;
$curYearName = $curYear['acyear_name'] ?? 'Not Set';

// HOD details
$currentHodName = 'Not Assigned';
$currentHodPhoto = '';
if (!empty($department['hod_teacher_id'])) {
    $hodQry = "SELECT teacher_name, teacher_photo FROM tbl_teacher WHERE teacher_id=" . intval($department['hod_teacher_id']);
    $hodRes = $con->query($hodQry);
    if ($hodRes->num_rows > 0) {
        $hodData = $hodRes->fetch_assoc();
        $currentHodName = $hodData['teacher_name'];
        $currentHodPhoto = $hodData['teacher_photo'];
    }
}

// Set HOD
if (isset($_POST['set_hod'])) {
    $newHodId = intval($_POST['hod_id']);
    $con->query("UPDATE tbl_department SET hod_teacher_id=$newHodId WHERE department_id=$department_id");
    echo "<script>alert('HOD updated successfully'); window.location='CourseList.php?department_id=$department_id';</script>";
    exit;
}

// Add Course
if (isset($_POST['add_course'])) {
    $courseName = trim($con->real_escape_string($_POST['course_name']));
    $courseYears = intval($_POST['course_years']);
    if ($courseName != "" && $courseYears > 0) {
        $totalSem = $courseYears * 2;
        $con->query("INSERT INTO tbl_course(course_name, department_id, course_years, total_semesters) 
                     VALUES('$courseName',$department_id,$courseYears,$totalSem)");
        echo "<script>alert('Course added successfully'); window.location='CourseList.php?department_id=$department_id';</script>";
        exit;
    }
}

// Delete Course
if (isset($_GET['del_id'])) {
    $id = intval($_GET['del_id']);
    $con->query("DELETE FROM tbl_class WHERE course_id=$id");
    $con->query("DELETE FROM tbl_course WHERE course_id=$id");
    echo "<script>alert('Course deleted successfully'); window.location='CourseList.php?department_id=$department_id';</script>";
    exit;
}

// Fetch Teachers & Courses
$teacherRes = $con->query("SELECT teacher_id, teacher_name FROM tbl_teacher WHERE department_id=$department_id ORDER BY teacher_name ASC");
$courseRes = $con->query("SELECT * FROM tbl_course WHERE department_id=$department_id ORDER BY course_name ASC");

// Page Setup
$page_title = "Manage Courses";
$breadcrumb = '<span>Departments</span> <i class="fas fa-chevron-right"></i> <span>Courses</span>';
?>

<!-- INLINE THEME PATCH: Styles + JS to fix dropdown visibility -->
<style>
/* Fix for dropdown visibility inside dark-glass theme */
select.form-control {
  background: rgba(255, 255, 255, 0.05) !important;
  color: var(--text-primary) !important;
  border: 1px solid var(--border-glass) !important;
  border-radius: 10px !important;
  padding: 0.6rem 1rem !important;
  font-size: 0.95rem !important;
  min-width: 220px;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  appearance: none !important;
  backdrop-filter: blur(6px);
  position: relative;
  z-index: 2;
}

.select-wrap {
  position: relative;
  display: inline-block;
}
.select-wrap::after {
  content: "\f078";
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: rgba(255,255,255,0.8);
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
/* Fix: Manage Classes Button Theme (Dark Glass + Gradient Border) */
.action-btn.manage {
    background: rgba(102, 126, 234, 0.12); 
    color: var(--gradient-1);
    border: 1px solid rgba(102,126,234,0.4);
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.action-btn.manage:hover {
    background: rgba(102,126,234,0.22);
    transform: translateY(-3px);
    box-shadow: 0 5px 18px rgba(102,126,234,0.4);
}
/* Brighter, bolder Period Structure button */
.action-btn.period-strong {
    background: linear-gradient(135deg, #2563eb, #3b82f6) !important; /* deeper blue */
    color: #ffffff !important;
    font-weight: 700 !important;  /* EXTRA bold */
    padding: 0.55rem 1.2rem !important;
    border-radius: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    box-shadow: 0 0 15px rgba(59,130,246,0.7) !important; /* stronger glow */
    border: none !important;
}

.action-btn.period-strong:hover {
    transform: translateY(-3px);
    background: linear-gradient(135deg, #1e40af, #2563eb) !important;
    box-shadow: 0 0 25px rgba(59,130,246,0.95) !important;
}


</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const selects = document.querySelectorAll('select.form-control');
  selects.forEach(sel => {
    if (!sel.parentElement.classList.contains('select-wrap')) {
      const wrap = document.createElement('div');
      wrap.className = 'select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
    }
    for (let i = 0; i < sel.options.length; i++) {
      const opt = sel.options[i];
      opt.style.background = getComputedStyle(document.body).getPropertyValue('--primary-bg') || '#0a0e27';
      opt.style.color = getComputedStyle(document.body).getPropertyValue('--text-primary') || '#ffffff';
      opt.style.padding = '6px 8px';
    }
    sel.style.color = getComputedStyle(document.body).getPropertyValue('--text-primary') || '#fff';
    sel.addEventListener('change', () => {
      sel.style.color = getComputedStyle(document.body).getPropertyValue('--text-primary') || '#fff';
    });
  });
});
</script>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Department Header -->
    <div class="glass-card d-flex justify-between align-center">
        <div>
            <h2><?php echo htmlspecialchars($department['department_name']); ?> Department</h2>
            <p style="color: var(--text-secondary);">
                <i class="fas fa-calendar-alt"></i> Academic Year: 
                <strong><?php echo htmlspecialchars($curYearName); ?></strong>
            </p>
        </div>
        <div class="d-flex gap-1">
            <button class="btn-primary" id="openAddCourse"><i class="fas fa-plus"></i> Add Course</button>
            <a href="PeriodStructure.php?department_id=<?php echo $department_id; ?>" class="action-btn period-strong">
                <i class="fas fa-cogs"></i> Period Structure
            </a>
            <a href="DepartmentList.php" class="action-btn"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <!-- HOD Section -->
    <div class="glass-card d-flex justify-between align-center">
        <div class="d-flex align-center gap-2">
            <div class="user-avatar" style="width:70px; height:70px;">
                <img src="<?php echo $currentHodPhoto ? '../Assets/Files/Teacher/' . htmlspecialchars($currentHodPhoto) : '../Assets/Images/default_user.png'; ?>" alt="HOD">
            </div>
            <div>
                <h3>Head of Department</h3>
                <p style="color: var(--text-secondary); font-size:0.9rem;"><?php echo htmlspecialchars($currentHodName); ?></p>
            </div>
        </div>
        <div>
            <form method="post" class="d-flex gap-1">
                <select name="hod_id" class="form-control">
                    <option value="">-- Select Teacher --</option>
                    <?php while ($t = $teacherRes->fetch_assoc()) { ?>
                        <option value="<?php echo $t['teacher_id']; ?>" 
                            <?php echo ($department['hod_teacher_id'] == $t['teacher_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['teacher_name']); ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit" name="set_hod" class="btn-primary"><i class="fas fa-save"></i> Save</button>
            </form>
        </div>
    </div>

    <!-- Courses Section -->
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book-open"></i> Courses</h3>
        </div>

        <?php if ($courseRes->num_rows > 0): ?>
        <div class="dashboard-cards">
            <?php while ($c = $courseRes->fetch_assoc()): ?>
            <div class="stat-card">
                <div class="stat-card-icon"><i class="fas fa-book"></i></div>
                <div class="stat-card-label"><strong><?php echo htmlspecialchars($c['course_name']); ?></strong></div>
                <p class="mt-2" style="color: var(--text-secondary);">
                    <strong>Years:</strong> <?php echo $c['course_years']; ?><br>
                    <strong>Semesters:</strong> <?php echo $c['total_semesters']; ?>
                </p>
                <div class="mt-3 d-flex gap-1">
                    <a href="ClassList.php?course_id=<?php echo $c['course_id']; ?>&department_id=<?php echo $department_id; ?>" class="action-btn manage">
                        <i class="fas fa-users"></i> Manage Classes
                    </a>
                    <a href="CourseList.php?department_id=<?php echo $department_id; ?>&del_id=<?php echo $c['course_id']; ?>" class="action-btn delete" onclick="return confirm('Delete this course?');">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                <h3 class="empty-state-title">No Courses Found</h3>
                <p class="empty-state-text">Add new courses for this department using the “Add Course” button above.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal: Add Course -->
<div id="addCourseModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Add New Course</h3>
        <form method="post" class="mt-2">
            <div class="form-group">
                <label class="form-label">Course Name</label>
                <input type="text" name="course_name" class="form-control" placeholder="Enter Course Name" required>
            </div>
            <div class="form-group">
                <label class="form-label">Duration (Years)</label>
                <input type="number" name="course_years" class="form-control" placeholder="Enter Duration" min="1" required>
            </div>
            <button type="submit" name="add_course" class="btn-primary w-full mt-2">Add Course</button>
            <button type="button" id="closeAddCourse" class="action-btn mt-2 w-full"><i class="fas fa-times"></i> Cancel</button>
        </form>
    </div>
</div>

<!-- Modal JS -->
<script>
const modal = document.getElementById('addCourseModal');
document.getElementById('openAddCourse').addEventListener('click', () => {
    modal.style.display = 'flex';
    setTimeout(() => modal.style.opacity = '1', 10);
});
document.getElementById('closeAddCourse').addEventListener('click', () => {
    modal.style.opacity = '0';
    setTimeout(() => modal.style.display = 'none', 300);
});
</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
