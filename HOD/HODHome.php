<?php
session_start();
include("../Includes/HODHeader.php");

date_default_timezone_set('Asia/Kolkata');

// --- Check if HOD is logged in ---
if(!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php");

$hod_department_id = (int)$_SESSION['hod_department_id'];
$teacher_name = $_SESSION['hod_name'];
$hod_id = (int)$_SESSION['hod_id'];

// --- Fetch HOD photo (for page welcome; header uses session-based display) ---
$photoRes = $con->query("SELECT teacher_photo FROM tbl_teacher WHERE teacher_id='$hod_id'");
$teacher_photo_row = $photoRes ? $photoRes->fetch_assoc() : null;
$teacher_photo = $teacher_photo_row['teacher_photo'] ?? 'default.png';
$teacher_photo_path = file_exists("../Assets/Files/Teacher/$teacher_photo")
    ? "../Assets/Files/Teacher/$teacher_photo"
    : "../Assets/Images/default.png";

// --- Fetch department name ---
$deptRes = $con->query("SELECT department_name FROM tbl_department WHERE department_id='$hod_department_id'");
$department_name = ($deptRes && $row = $deptRes->fetch_assoc()) ? $row['department_name'] : 'Department';

// --- Department stats ---
$teachersCount = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT COUNT(*) as total_teachers 
    FROM tbl_teacher 
    WHERE department_id='$hod_department_id'
"))['total_teachers'];

$studentsCount = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT COUNT(*) as total_students 
    FROM tbl_student s
    JOIN tbl_class c ON s.class_id = c.class_id
    JOIN tbl_course cr ON c.course_id = cr.course_id
    WHERE cr.department_id='$hod_department_id' 
      AND c.is_completed=0
"))['total_students'];

$coursesCount = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT COUNT(*) as total_courses 
    FROM tbl_course 
    WHERE department_id='$hod_department_id'
"))['total_courses'];

// --- Today's classes ---
$dayname = date('l');
$classQry = "
    SELECT tt.*, c.class_name, s.subject_name, dp.start_time AS period_start, dp.end_time AS period_end
    FROM tbl_timetable tt
    JOIN tbl_class c ON tt.class_id = c.class_id
    JOIN tbl_subject s ON tt.subject_id = s.subject_id
    JOIN tbl_departmentperiods dp 
        ON tt.period_id = dp.period_id 
       AND dp.department_id = '$hod_department_id'
    WHERE tt.teacher_id = '$hod_id'
      AND tt.weekday = '$dayname'
      AND dp.is_break = 0
      AND tt.semester_id = c.semester_id
      AND c.is_completed = 0
    ORDER BY dp.start_time ASC
";
$classRes = mysqli_query($con, $classQry);

// --- Current period ---
$nowTime = date('H:i:s');
$currentClassQry = "
    SELECT tt.timetable_id
    FROM tbl_timetable tt
    JOIN tbl_class c ON tt.class_id = c.class_id
    JOIN tbl_departmentperiods dp 
        ON tt.period_id = dp.period_id 
       AND dp.department_id = '$hod_department_id'
    WHERE tt.teacher_id = '$hod_id'
      AND tt.weekday = '$dayname'
      AND dp.is_break = 0
      AND tt.semester_id = c.semester_id
      AND c.is_completed = 0
      AND '$nowTime' BETWEEN dp.start_time AND dp.end_time
    LIMIT 1
";
$currentClassRes = mysqli_query($con, $currentClassQry);
$currentClass = mysqli_fetch_assoc($currentClassRes);

// page meta (keeps consistent title)
$page_title = "HOD Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?php echo htmlspecialchars($page_title); ?> - Campus Connect</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<!-- Page-specific tweaks for premium class-cards -->
<style>
/* Premium horizontal class-item */
.class-item-premium {
    display:flex;
    align-items:center;
    gap:1rem;
    padding:1rem;
    border-radius:14px;
    position:relative;
    overflow:hidden;
    border-left: 6px solid transparent;
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
}
.class-item-premium:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 40px rgba(102,126,234,0.12);
}
/* status borders */
.class-status-current { border-left-image: linear-gradient(180deg, var(--gradient-1), var(--gradient-2)); border-left-style: solid; }
.class-status-marked  { border-left: 6px solid var(--success); }
.class-status-missed  { border-left: 6px solid var(--error); }

/* left info block */
.class-left {
    flex: 1 1 55%;
    min-width: 220px;
}
.class-left h4 { margin:0; font-size:1.05rem; }
.class-left p { margin:4px 0 0 0; color:var(--text-muted); font-size:0.95rem; }

