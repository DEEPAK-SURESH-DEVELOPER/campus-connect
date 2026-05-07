<?php
include("../Connection/Connection.php");

// --- Validate input ---
if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {

    $course_id = (int)$_GET['course_id']; // Sanitize to prevent SQL injection

    // Fetch total semesters safely
    $stmt = $con->prepare("SELECT total_semesters FROM tbl_course WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $course = $result->fetch_assoc();
        $total = (int)$course['total_semesters'];

        // Output dropdown options
        echo '<option value="">Select Semester</option>';
        for ($i = 1; $i <= $total; $i++) {
            echo '<option value="'.$i.'">Semester '.$i.'</option>';
        }
    } else {
        echo '<option value="">No semesters found</option>';
    }

    $stmt->close();

} else {
    echo '<option value="">Invalid course</option>';
}
?>
