<?php
include("../Includes/StudentHeader.php");
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/StudentSidebar.php");
$student_id = (int)$_SESSION['student_id'];

$studentSql = "
    SELECT s.*, c.class_name, c.semester_id, cr.course_name, d.department_name
    FROM tbl_student s
    LEFT JOIN tbl_class c ON s.class_id = c.class_id
    LEFT JOIN tbl_course cr ON c.course_id = cr.course_id
    LEFT JOIN tbl_department d ON cr.department_id = d.department_id
    WHERE s.student_id = '$student_id'
";
$studentRes = $con->query($studentSql);
if (!$studentRes || $studentRes->num_rows == 0) {
    echo "<script>alert('Student record not found'); window.location='../Guest/Login.php';</script>";
    exit;
}
$student = $studentRes->fetch_assoc();

$student_name = $student['student_name'];
$class_id = (int)$student['class_id'];
$semester_id = (int)$student['semester_id'];
$class_name = $student['class_name'];
$department_name = $student['department_name'];
$student_photo = $student['student_photo'] ?? 'default.png';
$photo_path = file_exists("../Assets/Files/Student/{$student_photo}") ? "../Assets/Files/Student/{$student_photo}" : "../Assets/Images/default.png";

// Attendance %
$attQ = "SELECT COUNT(*) AS total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present_count 
         FROM tbl_attendance_detail WHERE student_id = '$student_id'";
$attR = $con->query($attQ);
$attPerc = 0;
if ($attR) {
    $row = $attR->fetch_assoc();
    $total = (int)$row['total'];
    $present = (int)$row['present_count'];
    $attPerc = ($total > 0) ? round(($present / $total) * 100, 1) : 0;
}

// Today's timetable
$day = date('l');
$ttSql = "
    SELECT 
        tt.timetable_id, tt.subject_id, tt.teacher_id, tt.semester_id, dp.period_no,
        dp.start_time, dp.end_time, dp.is_break, s.subject_name, t.teacher_name,
        am.att_master_id, ad.status AS attendance_status
    FROM tbl_timetable tt
    JOIN tbl_departmentperiods dp ON tt.period_id = dp.period_id
    LEFT JOIN tbl_subject s ON tt.subject_id = s.subject_id
    LEFT JOIN tbl_teacher t ON tt.teacher_id = t.teacher_id
    LEFT JOIN tbl_attendance_master am ON am.timetable_id = tt.timetable_id AND am.attendance_date = CURDATE()
    LEFT JOIN tbl_attendance_detail ad ON ad.att_master_id = am.att_master_id AND ad.student_id = '$student_id'
    WHERE tt.class_id = '$class_id' AND tt.semester_id = '$semester_id' 
          AND tt.weekday = '$day' AND dp.is_break = 0
    ORDER BY dp.start_time ASC
";
$ttRes = $con->query($ttSql);

// Page configuration
$page_title = "Student Dashboard";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Dashboard</span>';

?>

<style>
/* Universal-aligned compact layout for student dashboard */
.main-content {
  margin-left: 280px;
  margin-top: 80px;
  padding: 2rem;
  min-height: calc(100vh - 80px);
}

/* Compact welcome section */
.welcome-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  background: var(--card-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border-glass);
  border-radius: 18px;
  padding: 1.5rem 2rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  transition: 0.3s;
}
.welcome-section:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 40px rgba(102,126,234,0.3);
}
.welcome-photo {
  width: 85px;
  height: 85px;
  border-radius: 50%;
  border: 3px solid var(--gradient-1);
  box-shadow: 0 0 20px var(--glow-primary);
  object-fit: cover;
}
.welcome-text h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.welcome-text p {
  margin-top: 0.3rem;
  color: var(--text-secondary);
  font-size: 0.95rem;
}

/* Attendance Card Row */
.cards-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-top: 2rem;
}
.card-item {
  flex: 1 1 250px;
  background: var(--card-bg);
  backdrop-filter: blur(20px);
  border-radius: 18px;
  border: 1px solid var(--border-glass);
  padding: 1.8rem;
  text-align: center;
  transition: 0.3s;
  box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}
