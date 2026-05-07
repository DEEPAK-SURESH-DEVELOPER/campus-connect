<?php
include("../Includes/TeacherHeader.php");

if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = $_SESSION['teacher_id'];
$sql = "SELECT * FROM tbl_complaint WHERE sender_type='Teacher' AND sender_id=? ORDER BY complaint_date DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();

$page_title = "My Complaints";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>My Complaints</span>';


?>

<div class="main-content">
    <div class="glass-card slide-up">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-comments"></i> My Complaints</h3>
            <a href="TeacherSendComplaint.php" class="btn-outline"><i class="fas fa-plus-circle"></i> New Complaint</a>
        </div>

        <?php if ($res->num_rows > 0): ?>
            <div class="complaint-list">
                <?php while ($row = $res->fetch_assoc()): ?>
                    <?php 
                        $status = $row['complaint_status'];
                        $statusClass = strtolower($status);
                        $statusBadge = match($status) {
                            'Resolved' => 'badge-success',
                            'Pending' => 'badge-warning',
                            'Rejected' => 'badge-error',
                            default => 'badge-info'
                        };
                    ?>
                    <div class="glass-card fade-in mb-2">
                        <div class="d-flex justify-between align-center mb-2">
                            <h3 class="text-gradient"><?= htmlspecialchars($row['complaint_subject']); ?></h3>
                            <span class="badge <?= $statusBadge; ?>"><?= htmlspecialchars($status); ?></span>
                        </div>
                        <p style="color:var(--text-secondary);"><?= nl2br(htmlspecialchars($row['complaint_details'])); ?></p>

                        <?php if (!empty($row['reply'])): ?>
                        <div class="glass-card mt-2" style="background:rgba(255,255,255,0.05); border-left:3px solid var(--gradient-1);">
                            <strong class="text-gradient"><i class="fas fa-reply"></i> Reply:</strong>
                            <p style="margin-top:0.5rem; color:var(--text-primary);"><?= nl2br(htmlspecialchars($row['reply'])); ?></p>
                            <small style="color:var(--text-muted);">Replied on: <?= date("d M Y, h:i A", strtotime($row['reply_date'])); ?></small>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-between align-center mt-3">
                            <small style="color:var(--text-muted);">
                                <i class="fas fa-calendar-alt"></i> Filed on: <?= date("d M Y, h:i A", strtotime($row['complaint_date'])); ?>
                            </small>
                            <?php if ($row['is_anonymous']): ?>
                                <span class="badge badge-info">Anonymous</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <div class="empty-state-title">No Complaints Found</div>
                <div class="empty-state-text">You haven’t submitted any complaints yet.</div>
                <a href="TeacherSendComplaint.php" class="btn-primary mt-2"><i class="fas fa-plus-circle"></i> Submit New</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>

<style>
.complaint-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.badge {
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-success {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.badge-warning {
    background: rgba(245, 158, 11, 0.15);
    color: var(--warning);
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.badge-error {
    background: rgba(239, 68, 68, 0.15);
    color: var(--error);
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.badge-info {
    background: rgba(59, 130, 246, 0.15);
    color: var(--info);
    border: 1px solid rgba(59, 130, 246, 0.3);
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
