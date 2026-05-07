<?php
include("../Includes/HODHeader.php");

if(!isset($_SESSION['hod_id']) || !isset($_SESSION['hod_department_id'])){
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$err = '';
$success = '';

$hod_id = (int)$_SESSION['hod_id'];
$hod_department_id = (int)$_SESSION['hod_department_id'];

if(isset($_POST['btn_send'])) {
    $title   = trim($_POST['txt_title']);
    $content = trim($_POST['txt_content']);
    $target_type = $_POST['target_type'];
    $file_name = null;
    $file_path = null;

    if($title === '') {
        $err = "⚠️ Title is required.";
    }

    // --- File Upload ---
    if(empty($err) && isset($_FILES['notice_file']) && $_FILES['notice_file']['name'] != '') {
        $allowedExt = ['pdf','txt','jpg','jpeg','png','doc','docx'];
        $uploadDir = __DIR__ . "/../Assets/Files/Notices/";
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $origName = basename($_FILES['notice_file']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if(!in_array($ext, $allowedExt)) {
            $err = "Invalid file type. Allowed: PDF, TXT, DOC, DOCX, JPG, JPEG, PNG.";
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

    // --- Insert into DB ---
    if(empty($err)) {
        $stmt = $con->prepare("INSERT INTO tbl_notice 
            (sender_type, sender_id, target_type, department_id, title, content, file_name, file_path)
            VALUES ('HOD', ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isissss", $hod_id, $target_type, $hod_department_id, $title, $content, $file_name, $file_path);

        if($stmt->execute()) {
            $success = "✅ Notice sent successfully!";
        } else {
            $err = "Database error: " . $stmt->error;
            if($file_path && file_exists(__DIR__ . '/../' . $file_path)) unlink(__DIR__ . '/../' . $file_path);
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Send Notice - Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
.form-group {
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
}
.form-group label {
    margin-bottom: .4rem;
    font-weight: 500;
    color: var(--text-primary);
}
.form-control, textarea, select {
    padding: 10px 14px;
    border-radius: 10px;
    border: none;
    outline: none;
    background: rgba(255,255,255,0.08);
    color: var(--text-primary);
    font-size: 0.95rem;
    width: 100%;
}
textarea { resize: vertical; min-height: 100px; }

/* ✅ FIX: Dropdown list styling for dark mode */
select.form-control option {
    background-color: #1e1e2f;
    color: #ffffff;
    padding: 8px;
}
select.form-control:focus {
    background: rgba(255,255,255,0.12);
    outline: 2px solid var(--gradient-1, #6366f1);
}

.btn-send {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all .3s ease;
}
.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(59,130,246,.3);
}
.alert {
    padding: 12px 18px;
    border-radius: 10px;
    margin-bottom: 1rem;
    font-weight: 500;
}
.alert.error {
    background: rgba(239,68,68,0.15);
    color: #f87171;
}
.alert.success {
    background: rgba(16,185,129,0.15);
    color: #34d399;
}
.back-link {
    margin-bottom: 1rem;
}
.back-link a {
    color: #60a5fa;
    text-decoration: none;
}
.back-link a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<main class="main-content">
  <div class="glass-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-bullhorn"></i> Send Department Notice</h3>
    </div>

    <div class="back-link">
      <a href="HODHome.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>

    <?php if($err): ?>
      <div class="alert error"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" style="margin-top:1rem;">
      <div class="form-group">
        <label for="txt_title">Notice Title</label>
        <input type="text" name="txt_title" id="txt_title" class="form-control" required placeholder="Enter notice title">
      </div>

      <div class="form-group">
        <label for="target_type">Send To</label>
        <select name="target_type" id="target_type" class="form-control" required>
          <option value="">-- Select Target --</option>
          <option value="DepartmentAll">All (Teachers + Students) of My Department</option>
          <option value="DepartmentTeachers">Teachers Only (My Department)</option>
        </select>
      </div>

      <div class="form-group">
        <label for="txt_content">Message (Optional)</label>
        <textarea name="txt_content" id="txt_content" class="form-control" rows="6" placeholder="Write your notice message..."></textarea>
      </div>

      <div class="form-group">
        <label for="notice_file">Attach File (Optional)</label>
        <input type="file" name="notice_file" id="notice_file" class="form-control" accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png">
        <small style="color: var(--text-muted);">Allowed: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG</small>
      </div>

      <button type="submit" name="btn_send" class="btn-send">
        <i class="fas fa-paper-plane"></i> Send Notice
      </button>
    </form>
  </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