/* time pill & countdown */
.class-meta {
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:0.5rem;
    min-width:170px;
}
.time-pill {
    padding:0.45rem 0.9rem;
    border-radius:999px;
    background: rgba(255,255,255,0.03);
    border:1px solid var(--border-glass);
    font-weight:600;
    font-size:0.95rem;
    color:var(--text-secondary);
}
.countdown-pill {
    padding:0.35rem 0.7rem;
    border-radius:10px;
    background: rgba(59,130,246,0.08);
    color:var(--info);
    font-weight:600;
    font-size:0.9rem;
    border:1px solid rgba(59,130,246,0.12);
}

/* floating attendance button */
.attendance-fab {
    padding:0.55rem 0.9rem;
    border-radius:999px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color:#fff;
    font-weight:700;
    border:none;
    cursor:pointer;
    box-shadow: 0 10px 30px rgba(102,126,234,0.25);
    transition: transform var(--transition-normal);
}
.attendance-fab:hover { transform: translateY(-3px); }

/* small icon column */
.class-icon {
    width:72px;
    height:72px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
    font-size:1.45rem;
    color:var(--text-primary);
    border:1px solid var(--border-glass);
}

/* glow for current */
.current-glow { box-shadow: 0 0 28px rgba(102,126,234,0.18); border-color: rgba(102,126,234,0.12); }

/* responsive tweaks */
@media (max-width: 768px){
    .class-item-premium { flex-direction:column; align-items:flex-start; padding:1rem; }
    .class-meta { align-items:flex-start; width:100%; }
    .class-left { width:100%; }
}
</style>
</head>
<body>
    

