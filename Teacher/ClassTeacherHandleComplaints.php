<?php
include("../Includes/TeacherHeader.php");

if (!isset($_SESSION['teacher_id'])) {
    header("location: ../Guest/Login.php");
    exit();
}
include("../Includes/TeacherSidebar.php");
$teacher_id = (int)$_SESSION['teacher_id'];

// Fetch classes handled
$clsQry = $con->prepare("SELECT class_id FROM tbl_class WHERE teacher_id=?");
$clsQry->bind_param("i", $teacher_id);
$clsQry->execute();
$res = $clsQry->get_result();
$classIds = [];

while ($r = $res->fetch_assoc()) {
    $classIds[] = $r['class_id'];
}
$clsQry->close();

$classList = empty($classIds) ? "0" : implode(",", $classIds);

// Reply handler
if(isset($_POST['btn_reply'])){
    $cid = $_POST['complaint_id'];
    $reply = $_POST['reply'];
    $status = $_POST['status'];

    $u = $con->prepare("UPDATE tbl_complaint SET reply=?, reply_date=NOW(), complaint_status=? WHERE complaint_id=?");
    $u->bind_param("ssi", $reply, $status, $cid);
    $u->execute();
    echo "<script>alert('Reply Sent Successfully'); location.href='ClassTeacherHandleComplaints.php';</script>";
    exit();
}

// sender details
function getSender($con,$type,$id){
    if($type == "Teacher"){
        $q = $con->query("SELECT teacher_name FROM tbl_teacher WHERE teacher_id=$id");
        $d = $q->fetch_assoc();
        return "Teacher: ".$d['teacher_name'];
    }
    if($type == "Student"){
        $q = $con->query("SELECT student_name FROM tbl_student WHERE student_id=$id");
        $d = $q->fetch_assoc();
        return "Student: ".$d['student_name'];
    }
    return "Anonymous";
}

// Get complaints
$q = $con->query("
    SELECT * FROM tbl_complaint 
    WHERE target_type='Class Teacher'
    AND target_id=$teacher_id
    AND class_id IN ($classList)
    ORDER BY complaint_date DESC
");

// Page setup
$page_title = "Class Teacher – Complaints";
$breadcrumb = "<span>Teacher</span> <i class='fas fa-chevron-right'></i> <span>Complaints</span>";

?>
<style>

    /* Universal Glass Dropdown */
select.form-control {
    background: rgba(255, 255, 255, 0.05) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-glass) !important;
    padding: 1rem 1.2rem !important;
    border-radius: 12px !important;
    backdrop-filter: blur(10px) !important;
    cursor: pointer;
}

/* On focus */
select.form-control:focus {
    background: rgba(255, 255, 255, 0.10) !important;
    border-color: var(--gradient-1) !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    outline: none;
}

/* Dropdown options (very important for readability) */
select.form-control option {
    background-color: var(--secondary-bg) !important;
    color: var(--text-primary) !important;
    padding: 10px !important;
}

/* Hover in dropdown */
select.form-control option:hover {
    background-color: rgba(102, 126, 234, 0.3) !important;
}

</style>
<div class="main-content">

    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-circle"></i> Complaints Assigned to You
            </h3>
        </div>

        <?php if($q->num_rows > 0){ ?>
            <div class="grid-complaints">
                <?php while($row = $q->fetch_assoc()): ?>

                <div class="glass-card complaint-box">

                    <h3 class="text-gradient"><?php echo $row['complaint_subject']; ?></h3>

                    <p class="text-muted" style="margin-top: 5px;">
                        <i class="fas fa-user"></i> 
                        <?php echo $row['is_anonymous'] ? "Anonymous" : getSender($con, $row['sender_type'], $row['sender_id']); ?>
                    </p>

                    <p class="text-muted">
                        <i class="fas fa-clock"></i>
                        <?php echo date("d M Y • h:i A", strtotime($row['complaint_date'])); ?>
                    </p>

                    <div class="glass-card" style="margin-top: 1rem;">
                        <p><?php echo nl2br(htmlspecialchars($row['complaint_details'])); ?></p>
                    </div>

                    <?php if(!empty($row['reply'])): ?>
                        <div class="glass-card" style="margin-top:1rem; border-left:3px solid var(--gradient-1);">
                            <strong>Your Reply:</strong>
                            <p><?php echo nl2br($row['reply']); ?></p>
                            <small class="text-muted">
                                <?php echo date("d M Y • h:i A", strtotime($row['reply_date'])); ?>
                            </small>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mt-3">
                        <textarea name="reply" class="form-control" placeholder="Type your reply..." required></textarea>

                        <!-- Styled dropdown -->
                        <select name="status" class="form-control" style="margin-top:1rem;">
                            <option value="Pending" <?php if($row['complaint_status']=='Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Reviewed" <?php if($row['complaint_status']=='Reviewed') echo 'selected'; ?>>Reviewed</option>
                            <option value="Resolved" <?php if($row['complaint_status']=='Resolved') echo 'selected'; ?>>Resolved</option>
                        </select>

                        <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">

                        <button name="btn_reply" class="btn-primary mt-2">Submit Reply</button>
                    </form>

                </div>

                <?php endwhile; ?>
            </div>

        <?php } else { ?>

            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <h3 class="empty-state-title">No complaints available</h3>
                <p class="empty-state-text">You have no pending complaints right now.</p>
            </div>

        <?php } ?>

    </div>
</div>


