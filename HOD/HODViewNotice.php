<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$hod_id = (int)$_SESSION['hod_id'];
$hod_department_id = (int)$_SESSION['hod_department_id'];

/*
  NOTE: We intentionally include Header + Sidebar BEFORE running the query.
  This prevents conflicts if Header/Sidebar re-include connection or do session checks.
*/
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>View Notices - HOD | Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal Theme -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* Page-specific styling (keeps look consistent with universal theme) */
.notice-container { display:flex; flex-direction:column; gap:1rem; }
.notice-card { background: rgba(255,255,255,0.05); border-radius:12px; padding:20px; box-shadow:0 5px 15px rgba(0,0,0,0.25); backdrop-filter:blur(8px); transition:all .25s; }
.notice-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.35); }
.notice-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; color:var(--text-secondary); font-size:0.9rem; }
.notice-sender { color:#a5b4fc; font-weight:600; }
.notice-time { font-style:italic; color:#9ca3af; }
.notice-title { font-size:1.15rem; font-weight:600; color:#fff; margin-bottom:6px; }
.notice-content { color:#e5e7eb; font-size:0.95rem; line-height:1.5; margin-bottom:10px; }
.notice-file { border-top:1px solid rgba(255,255,255,0.1); padding-top:10px; margin-top:10px; }
.file-label { color:#fff; font-weight:500; display:block; margin-bottom:6px; }
.btn-preview, .btn-download { background:linear-gradient(135deg,#6366f1,#8b5cf6); border:none; color:#fff; border-radius:8px; padding:8px 14px; margin-right:6px; font-size:0.9rem; cursor:pointer; }
.preview-box { margin-top:10px; display:none; }
.preview-box img, .preview-box iframe { width:100%; max-height:400px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); }
.no-notice { text-align:center; color:var(--text-secondary); font-style:italic; margin-top:1rem; }
.back-link { text-decoration:none; background:rgba(255,255,255,0.1); padding:8px 14px; border-radius:8px; color:#fff; font-size:0.9rem; }
.back-link:hover { background:rgba(255,255,255,0.2); }
.debug { margin-top:1rem; color:#f87171; font-family:monospace; background:rgba(248,113,113,0.06); padding:10px; border-radius:8px; }
</style>

<script>
function togglePreview(btn, filePath, ext){
  const previewBox = btn.parentElement.querySelector(".preview-box");
  if (!previewBox) return;
  if (previewBox.style.display === "block") {
      previewBox.style.display = "none";
      previewBox.innerHTML = "";
      btn.textContent = "👁️ Preview";
      return;
  }
  let html = "";
  if (["jpg","jpeg","png","gif"].includes(ext)) {
      html = `<img src='../${filePath}' alt='Preview'>`;
  } else if (ext === "pdf") {
      html = `<iframe src='../${filePath}' frameborder='0'></iframe>`;
  } else {
      html = `<p style='color:#ccc;'>Preview not available for this file type.</p>`;
  }
  previewBox.innerHTML = html;
  previewBox.style.display = "block";
  btn.textContent = "❌ Close Preview";
}
</script>
</head>
<body>
<main class="main-content">
  <div class="glass-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 class="card-title"><i class="fas fa-bullhorn"></i> Department Notices</h3>
      <a href="HODNotice.php" class="back-link"><i class="fas fa-plus"></i> Send New Notice</a>
    </div>

    <div class="notice-container">
    <?php
    // Run the DB query AFTER header/sidebar includes to avoid conflicts
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
        n.target_type IN ('AllUsers','AllTeachers','AllHODs')
        OR (n.department_id = ? AND n.target_type IN ('DepartmentUsers','DepartmentTeachers','DepartmentAll'))
        OR (n.sender_type='HOD' AND n.sender_id=?)
    ORDER BY n.notice_date DESC
    ";

    if ($stmt = $con->prepare($qry)) {
        $stmt->bind_param("ii", $hod_department_id, $hod_id);
        $ok = $stmt->execute();
        if (!$ok) {
            // show DB error (helpful for debugging when header causes issues)
            echo "<div class='debug'>DB execute failed: " . htmlspecialchars($stmt->error) . "</div>";
        } else {
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $senderType = htmlspecialchars($row['sender_type']);
                    $senderName = htmlspecialchars($row['sender_name'] ?? 'Unknown');
                    $title = htmlspecialchars($row['title']);
                    $content = nl2br(htmlspecialchars($row['content']));
                    $filePath = $row['file_path'];
                    $fileName = htmlspecialchars($row['file_name']);
                    $noticeDate = date("d M Y, h:i A", strtotime($row['notice_date']));
                    $stypeClass = preg_replace('/[^A-Za-z0-9_-]/', '', $senderType);

                    echo "<div class='notice-card notice-{$stypeClass}'>
                            <div class='notice-header'>
                              <div class='notice-sender'>{$senderType}: {$senderName}</div>
                              <div class='notice-time'>{$noticeDate}</div>
                            </div>
                            <div class='notice-title'>{$title}</div>
                            <div class='notice-content'>{$content}</div>";
                    if (!empty($filePath)) {
                        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                        $relPath = htmlspecialchars($filePath);
                        echo "<div class='notice-file'>
                                <span class='file-label'>📎 {$fileName}</span>
                                <button type='button' class='btn-preview' onclick=\"togglePreview(this, '{$relPath}', '{$ext}')\">👁️ Preview</button>
                                <a class='btn-download' href='../{$relPath}' download>⬇️ Download</a>
                                <div class='preview-box'></div>
                              </div>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<div class='no-notice'>No notices available.</div>";
            }
            $stmt->close();
        }
    } else {
        // prepare failed
        echo "<div class='debug'>DB prepare failed: " . htmlspecialchars($con->error) . "</div>";
    }
    ?>
    </div>
  </div>
</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
