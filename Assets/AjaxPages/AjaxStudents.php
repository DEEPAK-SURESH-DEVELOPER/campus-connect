<?php
include("../Connection/Connection.php");

// --- Handle delete ---
if (isset($_GET['delID'])) {
    $delID = (int)$_GET['delID'];
    $stmt = $con->prepare("DELETE FROM tbl_student WHERE student_id = ?");
    $stmt->bind_param("i", $delID);
    $stmt->execute();
    $stmt->close();
}

// --- If a class is selected ---
if (isset($_GET['class_id']) && $_GET['class_id'] != "") {
    $classId = (int)$_GET['class_id'];

    // Fetch students for selected class
    $qry = "
        SELECT s.student_id, s.student_name, s.student_photo
        FROM tbl_student s
        INNER JOIN tbl_class c ON s.class_id = c.class_id
        WHERE s.class_id = ? AND c.is_completed = 0
        ORDER BY s.student_name ASC
    ";
    $stmt = $con->prepare($qry);
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $res = $stmt->get_result();

    echo '
    <table class="glass-table" style="width:100%;">
        <thead>
            <tr>
                <th>SI. No</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

    if ($res->num_rows > 0) {
        $i = 1;
        while ($row = $res->fetch_assoc()) {
            $photo = $row['student_photo'];
            $photoPath = "../Assets/Files/Student/" . $photo; // ✅ Correct relative path

            if (empty($photo) || !file_exists($photoPath)) {
                $photoPath = "../Assets/Images/default.png"; // ✅ Default fallback image
            }

            echo '
            <tr>
                <td>'.$i.'</td>
                <td>
                    <div class="profile-avatar" 
                         style="width:55px;height:55px;border-radius:50%;overflow:hidden;border:2px solid var(--gradient-1);margin:auto;">
                        <img src="../Assets/Files/Student/'.htmlspecialchars($photo).'" alt="Student Photo" 
                             style="width:100%;height:100%;object-fit:cover;">
                    </div>
                </td>
                <td style="font-weight:600;">'.htmlspecialchars($row['student_name']).'</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn view" 
                            onclick="location.href=\'../HOD/StudentProfileHOD.php?student_id='.$row['student_id'].'\'">
                            <i class="fas fa-user"></i> Profile
                        </button>
                        <button class="action-btn edit" 
                            onclick="location.href=\'../HOD/HODStudentReport.php?student_id='.$row['student_id'].'\'">
                            <i class="fas fa-chart-bar"></i> Report
                        </button>
                        <button class="action-btn delete" 
                            onclick="deleteStudent('.$row['student_id'].')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>';
            $i++;
        }
    } else {
        echo '<tr><td colspan="4" class="text-center">No students found for this class.</td></tr>';
    }

    echo '</tbody></table>';
    $stmt->close();
}
?>
