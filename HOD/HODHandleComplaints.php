<?php
include("../Includes/HODHeader.php");

if (!isset($_SESSION['hod_id']) || !isset($_SESSION['hod_department_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php");
$dep_id = $_SESSION['hod_department_id'];

// --- Get sender details function ---
function getSenderDetails($con, $type, $id, $isAnonymous) {
    if ($isAnonymous) return ['name'=>'Anonymous','extra'=>''];
    if ($type === 'Student') {
        $sql = "SELECT s.student_name, c.class_name, d.department_name
                FROM tbl_student s 
                JOIN tbl_class c ON s.class_id=c.class_id
                JOIN tbl_course cr ON c.course_id = cr.course_id
                JOIN tbl_department d ON cr.department_id=d.department_id
                WHERE s.student_id=?";
    } else {
        $sql = "SELECT t.teacher_name, d.department_name
                FROM tbl_teacher t 
                JOIN tbl_department d ON t.department_id=d.department_id
                WHERE t.teacher_id=?";
    }
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if (!$r) return ['name'=>'Unknown','extra'=>''];
    return ($type==='Student')
        ? ['name'=>$r['student_name'],'extra'=>$r['class_name']." - ".$r['department_name']]
        : ['name'=>$r['teacher_name'],'extra'=>$r['department_name']];
}

// --- Reply handler ---
if (isset($_POST['btn_reply'])) {
    $cid = (int)$_POST['complaint_id'];
    $reply = trim($_POST['reply']);
    $status = $_POST['status'];
    if ($reply !== '') {
        $stmt = $con->prepare("UPDATE tbl_complaint SET reply=?, reply_date=NOW(), complaint_status=? WHERE complaint_id=?");
        $stmt->bind_param("ssi",$reply,$status,$cid);
        $stmt->execute();
    }
}

// --- Fetch HOD complaints ---
$stmt = $con->prepare("SELECT * FROM tbl_complaint WHERE target_type='HOD' AND department_id=? ORDER BY complaint_date DESC");
$stmt->bind_param("i",$dep_id);
$stmt->execute();
$res = $stmt->get_result();

$page_title = "Department Complaints";
$breadcrumb = '<span>Support</span> <i class="fas fa-chevron-right"></i> <span>HOD Complaints</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Department Complaint Management - HOD</title>

<style>

.main-content {
  padding: 1.5rem;
}

.complaint-container {
  max-width: 1000px;
  margin: 0 auto;
}

.complaint-title {
  color: var(--gradient-1);
  text-align: center;
  font-size: 1.7rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.complaint-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Complaint Card */
.complaint-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  transition: 0.3s ease;
}
.complaint-card:hover {
  box-shadow: 0 0 15px rgba(99,102,241,0.25);
}

.complaint-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.8rem;
}
.complaint-header h3 {
  color: var(--text-primary);
  font-size: 1.1rem;
  font-weight: 600;
}
.status {
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.3rem 0.7rem;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* Status Colors */
.status.reviewed {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: #fff;
}
.status.resolved {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #fff;
}
.status.pending {
  background: linear-gradient(135deg, #f59e0b, #f97316);
  color: #fff;
}

/* Complaint Meta */
.complaint-meta {
  font-size: 0.9rem;
  color: var(--text-secondary);
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}
.complaint-meta .meta-extra {
  color: var(--gradient-1);
  font-weight: 600;
}
.complaint-meta .date {
  font-size: 0.85rem;
  opacity: 0.8;
}

/* Body */
.complaint-body {
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border-glass);
  border-radius: 10px;
  padding: 0.9rem 1rem;
  margin-bottom: 1rem;
}
.complaint-body p {
  margin: 0;
  line-height: 1.5;
  color: var(--text-primary);
}

/* Reply Section */
.complaint-reply {
  background: rgba(34,197,94,0.1);
  border: 1px solid rgba(34,197,94,0.3);
  border-radius: 10px;
  padding: 0.7rem 1rem;
  margin-top: 0.8rem;
}
.complaint-reply strong {
  color: #22c55e;
}
.complaint-reply small {
  color: var(--text-secondary);
}

/* Action Form */
.complaint-action textarea {
  width: 100%;
  padding: 0.7rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--text-primary);
  margin-bottom: 0.6rem;
  resize: none;
  min-height: 70px;
  transition: 0.3s ease;
}
.complaint-action textarea:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 6px rgba(99,102,241,0.4);
}

.complaint-action select {
  width: 180px;
  background: rgba(255,255,255,0.08);
  border: 1px solid var(--border-glass);
  border-radius: 8px;
  color: var(--text-primary);
  padding: 0.6rem;
  margin-right: 0.5rem;
}
.complaint-action select option {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

.btn-reply {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 0.7rem 1.4rem;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s ease;
}
.btn-reply:hover {
  box-shadow: 0 0 10px rgba(99,102,241,0.5);
}

/* No complaint */
.no-complaint {
  text-align: center;
  color: var(--text-secondary);
  padding: 2rem;
  background: rgba(255,255,255,0.05);
  border-radius: 12px;
}

/* Responsive */
@media(max-width:768px){
  .complaint-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .complaint-meta {
    flex-direction: column;
    gap: 0.3rem;
  }
  .complaint-action select {
    width: 100%;
    margin-bottom: 0.6rem;
  }
}
</style>
</head>

<body>
<div class="main-content">
  <div class="complaint-container">
    <h2 class="complaint-title"><i class="fas fa-comments"></i> Department Complaints</h2>
    <div class="complaint-list">
      <?php
      if ($res->num_rows>0){
        while($row=$res->fetch_assoc()){
          $sender=getSenderDetails($con,$row['sender_type'],$row['sender_id'],$row['is_anonymous']);
          $statusClass=strtolower($row['complaint_status']);
      ?>
      <div class="complaint-card">
        <div class="complaint-header">
          <h3><?= htmlspecialchars($row['complaint_subject']); ?></h3>
          <span class="status <?= $statusClass ?>"><?= htmlspecialchars($row['complaint_status']); ?></span>
        </div>
        <div class="complaint-meta">
          <span><strong>From:</strong> <?= htmlspecialchars($sender['name']); ?></span>
          <span class="meta-extra"><?= htmlspecialchars($sender['extra']); ?></span>
          <span class="date"><?= date("d M Y, h:i A",strtotime($row['complaint_date'])); ?></span>
        </div>
        <div class="complaint-body">
          <p><?= nl2br(htmlspecialchars($row['complaint_details'])); ?></p>
          <?php if($row['reply']): ?>
          <div class="complaint-reply">
            <strong>Previous Reply:</strong>
            <p><?= nl2br(htmlspecialchars($row['reply'])); ?></p>
            <small>Replied on <?= date("d M Y, h:i A",strtotime($row['reply_date'])); ?></small>
          </div>
          <?php endif; ?>
        </div>
        <div class="complaint-action">
          <form method="post">
            <input type="hidden" name="complaint_id" value="<?= $row['complaint_id']; ?>">
            <textarea name="reply" placeholder="Write reply..." required></textarea>
            <select name="status" required>
              <option value="Reviewed">Reviewed</option>
              <option value="Resolved">Resolved</option>
            </select>
            <button type="submit" name="btn_reply" class="btn-reply"><i class="fas fa-paper-plane"></i> Send Reply</button>
          </form>
        </div>
      </div>
      <?php 
        } 
      } else {
        echo "<p class='no-complaint'>No complaints found.</p>";
      }
      ?>
    </div>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>