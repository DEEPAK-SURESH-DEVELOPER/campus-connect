<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
if (!isset($_GET['class_id']) || !isset($_GET['department_id'])) {
    echo "<script>alert('Missing parameters!'); window.location='AdminHome.php';</script>";
    exit;
}
$class_id = (int)$_GET['class_id'];
$department_id = (int)$_GET['department_id'];

/* ============================
   Load Class Info
============================ */
$classRes = $con->query("SELECT * FROM tbl_class WHERE class_id = $class_id LIMIT 1");
if (!$classRes || $classRes->num_rows === 0) {
    echo "<script>alert('Invalid class!'); window.location='AdminHome.php';</script>";
    exit;
}
$classRow = $classRes->fetch_assoc();
$class_name  = $classRow['class_name'];
$course_id   = (int)$classRow['course_id'];
$semester_id = (int)$classRow['semester_id'];

$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

/* ============================
   Fetch Periods & Subjects
============================ */
$periods = [];
$periodRes = $con->query("SELECT * FROM tbl_departmentperiods WHERE department_id = $department_id ORDER BY period_id ASC");
while ($r = $periodRes->fetch_assoc()) $periods[] = $r;

$subRes = $con->query("SELECT * FROM tbl_subject WHERE course_id = $course_id AND semester_id = $semester_id ORDER BY subject_name ASC");

