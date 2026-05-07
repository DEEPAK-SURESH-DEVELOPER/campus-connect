<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

if (!isset($_GET['department_id'])) {
    echo "<script>alert('Invalid department!'); window.location='AdminHome.php';</script>";
    exit;
}

$department_id = intval($_GET['department_id']);
$existing = $con->query("SELECT * FROM tbl_departmentperiods WHERE department_id='$department_id' ORDER BY period_no");

if (isset($_POST['btn_save'])) {
    $apply_all = isset($_POST['apply_all']) ? 1 : 0;
    $period_ids = $_POST['period_id'] ?? [];
    $period_nos = $_POST['period_no'];
    $start_times = $_POST['start_time'];
    $end_times = $_POST['end_time'];

    // Delete removed rows
    $existingIds = [];
    $existing->data_seek(0);
    while ($r = $existing->fetch_assoc()) $existingIds[] = $r['period_id'];

    $submittedIds = array_filter($period_ids, fn($id) => !empty($id));
    $toDelete = array_diff($existingIds, $submittedIds);
    if (!empty($toDelete)) {
        $con->query("DELETE FROM tbl_departmentperiods WHERE period_id IN (" . implode(',', $toDelete) . ")");
    }

    // Insert / Update Periods
    for ($i = 0; $i < count($period_nos); $i++) {
        $pno = intval($period_nos[$i]);
        $st = $start_times[$i];
        $et = $end_times[$i];

        if (!empty($period_ids[$i])) {
            $pid = intval($period_ids[$i]);
            $con->query("UPDATE tbl_departmentperiods 
                        SET period_no='$pno', start_time='$st', end_time='$et' 
                        WHERE period_id='$pid'");
        } else {
            $con->query("INSERT INTO tbl_departmentperiods (department_id, period_no, start_time, end_time) 
                        VALUES ('$department_id', '$pno', '$st', '$et')");
        }
    }

    // Apply to all departments if selected
    if ($apply_all == 1) {
        $deptQry = $con->query("SELECT department_id FROM tbl_department WHERE department_id!='$department_id'");
        while ($dept = $deptQry->fetch_assoc()) {
            $did = $dept['department_id'];
            $con->query("DELETE FROM tbl_departmentperiods WHERE department_id='$did'");
            for ($i = 0; $i < count($period_nos); $i++) {
                $pno = intval($period_nos[$i]);
                $st = $start_times[$i];
                $et = $end_times[$i];
                $con->query("INSERT INTO tbl_departmentperiods (department_id, period_no, start_time, end_time) 
                             VALUES ('$did', '$pno', '$st', '$et')");
            }
        }
    }

    echo "<script>alert('Period structure saved successfully!'); location.href='CourseList.php?department_id=$department_id';</script>";
    exit;
}

$page_title = "Period Structure";
$breadcrumb = '<span>Departments</span> <i class="fas fa-chevron-right"></i> <span>Period Structure</span>';
?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Header -->
    <div class="glass-card d-flex align-center justify-between">
        <h2><i class="fas fa-clock"></i> Set Period Structure</h2>
        <a href="CourseList.php?department_id=<?php echo $department_id; ?>" class="action-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Period Table -->
    <div class="glass-card">
        <form method="post">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table"></i> Period Timings</h3>
            </div>
            <div class="table-wrapper" style="overflow-x:auto;">
                <table class="glass-table" id="periodTable">
                    <thead>
                        <tr>
                            <th>Period No</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($existing->num_rows > 0): ?>
                            <?php while ($row = $existing->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="number" class="form-control" name="period_no[]" value="<?php echo $row['period_no']; ?>" required></td>
                                    <td><input type="time" class="form-control" name="start_time[]" value="<?php echo $row['start_time']; ?>" required></td>
                                    <td><input type="time" class="form-control" name="end_time[]" value="<?php echo $row['end_time']; ?>" required></td>
                                    <td><button type="button" class="action-btn delete remove-btn"><i class="fas fa-times"></i> Remove</button></td>
                                    <input type="hidden" name="period_id[]" value="<?php echo $row['period_id']; ?>">
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td><input type="number" class="form-control" name="period_no[]" value="1" required></td>
                                <td><input type="time" class="form-control" name="start_time[]" required></td>
                                <td><input type="time" class="form-control" name="end_time[]" required></td>
                                <td><button type="button" class="action-btn delete remove-btn"><i class="fas fa-times"></i> Remove</button></td>
                                <input type="hidden" name="period_id[]" value="">
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-between align-center mt-3">
                <button type="button" class="btn-primary" onclick="addRow()">
                    <i class="fas fa-plus-circle"></i> Add Period
                </button>
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="apply_all">
                    <span>Apply this structure to all departments</span>
                </label>
            </div>

            <div class="mt-3">
                <button type="submit" name="btn_save" class="btn-primary w-full">
                    <i class="fas fa-save"></i> Save Structure
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function addRow() {
    const rowCount = $('#periodTable tbody tr').length + 1;
    const newRow = `
        <tr>
            <td><input type="number" class="form-control" name="period_no[]" value="${rowCount}" required></td>
            <td><input type="time" class="form-control" name="start_time[]" required></td>
            <td><input type="time" class="form-control" name="end_time[]" required></td>
            <td><button type="button" class="action-btn delete remove-btn"><i class="fas fa-times"></i> Remove</button></td>
            <input type="hidden" name="period_id[]" value="">
        </tr>`;
    $('#periodTable tbody').append(newRow);
}

$(document).on('click', '.remove-btn', function() {
    $(this).closest('tr').remove();
});
</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
