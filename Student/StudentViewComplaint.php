<?php
include("../Includes/StudentHeader.php");
if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}

include("../Includes/StudentSidebar.php");

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM tbl_complaint WHERE sender_type='Student' AND sender_id=? ORDER BY complaint_date DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();

/* --- Universal theme info --- */
$page_title = "My Complaints";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Complaints</span>';

?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Header -->
    <div class="glass-card fade-in text-center">
        <h2 class="text-gradient"><i class="fas fa-comment-dots"></i> My Complaints</h2>
        <p style="color: var(--text-secondary);">Track your submitted complaints and their current status</p>
    </div>

    <!-- Complaints List -->
    <div class="glass-card slide-up">
        <?php if ($res->num_rows > 0): ?>
            <div class="timeline">
                <?php while ($row = $res->fetch_assoc()):
                    $status = $row['complaint_status'];
                    $statusClass = strtolower($status);
                    $badgeClass = $statusClass == 'resolved' ? 'badge-success' : ($statusClass == 'pending' ? 'badge-warning' : 'badge-info');
                    $isAnonymous = $row['is_anonymous'] ? "<span class='badge badge-info'>Anonymous</span>" : "";
                ?>
                <div class="timeline-item complaint-<?php echo $statusClass; ?>">
                    <div class="timeline-date"><?php echo date("d M Y, h:i A", strtotime($row['complaint_date'])); ?></div>
                    <div class="timeline-content">
                        <h4 class="text-gradient"><?php echo htmlspecialchars($row['complaint_subject']); ?></h4>
                        <p style="color: var(--text-secondary); margin-top:0.5rem;"><?php echo nl2br(htmlspecialchars($row['complaint_details'])); ?></p>

                        <!-- Complaint Meta -->
                        <div class="mt-2 d-flex align-center gap-2">
                            <span class="badge <?php echo $badgeClass; ?>"><i class="fas fa-circle"></i> <?php echo ucfirst($status); ?></span>
                            <?php echo $isAnonymous; ?>
                        </div>

                        <!-- Reply Section -->
                        <?php if (!empty($row['reply'])): ?>
                        <div class="glass-card mt-3" style="padding:1rem; background: rgba(255,255,255,0.03);">
                            <h5 class="text-gradient mb-1"><i class="fas fa-reply"></i> Reply from <?php echo htmlspecialchars($row['target_type']); ?></h5>
                            <p style="color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($row['reply'])); ?></p>
                            <small style="color: var(--text-muted);">Replied on: <?php echo date("d M Y, h:i A", strtotime($row['reply_date'])); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                <div class="empty-state-title">No Complaints Found</div>
                <div class="empty-state-text">You haven't submitted any complaints yet.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="StudentSendComplaint.php" class="btn-primary"><i class="fas fa-plus"></i> Submit New Complaint</a>
       <!-- <a href="StudentHome.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back to Home</a> -->
    </div>

</div>

<!-- INTERNAL STYLES -->
<style>
.timeline-item {
    transition: all 0.3s ease;
}
.timeline-item:hover .timeline-content {
    transform: translateX(10px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.25);
}
.glass-card h5 {
    font-weight: 600;
}
.complaint-pending .timeline-content {
    border-left: 4px solid var(--warning);
}
.complaint-resolved .timeline-content {
    border-left: 4px solid var(--success);
}
.complaint-closed .timeline-content {
    border-left: 4px solid var(--info);
}
</style>

<!-- UNIVERSAL JS -->
<script src="../Assets/JS/universal.js"></script>
</body>
</html>
