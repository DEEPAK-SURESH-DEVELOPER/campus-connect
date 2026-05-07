<?php
/**
 * HOD Dashboard - Campus Connect
 * Department-specific overview for Head of Department
 */

session_start();
include("../Assets/Connection/Connection.php");

// Security: Check if HOD is logged in
if(!isset($_SESSION['hod_id'])) {
    header("location: ../Guest/Login.php");
    exit();
}

// Get HOD information
$hod_id = $_SESSION['hod_id'];
$hod_name = $_SESSION['hod_name'];
$department_id = $_SESSION['hod_department_id'];

// Dynamic greeting based on time
$hour = (int)date('H');
$greet = $hour < 12 ? "Good Morning" : ($hour < 17 ? "Good Afternoon" : "Good Evening");

// Get department name
$dept_query = mysqli_query($con, "SELECT department_name FROM tbl_department WHERE department_id='$department_id'");
$department_name = mysqli_fetch_assoc($dept_query)['department_name'] ?? 'Your Department';

// Fetch dashboard statistics for department
// Department Students
$student_query = mysqli_query($con, "
    SELECT COUNT(DISTINCT s.student_id) as total 
    FROM tbl_student s
    INNER JOIN tbl_class c ON s.class_id = c.class_id
    INNER JOIN tbl_course co ON c.course_id = co.course_id
    WHERE co.department_id='$department_id' AND s.is_active=1
");
$dept_students = mysqli_fetch_assoc($student_query)['total'] ?? 0;

// Department Faculty
$faculty_query = mysqli_query($con, "SELECT COUNT(*) AS total FROM tbl_teacher WHERE department_id='$department_id'");
$dept_faculty = mysqli_fetch_assoc($faculty_query)['total'] ?? 0;

// Pending Complaints (department)
$complaint_query = mysqli_query($con, "
    SELECT COUNT(*) AS total FROM tbl_complaint 
    WHERE department_id='$department_id' AND complaint_status='Pending'
");
$pending_complaints = mysqli_fetch_assoc($complaint_query)['total'] ?? 0;

// Department Courses
$course_query = mysqli_query($con, "SELECT COUNT(*) AS total FROM tbl_course WHERE department_id='$department_id'");
$dept_courses = mysqli_fetch_assoc($course_query)['total'] ?? 0;

// Active Classes
$class_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_class c
    INNER JOIN tbl_course co ON c.course_id = co.course_id
    WHERE co.department_id='$department_id' AND c.is_completed=0
");
$active_classes = mysqli_fetch_assoc($class_query)['total'] ?? 0;

// Chart data - Department attendance average (last 7 days)
$attendance_days = [];
$attendance_percentages = [];
for($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime("-$i days"));
    $attendance_days[] = $day_name;
    
    // Get total students in department
    $total_dept_students = $dept_students;
    
    // Get present count for the day
    $present_query = mysqli_query($con, "
        SELECT COUNT(DISTINCT ad.student_id) as present_count
        FROM tbl_attendance_detail ad
        INNER JOIN tbl_attendance_master am ON ad.att_master_id = am.att_master_id
        INNER JOIN tbl_student s ON ad.student_id = s.student_id
        INNER JOIN tbl_class c ON s.class_id = c.class_id
        INNER JOIN tbl_course co ON c.course_id = co.course_id
        WHERE co.department_id='$department_id' 
        AND am.attendance_date='$date' 
        AND ad.status='Present'
    ");
    $present_count = mysqli_fetch_assoc($present_query)['present_count'] ?? 0;
    
    $percentage = $total_dept_students > 0 ? round(($present_count / $total_dept_students) * 100, 1) : 0;
    $attendance_percentages[] = $percentage;
}

// Complaint counts by status
$resolved_query = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_complaint WHERE department_id='$department_id' AND complaint_status='Resolved'");
$resolved_count = mysqli_fetch_assoc($resolved_query)['count'] ?? 0;

$pending_query = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_complaint WHERE department_id='$department_id' AND complaint_status='Pending'");
$pending_count = mysqli_fetch_assoc($pending_query)['count'] ?? 0;

$reviewed_query = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_complaint WHERE department_id='$department_id' AND complaint_status='Reviewed'");
$reviewed_count = mysqli_fetch_assoc($reviewed_query)['count'] ?? 0;

// Recent department complaints
$recent_complaints = mysqli_query($con, "
    SELECT c.*, 
           CASE 
               WHEN c.sender_type='Student' THEN s.student_name
               WHEN c.sender_type='Teacher' THEN t.teacher_name
           END as sender_name
    FROM tbl_complaint c
    LEFT JOIN tbl_student s ON c.sender_id = s.student_id AND c.sender_type='Student'
    LEFT JOIN tbl_teacher t ON c.sender_id = t.teacher_id AND c.sender_type='Teacher'
    WHERE c.department_id='$department_id'
    ORDER BY c.complaint_date DESC
    LIMIT 5
");

// Recent department notices
$recent_notices = mysqli_query($con, "
    SELECT * FROM tbl_notice 
    WHERE (target_type IN ('DepartmentAll', 'DepartmentUsers', 'DepartmentTeachers') AND department_id='$department_id')
    OR target_type IN ('AllUsers', 'AllTeachers', 'AllHODs')
    ORDER BY notice_date DESC 
    LIMIT 5
");

// Page configuration
$page_title = "HOD Dashboard";
$use_charts = true;

include("../Includes/Header.php");
include("../Includes/Sidebar.php");
?>

<!-- Main Content -->
<div class="main-content">
    
    <!-- Welcome Card -->
    <div class="glass-card" style="animation: fadeInUp 0.6s ease;">
        <h2><?php echo htmlspecialchars($greet); ?>, <?php echo htmlspecialchars($hod_name); ?>! 👋</h2>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
            Welcome to your HOD Dashboard. Managing <strong style="color: var(--gradient-1);"><?php echo htmlspecialchars($department_name); ?></strong> Department.
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-cards">
        <!-- Department Students -->
        <div class="stat-card" style="animation-delay: 0.1s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $dept_students; ?>">0</div>
            <div class="stat-card-label">Department Students</div>
            <div class="stat-card-trend">
                <i class="fas fa-users"></i> Active
            </div>
        </div>

        <!-- Department Faculty -->
        <div class="stat-card" style="animation-delay: 0.2s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $dept_faculty; ?>">0</div>
            <div class="stat-card-label">Faculty Members</div>
            <div class="stat-card-trend">
                <i class="fas fa-user-tie"></i> Teaching
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
                <?php echo $pending_complaints > 0 ? 'Needs Review' : 'All Clear'; ?>
            </div>
        </div>

        <!-- Active Classes -->
        <div class="stat-card" style="animation-delay: 0.4s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-users-class"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $active_classes; ?>">0</div>
            <div class="stat-card-label">Active Classes</div>
            <div class="stat-card-trend">
                <i class="fas fa-book-open"></i> Running
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
            <div class="quick-action-btn" onclick="location.href='Reports.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <span class="quick-action-label">Department Reports</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Teachers.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <span class="quick-action-label">Manage Faculty</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Complaints.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <span class="quick-action-label">Review Complaints</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Notices.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <span class="quick-action-label">Post Notice</span>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card text-center">
            <i class="fas fa-graduation-cap" style="font-size: 2rem; color: var(--gradient-1); margin-bottom: 1rem;"></i>
            <div class="stat-card-value" data-count="<?php echo $dept_courses; ?>">0</div>
            <div class="stat-card-label">Courses Offered</div>
        </div>
        
        <div class="glass-card text-center">
            <i class="fas fa-clipboard-check" style="font-size: 2rem; color: var(--gradient-2); margin-bottom: 1rem;"></i>
            <div class="stat-card-value" data-count="<?php echo $resolved_count; ?>">0</div>
            <div class="stat-card-label">Resolved Complaints</div>
        </div>
    </div>

    <!-- Content Grid: Charts & Tables -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        
        <!-- Department Attendance Chart -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Department Attendance
                </h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Last 7 Days Average</span>
            </div>
            <div class="chart-container">
                <canvas id="attendanceChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Complaint Status Chart -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Complaint Overview
                </h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">By Status</span>
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
                <a href="Complaints.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
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

        <!-- Department Notices -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullhorn"></i> Department Notices
                </h3>
                <a href="Notices.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
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
    
    // Department Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if(attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($attendance_days); ?>,
                datasets: [{
                    label: 'Attendance %',
                    data: <?php echo json_encode($attendance_percentages); ?>,
                    borderColor: 'rgba(118, 75, 162, 1)',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(118, 75, 162, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(118, 75, 162, 1)',
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
                        borderColor: 'rgba(118, 75, 162, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.6)',
                            font: {
                                family: 'Poppins'
                            },
                            callback: function(value) {
                                return value + '%';
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

    // Complaint Status Bar Chart
    const complaintCtx = document.getElementById('complaintChart');
    if(complaintCtx) {
        new Chart(complaintCtx, {
            type: 'bar',
            data: {
                labels: ['Resolved', 'Pending', 'Reviewed'],
                datasets: [{
                    label: 'Complaints',
                    data: [<?php echo $resolved_count; ?>, <?php echo $pending_count; ?>, <?php echo $reviewed_count; ?>],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(59, 130, 246, 0.7)'
                    ],
                    borderColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(59, 130, 246, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
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
                        padding: 12
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
                            },
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
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
});
</script>

<?php include("../Includes/Footer.php"); ?>