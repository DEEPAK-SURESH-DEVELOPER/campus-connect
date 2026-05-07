<?php
include("../Includes/TeacherHeader.php");
date_default_timezone_set('Asia/Kolkata');

if(!isset($_SESSION['teacher_id'])){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");

$teacher_id = (int)$_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$department_id = $_SESSION['department_id'];
$designation_id = $_SESSION['designation_id'];
$is_class_teacher = $_SESSION['is_class_teacher'] ?? false;
$class_id = $_SESSION['class_id'] ?? null;

include("../Assets/Connection/Connection.php");

// --- Teacher photo ---
$photoRes = $con->query("SELECT teacher_photo FROM tbl_teacher WHERE teacher_id='$teacher_id'");
$teacher_photo_row = $photoRes ? $photoRes->fetch_assoc() : null;
$teacher_photo = $teacher_photo_row['teacher_photo'] ?? 'default.png';
$teacher_photo_path = file_exists("../Assets/Files/Teacher/$teacher_photo")
    ? "../Assets/Files/Teacher/$teacher_photo"
    : "../Assets/Images/default.png";

// --- Designation ---
$desigRes = $con->query("SELECT designation_name FROM tbl_designation WHERE designation_id='$designation_id'");
$designation_name = ($desigRes && $row = $desigRes->fetch_assoc()) ? $row['designation_name'] : 'Teacher';

// --- Class teacher info ---
$studentsCount = 0;
$class_name = '';
if($is_class_teacher && $class_id){
    $classRes = $con->query("SELECT class_name FROM tbl_class WHERE class_id='$class_id'");
    $row = $classRes ? $classRes->fetch_assoc() : null;
    $class_name = $row ? $row['class_name'] : '';
    $studentsCount = mysqli_fetch_assoc(mysqli_query($con,"
        SELECT COUNT(*) as total_students FROM tbl_student WHERE class_id='$class_id'
    "))['total_students'];
}

// --- Today classes ---
$dayname = date('l');
$classQry = "
    SELECT tt.*, c.class_name, s.subject_name, dp.start_time AS period_start, dp.end_time AS period_end
    FROM tbl_timetable tt
    JOIN tbl_class c ON tt.class_id = c.class_id
    JOIN tbl_subject s ON tt.subject_id = s.subject_id
    JOIN tbl_departmentperiods dp 
        ON tt.period_id = dp.period_id 
       AND dp.department_id = '$department_id'
    WHERE tt.teacher_id = '$teacher_id' 
      AND tt.weekday = '$dayname'
      AND dp.is_break = 0
      AND tt.semester_id = c.semester_id
      AND c.is_completed = 0
    ORDER BY dp.start_time ASC
";
$classRes = mysqli_query($con, $classQry);

// Current class
$nowTime = date('H:i:s');
$currentClassQry = "
    SELECT tt.timetable_id
    FROM tbl_timetable tt
    JOIN tbl_class c ON tt.class_id = c.class_id
    JOIN tbl_departmentperiods dp 
        ON tt.period_id = dp.period_id 
       AND dp.department_id = '$department_id'
    WHERE tt.teacher_id = '$teacher_id' 
      AND tt.weekday = '$dayname' 
      AND dp.is_break = 0
      AND tt.semester_id = c.semester_id
      AND c.is_completed = 0
      AND '$nowTime' BETWEEN dp.start_time AND dp.end_time
    LIMIT 1
";
$currentClassRes = mysqli_query($con, $currentClassQry);
$currentClass = mysqli_fetch_assoc($currentClassRes);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>

<link rel="stylesheet" href="../Assets/CSS/universal.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* FULL-WIDTH HOD STYLE CLASS CARDS */
.classes-grid {
    display:flex;
    flex-direction:column;
    gap:1rem;
    width:100%;
}

.class-item-premium {
    display:flex;
    align-items:center;
    gap:1.2rem;
    padding:1.2rem 1.4rem;
    border-radius:14px;
    width:100%;
    background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
    border-left:6px solid transparent;
    transition:.3s ease;
    box-shadow:0 10px 35px rgba(0,0,0,0.25);
}
.class-item-premium:hover {
    transform:translateY(-6px);
    box-shadow:0 20px 45px rgba(102,126,234,0.25);
}

/* Status Borders */
.class-status-current {
    border-left-image: linear-gradient(180deg, var(--gradient-1), var(--gradient-2));
    border-left-style: solid;
}
.class-status-marked { border-left:6px solid var(--success); }
.class-status-missed { border-left:6px solid var(--error); }

.current-glow {
    box-shadow:0 0 25px rgba(102,126,234,0.25);
}

/* Icon Box */
.class-icon {
    width:75px;
    height:75px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,0.05);
    border:1px solid var(--border-glass);
    font-size:1.7rem;
}

/* Left Block */
.class-left {
    flex:1;
}
.class-left h4 {
    margin:0;
    font-size:1.2rem;
    font-weight:700;
}
.class-left p {
    margin:6px 0 0 0;
    color:var(--text-muted);
}

/* Right Column */
.class-meta {
    min-width:170px;
    display:flex;
    align-items:flex-end;
    flex-direction:column;
    gap:0.5rem;
}

/* Countdown */
.countdown-pill {
    padding:0.45rem 0.9rem;
    border-radius:10px;
    background:rgba(59,130,246,0.08);
    border:1px solid rgba(59,130,246,0.2);
    color:var(--info);
    font-weight:600;
}

/* Attendance Button */
.attendance-fab {
    padding:0.6rem 1rem;
    border-radius:999px;
    background:linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    font-weight:700;
    color:white;
    text-decoration:none;
    box-shadow:0 8px 25px rgba(102,126,234,0.35);
    transition:.3s;
}
.attendance-fab:hover {
    transform:translateY(-4px);
}

</style>
</head>
<body>

<main class="main-content">

    <!-- TOP PROFILE -->
    <div class="glass-card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
            <div style="display:flex;gap:1rem;align-items:center;">
                <div class="profile-avatar small" style="width:95px;height:95px;">
                    <img src="<?= htmlspecialchars($teacher_photo_path) ?>">
                </div>
                <div>
                    <div class="profile-name">Welcome, <?= htmlspecialchars($teacher_name) ?> 👋</div>
                    <div class="profile-role"><?= htmlspecialchars($designation_name) ?></div>

                    <?php if($is_class_teacher): ?>
                    <div style="margin-top:8px;color:var(--text-secondary);">
                        Class Teacher: <b><?= $class_name ?></b> • <?= $studentsCount ?> Students
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="color:var(--text-secondary);">
                <?= date("l, d M Y") ?>
            </div>
        </div>
    </div>

    <!-- TODAY CLASSES -->
    <section class="glass-card mt-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-day"></i> Today’s Classes</h3>
        </div>

        <div class="classes-grid">

<?php if(mysqli_num_rows($classRes) > 0){ ?>

<?php while($row = mysqli_fetch_assoc($classRes)){
    
    $start = $row['period_start'];
    $end   = $row['period_end'];

    $periodStart = date("h:i A", strtotime($start));
    $periodEnd   = date("h:i A", strtotime($end));

    $className = htmlspecialchars($row['class_name']);
    $subject   = htmlspecialchars($row['subject_name']);
    $tid       = $row['timetable_id'];

    // Attendance check
    $att = mysqli_fetch_assoc(mysqli_query($con,"
        SELECT COUNT(*) as cnt 
        FROM tbl_attendance_master
        WHERE timetable_id='$tid'
        AND attendance_date='".date('Y-m-d')."'
    "));
    $attendanceMarked = $att['cnt'] > 0;

    $now = date("H:i:s");

    // STATUS MAPPING
    $statusClassHOD = "class-item-premium";
    if($currentClass && $currentClass['timetable_id'] == $tid && !$attendanceMarked){
        $statusClassHOD .= " class-status-current current-glow";
        $statusText = "Ongoing";
    }
    elseif($attendanceMarked){
        $statusClassHOD .= " class-status-marked";
        $statusText = "Marked";
    }
    elseif($now > $end){
        $statusClassHOD .= " class-status-missed";
        $statusText = "Missed";
    }
    else{
        $statusText = "Upcoming";
    }
?>

<div class="<?= $statusClassHOD ?>">

    <div class="class-icon">
        <i class="fas fa-chalkboard"></i>
    </div>

    <div class="class-left">
        <h4><?= $className ?> — <b><?= $subject ?></b></h4>
        <p>Time: <?= $periodStart ?> – <?= $periodEnd ?></p>
    </div>

    <div class="class-meta">

        <div class="countdown-pill countdown"
            data-start="<?= $start ?>"
            data-end="<?= $end ?>"
            data-status="<?= $statusText ?>">
            <?= $statusText ?>
        </div>

        <?php if(!$attendanceMarked && !($now > $end)): ?>
            <a href="../Teacher/MarkAttendance.php?timetable_id=<?= $tid ?>" class="attendance-fab">Mark Attendance</a>
        <?php else: ?>
            <button class="attendance-fab" style="opacity:.6;cursor:not-allowed;">
                <i class="fas fa-check"></i> <?= $attendanceMarked ? "Done" : "Closed" ?>
            </button>
        <?php endif; ?>

    </div>

</div>

<?php } ?>

<?php } else { ?>

<div style="text-align:center;">
    <div class="empty-state-icon"><i class="fas fa-calendar-times"></i></div>
    <div class="empty-state-title">No Classes Today</div>
</div>

<?php } ?>

        </div>
    </section>

</main>

<script src="../Assets/JS/universal.js"></script>

<!-- LIVE REAL-TIME COUNTDOWN -->
<script>
(function(){

    function parse(t){
        let p = t.split(":");
        return [ +p[0], +p[1], +p[2] ];
    }

    function update(){
        let now = new Date();

        document.querySelectorAll(".countdown").forEach(cd=>{
            let s = parse(cd.dataset.start);
            let e = parse(cd.dataset.end);
            let start = new Date();
            let end   = new Date();
            start.setHours(s[0],s[1],s[2]);
            end.setHours(e[0],e[1],e[2]);

            let fixedStatus = cd.dataset.status;

            if(fixedStatus=="Marked" || fixedStatus=="Missed"){
                cd.textContent = fixedStatus;
                return;
            }

            if(now < start){
                let diff = start - now;

                let h = Math.floor(diff/(1000*60*60));
                let m = Math.floor((diff%(1000*60*60))/(1000*60));
                let sec = Math.floor((diff%(1000*60))/1000);

                cd.textContent = 
                    (h>0 ? h+"h " : "") + m+"m "+sec+"s • Starts Soon";
            }
            else if(now >= start && now <= end){
                let diff = end - now;

                let m = Math.floor((diff%(1000*60*60))/(1000*60));
                let sec = Math.floor((diff%(1000*60))/1000);

                cd.textContent = "Ongoing • "+m+"m "+sec+"s left";
            }
            else{
                cd.textContent = "⛔ Time Over";
            }
        });
    }

    update();
    setInterval(update,1000);

})();
</script>

</body>
</html>
