<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

$admin_id = $_SESSION['admin_id'];

// Fetch admin info
$adminQry = "SELECT admin_name, admin_photo FROM tbl_admin WHERE admin_id = $admin_id";
$adminRes = $con->query($adminQry);
$adminData = $adminRes->fetch_assoc();
$admin_name = $adminData['admin_name'];
$admin_photo = $adminData['admin_photo'] ?? 'default.png';

// Current academic year
$curYearRes = $con->query("SELECT * FROM tbl_academicyear WHERE is_current=1 LIMIT 1");
$curYear = $curYearRes->num_rows > 0 ? $curYearRes->fetch_assoc() : null;
$curYearName = $curYear['acyear_name'] ?? 'Not Set';

// Add department
if (isset($_POST['add_dept'])) {
    $dept = trim($con->real_escape_string($_POST['dept_name']));
    if ($dept !== "") {
        $con->query("INSERT INTO tbl_department(department_name) VALUES('$dept')");
        echo "<script>alert('Department added!');window.location='DepartmentList.php';</script>";
        exit;
    } else {
        echo "<script>alert('Enter a valid department name.');window.location='DepartmentList.php';</script>";
        exit;
    }
}

// Delete department
if (isset($_GET['confirm_del'])) {
    $id = intval($_GET['confirm_del']);
    $con->query("DELETE FROM tbl_course WHERE department_id=$id");
    $con->query("DELETE FROM tbl_department WHERE department_id=$id");
    echo "<script>alert('Department deleted!');window.location='DepartmentList.php';</script>";
    exit;
}

// Fetch all departments
$depRes = $con->query("SELECT * FROM tbl_department ORDER BY department_name ASC");

// Page config for template
$page_title = "Manage Departments";
$breadcrumb = '<span>Master Entries</span> <i class="fas fa-chevron-right"></i> <span>Departments</span>';
?>

<style>
    /* -------------------------------
   Bright Neon Blue Buttons (View)
-------------------------------- */
.btn-glow-blue,
.action-btn.view {
    background: linear-gradient(135deg, #3b82f6, #60a5fa) !important;
    color: #ffffff !important;
    padding: 0.45rem 1rem !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 0.82rem !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    min-width: 120px;
    height: 38px;
    border: none !important;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 0 12px rgba(59,130,246,0.55) !important;
}

.btn-glow-blue:hover,
.action-btn.view:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 22px rgba(59,130,246,0.9) !important;
    background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
}

</style>
<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Header Card -->
    <div class="glass-card d-flex align-center justify-between">
        <div class="d-flex align-center gap-2">
            <div class="user-avatar">
                <img src="../Assets/Files/Admin/<?php echo htmlspecialchars($admin_photo); ?>" alt="Admin">
            </div>
            <div>
                <h2><?php echo htmlspecialchars($admin_name); ?></h2>
                <p style="color: var(--text-secondary);">System Administrator</p>
                <span class="badge badge-info">Academic Year: <?php echo htmlspecialchars($curYearName); ?></span>
            </div>
        </div>
        <button class="btn-primary" id="openModalBtn"><i class="fas fa-plus"></i> Add Department</button>
    </div>

    <!-- Department List -->
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-building"></i> Department Overview</h3>
        </div>

        <?php if ($depRes->num_rows > 0): ?>
        <div class="dashboard-cards">
            <?php while ($row = $depRes->fetch_assoc()): 
                $dept_id = $row['department_id'];
                // HOD name
                $hod_name = 'Not Assigned';
                if (!empty($row['hod_teacher_id'])) {
                    $hodQry = "SELECT teacher_name FROM tbl_teacher WHERE teacher_id = " . intval($row['hod_teacher_id']);
                    $hodRes = $con->query($hodQry);
                    if ($hodRes->num_rows > 0) {
                        $hodData = $hodRes->fetch_assoc();
                        $hod_name = $hodData['teacher_name'];
                    }
                }
                // Course count
                $courseCountQry = "SELECT COUNT(*) AS count FROM tbl_course WHERE department_id = $dept_id";
                $courseCountRes = $con->query($courseCountQry);
                $courseCount = $courseCountRes->fetch_assoc()['count'];
            ?>
            <div class="stat-card">
                <div class="stat-card-icon"><i class="fas fa-university"></i></div>
                <div class="stat-card-label"><strong><?php echo htmlspecialchars($row['department_name']); ?></strong></div>
                <p class="mt-2" style="color: var(--text-secondary);">
                    <strong>HOD:</strong> <?php echo htmlspecialchars($hod_name); ?><br>
                    <strong>Courses:</strong> <?php echo $courseCount; ?>
                </p>
                <div class="mt-3 d-flex gap-1">
                    <a href="CourseList.php?department_id=<?php echo $dept_id; ?>" class="btn-glow-blue"><i class="fas fa-eye"></i> View</a>
                    <a href="DepartmentList.php?confirm_del=<?php echo $dept_id; ?>" class="action-btn delete" onclick="return confirm('Delete this department and its courses?');">
                        <i class="fas fa-trash-alt"></i> Delete
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                <h3 class="empty-state-title">No Departments Found</h3>
                <p class="empty-state-text">Start by adding your first department using the “Add Department” button above.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal: Add Department -->
<div id="addDeptModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Add New Department</h3>
        <form method="post" class="mt-2">
            <div class="form-group">
                <label class="form-label">Department Name</label>
                <input type="text" name="dept_name" class="form-control" placeholder="Enter Department Name" required>
            </div>
            <button type="submit" name="add_dept" class="btn-primary w-full mt-2">Add Department</button>
        </form>
        <button class="action-btn mt-3" id="closeModalBtn"><i class="fas fa-times"></i> Cancel</button>
    </div>
</div>

<script>
// Modal Logic (universal style)
document.getElementById("openModalBtn").addEventListener("click", () => {
    document.getElementById("addDeptModal").style.display = "flex";
    setTimeout(() => document.getElementById("addDeptModal").style.opacity = "1", 10);
});
document.getElementById("closeModalBtn").addEventListener("click", () => {
    document.getElementById("addDeptModal").style.opacity = "0";
    setTimeout(() => document.getElementById("addDeptModal").style.display = "none", 300);
});
</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
