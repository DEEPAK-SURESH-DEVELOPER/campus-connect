<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

if (isset($_POST["btn_save"])) {
    $des = trim($_POST["txt_des"]);
    if ($des !== "") {
        $sql = "INSERT INTO tbl_designation(designation_name) VALUES('$des')";
        if ($con->query($sql)) {
            echo "<script>alert('Designation Added'); window.location='Designation.php';</script>";
        }
    } else {
        echo "<script>alert('Enter a valid designation name');</script>";
    }
}

// Delete Designation
if (isset($_GET["delID"])) {
    $delqry = "DELETE FROM tbl_designation WHERE designation_id='" . intval($_GET["delID"]) . "'";
    if ($con->query($delqry)) {
        echo "<script>alert('Designation Deleted'); window.location='Designation.php';</script>";
    }
}

$page_title = "Designation Management";
$breadcrumb = '<span>Administration</span> <i class="fas fa-chevron-right"></i> <span>Designations</span>';
?>

<!-- INTERNAL STYLING -->
<style>
.main-content {
  padding: 1.5rem;
}

/* Header */
.page-header {
  margin-bottom: 1.5rem;
}
.page-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
}
.page-header p {
  color: var(--text-secondary);
  margin-top: 0.3rem;
}

/* Form Card */
.form-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1.5rem 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
  margin-bottom: 2rem;
  color: var(--text-primary);
}
.form-card h3 {
  margin-bottom: 1rem;
  color: var(--gradient-1);
}
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: var(--text-secondary);
}
.form-group input[type="text"] {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--text-primary);
  outline: none;
  transition: all 0.3s ease;
}
.form-group input[type="text"]:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 6px rgba(102,126,234,0.4);
}

/* Button Group */
.button-group {
  display: flex;
  justify-content: flex-start;
  gap: 1rem;
}
.btn-primary, .btn-secondary {
  border: none;
  padding: 0.7rem 1.3rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}
.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
}
.btn-primary:hover { opacity: 0.9; }
.btn-secondary {
  background: rgba(255,255,255,0.1);
  color: var(--text-primary);
  border: 1px solid var(--border-glass);
}
.btn-secondary:hover {
  background: rgba(255,255,255,0.15);
}

/* Table Card */
.table-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1.5rem 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
.table-card h3 {
  color: var(--gradient-1);
  margin-bottom: 1rem;
}
.glass-table {
  width: 100%;
  border-collapse: collapse;
}
.glass-table th, .glass-table td {
  padding: 0.9rem;
  text-align: left;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  color: var(--text-primary);
}
.glass-table th {
  background: rgba(255,255,255,0.08);
  font-weight: 600;
  color: var(--gradient-1);
}
.glass-table tr:hover {
  background: rgba(255,255,255,0.05);
  transition: 0.3s ease;
}
.no-data {
  text-align: center;
  color: var(--text-secondary);
  padding: 1.2rem;
}

.action-link.delete-link {
  margin: 0 5px;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.85rem;
  transition: 0.3s ease;
  background: rgba(239,68,68,0.1);
  color: #f87171;
  border: 1px solid rgba(239,68,68,0.3);
}
.action-link.delete-link:hover {
  background: rgba(239,68,68,0.15);
  opacity: 0.85;
}

</style>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="page-header">
    <h2><i class="fas fa-briefcase"></i> Designation Management</h2>
    <p>Manage all designations in the system</p>
  </div>

  <!-- Add Designation Form -->
  <div class="form-card">
    <h3><i class="fas fa-plus-circle"></i> Add New Designation</h3>
    <form method="post">
      <div class="form-group">
        <label for="txt_des">Designation Name</label>
        <input type="text" name="txt_des" id="txt_des" placeholder="Enter designation name" required>
      </div>
      <div class="button-group">
        <input type="submit" name="btn_save" value="Add Designation" class="btn-primary">
        <input type="reset" value="Clear" class="btn-secondary">
      </div>
    </form>
  </div>

  <!-- Existing Designations Table -->
  <div class="table-card">
    <h3><i class="fas fa-list"></i> Existing Designations</h3>
    <table class="glass-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Designation</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 0;
        $selqry = "SELECT * FROM tbl_designation ORDER BY designation_name ASC";
        $rows = $con->query($selqry);
        if ($rows->num_rows > 0) {
            while ($data = $rows->fetch_assoc()) {
                $i++;
                echo "
<tr>
    <td>$i</td>
    <td>" . htmlspecialchars($data['designation_name']) . "</td>
    <td>
        <a href='Designation.php?delID=" . $data['designation_id'] . "' 
           class='action-link delete-link' 
           onclick='return confirm(\"Are you sure you want to delete this designation?\");'>
            <i class='fas fa-trash'></i> Delete
        </a>
    </td>
</tr>";


            }
        } else {
            echo "<tr><td colspan='3' class='no-data'>No designations found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>