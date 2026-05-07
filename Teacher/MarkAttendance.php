<?php
include("../Includes/TeacherHeader.php");

date_default_timezone_set('Asia/Kolkata');

if(!isset($_SESSION['teacher_id'])){
    header("location:../Login.php");
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = $_SESSION['teacher_id'];
$weekday = date('l');
$today = date('Y-m-d');
$message = "";
$has_active_class = false;

function now_time() {
    return date('H:i:s');
}

$timetable_id_from_get = isset($_GET['timetable_id']) ? (int)$_GET['timetable_id'] : null;
$timetable_data = null;

/* ---------- When timetable id is passed via GET ---------- */
if ($timetable_id_from_get) {
    $safe_tid = $timetable_id_from_get;
    $sql = "
      SELECT t.*, p.start_time, p.end_time, c.class_name, s.subject_name
      FROM tbl_timetable t
      INNER JOIN tbl_departmentperiods p ON t.period_id = p.period_id
      INNER JOIN tbl_class c ON t.class_id = c.class_id
      INNER JOIN tbl_subject s ON t.subject_id = s.subject_id
      WHERE t.timetable_id = '$safe_tid'
        AND t.teacher_id = '$teacher_id'
        AND t.weekday = '$weekday'
        AND t.semester_id = c.semester_id
        AND c.is_completed = 0
    ";
    $res = $con->query($sql);
    if ($res && $res->num_rows > 0) {
        $timetable_data = $res->fetch_assoc();
    } else {
        $message = "⚠️ Selected period is not valid (semester mismatch or class completed).";
    }
}

/* ---------- Auto detect current class ---------- */
if (!$timetable_data) {
    $current_time = now_time();
    $qry = "
      SELECT t.*, p.start_time, p.end_time, c.class_name, s.subject_name
      FROM tbl_timetable t
      INNER JOIN tbl_departmentperiods p ON t.period_id = p.period_id
      INNER JOIN tbl_class c ON t.class_id = c.class_id
      INNER JOIN tbl_subject s ON t.subject_id = s.subject_id
      WHERE t.teacher_id='$teacher_id'
        AND t.weekday='$weekday'
        AND p.start_time <= '$current_time'
        AND p.end_time >= '$current_time'
        AND t.semester_id = c.semester_id
        AND c.is_completed = 0
      LIMIT 1
    ";
    $result = $con->query($qry);
    if ($result && $result->num_rows > 0) {
        $timetable_data = $result->fetch_assoc();
    }
}

/* ---------- Validation ---------- */
if ($timetable_data) {
    $timetable_id = $timetable_data['timetable_id'];
    $class_id = $timetable_data['class_id'];
    $subject_id = $timetable_data['subject_id'];
    $period_id = $timetable_data['period_id'];
    $subject_name = $timetable_data['subject_name'];
    $class_name = $timetable_data['class_name'];
    $start_time = $timetable_data['start_time'];
    $end_time = $timetable_data['end_time'];

    $current_time = now_time();

    if ($current_time < $start_time) {
        $message = "⏳ This period hasn't started yet. Starts at " . date("h:i A", strtotime($start_time)) . ".";
    } else if ($current_time > $end_time) {
        $message = "⛔ This period has already ended at " . date("h:i A", strtotime($end_time)) . ".";
    } else {
        $check = "SELECT * FROM tbl_attendance_master 
                  WHERE timetable_id='$timetable_id' 
                  AND attendance_date='$today'";
        $checkres = $con->query($check);
        if ($checkres && $checkres->num_rows > 0) {
            $message = "✅ Attendance already marked for this period.";
        } else {
            $has_active_class = true;
        }
    }
} else {
    if ($message === "") {
        $message = "⚠️ No active class currently. Attendance can be marked only during your scheduled period.";
    }
}

/* ---------- Attendance submission ---------- */
if (isset($_POST['btn_submit'])) {
    if (!isset($timetable_id)) {
        $message = "⛔ No valid timetable selected.";
    } else {
        $current_time = now_time();
        $verify = "
          SELECT t.*, p.start_time, p.end_time, c.is_completed, c.semester_id
          FROM tbl_timetable t
          INNER JOIN tbl_departmentperiods p ON t.period_id = p.period_id
          INNER JOIN tbl_class c ON t.class_id = c.class_id
          WHERE t.timetable_id = '$timetable_id'
            AND t.teacher_id = '$teacher_id'
            AND t.weekday = '$weekday'
            AND t.semester_id = c.semester_id
            AND c.is_completed = 0
          LIMIT 1
        ";
        $verifyres = $con->query($verify);

        if (!$verifyres || $verifyres->num_rows == 0) {
            $message = "⛔ Attendance cannot be marked. Class might be completed.";
        } else {
            $vrow = $verifyres->fetch_assoc();
            $v_start = $vrow['start_time'];
            $v_end = $vrow['end_time'];

            if ($current_time < $v_start || $current_time > $v_end) {
                $message = "⛔ Attendance period ended or not started.";
            } else {
                $check2 = "SELECT * FROM tbl_attendance_master WHERE timetable_id='$timetable_id' AND attendance_date='$today' LIMIT 1";
                $check2res = $con->query($check2);
                if ($check2res && $check2res->num_rows > 0) {
                    $message = "✅ Attendance already marked for this period.";
                } else {
                    $status = $_POST['status'] ?? [];
                    $insert_master = "
                      INSERT INTO tbl_attendance_master(timetable_id, teacher_id, class_id, subject_id, period_id, attendance_date)
                      VALUES('$timetable_id', '$teacher_id', '$class_id', '$subject_id', '$period_id', '$today')
                    ";
                    if ($con->query($insert_master)) {
                        $att_master_id = $con->insert_id;
                        foreach ($status as $student_id => $value) {
                            $sid = (int)$student_id;
                            $val = $con->real_escape_string($value);
                            $insert_detail = "
                              INSERT INTO tbl_attendance_detail(att_master_id, student_id, status)
                              VALUES('$att_master_id', '$sid', '$val')
                            ";
                            $con->query($insert_detail);
                        }
                        $message = "✅ Attendance successfully marked.";
                        $has_active_class = false;
                    } else {
                        $message = "❌ Error saving attendance: " . $con->error;
                    }
                }
            }
        }
    }
}

$page_title = "Mark Attendance";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Attendance</span>';
?>

<div class="main-content">
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Attendance Panel</h3>
        </div>

        <?php if($message != ""): ?>
            <div class="glass-card" style="padding:1rem; margin-bottom:1rem;">
                <p style="color:var(--text-secondary); font-weight:500;"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <?php if($has_active_class): ?>
        <div class="glass-card">
            <div class="d-flex justify-between align-center mb-3">
                <div>
                    <h2 class="text-gradient"><?= htmlspecialchars($subject_name . " - " . $class_name) ?></h2>
                    <p style="color:var(--text-secondary);">
                        <i class="fas fa-clock"></i> 
                        <?= date("h:i A", strtotime($start_time)) ?> - <?= date("h:i A", strtotime($end_time)) ?>
                    </p>
                </div>
                <div class="text-right">
                    <p style="color:var(--text-muted);">Time Remaining:</p>
                    <h3 id="countdown" class="text-gradient">--:--</h3>
                </div>
            </div>

            <form method="post" id="attendanceForm">
                <table class="glass-table" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $students = $con->query("SELECT * FROM tbl_student WHERE class_id='$class_id' ORDER BY student_name");
                        $i=0;
                        while($srow = $students->fetch_assoc()):
                            $i++;
                        ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td><?= htmlspecialchars($srow['student_name']) ?></td>
                            <td>
                                <select name="status[<?= (int)$srow['student_id'] ?>]" class="form-control">
                                    <option value="Present">✅ Present</option>
                                    <option value="Absent">❌ Absent</option>
                                </select>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="text-center mt-3">
                    <button type="submit" name="btn_submit" id="submitBtn" class="btn-primary">
                        <i class="fas fa-save"></i> Submit Attendance
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const endTime = new Date('<?php echo date("Y-m-d"); ?> <?php echo $end_time ?? ""; ?>').getTime();
const countdownEl = document.getElementById("countdown");
const submitBtn = document.getElementById("submitBtn");

if(countdownEl){
  const timer = setInterval(() => {
    const now = new Date().getTime();
    const distance = endTime - now;

    if (distance <= 0) {
      clearInterval(timer);
      countdownEl.innerHTML = "00:00";
      if(submitBtn){
        submitBtn.disabled = true;
        submitBtn.textContent = "Time Over ⛔";
        submitBtn.style.background = "#555";
      }
    } else {
      const totalMinutes = Math.floor(distance / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      const hours = Math.floor(totalMinutes / 60);
      const displayMinutes = totalMinutes % 60;
      countdownEl.innerHTML = 
        (hours>0 ? (hours<10?'0':'')+hours+':' : '') +
        (displayMinutes<10?'0':'')+displayMinutes+":"+(seconds<10?'0':'')+seconds;
    }
  }, 1000);
}
</script>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>
</body>
</html>
