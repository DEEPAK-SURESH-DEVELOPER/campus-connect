<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
if(!isset($_GET['teacher_id'])){
    echo "<script>alert('Invalid teacher'); window.location='TeacherList.php';</script>";
    exit;
}

$teacher_id = (int)$_GET['teacher_id'];

// --- Fetch Teacher Info ---
$teacherQry = "
    SELECT t.teacher_id, t.teacher_name, t.teacher_photo, d.designation_name
    FROM tbl_teacher t
    JOIN tbl_designation d ON t.designation_id = d.designation_id
    WHERE t.teacher_id = '$teacher_id'
";
$teacherRes = mysqli_query($con, $teacherQry);

if(!$teacherRes || mysqli_num_rows($teacherRes)==0){
    echo "<script>alert('Teacher not found'); window.location='TeacherList.php';</script>";
    exit;
}
$teacher = mysqli_fetch_assoc($teacherRes);

// --- Safe Photo Handling ---
$photo = $teacher['teacher_photo'] ?? '';
$photoPath = "../Assets/Files/Teacher/" . $photo;
if(empty($photo) || !file_exists($photoPath)) {
    $photoPath = "../Assets/Images/default.png";
}

// --- Fetch Periods ---
$periodsQry = "
    SELECT dp.period_id, dp.period_no, dp.start_time, dp.end_time, dp.is_break
    FROM tbl_departmentperiods dp
    WHERE dp.department_id = (SELECT department_id FROM tbl_teacher WHERE teacher_id='$teacher_id')
    ORDER BY dp.period_no ASC
";
$periodRes = mysqli_query($con, $periodsQry);

$periods = [];
while($row = mysqli_fetch_assoc($periodRes)){
    $periods[] = $row;
}

$timetableQry = "
    SELECT tt.*, s.subject_name, s.semester_id, c.course_name, cls.class_name, dp.period_no, dp.is_break
    FROM tbl_timetable tt
    JOIN tbl_subject s ON tt.subject_id = s.subject_id
    JOIN tbl_course c ON s.course_id = c.course_id
    JOIN tbl_class cls ON tt.class_id = cls.class_id
    JOIN tbl_departmentperiods dp ON tt.period_id = dp.period_id
    WHERE tt.teacher_id = '$teacher_id'
      AND tt.semester_id = cls.semester_id
      AND cls.is_completed = 0
    ORDER BY FIELD(tt.weekday,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'),
             dp.period_no
";

$timetableRes = mysqli_query($con, $timetableQry);

// --- Organize timetable by weekday + period ---
$ttData = [];
while($row = mysqli_fetch_assoc($timetableRes)){
    $ttData[$row['weekday']][$row['period_no']] = $row;
}

$weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// --- Page Meta ---
$page_title = "Teacher Timetable";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> 
               <a href="TeacherList.php">Teacher List</a> 
               <i class="fas fa-chevron-right"></i> <span>Timetable</span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?> - Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal CSS -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">
</head>
<body>
<main class="main-content">

    <!-- Teacher Header Card -->
    <div class="glass-card" style="display:flex;align-items:center;gap:1.5rem;">
        <div class="profile-avatar" style="width:90px;height:90px;border:3px solid var(--gradient-1);border-radius:50%;overflow:hidden;">
            <img src="<?php echo htmlspecialchars($photoPath); ?>" 
                 alt="Teacher Photo" 
                 style="width:100%;height:100%;object-fit:cover;">
        </div>
        <div>
            <h2 style="margin:0;"><?php echo htmlspecialchars($teacher['teacher_name']); ?></h2>
            <p style="color:var(--text-secondary);margin:0.3rem 0;">
                <strong>Designation:</strong> <?php echo htmlspecialchars($teacher['designation_name']); ?>
            </p>
        </div>
        <div style="margin-left:auto;">
            <button class="btn-outline" onclick="location.href='TeacherList.php'">
                <i class="fas fa-arrow-left"></i> Back to Teacher List
            </button>
        </div>
    </div>

    <!-- Weekly Timetable -->
    <div class="glass-card mt-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Weekly Timetable</h3>
        </div>

        <div class="timetable-wrapper" style="overflow-x:auto;margin-top:1rem;">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Day / Period</th>
                        <?php 
                        $periodIndex = 1;
                        foreach($periods as $p){
                            $start = date("g:i A", strtotime($p['start_time']));
                            $end   = date("g:i A", strtotime($p['end_time']));
                            if($p['is_break']==0){
                                echo "<th>Period {$periodIndex}<br><small>{$start} - {$end}</small></th>";
                                $periodIndex++;
                            } else {
                                echo "<th class='break-cell'>Break<br><small>{$start} - {$end}</small></th>";
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($weekdays as $day): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($day); ?></td>
                            <?php foreach($periods as $p): ?>
                                <?php if($p['is_break']==0): ?>
                                    <?php if(isset($ttData[$day][$p['period_no']])): 
                                        $row = $ttData[$day][$p['period_no']]; ?>
                                        <td class="period-cell">
                                            <div class="subject-name"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                                            <div class="class-name"><?php echo htmlspecialchars($row['class_name']); ?></div>
                                            <div class="course-sem">
                                                <?php echo htmlspecialchars($row['course_name']); ?> | Sem <?php echo htmlspecialchars($row['semester_id']); ?>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td class="period-cell empty">—</td>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <td class="break-cell">Break</td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Universal JS -->
<script src="../Assets/JS/universal.js"></script>

</body>
</html>
