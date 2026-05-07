<?php 
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
$err = '';
$success = '';

if(isset($_POST['btn_send'])) {
    $title   = trim($_POST['txt_title']);
    $content = trim($_POST['txt_content']);
    $target_type = $_POST['target_type']; 
    $department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : NULL;

    $file_name = null;
    $file_path = null;

    if($title === '') $err = "Title is required.";

    // Handle file upload
    if(empty($err) && isset($_FILES['notice_file']) && $_FILES['notice_file']['name'] != '') {
        $allowedExt = ['pdf','txt','jpg','jpeg','png'];
        $uploadDir = __DIR__ . "/../Assets/Files/Notices/";
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $origName = basename($_FILES['notice_file']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if(!in_array($ext, $allowedExt)) {
            $err = "Invalid file type. Allowed: PDF, TXT, JPG, JPEG, PNG";
        } else {
            $safeBase = preg_replace('/[^A-Za-z0-9_\-\.]/','_', pathinfo($origName, PATHINFO_FILENAME));
            $file_name = time().'_'.$safeBase.'.'.$ext;
            $dest = $uploadDir . $file_name;
            if(move_uploaded_file($_FILES['notice_file']['tmp_name'], $dest)) {
                $file_path = "Assets/Files/Notices/" . $file_name;
            } else {
                $err = "File upload failed.";
            }
        }
    }

    if(empty($err)) {
        $stype = 'Admin';
        $sid = (int)$_SESSION['admin_id'];

        $stmt = $con->prepare("INSERT INTO tbl_notice 
            (sender_type, sender_id, target_type, department_id, title, content, file_name, file_path)
            VALUES (?,?,?,?,?,?,?,?)");

        $stmt->bind_param("sisissss", $stype, $sid, $target_type, $department_id, $title, $content, $file_name, $file_path);

        if($stmt->execute()) {
            $success = "Notice sent successfully.";
        } else {
            $err = "Database error: " . $stmt->error;
            if($file_path && file_exists(__DIR__ . '/../' . $file_path)) unlink(__DIR__ . '/../' . $file_path);
        }
        $stmt->close();
    }
}

$page_title = "Send Notice";
$breadcrumb = '<span>Communication</span> <i class="fas fa-chevron-right"></i> <span>Notice Board</span>';
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Send Notice - Admin</title>

<!-- INTERNAL CSS -->
<style>
.main-content {
  padding: 1.5rem;
}

.notice-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  color: var(--text-primary);
  max-width: 800px;
  margin: 0 auto;
}

.notice-card h2 {
  color: var(--gradient-1);
  text-align: center;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.notice-form label {
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.4rem;
  display: block;
}

.notice-form input[type="text"],
.notice-form select,
.notice-form textarea,
.notice-form input[type="file"] {
  width: 100%;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--text-primary);
  font-size: 0.95rem;
  outline: none;
  transition: all 0.3s ease;
  backdrop-filter: blur(6px);
}
.notice-form input:focus,
.notice-form select:focus,
.notice-form textarea:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 6px rgba(99,102,241,0.4);
}

textarea {
  resize: none;
  min-height: 100px;
}

.btn-send {
  display: inline-block;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  font-weight: 600;
  border: none;
  padding: 0.9rem 1.6rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.5rem;
}
.btn-send:hover {
  opacity: 0.9;
  box-shadow: 0 0 10px rgba(99,102,241,0.5);
}

.alert {
  padding: 0.9rem 1.1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-weight: 500;
}
.alert.error {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.3);
  color: #f87171;
}
.alert.success {
  background: rgba(34,197,94,0.1);
  border: 1px solid rgba(34,197,94,0.3);
  color: #4ade80;
}

/* Department Dropdown Row */
#dept_row {
  margin-bottom: 1rem;
}

@media (max-width: 768px) {
  .notice-card {
    padding: 1.2rem;
  }
}
</style>

<script>
function toggleDeptSelect() {
  const target = document.getElementById("target_type").value;
  const deptRow = document.getElementById("dept_row");
  deptRow.style.display = (target === "DepartmentUsers") ? "block" : "none";
}
</script>
</head>

<body>
<div class="main-content">
  <div class="notice-card">
    <h2><i class="fas fa-bullhorn"></i> Send Notice</h2>

    <?php if($err): ?>
      <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
      <div class="alert success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="notice-form">
      <label>Title</label>
      <input type="text" name="txt_title" placeholder="Enter notice title" required>

      <label>Send To</label>
      <select name="target_type" id="target_type" required onchange="toggleDeptSelect()">
        <option value="">-- Select Target --</option>
        <option value="AllUsers">All Users (Students + Teachers + HODs)</option>
        <option value="AllTeachers">All Teachers</option>
        <option value="AllHODs">All HODs</option>
        <option value="DepartmentUsers">All Users of a Department</option>
      </select>

      <div id="dept_row" style="display:none;">
        <label>Select Department</label>
        <select name="department_id">
          <option value="">-- Select Department --</option>
          <?php
          $deptRes = $con->query("SELECT department_id, department_name FROM tbl_department");
          while($dept = $deptRes->fetch_assoc()){
              echo '<option value="'.$dept['department_id'].'">'.htmlspecialchars($dept['department_name']).'</option>';
          }
          ?>
        </select>
      </div>

      <label>Message (optional)</label>
      <textarea name="txt_content" placeholder="Write your notice here..."></textarea>

      <label>Attach File (optional)</label>
      <input type="file" name="notice_file" accept=".pdf,.txt,.jpg,.jpeg,.png">

      <button type="submit" name="btn_send" class="btn-send">
        <i class="fas fa-paper-plane"></i> Send Notice
      </button>
    </form>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
