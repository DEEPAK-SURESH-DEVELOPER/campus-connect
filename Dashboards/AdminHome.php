<?php
/**
 * Admin Dashboard - Campus Connect
 * Complete system overview and management
 */

session_start();
include("../Assets/Connection/Connection.php");

// Security: Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("location: ../Guest/Login.php");
    exit();
}

// Get admin information
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Dynamic greeting based on time
$hour = (int)date('H');
$greet = $hour < 12 ? "Good Morning" : ($hour < 17 ? "Good Afternoon" : "Good Evening");

// Fetch dashboard statistics
// Total Active Students
// Fetch dashboard statistics (fixed to match your actual DB structure)

// ✅ Safe Stats Logic — Auto-detects columns before filtering
// ---------------------------
// Fetch dashboard statistics
// ---------------------------

// Toggle debug to true while testing to show SQL errors (set to false in production)
$DEBUG = true;

// Basic connection validation
if (!isset($con) || !($con instanceof mysqli)) {
    die("Database connection not found. Check Assets/Connection/Connection.php");
}

/**
 * Check whether a column exists in a table in the current database.
 * Uses INFORMATION_SCHEMA.COLUMNS for reliable checks across MySQL versions.
 */
function columnExists($con, $table, $column) {
    $db = mysqli_real_escape_string($con, mysqli_fetch_row(mysqli_query($con, "SELECT DATABASE()"))[0]);
    $table_esc = mysqli_real_escape_string($con, $table);
    $column_esc = mysqli_real_escape_string($con, $column);

    $sql = "
      SELECT COUNT(*) AS c 
      FROM INFORMATION_SCHEMA.COLUMNS 
      WHERE TABLE_SCHEMA = '$db' 
        AND TABLE_NAME = '$table_esc' 
        AND COLUMN_NAME = '$column_esc'
    ";
    $res = mysqli_query($con, $sql);
    if (!$res) return false;
    $row = mysqli_fetch_assoc($res);
    return ((int)$row['c'] > 0);
}

/**
 * Safe counter: counts by id column and optionally filters by an 'active' column only if it exists.
 * $table and $id_column should be actual table/PK names in your DB.
 * $active_column is optional and used only when present.
 */
function safeCount($con, $table, $id_column, $active_column = null, $active_value = '1') {
    $table_q = mysqli_real_escape_string($con, $table);
    $id_q = mysqli_real_escape_string($con, $id_column);

    $condition = "";
    if ($active_column && columnExists($con, $table, $active_column)) {
        $active_col_q = mysqli_real_escape_string($con, $active_column);
        // Use a safe, explicit condition
        $condition = " WHERE `$active_col_q` = '" . mysqli_real_escape_string($con, $active_value) . "'";
    }

    $sql = "SELECT COUNT(`$id_q`) AS total FROM `$table_q`$condition";
    $res = @mysqli_query($con, $sql);
    if (!$res) {
        if ($GLOBALS['DEBUG']) {
            echo "<pre style='color:orange'>SQL Error (safeCount): " . htmlspecialchars(mysqli_error($con)) . "\nQuery: " . htmlspecialchars($sql) . "</pre>";
        }
        return 0;
    }
    $row = mysqli_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

// ---------------
// Use safeCount()
// ---------------

// Students: use is_active if present, otherwise count all students
$total_students = safeCount($con, 'tbl_student', 'student_id', 'is_active');

// Teachers: try is_active, fallback to total count if column missing
$total_teachers = safeCount($con, 'tbl_teacher', 'teacher_id', 'is_active');

// Complaints: for pending we need status-specific count (only if column exists)
$pending_complaints = 0;
if (columnExists($con, 'tbl_complaint', 'complaint_status')) {
    $sql = "SELECT COUNT(complaint_id) AS total FROM `tbl_complaint` WHERE `complaint_status` = 'Pending'";
    $res = @mysqli_query($con, $sql);
    if ($res) {
        $pending_complaints = (int)(mysqli_fetch_assoc($res)['total'] ?? 0);
    } else if ($DEBUG) {
        echo "<pre style='color:orange'>SQL Error (pending complaints): " . htmlspecialchars(mysqli_error($con)) . "\nQuery: " . htmlspecialchars($sql) . "</pre>";
    }
} else {
    // If complaint_status doesn't exist, just return total complaints as pending = 0
    $pending_complaints = 0;
}

// Notices posted today (ensure notice_date exists)
$notices_today = 0;
if (columnExists($con, 'tbl_notice', 'notice_date')) {
    $sql = "SELECT COUNT(notice_id) AS total FROM `tbl_notice` WHERE DATE(`notice_date`) = CURDATE()";
    $res = @mysqli_query($con, $sql);
    if ($res) $notices_today = (int)(mysqli_fetch_assoc($res)['total'] ?? 0);
    else if ($DEBUG) echo "<pre style='color:orange'>SQL Error (notices_today): " . htmlspecialchars(mysqli_error($con)) . "\nQuery: " . htmlspecialchars($sql) . "</pre>";
} else {
    $notices_today = 0;
}

// Departments
$total_departments = safeCount($con, 'tbl_department', 'department_id');

// Active classes: prefer is_completed column; if missing, count all classes
$total_classes = 0;
if (columnExists($con, 'tbl_class', 'is_completed')) {
    $sql = "SELECT COUNT(class_id) AS total FROM `tbl_class` WHERE `is_completed` = '0'";
} else {
    $sql = "SELECT COUNT(class_id) AS total FROM `tbl_class`";
}
$res = @mysqli_query($con, $sql);
if ($res) $total_classes = (int)(mysqli_fetch_assoc($res)['total'] ?? 0);
else if ($DEBUG) echo "<pre style='color:orange'>SQL Error (classes): " . htmlspecialchars(mysqli_error($con)) . "\nQuery: " . htmlspecialchars($sql) . "</pre>";

// Fetch chart data - Last 7 days attendance overview
$attendance_days = [];
$attendance_counts = [];
for($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime("-$i days"));
    $attendance_days[] = $day_name;
    
    $att_query = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_attendance_master WHERE attendance_date='$date'");
    $att_count = mysqli_fetch_assoc($att_query)['count'] ?? 0;
    $attendance_counts[] = $att_count;
}

