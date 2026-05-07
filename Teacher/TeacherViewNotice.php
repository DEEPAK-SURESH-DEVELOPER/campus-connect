<?php
include("../Includes/TeacherHeader.php");

if(!isset($_SESSION['teacher_id']) || !isset($_SESSION['department_id'])){
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = (int)$_SESSION['teacher_id'];
$department_id = (int)$_SESSION['department_id'];
$class_id = $_SESSION['class_id'] ?? 0;
$is_class_teacher = $_SESSION['is_class_teacher'] ?? false;

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
    n.target_type IN ('AllUsers','AllTeachers')
    OR (n.department_id = ? AND n.target_type IN ('DepartmentUsers','DepartmentTeachers','DepartmentAll'))
    OR (n.class_id = ? AND n.target_type = 'ClassStudents')
    OR (n.sender_type = 'Teacher' AND n.sender_id = ?)
ORDER BY n.notice_date DESC
";

$stmt = $con->prepare($qry);
$stmt->bind_param("iii", $department_id, $class_id, $teacher_id);
$stmt->execute();
$res = $stmt->get_result();

$page_title = "View Notices";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Notices</span>';


?>

<div class="main-content">
  <div class="glass-card slide-up">
      <div class="card-header">
          <h3 class="card-title"><i class="fas fa-bullhorn"></i> Notices</h3>
          <a href="TeacherHome.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php
      if($res->num_rows > 0){
          echo '<div class="notice-list">';
          while($row = $res->fetch_assoc()){
              $senderType = htmlspecialchars($row['sender_type']);
              $senderName = htmlspecialchars($row['sender_name'] ?? 'Unknown');
              $title = htmlspecialchars($row['title']);
              $content = nl2br(htmlspecialchars($row['content']));
              $filePath = $row['file_path'];
              $fileName = htmlspecialchars($row['file_name']);
              $noticeDate = date("d M Y, h:i A", strtotime($row['notice_date']));
              $stypeClass = preg_replace('/[^A-Za-z0-9_-]/', '', $senderType);
      ?>
          <div class="glass-card fade-in mb-3 notice-card">
              <div class="d-flex justify-between align-center mb-2">
                  <div>
                      <strong class="text-gradient"><?= $senderType ?>:</strong>
                      <span style="color:var(--text-secondary)"><?= $senderName ?></span>
                  </div>
                  <small style="color:var(--text-muted);"><i class="fas fa-clock"></i> <?= $noticeDate ?></small>
              </div>
              <h3 style="margin-bottom:0.5rem;"><?= $title ?></h3>
              <p style="color:var(--text-secondary);"><?= $content ?></p>

              <?php if(!empty($filePath)): 
                  $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                  $relPath = htmlspecialchars($filePath);
              ?>
              <div class="notice-file mt-2">
                  <div class="d-flex gap-2 align-center">
                      <span class="file-label"><i class="fas fa-paperclip"></i> <?= $fileName ?></span>
                      <button type="button" class="btn-outline small-btn" onclick="togglePreview(this, '<?= $relPath ?>', '<?= $ext ?>')">
                          <i class="fas fa-eye"></i> Preview
                      </button>
                      <a class="btn-primary small-btn" href="../<?= $relPath ?>" download>
                          <i class="fas fa-download"></i> Download
                      </a>
                  </div>
                  <div class="preview-box mt-2"></div>
              </div>
              <?php endif; ?>
          </div>
      <?php
          }
          echo '</div>';
      } else {
          echo '
          <div class="empty-state">
              <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
              <div class="empty-state-title">No Notices Found</div>
              <div class="empty-state-text">There are no notices available for you right now.</div>
          </div>';
      }
      ?>
  </div>
</div>

<script>
function togglePreview(btn, filePath, ext) {
  const previewBox = btn.closest('.notice-file').querySelector(".preview-box");
  if (previewBox.style.display === "block") {
      previewBox.style.display = "none";
      btn.innerHTML = '<i class="fas fa-eye"></i> Preview';
      previewBox.innerHTML = "";
      return;
  }
  let html = "";
  if (["jpg","jpeg","png","gif"].includes(ext)) {
      html = `<img src='../${filePath}' alt='Preview' style='max-width:100%; border-radius:10px;'>`;
  } else if (ext === "pdf") {
      html = `<iframe src='../${filePath}' frameborder='0' style='width:100%; height:400px; border-radius:12px;'></iframe>`;
  } else {
      html = `<p style='color:var(--text-secondary);'>Preview not available for this file type.</p>`;
  }
  previewBox.innerHTML = html;
  previewBox.style.display = "block";
  btn.innerHTML = '<i class="fas fa-times"></i> Close Preview';
}
</script>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>

<style>
.notice-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.notice-card {
    transition: all 0.3s ease;
}
.notice-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}
.notice-file .file-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}
.preview-box {
    display: none;
    margin-top: 1rem;
    border: 1px solid var(--border-glass);
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    padding: 1rem;
    backdrop-filter: blur(20px);
}
.small-btn {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 8px;
}
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}
.empty-state-title {
    font-size: 1.4rem;
    color: var(--text-primary);
    font-weight: 600;
}
.empty-state-text {
    color: var(--text-secondary);
}
</style>
</body>
</html>
