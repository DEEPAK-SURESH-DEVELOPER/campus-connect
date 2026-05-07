<?php
/**
 * Student Dashboard - Campus Connect
 * Personalized stats and easy access to materials
 */

session_start();
include("../Assets/Connection/Connection.php");

// Security: Check if student is logged in
if(!isset($_SESSION['student_id'])) {
    header("location: ../Guest/Login.php");
    exit();
}

// Get student information
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$class_id = $_SESSION['class_id'];

// Dynamic greeting based on time
$hour = (int)date('H');
$greet = $hour < 12 ? "Good Morning" : ($hour < 17 ? "Good Afternoon" : "Good Evening");

// Get class information
$class_query = mysqli_query($con, "
    SELECT c.class_name, co.course_name, s.semester_name 
    FROM tbl_class c
    INNER JOIN tbl_course co ON c.course_id = co.course_id
    INNER JOIN tbl_semester s ON c.semester_id = s.semester_id
    WHERE c.class_id='$class_id'
");
$class_info = mysqli_fetch_assoc($class_query);
$class_name = $class_info['class_name'] ?? 'Your Class';
$course_name = $class_info['course_name'] ?? '';
$semester_name = $class_info['semester_name'] ?? '';

// Fetch dashboard statistics
// Calculate Attendance Percentage
$total_attendance_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_attendance_detail ad
    INNER JOIN tbl_attendance_master am ON ad.att_master_id = am.att_master_id
    WHERE ad.student_id='$student_id'
");
$total_attendance = mysqli_fetch_assoc($total_attendance_query)['total'] ?? 0;

$present_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_attendance_detail ad
    INNER JOIN tbl_attendance_master am ON ad.att_master_id = am.att_master_id
    WHERE ad.student_id='$student_id' AND ad.status='Present'
");
$present_count = mysqli_fetch_assoc($present_query)['total'] ?? 0;

$attendance_percentage = $total_attendance > 0 ? round(($present_count / $total_attendance) * 100, 1) : 0;

// Pending Complaints
$my_complaints_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_complaint 
    WHERE sender_id='$student_id' AND sender_type='Student' AND complaint_status='Pending'
");
$pending_complaints = mysqli_fetch_assoc($my_complaints_query)['total'] ?? 0;

// Total Complaints
$total_complaints_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_complaint 
    WHERE sender_id='$student_id' AND sender_type='Student'
");
$total_complaints = mysqli_fetch_assoc($total_complaints_query)['total'] ?? 0;

// Available Study Materials
$materials_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_study_material 
    WHERE class_id='$class_id'
");
$available_materials = mysqli_fetch_assoc($materials_query)['total'] ?? 0;

// Notices Today
$notices_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_notice 
    WHERE DATE(notice_date) = CURDATE()
    AND (target_type IN ('AllUsers', 'ClassStudents') 
         OR (target_type='ClassStudents' AND class_id='$class_id'))
");
$notices_today = mysqli_fetch_assoc($notices_query)['total'] ?? 0;

// Chart data - Personal attendance progress (last 10 sessions)
$attendance_progress = mysqli_query($con, "
    SELECT am.attendance_date, ad.status
    FROM tbl_attendance_detail ad
    INNER JOIN tbl_attendance_master am ON ad.att_master_id = am.att_master_id
    WHERE ad.student_id='$student_id'
    ORDER BY am.attendance_date DESC
    LIMIT 10
");

$attendance_dates = [];
$attendance_statuses = [];
while($att = mysqli_fetch_assoc($attendance_progress)) {
    $attendance_dates[] = date('M d', strtotime($att['attendance_date']));
    $attendance_statuses[] = $att['status'] == 'Present' ? 1 : 0;
}
$attendance_dates = array_reverse($attendance_dates);
$attendance_statuses = array_reverse($attendance_statuses);

// Recent study materials
$recent_materials = mysqli_query($con, "
    SELECT sm.*, s.subject_name, 
           CASE 
               WHEN sm.uploader_type='Teacher' THEN t.teacher_name
               ELSE 'Admin'
           END as uploader_name
    FROM tbl_study_material sm
    INNER JOIN tbl_subject s ON sm.subject_id = s.subject_id
    LEFT JOIN tbl_teacher t ON sm.uploaded_by = t.teacher_id AND sm.uploader_type='Teacher'
    WHERE sm.class_id='$class_id'
    ORDER BY sm.upload_date DESC
    LIMIT 5
");

// Recent notices
$recent_notices = mysqli_query($con, "
    SELECT * FROM tbl_notice 
    WHERE (target_type IN ('AllUsers', 'ClassStudents') 
           OR (target_type='ClassStudents' AND class_id='$class_id'))
    ORDER BY notice_date DESC 
    LIMIT 5
");

// My recent complaints
$my_complaints = mysqli_query($con, "
    SELECT * FROM tbl_complaint 
    WHERE sender_id='$student_id' AND sender_type='Student'
    ORDER BY complaint_date DESC
    LIMIT 5
");

// Page configuration
$page_title = "Student Dashboard";
$use_charts = true;

include("../Includes/Header.php");
include("../Includes/Sidebar.php");
?>

<!-- Main Content -->
<div class="main-content">
    
    <!-- Welcome Card -->
    <div class="glass-card" style="animation: fadeInUp 0.6s ease;">
        <h2><?php echo htmlspecialchars($greet); ?>, <?php echo htmlspecialchars($student_name); ?>! 👋</h2>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
            Welcome to your Student Dashboard. You're enrolled in <strong style="color: var(--gradient-1);"><?php echo htmlspecialchars($course_name); ?></strong> - <?php echo htmlspecialchars($semester_name); ?>.
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-cards">
        <!-- Attendance Percentage -->
        <div class="stat-card" style="animation-delay: 0.1s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $attendance_percentage; ?>">0</div>
            <div class="stat-card-label">Attendance %</div>
            <div class="stat-card-trend <?php echo $attendance_percentage < 75 ? 'down' : ''; ?>">
                <i class="fas fa-<?php echo $attendance_percentage >= 75 ? 'check-circle' : 'exclamation-triangle'; ?>"></i> 
                <?php echo $attendance_percentage >= 75 ? 'Good Standing' : 'Needs Improvement'; ?>
            </div>
        </div>

        <!-- Pending Complaints -->
        <div class="stat-card" style="animation-delay: 0.2s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $pending_complaints; ?>">0</div>
            <div class="stat-card-label">Pending Complaints</div>
            <div class="stat-card-trend">
                <i class="fas fa-clock"></i> Of <?php echo $total_complaints; ?> total
            </div>
        </div>

        <!-- Study Materials -->
        <div class="stat-card" style="animation-delay: 0.3s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $available_materials; ?>">0</div>
            <div class="stat-card-label">Materials Available</div>
            <div class="stat-card-trend">
                <i class="fas fa-download"></i> Ready to download
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
                <i class="fas fa-bell"></i> New updates
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
            <div class="quick-action-btn" onclick="location.href='Complaint.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-file-medical"></i>
                </div>
                <span class="quick-action-label">File Complaint</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='StudyMaterials.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
                <span class="quick-action-label">Study Materials</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Attendance.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <span class="quick-action-label">Check Attendance</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='Notices.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <span class="quick-action-label">Read Notices</span>
            </div>
        </div>
    </div>

    <!-- Progress Info -->
    <div class="glass-card" style="animation: fadeInUp 0.6s ease;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> My Progress
            </h3>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div style="text-align: center; padding: 1.5rem;">
                <i class="fas fa-user-check" style="font-size: 2.5rem; color: var(--success); margin-bottom: 1rem;"></i>
                <div class="stat-card-value" data-count="<?php echo $present_count; ?>">0</div>
                <div class="stat-card-label">Classes Attended</div>
            </div>
            
            <div style="text-align: center; padding: 1.5rem;">
                <i class="fas fa-user-times" style="font-size: 2.5rem; color: var(--error); margin-bottom: 1rem;"></i>
                <div class="stat-card-value" data-count="<?php echo $total_attendance - $present_count; ?>">0</div>
                <div class="stat-card-label">Classes Missed</div>
            </div>
            
            <div style="text-align: center; padding: 1.5rem;">
                <i class="fas fa-percentage" style="font-size: 2.5rem; color: var(--gradient-1); margin-bottom: 1rem;"></i>
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <div class="progress-bar" style="width: 100px; height: 10px;">
                        <div class="progress-fill" style="width: <?php echo $attendance_percentage; ?>%;"></div>
                    </div>
                    <span style="font-weight: 600; color: var(--text-primary);"><?php echo $attendance_percentage; ?>%</span>
                </div>
                <div class="stat-card-label">Overall Attendance</div>
            </div>
        </div>
    </div>

    <!-- Content Grid: Chart & Tables -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        
        <!-- Attendance Progress Chart -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area"></i> Attendance Progress
                </h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Last 10 Sessions</span>
            </div>
            <div class="chart-container">
                <canvas id="attendanceChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- My Complaints Status -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks"></i> My Complaints
                </h3>
                <a href="MyComplaints.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
            </div>

            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($my_complaints) > 0):
                        while($complaint = mysqli_fetch_assoc($my_complaints)): 
                            $status_class = $complaint['complaint_status'] == 'Resolved' ? 'success' : 
                                           ($complaint['complaint_status'] == 'Pending' ? 'warning' : 'info');
                    ?>
                    <tr>
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
                        <td colspan="3" class="text-center" style="padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-muted); opacity: 0.3;"></i>
                            <p style="color: var(--text-muted); margin-top: 1rem;">No complaints filed yet</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem;">
        
        <!-- Study Materials -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i> Recent Study Materials
                </h3>
                <a href="StudyMaterials.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
            </div>

            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Uploaded By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($recent_materials) > 0):
                        while($material = mysqli_fetch_assoc($recent_materials)): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars(substr($material['title'], 0, 20)) . '...'; ?></td>
                        <td><?php echo htmlspecialchars($material['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['uploader_name']); ?></td>
                        <td>
                            <button class="btn-icon" data-tooltip="Download">
                                <i class="fas fa-download"></i>
                            </button>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 2rem;">
                            <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--text-muted); opacity: 0.3;"></i>
                            <p style="color: var(--text-muted); margin-top: 1rem;">No materials available yet</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Notices -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullhorn"></i> Recent Notices
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
                    <p class="empty-state-text">No notices available</p>
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
    
    // Attendance Progress Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if(attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($attendance_dates); ?>,
                datasets: [{
                    label: 'Attendance',
                    data: <?php echo json_encode($attendance_statuses); ?>,
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(16, 185, 129, 1)',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    stepped: false
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
                        borderColor: 'rgba(16, 185, 129, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y === 1 ? 'Present' : 'Absent';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 1,
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
                                return value === 1 ? 'Present' : 'Absent';
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
});
</script>

<?php include("../Includes/Footer.php"); ?>