// Complaint status breakdown for pie chart
$resolved_query = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_complaint WHERE complaint_status='Resolved'");
$resolved_count = mysqli_fetch_assoc($resolved_query)['count'] ?? 0;

$pending_query = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_complaint WHERE complaint_status='Pending'");
$pending_count = mysqli_fetch_assoc($pending_query)['count'] ?? 0;

$reviewed_query = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_complaint WHERE complaint_status='Reviewed'");
$reviewed_count = mysqli_fetch_assoc($reviewed_query)['count'] ?? 0;

// Recent complaints
$recent_complaints = mysqli_query($con, "
    SELECT c.*, 
           CASE 
               WHEN c.sender_type='Student' THEN s.student_name
               WHEN c.sender_type='Teacher' THEN t.teacher_name
           END as sender_name
    FROM tbl_complaint c
    LEFT JOIN tbl_student s ON c.sender_id = s.student_id AND c.sender_type='Student'
    LEFT JOIN tbl_teacher t ON c.sender_id = t.teacher_id AND c.sender_type='Teacher'
    ORDER BY c.complaint_date DESC
    LIMIT 5
");

// Recent notices
$recent_notices = mysqli_query($con, "
    SELECT * FROM tbl_notice 
    ORDER BY notice_date DESC 
    LIMIT 5
");

// Page configuration
$page_title = "Admin Dashboard";
$use_charts = true;

include("../Includes/Header.php");
include("../Includes/Sidebar.php");
?>

<!-- Main Content -->
<div class="main-content">
    
    <!-- Welcome Card -->
    <div class="glass-card" style="animation: fadeInUp 0.6s ease;">
        <h2><?php echo htmlspecialchars($greet); ?>, <?php echo htmlspecialchars($admin_name); ?>! 👋</h2>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
            Welcome to your Admin Dashboard. Here's an overview of Campus Connect today.
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-cards">
        <!-- Total Students -->
        <div class="stat-card" style="animation-delay: 0.1s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $total_students; ?>">0</div>
            <div class="stat-card-label">Total Students</div>
            <div class="stat-card-trend">
                <i class="fas fa-arrow-up"></i> Active
            </div>
        </div>

        <!-- Total Teachers -->
        <div class="stat-card" style="animation-delay: 0.2s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $total_teachers; ?>">0</div>
            <div class="stat-card-label">Faculty Members</div>
            <div class="stat-card-trend">
                <i class="fas fa-users"></i> Teaching
            </div>
        </div>

        <!-- Pending Complaints -->
        <div class="stat-card" style="animation-delay: 0.3s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $pending_complaints; ?>">0</div>
            <div class="stat-card-label">Pending Complaints</div>
            <div class="stat-card-trend <?php echo $pending_complaints > 0 ? 'down' : ''; ?>">
                <i class="fas fa-<?php echo $pending_complaints > 0 ? 'exclamation-triangle' : 'check-circle'; ?>"></i> 
                <?php echo $pending_complaints > 0 ? 'Needs Attention' : 'All Clear'; ?>
            </div>
        </div>

        <!-- Notices Today -->
        <div class="stat-card" style="animation-delay: 0.4s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $notices_today; ?>">0</div>
            <div class="stat-card-label">Notices Today</div>
            <div class="stat-card-trend">
                <i class="fas fa-calendar-day"></i> Posted
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass-card" style="animation: fadeInUp 0.6s ease;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bolt"></i> Quick Actions
            </h3>
        </div>
        
        <div class="quick-actions">
            <div class="quick-action-btn" onclick="location.href='Notice.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span class="quick-action-label">Add Notice</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Student.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <span class="quick-action-label">Add Student</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Teacher.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <span class="quick-action-label">Manage Users</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Reports.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="quick-action-label">View Reports</span>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card text-center">
            <i class="fas fa-building" style="font-size: 2rem; color: var(--gradient-1); margin-bottom: 1rem;"></i>
            <div class="stat-card-value" data-count="<?php echo $total_departments; ?>">0</div>
            <div class="stat-card-label">Departments</div>
        </div>
        
        <div class="glass-card text-center">
            <i class="fas fa-users" style="font-size: 2rem; color: var(--gradient-2); margin-bottom: 1rem;"></i>
            <div class="stat-card-value" data-count="<?php echo $total_classes; ?>">0</div>
            <div class="stat-card-label">Active Classes</div>
        </div>
    </div>

    <!-- Content Grid: Charts & Tables -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        
        <!-- Attendance Overview Chart -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Attendance Overview
                </h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Last 7 Days</span>
            </div>
            <div class="chart-container">
                <canvas id="attendanceChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Complaint Status Breakdown -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Complaint Status
                </h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Distribution</span>
            </div>
            <div class="chart-container">
                <canvas id="complaintChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem;">
        
        <!-- Recent Complaints -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Recent Complaints
                </h3>
                <a href="Complaint.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
            </div>

            <table class="glass-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($recent_complaints) > 0):
                        while($complaint = mysqli_fetch_assoc($recent_complaints)): 
                            $status_class = $complaint['complaint_status'] == 'Resolved' ? 'success' : 
                                           ($complaint['complaint_status'] == 'Pending' ? 'warning' : 'info');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($complaint['sender_name'] ?? 'Anonymous'); ?></td>
                        <td><?php echo htmlspecialchars(substr($complaint['complaint_subject'], 0, 30)) . '...'; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($complaint['complaint_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d', strtotime($complaint['complaint_date'])); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-muted); opacity: 0.3;"></i>
                            <p style="color: var(--text-muted); margin-top: 1rem;">No complaints yet</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Latest Notices -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullhorn"></i> Latest Notices
                </h3>
                <a href="Notice.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
            </div>

            <div class="timeline">
                <?php 
                if(mysqli_num_rows($recent_notices) > 0):
                    while($notice = mysqli_fetch_assoc($recent_notices)): 
                ?>
                <div class="timeline-item">
                    <div class="timeline-date">
                        <?php echo date('M d, Y', strtotime($notice['notice_date'])); ?>
                    </div>
                    <div class="timeline-content">
                        <h4><?php echo htmlspecialchars($notice['title']); ?></h4>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                            <?php echo htmlspecialchars(substr($notice['content'] ?? 'No content', 0, 100)) . '...'; ?>
                        </p>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <p class="empty-state-text">No notices posted yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer Info -->
    <div class="glass-card" style="margin-top: 2rem; text-align: center;">
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            <i class="fas fa-info-circle"></i> Last updated on <?php echo date('F d, Y \a\t h:i A'); ?>
        </p>
    </div>

</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Attendance Overview Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if(attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($attendance_days); ?>,
                datasets: [{
                    label: 'Attendance Sessions',
                    data: <?php echo json_encode($attendance_counts); ?>,
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(102, 126, 234, 1)',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(21, 25, 50, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#b8c1ec',
                        borderColor: 'rgba(102, 126, 234, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.6)',
                            font: {
                                family: 'Poppins'
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.6)',
                            font: {
                                family: 'Poppins'
                            }
                        }
                    }
                }
            }
        });
    }

    // Complaint Status Pie Chart
    const complaintCtx = document.getElementById('complaintChart');
    if(complaintCtx) {
        new Chart(complaintCtx, {
            type: 'doughnut',
            data: {
                labels: ['Resolved', 'Pending', 'Reviewed'],
                datasets: [{
                    data: [<?php echo $resolved_count; ?>, <?php echo $pending_count; ?>, <?php echo $reviewed_count; ?>],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(59, 130, 246, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.8)',
                            font: {
                                family: 'Poppins',
                                size: 12
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(21, 25, 50, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#b8c1ec',
                        borderColor: 'rgba(102, 126, 234, 0.3)',
                        borderWidth: 1,
                        padding: 12
                    }
                }
            }
        });
    }
});
</script>