<main class="main-content">

    <!-- Welcome / Profile -->
    <section class="glass-card profile-card" style="display:flex;align-items:center;gap:1.25rem;">
        <div class="profile-avatar" style="width:88px;height:88px;border-radius:50%;border:4px solid var(--gradient-1);overflow:hidden;">
            <img src="<?php echo htmlspecialchars($teacher_photo_path); ?>" alt="HOD Photo">
        </div>
        <div style="flex:1;">
            <div class="profile-name"><?php echo htmlspecialchars($teacher_name); ?> <span style="font-size:0.95rem;margin-left:6px;">👋</span></div>
            <div class="profile-role"><?php echo htmlspecialchars($department_name); ?></div>
        </div>
        <div style="display:flex;gap:0.6rem;">
            <button class="action-btn" onclick="location.href='TeacherList.php'"><i class="fas fa-user-tie"></i> Manage Faculty</button>
            <button class="action-btn" onclick="location.href='ManageStudents.php'"><i class="fas fa-users"></i> Manage Students</button>
        </div>
    </section>

    <!-- Stat Cards -->
    <div class="dashboard-cards mt-2">
        <div class="stat-card" role="button" onclick="location.href='TeacherList.php'">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            </div>
            <div class="stat-card-value"><?php echo (int)$teachersCount; ?></div>
            <div class="stat-card-label">Total Teachers</div>
        </div>

        <div class="stat-card" role="button" onclick="location.href='ManageStudents.php'">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="fas fa-user-graduate"></i></div>
            </div>
            <div class="stat-card-value"><?php echo (int)$studentsCount; ?></div>
            <div class="stat-card-label">Total Students</div>
        </div>

        <div class="stat-card" role="button" onclick="location.href='HODViewCourses.php'">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="fas fa-book-open"></i></div>
            </div>
            <div class="stat-card-value"><?php echo (int)$coursesCount; ?></div>
            <div class="stat-card-label">Total Courses</div>
        </div>
    </div>

    <!-- Today's Classes Header -->
    <section class="glass-card mt-2">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-day"></i> Today's Classes</h3>
            <div>
                <a href="HODTimetable.php" class="btn-outline">View Timetable</a>
            </div>
        </div>

        <div class="classes-list mt-2">
            <?php
            if(mysqli_num_rows($classRes) > 0){
                while($row = mysqli_fetch_assoc($classRes)){
                    $periodStart = date("h:i A", strtotime($row['period_start']));
                    $periodEnd = date("h:i A", strtotime($row['period_end']));
                    $className = htmlspecialchars($row['class_name']);
                    $subject = htmlspecialchars($row['subject_name']);
                    $timetable_id = (int)$row['timetable_id'];

                    $attCheck = mysqli_fetch_assoc(mysqli_query($con,"
                        SELECT COUNT(*) as cnt 
                        FROM tbl_attendance_master 
                        WHERE timetable_id='$timetable_id' 
                          AND attendance_date='".date('Y-m-d')."'
                    "));
                    $attendanceMarked = $attCheck['cnt'] > 0;

                    // determine status class
                    $cardExtra = 'class-item-premium';
                    if($currentClass && $currentClass['timetable_id'] == $timetable_id && !$attendanceMarked){
                        $cardExtra .= ' class-status-current current-glow';
                        $statusLabel = 'Ongoing';
                    } else if($attendanceMarked){
                        $cardExtra .= ' class-status-marked';
                        $statusLabel = 'Marked';
                    } else {
                        // if time passed and not marked
                        $now = date('H:i:s');
                        if($now > $row['period_end']){
                            $cardExtra .= ' class-status-missed';
                            $statusLabel = 'Missed';
                        } else {
                            $cardExtra .= '';
                            $statusLabel = 'Upcoming';
                        }
                    }

                    // readable status text for JS fallback
                    $now = date('H:i:s');
                    if($attendanceMarked){
                        $statusText = '✅ Attendance marked';
                    } else if($now > $row['period_end']){
                        $statusText = '⛔ Time over - Attendance not marked';
                    } else {
                        $statusText = '';
                    }
                    ?>
                    <div class="<?php echo $cardExtra; ?> mb-1" style="display:flex;align-items:center;justify-content:space-between;">
                        <div class="class-icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>

                        <div class="class-left">
                            <h4><?php echo $className; ?> — <span style="font-weight:700;"><?php echo $subject; ?></span></h4>
                           <!-- <p>Teacher: <strong><?php echo htmlspecialchars($teacher_name); ?></strong></p> -->
                            <p style="margin-top:6px;color:var(--text-muted);font-size:0.92rem;">Time: <?php echo $periodStart; ?> — <?php echo $periodEnd; ?></p>
                        </div>

                        <div class="class-meta">
                           <!-- <div class="time-pill"><?php echo $periodStart; ?> — <?php echo $periodEnd; ?></div> -->
                            <div class="countdown-pill countdown" data-start="<?php echo $row['period_start']; ?>" data-end="<?php echo $row['period_end']; ?>" data-status="<?php echo htmlspecialchars($statusText); ?>"><?php echo htmlspecialchars($statusLabel); ?></div>

                            <?php if(!$attendanceMarked && !($now > $row['period_end'])): ?>
                                <a href="MarkAttendance.php?timetable_id=<?php echo $timetable_id; ?>" class="attendance-fab">Mark Attendance</a>
                            <?php else: ?>
                                <button class="attendance-fab" style="background:rgba(0,0,0,0.18);box-shadow:none;cursor:default;">
                                    <i class="fas fa-check" style="margin-right:8px;"></i><?php echo $attendanceMarked ? 'Done' : 'Closed'; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                } // end while
            } else {
                echo '<div class="empty-state"><div class="empty-state-icon"><i class="fas fa-calendar-times"></i></div><div class="empty-state-title">No classes scheduled for today.</div><div class="empty-state-text">Relax — there are no classes assigned for today.</div></div>';
            }
            ?>
        </div>
    </section>

</main>

<!-- Universal JS -->
<script src="../Assets/JQ/JQuery.js"></script>
<script src="../Assets/JS/universal.js"></script>

<!-- Countdown script (premium variant) -->
<script>
(function(){
    function parseTimeParts(t){
        var parts = t.split(':').map(function(p){ return parseInt(p,10); });
        return parts;
    }

    function updateCountdowns() {
        var now = new Date();

        document.querySelectorAll('.countdown').forEach(function(cd){
            var startParts = parseTimeParts(cd.dataset.start);
            var endParts = parseTimeParts(cd.dataset.end);
            var start = new Date(); start.setHours(startParts[0], startParts[1] || 0, startParts[2] || 0);
            var end = new Date(); end.setHours(endParts[0], endParts[1] || 0, endParts[2] || 0);

            var attendanceBtn = cd.parentElement.querySelector('.attendance-fab, .btn-primary, .attendance-btn');
            var statusText = cd.dataset.status || '';

            // if server-side set a statusText (attendance marked / time over), show it
            if(statusText){
                cd.textContent = statusText;
                if(statusText.includes('marked') || statusText.includes('Time over')){
                    if(attendanceBtn) attendanceBtn.style.display = 'none';
                }
                return;
            }

            if(now < start){
                var diff = start - now;
                var h = Math.floor(diff / (1000*60*60));
                var m = Math.floor((diff % (1000*60*60)) / (1000*60));
                var s = Math.floor((diff % (1000*60)) / 1000);
                cd.textContent = h > 0 ? 'Starts in '+h+'h '+m+'m '+s+'s' : 'Starts in '+m+'m '+s+'s';
                if(attendanceBtn) attendanceBtn.style.display = 'none';
            } else if(now >= start && now <= end){
                var remaining = end - now;
                var m = Math.floor((remaining % (1000*60*60)) / (1000*60));
                var s = Math.floor((remaining % (1000*60)) / 1000);
                cd.textContent = 'Ongoing - '+m+'m '+s+'s left';
                if(attendanceBtn) attendanceBtn.style.display = 'inline-block';
            } else {
                cd.textContent = '⛔ Time over - Attendance not marked';
                if(attendanceBtn) attendanceBtn.style.display = 'none';
            }
        });
    }

    updateCountdowns();
    setInterval(updateCountdowns, 1000);
})();
</script>

</body>
</html>
