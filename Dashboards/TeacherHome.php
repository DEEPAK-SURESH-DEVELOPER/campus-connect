<?php
/**
 * Teacher Dashboard - Campus Connect
 * Class-level operations and study material management
 */

session_start();
include("../Assets/Connection/Connection.php");

// Security: Check if teacher is logged in
if(!isset($_SESSION['teacher_id'])) {
    header("location: ../Guest/Login.php");
    exit();
}

// Get teacher information
$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$is_class_teacher = isset($_SESSION['is_class_teacher']) && $_SESSION['is_class_teacher'];
$class_id = $_SESSION['class_id'] ?? null;

// Dynamic greeting based on time
$hour = (int)date('H');
$greet = $hour < 12 ? "Good Morning" : ($hour < 17 ? "Good Afternoon" : "Good Evening");

// Fetch dashboard statistics
// My Classes (subjects assigned)
$classes_query = mysqli_query($con, "
    SELECT COUNT(DISTINCT class_id) as total 
    FROM tbl_timetable 
    WHERE teacher_id='$teacher_id'
");
$my_classes = mysqli_fetch_assoc($classes_query)['total'] ?? 0;

// Total Students (if class teacher)
$total_students = 0;
if($is_class_teacher && $class_id) {
    $student_query = mysqli_query($con, "
        SELECT COUNT(*) as total FROM tbl_student 
        WHERE class_id='$class_id' AND is_active=1
    ");
    $total_students = mysqli_fetch_assoc($student_query)['total'] ?? 0;
}

// Uploaded Study Materials
$materials_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_study_material 
    WHERE uploaded_by='$teacher_id' AND uploader_type='Teacher'
");
$uploaded_materials = mysqli_fetch_assoc($materials_query)['total'] ?? 0;

// Pending Complaints (if class teacher)
$pending_complaints = 0;
if($is_class_teacher && $class_id) {
    $complaint_query = mysqli_query($con, "
        SELECT COUNT(*) as total FROM tbl_complaint 
        WHERE class_id='$class_id' AND complaint_status='Pending'
    ");
    $pending_complaints = mysqli_fetch_assoc($complaint_query)['total'] ?? 0;
}

// Attendance sessions this week
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$attendance_sessions_query = mysqli_query($con, "
    SELECT COUNT(*) as total FROM tbl_attendance_master 
    WHERE teacher_id='$teacher_id' 
    AND attendance_date BETWEEN '$week_start' AND '$week_end'
");
$attendance_sessions = mysqli_fetch_assoc($attendance_sessions_query)['total'] ?? 0;

// Chart data - Attendance distribution across week
$days_of_week = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$attendance_counts = [];
for($i = 0; $i < 6; $i++) {
    $date = date('Y-m-d', strtotime("monday this week +$i days"));
    $att_query = mysqli_query($con, "
        SELECT COUNT(*) as count FROM tbl_attendance_master 
        WHERE teacher_id='$teacher_id' AND attendance_date='$date'
    ");
    $attendance_counts[] = mysqli_fetch_assoc($att_query)['count'] ?? 0;
}

// Get class students (if class teacher)
$class_students = null;
if($is_class_teacher && $class_id) {
    $class_students = mysqli_query($con, "
        SELECT student_id, student_name, student_email, student_photo 
        FROM tbl_student 
        WHERE class_id='$class_id' AND is_active=1 
        ORDER BY student_name 
        LIMIT 6
    ");
}

// Recent study materials
$recent_materials = mysqli_query($con, "
    SELECT sm.*, s.subject_name, c.class_name
    FROM tbl_study_material sm
    INNER JOIN tbl_subject s ON sm.subject_id = s.subject_id
    INNER JOIN tbl_class c ON sm.class_id = c.class_id
    WHERE sm.uploaded_by='$teacher_id' AND sm.uploader_type='Teacher'
    ORDER BY sm.upload_date DESC
    LIMIT 5
");

// Recent notices
$recent_notices = mysqli_query($con, "
    SELECT * FROM tbl_notice 
    WHERE target_type IN ('AllTeachers', 'AllUsers', 'DepartmentTeachers', 'DepartmentAll')
    ORDER BY notice_date DESC 
    LIMIT 5
");

// Page configuration
$page_title = "Teacher Dashboard";
$use_charts = true;

include("../Includes/Header.php");
include("../Includes/Sidebar.php");
?>

<!-- Main Content -->
<div class="main-content">
    
    <!-- Welcome Card -->
    <div class="glass-card" style="animation: fadeInUp 0.6s ease;">
        <h2><?php echo htmlspecialchars($greet); ?>, <?php echo htmlspecialchars($teacher_name); ?>! 👋</h2>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
            Welcome to your Teacher Dashboard. 
            <?php if($is_class_teacher): ?>
                <strong style="color: var(--gradient-1);">Class Teacher</strong> access enabled.
            <?php endif; ?>
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-cards">
        <!-- My Classes -->
        <div class="stat-card" style="animation-delay: 0.1s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-chalkboard"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $my_classes; ?>">0</div>
            <div class="stat-card-label">My Classes</div>
            <div class="stat-card-trend">
                <i class="fas fa-book-open"></i> Teaching
            </div>
        </div>

        <!-- Total Students (Class Teacher) -->
        <div class="stat-card" style="animation-delay: 0.2s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $total_students; ?>">0</div>
            <div class="stat-card-label"><?php echo $is_class_teacher ? 'Class Students' : 'Teaching Students'; ?></div>
            <div class="stat-card-trend">
                <i class="fas fa-users"></i> Active
            </div>
        </div>

        <!-- Study Materials -->
        <div class="stat-card" style="animation-delay: 0.3s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $uploaded_materials; ?>">0</div>
            <div class="stat-card-label">Study Materials</div>
            <div class="stat-card-trend">
                <i class="fas fa-upload"></i> Uploaded
            </div>
        </div>

        <!-- Pending Complaints / Attendance -->
        <div class="stat-card" style="animation-delay: 0.4s;">
            <div class="stat-card-header">
                <div class="stat-card-icon">
                    <i class="fas fa-<?php echo $is_class_teacher ? 'exclamation-circle' : 'calendar-check'; ?>"></i>
                </div>
            </div>
            <div class="stat-card-value" data-count="<?php echo $is_class_teacher ? $pending_complaints : $attendance_sessions; ?>">0</div>
            <div class="stat-card-label"><?php echo $is_class_teacher ? 'Pending Complaints' : 'Attendance (Week)'; ?></div>
            <div class="stat-card-trend <?php echo ($is_class_teacher && $pending_complaints > 0) ? 'down' : ''; ?>">
                <i class="fas fa-<?php echo $is_class_teacher ? 'tasks' : 'check-circle'; ?>"></i> 
                <?php echo $is_class_teacher ? 'To Review' : 'Sessions'; ?>
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
            <div class="quick-action-btn" onclick="location.href='Attendance.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span class="quick-action-label">Take Attendance</span>
            </div>

            <div class="quick-action-btn" onclick="location.href='StudyMaterials.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-upload"></i>
                </div>
                <span class="quick-action-label">Upload Material</span>
            </div>

            <?php if($is_class_teacher): ?>
            <div class="quick-action-btn" onclick="location.href='Complaints.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <span class="quick-action-label">View Complaints</span>
            </div>
            <?php endif; ?>

            <div class="quick-action-btn" onclick="location.href='Notices.php'">
                <div class="quick-action-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <span class="quick-action-label">Post Notice</span>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        
        <!-- Attendance Distribution Chart -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Attendance This Week
                </h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Sessions Taken</span>
            </div>
            <div class="chart-container">
                <canvas id="attendanceChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <?php if($is_class_teacher && $class_students): ?>
        <!-- Class Students -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> My Class Students
                </h3>
                <a href="Students.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; padding: 1rem 0;">
                <?php while($student = mysqli_fetch_assoc($class_students)): ?>
                <div class="profile-card" style="padding: 1rem; text-align: center; transition: transform 0.3s ease;">
                    <div class="profile-avatar" style="width: 80px; height: 80px; margin: 0 auto 0.8rem;">
                        <img src="../Assets/Images/Students/<?php echo htmlspecialchars($student['student_photo']); ?>" 
                             alt="<?php echo htmlspecialchars($student['student_name']); ?>"
                             onerror="this.src='../Assets/Images/default-avatar.jpg'">
                    </div>
                    <h5 style="font-size: 0.9rem; margin-bottom: 0.3rem;"><?php echo htmlspecialchars($student['student_name']); ?></h5>
                    <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($student['student_email']); ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Timetable Preview -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt"></i> Today's Schedule
                </h3>
                <a href="Timetable.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Full Timetable</a>
            </div>

            <div class="timeline">
                <?php
                $today = date('l'); // e.g., "Monday"
                $today_schedule = mysqli_query($con, "
                    SELECT tt.*, s.subject_name, c.class_name, dp.start_time, dp.end_time
                    FROM tbl_timetable tt
                    INNER JOIN tbl_subject s ON tt.subject_id = s.subject_id
                    INNER JOIN tbl_class c ON tt.class_id = c.class_id
                    INNER JOIN tbl_departmentperiods dp ON tt.period_id = dp.period_id
                    WHERE tt.teacher_id='$teacher_id' AND tt.weekday='$today'
                    ORDER BY dp.period_no
                    LIMIT 5
                ");
                
                if(mysqli_num_rows($today_schedule) > 0):
                    while($period = mysqli_fetch_assoc($today_schedule)):
                ?>
                <div class="timeline-item">
                    <div class="timeline-date">
                        <?php echo date('h:i A', strtotime($period['start_time'])); ?> - 
                        <?php echo date('h:i A', strtotime($period['end_time'])); ?>
                    </div>
                    <div class="timeline-content">
                        <h4><?php echo htmlspecialchars($period['subject_name']); ?></h4>
                        <p style="color: var(--text-secondary); margin-top: 0.3rem;">
                            <?php echo htmlspecialchars($period['class_name']); ?>
                        </p>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <p class="empty-state-text">No classes scheduled for today</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activity Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem;">
        
        <!-- Recent Study Materials -->
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Recent Study Materials
                </h3>
                <a href="StudyMaterials.php" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
            </div>

            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($recent_materials) > 0):
                        while($material = mysqli_fetch_assoc($recent_materials)): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars(substr($material['title'], 0, 25)) . '...'; ?></td>
                        <td><?php echo htmlspecialchars($material['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['class_name']); ?></td>
                        <td><?php echo date('M d', strtotime($material['upload_date'])); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 2rem;">
                            <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--text-muted); opacity: 0.3;"></i>
                            <p style="color: var(--text-muted); margin-top: 1rem;">No materials uploaded yet</p>
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
    
    // Attendance Distribution Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if(attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($days_of_week); ?>,
                datasets: [{
                    label: 'Sessions Taken',
                    data: <?php echo json_encode($attendance_counts); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    borderRadius: 10
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

    // Hover effect on profile cards
    document.querySelectorAll('.profile-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.05)';
            this.style.boxShadow = '0 10px 30px rgba(102, 126, 234, 0.3)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php include("../Includes/Footer.php"); ?>