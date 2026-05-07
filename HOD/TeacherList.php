<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$hod_department_id = (int)$_SESSION['hod_department_id'];

// --- Fetch teachers under this HOD ---
$teacherQry = "SELECT t.teacher_id, t.teacher_name, t.teacher_photo, d.designation_name
               FROM tbl_teacher t
               JOIN tbl_designation d ON t.designation_id = d.designation_id
               WHERE t.department_id='$hod_department_id'";
$teacherRes = mysqli_query($con, $teacherQry);

// Page meta for header
$page_title = "Faculty Members";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Teacher List</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo $page_title; ?> - Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Universal CSS -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">
</head>
<body>
<style>

/* Wrap buttons evenly */
.action-buttons {
    display: flex;
    gap: 0.6rem;
    justify-content: center;
}

/* Universal size fix */
.action-buttons .action-btn {
    padding: 0.45rem 1rem !important;
    font-size: 0.82rem !important;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    min-width: 115px;
    height: 38px;
    border: none;
    cursor: pointer;
    transition: 0.25s ease;
}

/* Neon Blue (VIEW / PROFILE / TIMETABLE) */
.action-btn.view {
    background: linear-gradient(135deg, #3b82f6, #60a5fa) !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(59,130,246,0.6);
}
.action-btn.view:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 22px rgba(59,130,246,0.95);
}

/* Neon Green (ASSIGN SUBJECT) */
.action-btn.edit {
    background: linear-gradient(135deg, #34d399, #10b981) !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(16,185,129,0.6);
}
.action-btn.edit:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 22px rgba(16,185,129,0.95);
}

/* Blue Timetable Button (same as view) */
.action-btn {
    background: linear-gradient(135deg, #3b82f6, #60a5fa) !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(59,130,246,0.6);
}
.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 22px rgba(59,130,246,0.95);
}
/* Neon Purple Timetable Button */
.action-btn.timetable {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa) !important;  /* violet to soft purple */
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(139,92,246,0.55) !important;
}

.action-btn.timetable:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 22px rgba(167,139,250,0.95) !important;
}


</style>

<!-- Main Content -->
<main class="main-content">

    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users"></i> Teachers in Your Department</h3>
            <button class="btn-outline" onclick="location.href='HODHome.php'"><i class="fas fa-arrow-left"></i> Back to Dashboard</button>
        </div>

        <?php if($teacherRes && mysqli_num_rows($teacherRes) > 0): ?>
            <table class="glass-table" id="teacherTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $count = 1;
                while($row = mysqli_fetch_assoc($teacherRes)):
                    $tid = $row['teacher_id'];
                    $photo = !empty($row['teacher_photo']) ? $row['teacher_photo'] : 'default.png';
                    $name = htmlspecialchars($row['teacher_name']);
                    $designation = htmlspecialchars($row['designation_name']);
                ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td>
                            <div class="profile-avatar" style="width:55px;height:55px;border-radius:50%;overflow:hidden;border:2px solid var(--gradient-1);margin:auto;">
                                <img src="../Assets/Files/Teacher/<?php echo htmlspecialchars($photo); ?>" alt="Photo" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                        </td>
                        <td style="font-weight:600;"><?php echo $name; ?></td>
                        <td><?php echo $designation; ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" onclick="location.href='TeacherProfileHod.php?teacher_id=<?php echo $tid; ?>'">
                                    <i class="fas fa-user"></i> Profile
                                </button>
                                <button class="action-btn edit" onclick="location.href='AssignSubjects.php?teacher_id=<?php echo $tid; ?>'">
                                    <i class="fas fa-book"></i> Assign
                                </button>
                                <button class="action-btn timetable" onclick="location.href='TeacherTimeTableHod.php?teacher_id=<?php echo $tid; ?>'">
    <i class="fas fa-calendar-alt"></i> Timetable
</button>

                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-user-slash"></i></div>
                <div class="empty-state-title">No Teachers Found</div>
                <div class="empty-state-text">There are no teachers listed under your department yet.</div>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- Universal JS -->
<script src="../Assets/JS/universal.js"></script>

<script>
// Table fade animation
document.addEventListener("DOMContentLoaded", () => {
    const rows = document.querySelectorAll("#teacherTable tbody tr");
    rows.forEach((row, i) => {
        row.style.opacity = "0";
        setTimeout(() => {
            row.style.transition = "opacity 0.5s ease";
            row.style.opacity = "1";
        }, i * 100);
    });
});
</script>

</body>
</html>
