<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
date_default_timezone_set('Asia/Kolkata');
$teacher_id = (int)$_SESSION['hod_id'];
$weekday     = date('l');
$today       = date('Y-m-d');
$message     = "";
$has_active_class = false;
function now_time(){ return date('H:i:s'); }

$timetable_id_from_get = isset($_GET['timetable_id']) ? (int)$_GET['timetable_id'] : null;
$timetable_data = null;

/* ---------- 1. Load timetable info ---------- */
if ($timetable_id_from_get) {
    $sql = "
      SELECT t.*, p.start_time, p.end_time, c.class_name, s.subject_name
      FROM tbl_timetable t
      INNER JOIN tbl_departmentperiods p ON t.period_id = p.period_id
      INNER JOIN tbl_class c ON t.class_id = c.class_id
      INNER JOIN tbl_subject s ON t.subject_id = s.subject_id
      WHERE t.timetable_id = '$timetable_id_from_get'
        AND t.teacher_id = '$teacher_id'
        AND t.weekday = '$weekday'
        AND t.semester_id = c.semester_id
        AND c.is_completed = 0
    ";
    $res = $con->query($sql);
    if($res && $res->num_rows>0) $timetable_data=$res->fetch_assoc();
    else $message="⚠️ Selected period is not valid.";
}

/* ---------- 2. Auto detect current period ---------- */
if(!$timetable_data){
    $now = now_time();
    $qry = "
      SELECT t.*, p.start_time, p.end_time, c.class_name, s.subject_name
      FROM tbl_timetable t
      INNER JOIN tbl_departmentperiods p ON t.period_id = p.period_id
      INNER JOIN tbl_class c ON t.class_id = c.class_id
      INNER JOIN tbl_subject s ON t.subject_id = s.subject_id
      WHERE t.teacher_id='$teacher_id'
        AND t.weekday='$weekday'
        AND p.start_time <= '$now' AND p.end_time >= '$now'
        AND t.semester_id = c.semester_id
        AND c.is_completed = 0
      LIMIT 1
    ";
    $res = $con->query($qry);
    if($res && $res->num_rows>0) $timetable_data=$res->fetch_assoc();
}

/* ---------- 3. Validate period time ---------- */
if($timetable_data){
    extract($timetable_data);
    $start_time=$timetable_data['start_time']; $end_time=$timetable_data['end_time'];
    $subject_name=$timetable_data['subject_name']; $class_name=$timetable_data['class_name'];

    $now=now_time();
    if($now<$start_time)          $message="⏳ Period starts at ".date("h:i A",strtotime($start_time));
    elseif($now>$end_time)        $message="⛔ Period ended at ".date("h:i A",strtotime($end_time));
    else{
        $check=$con->query("SELECT 1 FROM tbl_attendance_master WHERE timetable_id='$timetable_id' AND attendance_date='$today'");
        if($check && $check->num_rows>0) $message="✅ Attendance already marked.";
        else $has_active_class=true;
    }
}else if($message==="") $message="⚠️ No active class currently.";

