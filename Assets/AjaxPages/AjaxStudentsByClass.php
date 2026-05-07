<?php
include("../Connection/Connection.php");
$returnUrl = 'StudentListAdmin.php';

// Handle delete
if(isset($_GET['delID'])){
    $delID = (int)$_GET['delID'];
    $con->query("DELETE FROM tbl_student WHERE student_id='$delID'");
}

// If a class is selected
if(isset($_GET['class_id']) && $_GET['class_id'] != ""){
    $classId = (int)$_GET['class_id'];

    // Fetch students from ongoing classes, sorted by name
    $qry = "
        SELECT s.*
        FROM tbl_student s
        INNER JOIN tbl_class c ON s.class_id = c.class_id
        WHERE s.class_id = '$classId'
          AND c.is_completed = 0
        ORDER BY s.student_name ASC
    ";
    $res = $con->query($qry);

    echo '<table class="student-table glass-table">
          <thead>
          <tr>
              <th>SI. No</th>
              <th>Photo</th>
              <th>Name</th>
              <th>Actions</th>
          </tr>
          </thead>
          <tbody>';

    if($res->num_rows > 0){
        $i = 1;
        while($row = $res->fetch_assoc()){
            $photo = !empty($row['student_photo']) ? $row['student_photo'] : 'default.png';

            echo '
            <tr> 
                <td>'.$i.'</td>
                <td>
                    <img src="../Assets/Files/Student/'.htmlspecialchars($photo).'" 
                         class="student-photo" alt="Photo">
                </td>
                <td>'.htmlspecialchars($row['student_name']).'</td>
                <td>
                    <div class="action-buttons">
                        <a href="StudentProfileAdmin.php?student_id='.$row['student_id'].'&return_url='.urlencode($returnUrl).'" 
                           class="action-btn profile">
                           <i class="fas fa-id-card"></i> Profile
                        </a>

                        <a href="AdminStudentReport.php?student_id='.$row['student_id'].'" 
                           class="action-btn report">
                           <i class="fas fa-chart-line"></i> Report
                        </a>

                        <a href="javascript:void(0);" 
                           onclick="deleteStudent('.$row['student_id'].')" 
                           class="action-btn delete">
                           <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </td>
            </tr>';
            $i++;
        }
    } else {
        echo '<tr><td colspan="4" align="center" style="color:var(--text-secondary);">No students found for this class.</td></tr>';
    }

    echo '</tbody></table>';
}
?>
