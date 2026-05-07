<?php
// Sidebar.php - Reusable Sidebar Component
// Place in: Includes/Sidebar.php

// Define navigation based on user role
$navigation = [];

if(isset($_SESSION['admin_id'])) {
    // ADMIN NAVIGATION
    $navigation = [
        ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'AdminHome.php'],
        ['icon' => 'building', 'label' => 'Departments', 'url' => 'DepartmentList.php'],
        ['icon' => 'award', 'label' => 'Designations', 'url' => 'Designation.php'],
        ['icon' => 'chalkboard-teacher', 'label' => 'Teachers', 'url' => 'AdminTeacherList.php'],
        ['icon' => 'user-graduate', 'label' => 'Students', 'url' => 'StudentList.php'],
        
        ['icon' => 'calendar-alt', 'label' => 'Academic Year', 'url' => 'ManageAcademicYear.php'],
        ['icon' => 'bullhorn', 'label' => 'Notices', 'url' => 'AdminNotice.php'],
        ['icon' => 'exclamation-circle', 'label' => 'Complaints', 'url' => 'AdminHandleComplaints.php'],
       
        
        
        
        ['icon' => 'clock', 'label' => 'Teacher Registration', 'url' => 'TeacherRegistration.php'],
        ['icon' => 'users', 'label' => 'Promote Semester', 'url' => 'SemesterPromotion.php'],
        ['icon' => 'cog', 'label' => 'Profile', 'url' => 'AdminProfile.php'],
    ];
} elseif(isset($_SESSION['hod_id'])) {
    // HOD NAVIGATION
    $navigation = [
        ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'HODHome.php'],
      //  ['icon' => 'building', 'label' => 'My Department', 'url' => 'Department.php'],
        ['icon' => 'chalkboard-teacher', 'label' => 'Teachers', 'url' => 'TeacherList.php'],
        ['icon' => 'user-graduate', 'label' => 'Students', 'url' => 'ManageStudents.php'],
        ['icon' => 'users', 'label' => 'Classes', 'url' => 'HODClasses.php'],
        ['icon' => 'clock', 'label' => 'Timetable', 'url' => 'HODTimetable.php'],
        ['icon' => 'calendar-check', 'label' => 'Mark Attendance', 'url' => 'MarkAttendance.php'],
        ['icon' => 'bullhorn', 'label' => 'Send Notices', 'url' => 'HODNotice.php'],
        ['icon' => 'exclamation-circle', 'label' => 'Complaints', 'url' => 'HODHandleComplaints.php'],
        ['icon' => 'book', 'label' => 'View Courses', 'url' => 'HODViewCourses.php'],
        ['icon' => 'chart-bar', 'label' => 'View Notices', 'url' => 'HODViewNotice.php'],
        ['icon' => 'user-circle', 'label' => 'My Profile', 'url' => 'HODProfile.php'],
    ];
} elseif(isset($_SESSION['teacher_id'])) {
    // TEACHER NAVIGATION
    $is_class_teacher = isset($_SESSION['is_class_teacher']) && $_SESSION['is_class_teacher'];
    
    $navigation = [
        ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'TeacherHome.php'],
        ['icon' => 'calendar-alt', 'label' => 'My Timetable', 'url' => 'TeacherTimetable.php'],
        ['icon' => 'calendar-check', 'label' => 'Mark Attendance', 'url' => 'MarkAttendance.php'],
        ['icon' => 'clipboard-list', 'label' => 'Attendance Reports', 'url' => 'TeacherStudentReport.php'],
        ['icon' => 'book', 'label' => 'Send Complaint', 'url' => 'TeacherSendComplaint.php'],
        ['icon' => 'tasks', 'label' => 'View My Complaints', 'url' => 'TeacherViewComplaint.php'],
        ['icon' => 'bullhorn', 'label' => 'Notices', 'url' => 'TeacherViewNotice.php'],
    ];
    
    // Add class teacher specific items
    if($is_class_teacher) {
        $navigation[] = ['icon' => 'users-cog', 'label' => 'Register Student', 'url' => 'StudentRegistration.php'];
        $navigation[] = ['icon' => 'exclamation-circle', 'label' => 'Class Complaints', 'url' => 'ClassTeacherHandleComplaints.php'];
        $navigation[] = ['icon' => 'user-graduate', 'label' => 'Students', 'url' => 'StudentList.php'];
    }
    
    $navigation[] = ['icon' => 'user-circle', 'label' => 'My Profile', 'url' => 'TeacherProfile.php'];
    
} elseif(isset($_SESSION['student_id'])) {
    // STUDENT NAVIGATION
    $navigation = [
        ['icon' => 'tachometer-alt', 'label' => 'Dashboard', 'url' => 'StudentHome.php'],
        ['icon' => 'calendar-alt', 'label' => 'My Timetable', 'url' => 'StudentViewTimetable.php'],
        ['icon' => 'calendar-check', 'label' => 'My Attendance', 'url' => 'ViewAttendance.php'],
        //['icon' => 'book', 'label' => 'Study Materials', 'url' => 'StudyMaterials.php'],
        //['icon' => 'tasks', 'label' => 'Attendance Detailed', 'url' => 'ViewAttendanceDetailed.php'],
        ['icon' => 'bullhorn', 'label' => 'Notices', 'url' => 'StudentViewNotice.php'],
        ['icon' => 'exclamation-circle', 'label' => 'Submit Complaint', 'url' => 'StudentSendComplaint.php'],
        ['icon' => 'history', 'label' => 'My Complaints', 'url' => 'StudentViewComplaint.php'],
        //['icon' => 'download', 'label' => 'Downloads', 'url' => 'Downloads.php'],
        ['icon' => 'user-circle', 'label' => 'My Profile', 'url' => 'StudentProfile.php'],
    ];
}

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
    <div class="logo-icon">
        <i class="fas fa-graduation-cap"></i>
    </div>
    <h2 class="gradient-text">CampusConnect</h2>
