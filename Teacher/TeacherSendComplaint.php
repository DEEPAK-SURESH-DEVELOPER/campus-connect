<?php
include("../Includes/TeacherHeader.php");
if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = $_SESSION['teacher_id'];

// Fetch department_id of teacher
$qry = $con->prepare("SELECT department_id FROM tbl_teacher WHERE teacher_id=?");
$qry->bind_param("i", $teacher_id);
$qry->execute();
$department_id = $qry->get_result()->fetch_assoc()['department_id'];

$message = "";

if (isset($_POST['btn_submit'])) {
    $target_type = $_POST['target_type'];
    $subject = $_POST['subject'];
    $details = $_POST['details'];
    $is_anonymous = isset($_POST['anonymous']) ? 1 : 0;

    $target_id = null;
    $class_id = null;

    if ($target_type == 'HOD') {
        $stmt = $con->prepare("SELECT hod_teacher_id FROM tbl_department WHERE department_id=?");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $target_id = $stmt->get_result()->fetch_assoc()['hod_teacher_id'];
    }

    $ins = $con->prepare("INSERT INTO tbl_complaint 
        (sender_type, sender_id, target_type, target_id, department_id, class_id, complaint_subject, complaint_details, is_anonymous)
        VALUES ('Teacher', ?, ?, ?, ?, ?, ?, ?, ?)");
    $empty = null;
    $ins->bind_param("isiiissi", $teacher_id, $target_type, $target_id, $department_id, $class_id, $subject, $details, $is_anonymous);

    if ($ins->execute()) {
        echo "<script>
                alert('Complaint sent successfully!');
                window.location = 'TeacherViewComplaint.php';
              </script>";
        exit;
    } else {
        $message = "<div class='toast-notification error'><i class='fas fa-exclamation-circle'></i> Error sending complaint.</div>";
    }
}

$page_title = "Submit Complaint";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Submit Complaint</span>';
?>

<div class="main-content">
    <div class="glass-card slide-up">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-comment-dots"></i> Submit a Complaint</h3>
        </div>

        <?php if ($message): ?>
            <?= $message ?>
        <?php endif; ?>

        <form method="post" class="fade-in">
            <div class="form-group">
                <label class="form-label">Send Complaint To:</label>
                <select name="target_type" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="HOD">Head of Department (HOD)</option>
                    <option value="Admin">Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Subject:</label>
                <input type="text" name="subject" class="form-control" placeholder="Enter complaint subject..." required>
            </div>

            <div class="form-group">
                <label class="form-label">Details:</label>
                <textarea name="details" class="form-control" rows="5" placeholder="Describe your issue in detail..." required></textarea>
            </div>

            <div class="form-group d-flex align-center gap-1">
                <input type="checkbox" name="anonymous" id="anonymous">
                <label for="anonymous" style="color: var(--text-secondary); cursor:pointer;">Submit as Anonymous</label>
            </div>

            <div class="text-center mt-3">
                <button type="submit" name="btn_submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Complaint
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>

<style>
/* Additional Styling for Complaint Form */
form {
    max-width: 700px;
    margin: 0 auto;
}
.form-group {
    margin-bottom: 1.5rem;
}
textarea.form-control {
    resize: vertical;
}
.toast-notification.error {
    border-left: 4px solid var(--error);
    background: rgba(239, 68, 68, 0.15);
    color: var(--error);
    font-weight: 500;
}
.btn-primary i {
    margin-right: 8px;
}
/* DARK DROPDOWN FIX – matches universal theme */
.form-control,
select.form-control {
    background: rgba(255,255,255,0.08) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-glass) !important;
    border-radius: 10px !important;
    padding: 0.75rem 1rem !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    backdrop-filter: blur(6px) !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
}

/* Dropdown list items */
.form-control option {
    background-color: rgba(10,14,39,0.95) !important;
    color: #ffffff !important;
    padding: 6px 10px !important;
}

/* Focus effect */
.form-control:focus {
    border-color: var(--gradient-1) !important;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.35) !important;
}

</style>
</body>
</html>
