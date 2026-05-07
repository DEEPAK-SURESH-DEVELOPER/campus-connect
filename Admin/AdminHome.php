<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
date_default_timezone_set('Asia/Kolkata');
$admin_id = (int)$_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

// --- Admin Photo ---
$photoRes = $con->query("SELECT admin_photo FROM tbl_admin WHERE admin_id='$admin_id' LIMIT 1");
$admin_photo_row = $photoRes ? $photoRes->fetch_assoc() : null;
$admin_photo_filename = $admin_photo_row['admin_photo'] ?? '';
$user_photo = (!empty($admin_photo_filename) && file_exists("../Assets/Files/Admin/" . $admin_photo_filename))
    ? $admin_photo_filename
    : 'default.png';

// --- Dashboard Statistics ---
$depCount = (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM tbl_department"))['cnt'];
$teacherCount = (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM tbl_teacher"))['cnt'];
$studentCount = (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM tbl_student WHERE is_active=1"))['cnt'];
$courseCount = (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM tbl_course"))['cnt'];

// --- Chart Data ---
$deptLabels = [];
$studentCounts = [];
$teacherCounts = [];

$deptQry = "SELECT department_id, department_name FROM tbl_department ORDER BY department_name";
$deptRes = mysqli_query($con, $deptQry);
while ($d = mysqli_fetch_assoc($deptRes)) {
    $dept_id = (int)$d['department_id'];
    $dept_name = $d['department_name'];
    $deptLabels[] = $dept_name;

    $sQ = "
      SELECT COUNT(*) as cnt
      FROM tbl_student s 
      INNER JOIN tbl_class c ON s.class_id = c.class_id
      INNER JOIN tbl_course cr ON c.course_id = cr.course_id
      WHERE cr.department_id = '$dept_id' AND is_active=1
    ";
    $studentCounts[] = (int) mysqli_fetch_assoc(mysqli_query($con, $sQ))['cnt'];

    $tQ = "SELECT COUNT(*) as cnt FROM tbl_teacher WHERE department_id='$dept_id'";
    $teacherCounts[] = (int) mysqli_fetch_assoc(mysqli_query($con, $tQ))['cnt'];
}

// Page Configuration
$page_title = "Admin Dashboard";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Dashboard</span>';
$use_charts = true;
?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Welcome Section -->
    <div class="glass-card d-flex align-center gap-2">
        <div class="user-avatar">
            <img src="../Assets/Files/Admin/<?php echo htmlspecialchars($user_photo); ?>" alt="Admin Photo">
        </div>
        <div>
            <h2>Welcome back, <?php echo htmlspecialchars($admin_name); ?> 👋</h2>
            <p style="color: var(--text-secondary);">System Administrator Dashboard</p>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="dashboard-cards">
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-building"></i></div>
            <div class="stat-card-value"><?php echo $depCount; ?></div>
            <div class="stat-card-label">Departments</div>
        </div>

      <!--  <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-book-open"></i></div>
            <div class="stat-card-value"><?php echo $courseCount; ?></div>
            <div class="stat-card-label">Courses</div>
        </div> -->

        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-card-value"><?php echo $teacherCount; ?></div>
            <div class="stat-card-label">Faculty Members</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-card-value"><?php echo $studentCount; ?></div>
            <div class="stat-card-label">Active Students</div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Student Distribution by Department</h3>
        </div>
        <div class="chart-container">
            <canvas id="studentChart"></canvas>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Teacher Distribution by Department</h3>
        </div>
        <div class="chart-container">
            <canvas id="teacherChart"></canvas>
        </div>
    </div>

</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const deptLabels = <?php echo json_encode($deptLabels); ?>;
const studentData = <?php echo json_encode($studentCounts); ?>;
const teacherData = <?php echo json_encode($teacherCounts); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const studentChart = document.getElementById('studentChart');
    if (studentChart) {
        new Chart(studentChart, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Students',
                    data: studentData,
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: '#667eea',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                }
            }
        });
    }

    const teacherChart = document.getElementById('teacherChart');
    if (teacherChart) {
        new Chart(teacherChart, {
            type: 'doughnut',
            data: {
                labels: deptLabels,
                datasets: [{
                    data: teacherData,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#10b981', '#f59e0b',
                        '#ef4444', '#3b82f6', '#8b5cf6', '#14b8a6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.7)' } }
                }
            }
        });
    }
});
</script>

<!-- UNIVERSAL JS -->
<script src="../Assets/JS/universal.js"></script>

</body>
</html>
