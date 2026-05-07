<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

if(isset($_POST['btn_add_year'])){
    $startYear = intval($_POST['txt_start_year']);
    $endYear = $startYear + 1;
    $isCurrent = isset($_POST['chk_current']) ? 1 : 0;
    $semType = ($_POST['sem_type'] ?? 'odd');

    if($startYear > 0){
        if($isCurrent){
            $con->query("UPDATE tbl_academicyear SET is_current = 0 WHERE is_current = 1");
        }
        $yearName = $startYear."-".$endYear;
        $con->query("INSERT INTO tbl_academicyear(acyear_name, sem_type, is_current) 
                     VALUES('".$yearName."', '".$semType."', ".$isCurrent.")");
        echo "<script>alert('Academic year added successfully'); window.location='ManageAcademicYear.php';</script>";
        exit;
    }
}

if(isset($_GET['set_current'])){
    $id = intval($_GET['set_current']);
    $con->query("UPDATE tbl_academicyear SET is_current = 0 WHERE is_current = 1");
    $con->query("UPDATE tbl_academicyear SET is_current = 1 WHERE acyear_id=".$id);
    echo "<script>alert('Current academic year updated'); window.location='ManageAcademicYear.php';</script>";
    exit;
}

if(isset($_GET['del_id'])){
    $id = intval($_GET['del_id']);
    $con->query("DELETE FROM tbl_academicyear WHERE acyear_id=".$id);
    echo "<script>alert('Academic year deleted'); window.location='ManageAcademicYear.php';</script>";
    exit;
}

$yearsRes = $con->query("SELECT * FROM tbl_academicyear ORDER BY acyear_id ASC");

$page_title = "Manage Academic Years";
$breadcrumb = '<span>Administration</span> <i class="fas fa-chevron-right"></i> <span>Academic Years</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Academic Years</title>

<!-- INTERNAL CSS -->
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
  padding: 1.8rem 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.25);
  color: var(--text-primary);
  margin-bottom: 2rem;
  max-width: 700px;
}
.form-card h3 {
  margin-bottom: 1.2rem;
  color: var(--gradient-1);
}
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.3rem;
}
.form-group input[type="number"],
.form-group select {
  width: 100%;
  padding: 0.7rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--text-primary);
  outline: none;
  font-size: 0.9rem;
  transition: 0.3s ease;
}
.form-group input:focus, 
.form-group select:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 6px rgba(102,126,234,0.4);
}

.checkbox-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}
.checkbox-row label {
  color: var(--text-secondary);
  font-weight: 600;
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  border: none;
  padding: 0.8rem 1.3rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s ease;
}
.btn-primary:hover {
  opacity: 0.9;
  box-shadow: 0 0 10px rgba(99,102,241,0.4);
}

/* Table Card */
.table-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1.5rem 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.25);
  color: var(--text-primary);
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
  padding: 0.8rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  text-align: center;
  color: var(--text-primary);
}
.glass-table th {
  background: rgba(255,255,255,0.08);
  color: var(--gradient-1);
  font-weight: 600;
}
.glass-table tr:hover {
  background: rgba(255,255,255,0.05);
  transition: 0.3s ease;
}

/* Status badge */
.current {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #fff;
  padding: 0.3rem 0.7rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.85rem;
}
.not-current {
  color: var(--text-secondary);
}

/* Action Buttons */
.action-link {
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.85rem;
  transition: 0.3s ease;
  margin: 0 0.2rem;
}
.action-link:hover {
  opacity: 0.85;
}
.action-link.set {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: #fff;
}
.action-link.delete {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
}

/* Responsive */
@media(max-width:768px){
  .form-card, .table-card {
    padding: 1.2rem;
  }
  .glass-table th, .glass-table td {
    font-size: 0.85rem;
    padding: 0.6rem;
  }
}
/* ------------------------------
   DARK MODE DROPDOWN FIX
   (Matches universal theme)
------------------------------- */
.form-group select,
.form-group select option {
    background-color: rgba(15, 23, 42, 0.95) !important; /* dark navy */
    color: #ffffff !important;
}

/* Closed dropdown */
.form-group select {
    background: rgba(255,255,255,0.08) !important;
    border: 1px solid var(--border-glass);
    padding: 0.7rem 1rem;
    border-radius: 8px;
    backdrop-filter: blur(8px);
    cursor: pointer;
}

/* Options inside dropdown list */
.form-group select option {
    background: rgba(15, 23, 42, 0.95) !important;
    color: #fff !important;
    padding: 10px 12px !important;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* Hover/highlight */
.form-group select option:hover,
.form-group select option:checked {
    background: rgba(59,130,246,0.4) !important; /* neon-blue glow */
    color: #fff !important;
}

/* Focus glow */
.form-group select:focus {
    border-color: var(--gradient-1);
    box-shadow: 0 0 0 3px rgba(102,126,234,0.3) !important;
}

</style>
</head>

<body>
<div class="main-content">
  <div class="page-header">
    <h2><i class="fas fa-calendar-alt"></i> Manage Academic Years</h2>
    <p>Add, update, or view academic years</p>
  </div>

  <!-- Add Year Form -->
  <div class="form-card">
    <h3><i class="fas fa-plus-circle"></i> Add New Academic Year</h3>
    <form method="post">
      <div class="form-group">
        <label>Start Year</label>
        <input type="number" name="txt_start_year" placeholder="Enter start year (e.g., 2024)" required>
      </div>
      <div class="form-group">
        <label>Semester Type</label>
        <select name="sem_type">
          <option value="odd" selected>Odd (default)</option>
          <option value="even">Even</option>
        </select>
      </div>
      <div class="checkbox-row">
        <label><input type="checkbox" name="chk_current"> Set as Current</label>
      </div>
      <div class="form-group">
        <button type="submit" name="btn_add_year" class="btn-primary"><i class="fas fa-save"></i> Add Academic Year</button>
      </div>
    </form>
  </div>

  <!-- Existing Academic Years Table -->
  <div class="table-card">
    <h3><i class="fas fa-list"></i> Existing Academic Years</h3>
    <table class="glass-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Academic Year</th>
          <th>Semester Type</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $idx = 0;
        while($year = $yearsRes->fetch_assoc()){
          $idx++;
          echo "<tr>
            <td>$idx</td>
            <td>".htmlspecialchars($year['acyear_name'])."</td>
            <td>".htmlspecialchars(ucfirst($year['sem_type']))."</td>
            <td>".($year['is_current'] ? '<span class="current">Current</span>' : '<span class="not-current">Not Current</span>')."</td>
            <td>";
            if(!$year['is_current']){
              echo "<a href='ManageAcademicYear.php?set_current=".$year['acyear_id']."' class='action-link set' onclick='return confirm(\"Set this year as current?\");'><i class=\"fas fa-check\"></i> Set Current</a>";
            }
            echo "<a href='ManageAcademicYear.php?del_id=".$year['acyear_id']."' class='action-link delete' onclick='return confirm(\"Are you sure you want to delete this academic year?\");'><i class=\"fas fa-trash\"></i> Delete</a>
            </td>
          </tr>";
        }
        if($idx == 0){
          echo '<tr><td colspan="5" class="center">No academic years yet.</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
