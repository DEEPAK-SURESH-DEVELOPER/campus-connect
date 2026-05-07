<?php
include("../Connection/Connection.php");

// --- Validate Input ---
if (isset($_GET['course_id'], $_GET['semester'], $_GET['teacher_id'])) {

    $course_id  = (int)$_GET['course_id'];
    $semester   = (int)$_GET['semester'];
    $teacher_id = (int)$_GET['teacher_id'];

    // --- Fetch subjects for the selected course and semester ---
    $qry = "
        SELECT 
            s.subject_id, 
            s.subject_name,
            EXISTS (
                SELECT 1 
                FROM tbl_teachersubject ts 
                WHERE ts.teacher_id = ? AND ts.subject_id = s.subject_id
            ) AS assigned
        FROM tbl_subject s
        WHERE s.course_id = ? AND s.semester_id = ?
        ORDER BY s.subject_name ASC
    ";

    $stmt = $con->prepare($qry);
    $stmt->bind_param("iii", $teacher_id, $course_id, $semester);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        echo '<div class="subject-grid">';
        while ($row = $res->fetch_assoc()) {
            $checked = $row['assigned'] ? 'checked' : '';
            $subjectName = htmlspecialchars($row['subject_name']);
            $subjectId = (int)$row['subject_id'];

            echo '
            <label class="subject-checkbox">
                <input type="checkbox" name="subject_ids[]" value="'.$subjectId.'" '.$checked.'>
                <span class="subject-label">'.$subjectName.'</span>
            </label>';
        }
        echo '</div>';
    } else {
        echo '<div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-book"></i></div>
                <div class="empty-state-title">No Subjects Found</div>
                <div class="empty-state-text">No subjects are available for this semester.</div>
              </div>';
    }

    $stmt->close();
}
?>