</div>


    <!-- Navigation -->
    <nav>
        <ul class="sidebar-nav">
            <?php foreach($navigation as $item): ?>
            <li>
                <a href="<?php echo $item['url']; ?>" 
                   class="<?php echo ($current_page == $item['url']) ? 'active' : ''; ?>"
                   data-tooltip="<?php echo $item['label']; ?>">
                    <i class="fas fa-<?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <button class="theme-toggle-btn" data-tooltip="Toggle Theme">
            <i class="fas fa-moon"></i>
        </button>
        
        <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-glass);">
            <p style="font-size: 0.75rem; color: var(--text-muted);">
                Campus Connect v1.0
            </p>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay (for mobile) -->
<div class="sidebar-overlay"></div>

<style>
/* Sidebar Overlay for Mobile */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
}

/* Enhanced Sidebar Styles */
.sidebar-nav a {
    position: relative;
}

.sidebar-nav a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    transform: scaleY(0);
    transition: transform 0.3s ease;
    border-radius: 0 5px 5px 0;
}

.sidebar-nav a:hover::before,
.sidebar-nav a.active::before {
    transform: scaleY(1);
}

/* Glow effect on active link */
.sidebar-nav a.active {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    box-shadow: inset 0 0 20px rgba(102, 126, 234, 0.2);
}

.sidebar-nav a.active i {
    animation: iconPulse 2s ease-in-out infinite;
}

@keyframes iconPulse {
    0%, 100% {
        transform: scale(1);
        filter: drop-shadow(0 0 5px rgba(102, 126, 234, 0.5));
    }
    50% {
        transform: scale(1.1);
        filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.8));
    }
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
        box-shadow: none;
    }

    .sidebar.active {
        transform: translateX(0);
        box-shadow: 5px 0 30px rgba(0, 0, 0, 0.5);
    }
}
.sidebar-logo {
  text-align: center;
  padding: 25px 10px;
}

.logo-icon {
  font-size: 40px; /* size of the icon */
  color: #00ffe7;  /* neon blue – matches modern design */
  margin-bottom: 8px;
  transition: transform 0.3s ease, color 0.3s ease;
}

.logo-icon:hover {
  transform: scale(1.1);
  color: #0077ff;
}

.gradient-text {
  background: linear-gradient(45deg, #00ffe7, #0077ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 700;
  font-size: 1.4rem;
  letter-spacing: 1px;
}

@media (max-width: 768px) {
  .logo-icon {
    font-size: 32px;
  }
  .gradient-text {
    font-size: 1.1rem;
  }
}
.sidebar-nav a,
.sidebar-nav a::after {
    animation-duration: 1.6s !important;
    transition-duration: 1.6s !important;
}

</style>

<script>
// Enhanced Sidebar Mobile Functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    
    // Toggle sidebar on mobile
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            
            // Update icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            this.classList.remove('active');
            
            if (menuToggle) {
                const icon = menuToggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Close sidebar on link click (mobile only)
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                
                if (menuToggle) {
                    const icon = menuToggle.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
    });
    
    // Add ripple effect to sidebar links
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                top: ${y}px;
                left: ${x}px;
                background: radial-gradient(circle, rgba(102, 126, 234, 0.4) 0%, transparent 70%);
                border-radius: 50%;
                transform: scale(0);
                animation: sidebarRipple 0.6s ease-out;
                pointer-events: none;
                z-index: 0;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Add sidebar ripple animation
    const sidebarStyle = document.createElement('style');
    sidebarStyle.textContent = `
        @keyframes sidebarRipple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(sidebarStyle);
});
</script>