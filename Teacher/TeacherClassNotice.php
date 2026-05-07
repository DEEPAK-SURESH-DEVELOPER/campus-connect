<?php
include("../Includes/TeacherHeader.php");
session_start();

if(!isset($_SESSION['teacher_id']) || !isset($_SESSION['class_id']) || !$_SESSION['is_class_teacher']) {
    echo "<script>alert('Unauthorized access or session expired.'); window.location='../Guest/Login.php';</script>";
    exit;
}

$err = '';
$success = '';

$teacher_id = (int)$_SESSION['teacher_id'];
$class_id = (int)$_SESSION['class_id'];

if(isset($_POST['btn_send'])) {
    $title = trim($_POST['txt_title']);
    $content = trim($_POST['txt_content']);
    $file_name = null;
    $file_path = null;

    if($title === '') {
        $err = "Title is required.";
    }

    if(empty($err) && isset($_FILES['notice_file']) && $_FILES['notice_file']['name'] != '') {
        $allowedExt = ['pdf','txt','jpg','jpeg','png','doc','docx'];
        $uploadDir = __DIR__ . "/../Assets/Files/Notices/";
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $origName = basename($_FILES['notice_file']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if(!in_array($ext, $allowedExt)) {
            $err = "Invalid file type. Allowed: PDF, TXT, DOC, DOCX, JPG, JPEG, PNG";
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
        $stmt = $con->prepare("INSERT INTO tbl_notice 
            (sender_type, sender_id, class_id, target_type, title, content, file_name, file_path)
            VALUES ('ClassTeacher', ?, ?, 'ClassStudents', ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $teacher_id, $class_id, $title, $content, $file_name, $file_path);

        if($stmt->execute()) {
            $success = "Notice sent successfully to class students.";
        } else {
            $err = "Database error: " . $stmt->error;
            if($file_path && file_exists(__DIR__ . '/../' . $file_path)) unlink(__DIR__ . '/../' . $file_path);
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Send Notice - Class Teacher</title>
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
.form-wrapper {
    max-width: 650px;
    margin: auto;
}

.notice-card {
    background: var(--card-bg);
    border: 1px solid var(--border-glass);
    border-radius: 20px;
    padding: 2rem;
    backdrop-filter: blur(20px);
    box-shadow: var(--shadow-md);
    animation: fadeInUp 0.5s ease;
}

/* Title */
.notice-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: .8rem;
}

.notice-title i {
    font-size: 1.3rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Alerts */
.alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    font-weight: 600;
    animation: fadeIn 0.4s ease;
}

.alert.error {
    background: rgba(239,68,68,0.15);
    color: #ef4444;
    border-left: 4px solid #ef4444;
}

.alert.success {
    background: rgba(16,185,129,0.15);
    color: #10b981;
    border-left: 4px solid #10b981;
}

/* Buttons */
.btn-send {
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-normal);
    margin-top: 1rem;
}

.btn-send:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(102,126,234,0.4);
}

</style>
</head>

<body>

<?php include('../Includes/TeacherSidebar.php'); ?>

<div class="main-content">

    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bullhorn"></i> Send Notice
            </h3>
        </div>

        <div class="form-wrapper">
            <div class="notice-card">

                <!-- Alerts -->
                <?php if($err): ?>
                    <div class="alert error"><?= htmlspecialchars($err) ?></div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">

                    <div class="form-group">
                        <label class="form-label">Title <span style="color:red">*</span></label>
                        <input type="text" name="txt_title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message (Optional)</label>
                        <textarea name="txt_content" class="form-control" rows="6" placeholder="Write class notice..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Attach File (Optional)</label>
                        <input type="file" name="notice_file" class="form-control"
                               accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png">
                    </div>

                    <button type="submit" name="btn_send" class="btn-send">
                        <i class="fas fa-paper-plane"></i> Send Notice
                    </button>

                </form>

            </div>
        </div>
    </div>

</div>

</body>
</html>