$teacherRes = $con->query("
    SELECT ts.subject_id, t.teacher_id, t.teacher_name
    FROM tbl_teachersubject ts
    JOIN tbl_teacher t ON ts.teacher_id = t.teacher_id
    WHERE ts.subject_id IN (
        SELECT subject_id FROM tbl_subject WHERE course_id = $course_id AND semester_id = $semester_id
    )
");

$subjectTeachers = [];
if ($teacherRes) {
    while ($r = $teacherRes->fetch_assoc()) {
        $subjectTeachers[$r['subject_id']][] = [
            'teacher_id' => $r['teacher_id'],
            'teacher_name' => $r['teacher_name']
        ];
    }
}

/* ============================
   Build Assigned / Unassigned Options
============================ */
$assignedOptions = [];
$unassignedOptions = [];
if ($subRes) {
    while ($s = $subRes->fetch_assoc()) {
        $sid = (int)$s['subject_id'];
        if (!empty($subjectTeachers[$sid])) {
            foreach ($subjectTeachers[$sid] as $t) {
                $key = $sid . '-' . $t['teacher_id'];
                $assignedOptions[$key] = $s['subject_name'] . ' (' . $t['teacher_name'] . ')';
            }
        } else {
            $unassignedOptions[$sid] = $s['subject_name'] . ' (Not Assigned)';
        }
    }
}

/* ============================
   Load Existing Timetable
============================ */
$existing = [];
$timetableExists = false;
$tRes = $con->query("SELECT * FROM tbl_timetable WHERE class_id = $class_id AND semester_id = $semester_id");
if ($tRes) {
    while ($row = $tRes->fetch_assoc()) {
        $timetableExists = true;
        $existing[$row['weekday']][$row['period_id']] = [
            'subject_id' => $row['subject_id'],
            'teacher_id' => $row['teacher_id']
        ];
    }
}

/* ============================
   SAVE HANDLER (Fixed Original Logic)
============================ */
if (isset($_POST['btn_save'])) {

    foreach ($weekdays as $day) {
        foreach ($periods as $p) {
            $pid = (int)$p['period_id'];
            $field = "subject_{$day}_{$pid}";
            $val = $_POST[$field] ?? '';
            if ($val === '' || strpos($val, '-') === false) {
                echo "<script>alert('All periods must be filled before saving.'); window.history.back();</script>";
                exit;
            }
            [$sub_id, $teacher_id] = array_map('intval', explode('-', $val, 2));
        }
    }

    // conflict check + insert/update
    foreach ($weekdays as $day) {
        $escapedDay = $con->real_escape_string($day);
        foreach ($periods as $p) {
            $pid = (int)$p['period_id'];
            $val = $_POST["subject_{$day}_{$pid}"] ?? '';
            if ($val === '' || strpos($val, '-') === false) continue;
            [$sub_id, $teacher_id] = array_map('intval', explode('-', $val, 2));

            if ($teacher_id <= 0) continue;

            $conflictQry = "
                SELECT t.timetable_id, c.class_name, t.weekday, t.period_id, tr.teacher_name
                FROM tbl_timetable t
                JOIN tbl_class c ON t.class_id = c.class_id
                JOIN tbl_teacher tr ON t.teacher_id = tr.teacher_id
                WHERE t.teacher_id = $teacher_id
                AND t.period_id = $pid
                AND t.weekday = '$escapedDay'
                AND t.class_id != $class_id
                AND c.is_completed = 0
                LIMIT 1
            ";
            $conflictRes = $con->query($conflictQry);
            if ($conflictRes && $conflictRes->num_rows > 0) {
                $conf = $conflictRes->fetch_assoc();
                echo "<script>alert('Conflict: {$conf['teacher_name']} already has a class ({$conf['class_name']}) on {$conf['weekday']} Period {$conf['period_id']}'); window.history.back();</script>";
                exit;
            }

            $checkSql = "
                SELECT timetable_id FROM tbl_timetable
                WHERE class_id = $class_id AND semester_id = $semester_id
                AND period_id = $pid AND weekday = '$escapedDay' LIMIT 1
            ";
            $checkRes = $con->query($checkSql);
            if ($checkRes && $checkRes->num_rows > 0) {
                $con->query("
                    UPDATE tbl_timetable
                    SET subject_id = $sub_id, teacher_id = $teacher_id
                    WHERE class_id = $class_id AND semester_id = $semester_id
                    AND period_id = $pid AND weekday = '$escapedDay'
                ");
            } else {
                $con->query("
                    INSERT INTO tbl_timetable (class_id, semester_id, period_id, weekday, subject_id, teacher_id)
                    VALUES ($class_id, $semester_id, $pid, '$escapedDay', $sub_id, $teacher_id)
                ");
            }
        }
    }

    echo "<script>alert('Timetable saved successfully!'); window.location='CreateTimetable.php?class_id=$class_id&department_id=$department_id';</script>";
    exit;
}

$editMode = isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Create Timetable - <?php echo htmlspecialchars($class_name); ?></title>
<link rel="stylesheet" href="../Assets/CSS/universal.css">
<style>
:root {
  --sidebar-width: 260px;
  --header-height: 74px;
}

/* Layout + Centering */
body .timetable-page .page-inner {
  margin-left: var(--sidebar-width);
  padding-top: calc(var(--header-height) + 20px);
  padding-left: 28px;
  padding-right: 28px;
  display: flex;
  justify-content: center;
}

/* Card */
.timetable-card {
  width: 100%;
  max-width: 1160px;
  background: rgba(255,255,255,0.03);
  border-radius: 16px;
  padding: 26px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.45);
  border: 1px solid rgba(255,255,255,0.04);
}

/* Header */
.timetable-card .card-header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-bottom:18px;
}
.card-title {
  font-size:1.6rem;
  font-weight:700;
  color:var(--text-primary);
  text-align:center;
  flex:1;
}

/* Table */
.table-wrap {
  overflow-x:auto;
  padding:10px;
}
table.timetable {
  width:100%;
  border-collapse:collapse;
  min-width:820px;
}
table.timetable th, table.timetable td {
  padding:12px 10px;
  text-align:center;
  color:var(--text-primary);
  border:1px solid rgba(255,255,255,0.04);
}
table.timetable thead th {
  background:linear-gradient(90deg, var(--gradient-1), var(--gradient-2));
  color:#fff;
  font-weight:700;
}
table.timetable tbody tr:nth-child(even) td {
  background:rgba(255,255,255,0.02);
}

/* Uniform weekday gradient column */
table.timetable th:first-child, table.timetable td:first-child {
  background:linear-gradient(180deg, var(--gradient-1), var(--gradient-2))!important;
  color:#fff!important;
  font-weight:600;
  text-align:center;
  white-space:nowrap;
  border-right:1px solid rgba(255,255,255,0.08);
  box-shadow:inset 0 0 15px rgba(102,126,234,0.3);
}

/* Selects */
select.dropdown {
  background:rgba(255,255,255,0.05);
  border:1px solid var(--border-glass);
  border-radius:8px;
  padding:6px;
  color:var(--text-primary);
  width:100%;
}

/* Buttons */
.center { text-align:center; margin-top:20px; }
.btn-primary {
  background:linear-gradient(135deg,var(--gradient-1),var(--gradient-2));
  border:none;
  border-radius:10px;
  padding:10px 20px;
  color:white;
  font-weight:600;
  text-decoration:none;
  transition:0.3s;
}
.btn-primary:hover { transform:scale(1.05); }
.btn-secondary {
  background:transparent;
  border:1px solid var(--gradient-1);
  color:var(--text-primary);
  padding:8px 18px;
  border-radius:10px;
  text-decoration:none;
}
</style>
</head>
<body>
<div class="timetable-page">
  <div class="page-inner">
    <div class="timetable-card">
      <div class="card-header">
        <h2 class="card-title">Timetable — <?php echo htmlspecialchars($class_name); ?></h2>
        <div>
          <a href="ClassList.php?department_id=<?php echo $department_id; ?>&course_id=<?php echo $course_id; ?>" class="btn-secondary">Back</a>
          <?php if ($timetableExists && !$editMode): ?>
            <a href="CreateTimetable.php?class_id=<?php echo $class_id; ?>&department_id=<?php echo $department_id; ?>&edit=1" class="btn-primary">Edit</a>
          <?php endif; ?>
        </div>
      </div>

      <?php if (count($periods) === 0): ?>
        <p>No period structure set for this department.
        <a href="DepartmentPeriodSetting.php?department_id=<?php echo $department_id; ?>" class="btn-primary">Set Period Structure</a></p>
      <?php else: ?>
      <div class="table-wrap">
        <?php if ($timetableExists && !$editMode): ?>
          <table class="timetable">
            <thead>
              <tr>
                <th>Day / Period</th>
                <?php 
                $i=0;
                foreach ($periods as $p) {
                    $i++;
                    echo "<th>Period $i<br><small>".date("h:i A",strtotime($p['start_time']))." - ".date("h:i A",strtotime($p['end_time']))."</small></th>";
                } ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($weekdays as $day): ?>
                <tr>
                  <td><?php echo $day; ?></td>
                  <?php foreach ($periods as $p): 
                    $pid = $p['period_id'];
                    $subId = $existing[$day][$pid]['subject_id'] ?? null;
                    $teachId = $existing[$day][$pid]['teacher_id'] ?? null;
                    $label = '-';
                    if ($subId && $teachId) {

                     // Get subject name
                    $subName = $con->query("SELECT subject_name FROM tbl_subject WHERE subject_id = $subId")
                    ->fetch_row()[0] ?? null;

                    // Get teacher name
                    $teachName = $con->query("SELECT teacher_name FROM tbl_teacher WHERE teacher_id = $teachId")
                    ->fetch_row()[0] ?? null;

                    if ($subName && $teachName) {
                      $label = $subName . " (" . $teachName . ")";
                    }
                   }

                    ?>
                    <td><?php echo htmlspecialchars($label); ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <form method="post">
            <table class="timetable">
              <thead>
                <tr>
                  <th>Day / Period</th>
                  <?php 
                  $i=0;
                  foreach ($periods as $p) {
                      $i++;
                      echo "<th>Period $i<br><small>".date("h:i A",strtotime($p['start_time']))." - ".date("h:i A",strtotime($p['end_time']))."</small></th>";
                  } ?>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($weekdays as $day): ?>
                <tr>
                  <td><?php echo $day; ?></td>
                  <?php foreach ($periods as $p): 
                    $pid = $p['period_id'];
                    $selectedSubId = $existing[$day][$pid]['subject_id'] ?? '';
                    $selectedTeachId = $existing[$day][$pid]['teacher_id'] ?? '';
                    $selectedKey = ($selectedSubId && $selectedTeachId) ? ($selectedSubId . '-' . $selectedTeachId) : '';
                  ?>
                    <td>
                      <select name="subject_<?php echo $day . '_' . $pid; ?>" class="dropdown">
                        <option value="">Select Subject</option>
                        <?php foreach ($assignedOptions as $key => $label): ?>
                          <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($selectedKey === $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                          </option>
                        <?php endforeach; ?>
                        <?php foreach ($unassignedOptions as $sid => $label): ?>
                          <option value="" disabled><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <div class="center">
              <input type="submit" name="btn_save" value="Save Timetable" class="btn-primary">
            </div>
          </form>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