.card-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(102,126,234,0.4);
}
.card-item i {
  font-size: 2rem;
  margin-bottom: 0.6rem;
  background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.card-item h3 {
  font-size: 1rem;
  color: var(--text-secondary);
  margin-bottom: 0.4rem;
}
.card-item p {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Classes Section */
.classes-section {
  margin-top: 2rem;
  background: var(--card-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border-glass);
  border-radius: 18px;
  padding: 1.5rem 2rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.classes-section h2 {
  margin-bottom: 1rem;
  font-size: 1.3rem;
  background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Individual Class Cards */
.class-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1rem 1.5rem;
  margin-bottom: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: 0.3s;
}
.class-card:hover {
  transform: translateY(-3px);
  background: rgba(102,126,234,0.1);
  box-shadow: 0 8px 25px rgba(102,126,234,0.2);
}
.class-info h3 {
  margin: 0;
  font-size: 1rem;
  color: var(--text-primary);
}
.class-info p {
  margin: 0.3rem 0;
  font-size: 0.9rem;
  color: var(--text-secondary);
}
.countdown-timer {
  padding: 0.4rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}
.badge-info {
  background: linear-gradient(90deg,var(--gradient-1),#90a7ff);
  color: #fff;
}
.badge-warning {
  background: linear-gradient(90deg,#f59e0b,#fbbf24);
  color: #000;
}
.badge-success {
  background: linear-gradient(90deg,#10b981,#34d399);
  color: #fff;
}
.badge-error {
  background: linear-gradient(90deg,#ef4444,#f87171);
  color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-section {
    flex-direction: column;
    text-align: center;
  }
  .welcome-photo {
    width: 70px;
    height: 70px;
  }
}
</style>

<div class="main-content">
  <div class="student-dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <img src="<?= htmlspecialchars($photo_path) ?>" alt="Student Photo" class="welcome-photo">
      <div class="welcome-text">
        <h2>Welcome, <?= htmlspecialchars($student_name) ?> 👋</h2>
        <p><?= htmlspecialchars($department_name) ?> Department | <?= htmlspecialchars($class_name) ?></p>
      </div>
    </div>

    <!-- Attendance Card -->
    <div class="cards-row">
      <div class="card-item">
        <i class="fa-solid fa-user-check"></i>
        <h3>Overall Attendance</h3>
        <p><?= $attPerc ?>%</p>
      </div>
    </div>

    <!-- Today's Classes -->
    <div class="classes-section">
      <h2><i class="fas fa-calendar-day"></i> Today's Classes (<?= date('d M Y (l)') ?>)</h2>
      <?php if ($ttRes && $ttRes->num_rows > 0): ?>
        <?php while ($r = $ttRes->fetch_assoc()):
          $sub = htmlspecialchars($r['subject_name']);
          $tch = htmlspecialchars($r['teacher_name']);
          $st = date("H:i", strtotime($r['start_time']));
          $et = date("H:i", strtotime($r['end_time']));
          $status = $r['attendance_status'] ?? 'Not Marked';
          $start_time_js = date("H:i:s", strtotime($r['start_time']));
          $end_time_js = date("H:i:s", strtotime($r['end_time']));
        ?>
        <div class="class-card" data-start="<?= $start_time_js ?>" data-end="<?= $end_time_js ?>" data-status="<?= $status ?>">
          <div class="class-info">
            <h3><?= $sub ?> (<?= $st ?> - <?= $et ?>)</h3>
            <p><i class="fa-solid fa-user"></i> <?= $tch ?></p>
          </div>
          <div class="class-status">
            <span class="countdown-timer"></span>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No classes scheduled for today.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function updateCountdowns() {
    const now = new Date();
    document.querySelectorAll('.class-card').forEach(card => {
        const start = new Date();
        const [h1,m1,s1] = card.dataset.start.split(':').map(Number);
        start.setHours(h1,m1,s1,0);
        const end = new Date();
        const [h2,m2,s2] = card.dataset.end.split(':').map(Number);
        end.setHours(h2,m2,s2,0);

        const timer = card.querySelector('.countdown-timer');
        const status = card.dataset.status;

        if (now < start) {
            let diff = start - now;
            let hrs = Math.floor(diff/3600000);
            let mins = Math.floor((diff%3600000)/60000);
            timer.textContent = `Starts in ${hrs}h ${mins}m`;
            timer.className = 'countdown-timer badge-info';
        } 
        else if (now >= start && now <= end) {
            timer.textContent = 'Ongoing';
            timer.className = 'countdown-timer badge-warning';
        } 
        else {
            if (status === 'Present') {
                timer.textContent = 'Present';
                timer.className = 'countdown-timer badge-success';
            } else if (status === 'Absent') {
                timer.textContent = 'Absent';
                timer.className = 'countdown-timer badge-error';
            } else {
                timer.textContent = 'Not Marked';
                timer.className = 'countdown-timer badge-warning';
            }
        }
    });
}
setInterval(updateCountdowns, 60000);
updateCountdowns();
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../Assets/JS/universal.js"></script>
</body>
</html>