/* ---------- 4. Handle submit ---------- */
if(isset($_POST['btn_submit']) && isset($timetable_id)){
    $now=now_time();
    $v=$con->query("
      SELECT p.start_time,p.end_time,c.is_completed
      FROM tbl_timetable t
      JOIN tbl_departmentperiods p ON t.period_id=p.period_id
      JOIN tbl_class c ON t.class_id=c.class_id
      WHERE t.timetable_id='$timetable_id' AND t.teacher_id='$teacher_id'
    ");
    if(!$v || !$v->num_rows){ $message="⛔ Invalid timetable."; $has_active_class=false; }
    else{
        $vv=$v->fetch_assoc();
        if($now<$vv['start_time'] || $now>$vv['end_time']) $message="⛔ Not within class time.";
        else{
            $dup=$con->query("SELECT 1 FROM tbl_attendance_master WHERE timetable_id='$timetable_id' AND attendance_date='$today'");
            if($dup && $dup->num_rows){ $message="✅ Already marked."; }
            else{
                $status=$_POST['status']??[];
                $con->query("INSERT INTO tbl_attendance_master(timetable_id,teacher_id,class_id,subject_id,period_id,attendance_date)
                             VALUES('$timetable_id','$teacher_id','$class_id','$subject_id','$period_id','$today')");
                $mid=$con->insert_id;
                foreach($status as $sid=>$st){
                    $sid=(int)$sid; $st=$con->real_escape_string($st);
                    $con->query("INSERT INTO tbl_attendance_detail(att_master_id,student_id,status)
                                 VALUES('$mid','$sid','$st')");
                }
                $message="✅ Attendance successfully marked.";
                $has_active_class=false;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mark Attendance - Campus Connect</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
.alert{
    background:rgba(255,255,255,0.08);
    padding:12px 18px;
    border-radius:10px;
    margin-bottom:1rem;
    font-weight:500;
    color:var(--text-primary);
}
.attendance-table{
    width:100%;
    border-collapse:collapse;
    margin-top:1rem;
}
.attendance-table th, .attendance-table td{
    padding:10px 12px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.08);
}
.attendance-table th{
    background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));
    color:#fff;
}
.select-status{
    padding:6px 10px;
    border-radius:6px;
    border:none;
    background:rgba(255,255,255,0.1);
    color:#fff;
}
.mark-btn{
    margin-top:1rem;
    padding:10px 20px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:all .3s;
}
.mark-btn:hover{transform:translateY(-2px);box-shadow:0 4px 15px rgba(37,99,235,.3);}
.disabled{opacity:.6;cursor:not-allowed;}
.countdown-box{color:#facc15;text-align:right;}
</style>
</head>

<body>
<main class="main-content">
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Attendance Panel</h3>
        </div>

        <?php if($message!=""): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if($has_active_class): ?>
        <div class="class-card">
            <div class="header-info" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                <div>
                    <h2><?php echo htmlspecialchars($subject_name)." - ".htmlspecialchars($class_name); ?></h2>
                    <p><strong>Period:</strong> <?php echo date("h:i A",strtotime($start_time))." - ".date("h:i A",strtotime($end_time)); ?></p>
                </div>
                <div class="countdown-box">
                    <p>Time Remaining:</p>
                    <h3 id="countdown">--:--</h3>
                </div>
            </div>

            <form method="post" id="attendanceForm">
                <table class="attendance-table">
                    <thead><tr><th>#</th><th>Student Name</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                    $i=0;
                    $students=$con->query("SELECT student_id,student_name FROM tbl_student WHERE class_id='$class_id' ORDER BY student_name");
                    while($s=$students->fetch_assoc()){ $i++; ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo htmlspecialchars($s['student_name']); ?></td>
                            <td>
                                <select name="status[<?php echo $s['student_id']; ?>]" class="select-status">
                                    <option value="Present">✅ Present</option>
                                    <option value="Absent">❌ Absent</option>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <button type="submit" name="btn_submit" id="submitBtn" class="mark-btn">Submit Attendance</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
const endTime=new Date('<?php echo date("Y-m-d");?> <?php echo $end_time??'';?>').getTime();
const countdownEl=document.getElementById("countdown");
const submitBtn=document.getElementById("submitBtn");
if(countdownEl){
const timer=setInterval(()=>{
  const now=new Date().getTime();
  const diff=endTime-now;
  if(diff<=0){clearInterval(timer);countdownEl.textContent="00:00";
      if(submitBtn){submitBtn.disabled=true;submitBtn.classList.add("disabled");submitBtn.textContent="Time Over ⛔";}}
  else{
      const m=Math.floor((diff%(1000*60*60))/(1000*60));
      const s=Math.floor((diff%(1000*60))/1000);
      countdownEl.textContent=(m<10?"0"+m:m)+":"+(s<10?"0"+s:s);
  }
},1000);}
</script>
</body>
</html>
