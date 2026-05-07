<?php
include("../Includes/StudentHeader.php");
if (!isset($_SESSION['student_id']) || !isset($_SESSION['class_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}

include("../Includes/StudentSidebar.php");

$student_id = (int)$_SESSION['student_id'];
$class_id = (int)$_SESSION['class_id'];

// Get department_id via class → course → department
$department_id = 0;
$qryDept = $con->prepare("
    SELECT c.department_id 
    FROM tbl_course c
    INNER JOIN tbl_class cl ON c.course_id = cl.course_id
    WHERE cl.class_id = ?
");
$qryDept->bind_param("i", $class_id);
$qryDept->execute();
$qryDept->bind_result($department_id);
$qryDept->fetch();
$qryDept->close();

// Fetch notices for this student
$qry = "
SELECT n.*,
       CASE n.sender_type
            WHEN 'Admin' THEN 'Administrator'
            WHEN 'HOD' THEN (SELECT teacher_name FROM tbl_teacher WHERE teacher_id = n.sender_id)
            WHEN 'Teacher' THEN (SELECT teacher_name FROM tbl_teacher WHERE teacher_id = n.sender_id)
            ELSE 'Unknown'
       END AS sender_name
FROM tbl_notice n
WHERE
    n.target_type IN ('AllUsers')
    OR (n.department_id = ? AND n.target_type IN ('DepartmentUsers','DepartmentAll'))
    OR (n.class_id = ? AND n.target_type = 'ClassStudents')
ORDER BY n.notice_date DESC
";
$stmt = $con->prepare($qry);
$stmt->bind_param("ii", $department_id, $class_id);
$stmt->execute();
$res = $stmt->get_result();

// Universal Header + Sidebar
$page_title = "Notices";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Notices</span>';

?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Header Card -->
    <div class="glass-card fade-in text-center">
        <h2 class="text-gradient"><i class="fas fa-bullhorn"></i> Latest Notices</h2>
        <p style="color: var(--text-secondary);">All campus-wide, department, and class announcements</p>
    </div>

    <!-- Notices List -->
    <div class="glass-card slide-up">
        <?php if ($res->num_rows > 0): ?>
            <div class="timeline">
                <?php while ($row = $res->fetch_assoc()):
                    $senderType = htmlspecialchars($row['sender_type']);
                    $senderName = htmlspecialchars($row['sender_name'] ?? 'Unknown');
                    $title = htmlspecialchars($row['title']);
                    $content = nl2br(htmlspecialchars($row['content']));
                    $filePath = $row['file_path'];
                    $fileName = htmlspecialchars($row['file_name']);
                    $noticeDate = date("d M Y, h:i A", strtotime($row['notice_date']));
                    $stypeClass = preg_replace('/[^A-Za-z0-9_-]/', '', $senderType);
                ?>
                <div class="timeline-item notice-<?php echo strtolower($stypeClass); ?>">
                    <div class="timeline-date"><?php echo $noticeDate; ?></div>
                    <div class="timeline-content">
                        <h4 class="text-gradient"><?php echo $title; ?></h4>
                        <p style="color: var(--text-secondary); margin: 0.5rem 0;"><?php echo $content; ?></p>
                        <div class="mt-2" style="font-size: 0.85rem; color: var(--text-muted);">
                            <i class="fas fa-user"></i> <?php echo $senderType; ?>: <?php echo $senderName; ?>
                        </div>

                        <?php if (!empty($filePath)): 
                            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                            $relPath = htmlspecialchars($filePath); ?>
                            <div class="mt-3">
                                <div class="badge badge-info"><i class="fas fa-paperclip"></i> <?php echo $fileName; ?></div><br>
                                <div class="mt-2 d-flex gap-2">
                                    <button class="btn-outline btn-preview" onclick="togglePreview(this, '<?php echo $relPath; ?>', '<?php echo $ext; ?>')">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                    <a class="btn-primary" href="../<?php echo $relPath; ?>" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                                <div class="preview-box mt-2" style="display:none;"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
                <div class="empty-state-title">No Notices Available</div>
                <div class="empty-state-text">There are no new announcements for your class or department.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- INTERNAL STYLES -->
<style>
.preview-box {
    background: var(--card-bg);
    border: 1px solid var(--border-glass);
    border-radius: 12px;
    padding: 1rem;
    margin-top: 1rem;
    text-align: center;
    animation: fadeIn 0.3s ease;
}
.preview-box img, 
.preview-box iframe {
    width: 100%;
    max-height: 400px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}
.notice-admin .timeline-content {
    border-left: 4px solid var(--gradient-1);
}
.notice-hod .timeline-content {
    border-left: 4px solid var(--gradient-2);
}
.notice-teacher .timeline-content {
    border-left: 4px solid var(--gradient-3);
}
</style>

<!-- PREVIEW SCRIPT -->
<script>
function togglePreview(btn, filePath, ext) {
    const previewBox = btn.closest('.timeline-content').querySelector(".preview-box");
    if (previewBox.style.display === "block") {
        previewBox.style.display = "none";
        btn.innerHTML = '<i class="fas fa-eye"></i> Preview';
        previewBox.innerHTML = "";
        return;
    }
    let html = "";
    if (["jpg","jpeg","png","gif"].includes(ext)) {
        html = `<img src='../${filePath}' alt='Preview'>`;
    } else if (ext === "pdf") {
        html = `<iframe src='../${filePath}' frameborder='0'></iframe>`;
    } else {
        html = `<p style='color:var(--text-secondary);'>Preview not available for this file type.</p>`;
    }
    previewBox.innerHTML = html;
    previewBox.style.display = "block";
    btn.innerHTML = '<i class="fas fa-times"></i> Close Preview';
}
</script>

<!-- UNIVERSAL JS -->
<script src="../Assets/JS/universal.js"></script>
</body>
</html>
