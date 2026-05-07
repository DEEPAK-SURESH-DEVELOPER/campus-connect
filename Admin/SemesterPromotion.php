<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// MUST INCLUDE DB CONNECTION BEFORE AJAX
include("../Assets/Connection/Connection.php");

if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}

$admin_id   = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

/* ----------------------------------------------------
   1️⃣ AJAX PASSWORD CHECK — MUST BE BEFORE ANY HTML
---------------------------------------------------- */
if(isset($_POST['verify_promo']))
{
    header("Content-Type: application/json; charset=utf-8");

    $promo_password = trim($_POST['promo_password'] ?? '');

    // Fetch password
    $stmt = $con->prepare("SELECT admin_password FROM tbl_admin WHERE admin_id=? LIMIT 1");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res  = $stmt->get_result();
    $row  = $res->fetch_assoc();
    $stmt->close();

    if(!$row){
        echo json_encode(['status'=>'error','message'=>'Admin not found']);
        exit;
    }

    $db_password = trim($row['admin_password']);

    if($promo_password === $db_password){
        $_SESSION['promo_unlocked'] = true;
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Password incorrect']);
    }

    exit; // VERY IMPORTANT
}

/* ----------------------------------------------------
   2️⃣ RESET UNLOCK ON NORMAL PAGE LOAD (GET only)
---------------------------------------------------- */
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    unset($_SESSION['promo_unlocked']);
}

/* ----------------------------------------------------
   NOW LOAD HEADER + SIDEBAR (SAFE)
---------------------------------------------------- */
include("../Includes/AdminHeader.php");
include("../Includes/AdminSidebar.php");

/* ----------------------------------------------------
   3️⃣ ORIGINAL PROMOTION LOGIC
---------------------------------------------------- */

$yearRes = $con->query("SELECT * FROM tbl_academicyear WHERE is_current=1 LIMIT 1");
$year    = $yearRes->fetch_assoc();

$currentYear    = $year['acyear_name'] ?? 'Unknown';
$currentSem     = $year['sem_type'] ?? 'odd';
$currentAcyearId = $year['acyear_id'] ?? 0;

$activeClasses  = $con->query("SELECT COUNT(*) AS c FROM tbl_class WHERE is_completed=0")->fetch_assoc()['c'];
$activeStudents = $con->query("SELECT COUNT(*) AS c FROM tbl_student WHERE is_active=1")->fetch_assoc()['c'];

$msg = "";
$alertClass = "alert-box";

/* When promote button clicked */
if(isset($_POST['btn_promote']))
{
    if(empty($_SESSION['promo_unlocked'])){
        $msg = "Please authenticate before promoting semester.";
        $alertClass = "alert-box gray";
    }
    else
    {
        if($activeClasses == 0){
            $msg = "No active classes to promote.";
            $alertClass = "alert-box gray";
        } else {

            $classesProcessed = 0;
            $classesCompleted = 0;
            $studentsDeactivated = 0;

            $classRes = $con->query("
                SELECT c.class_id, c.semester_id, co.total_semesters
                FROM tbl_class c
                INNER JOIN tbl_course co ON c.course_id = co.course_id
                WHERE c.is_completed = 0
            ");

            while($row = $classRes->fetch_assoc()){
                $class_id   = $row['class_id'];
                $semester_id  = $row['semester_id'];
                $total_semesters = $row['total_semesters'];
                $classesProcessed++;

                if($semester_id < $total_semesters){
                    $con->query("UPDATE tbl_class SET semester_id = semester_id + 1 WHERE class_id=$class_id");
                } else {
                    $con->query("UPDATE tbl_class SET is_completed=1 WHERE class_id=$class_id");
                    $classesCompleted++;

                    $con->query("UPDATE tbl_student SET is_active=0 WHERE class_id=$class_id");
                    $studentsDeactivated += $con->affected_rows;
                }
            }

            $toSem  = ($currentSem == 'odd') ? 'even' : 'odd';
            $toYear = $currentYear;

            if($currentSem == 'odd'){
                $con->query("UPDATE tbl_academicyear SET sem_type='even' WHERE acyear_id=$currentAcyearId");
            } else {
                $con->query("UPDATE tbl_academicyear SET is_current=0 WHERE acyear_id=$currentAcyearId");
                list($start, $end) = explode('-', $currentYear);
                $newYear = ($start+1)."-".($end+1);
                $con->query("INSERT INTO tbl_academicyear(acyear_name,sem_type,is_current) VALUES('$newYear','odd',1)");
                $toYear = $newYear;
            }

            $remarks = "Promotion done. $classesProcessed class(es) processed, $classesCompleted completed, $studentsDeactivated student(s) deactivated.";

            $con->query("
                INSERT INTO tbl_semesterpromotion_log(promoted_by,promoted_on,from_year,to_year,from_sem,to_sem,remarks)
                VALUES('$admin_name',NOW(),'$currentYear','$toYear','$currentSem','$toSem','$remarks')
            ");

            $msg = "✅ Promotion complete. $classesProcessed class(es) processed, $classesCompleted completed.";
            $alertClass = "alert-box green";
        }

        unset($_SESSION['promo_unlocked']); // Clear unlock
    }
}

$logs = $con->query("SELECT * FROM tbl_semesterpromotion_log ORDER BY promoted_on DESC");
?>


<div class="page-wrapper">
<div class="main-content" id="mainContent">
    <div class="glass-card">
        <h1 class="page-title"><i class="fas fa-layer-group"></i> Semester Promotion</h1>

        <?php if($msg){ ?>
            <div class="<?php echo $alertClass; ?>"><?php echo $msg; ?></div>
        <?php } ?>

        <form method="post" class="centered-form">
            <!-- Disable button if not authenticated server-side (prevents accidental submit) -->
            <button type="submit" name="btn_promote" class="btn-promote" id="btnPromote"
                <?php echo empty($_SESSION['promo_unlocked']) ? 'disabled' : ''; ?>
                onclick="return confirm('Are you sure you want to promote all classes? This cannot be undone.');">
                <i class="fas fa-arrow-up"></i> Promote Semester
            </button>
        </form>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Active Classes</h3>
                <p><?php echo $activeClasses; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-graduate"></i>
                <h3>Active Students</h3>
                <p><?php echo $activeStudents; ?></p>
            </div>
        </div>
    </div>

    <div class="log-card">
        <h2 class="log-title"><i class="fas fa-clock-rotate-left"></i> Promotion Log</h2>
        <table class="log-table">
            <tr>
                <th>Promoted By</th>
                <th>Promoted On</th>
                <th>From Year</th>
                <th>To Year</th>
                <th>From Sem</th>
                <th>To Sem</th>
                <th>Remarks</th>
            </tr>
            <?php
            if($logs->num_rows > 0){
                while($r = $logs->fetch_assoc()){
                    echo "<tr>
                            <td>{$r['promoted_by']}</td>
                            <td>".date("d M Y, h:i A", strtotime($r['promoted_on']))."</td>
                            <td>{$r['from_year']}</td>
                            <td>{$r['to_year']}</td>
                            <td>{$r['from_sem']}</td>
                            <td>{$r['to_sem']}</td>
                            <td>{$r['remarks']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='no-data'>No promotion logs found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>
</div>

<!-- ================== NEW: Password Modal + Blur styles (fits your theme) ================== -->
<style>
/* Blur the main content (only when locked) */
#mainContent.locked {
    filter: blur(8px) saturate(0.9);
    pointer-events: none; /* prevent clicks */
    user-select: none;
    transition: filter 0.25s ease;
}

/* Modal overlay container centered in viewport but header+sidebar remain visible behind it */
/* Modal overlay always covers full screen for perfect centering */
/* Center modal inside content area, not whole screen */
.promo-modal-overlay {
    position: fixed;
    top: 80px;               /* below header */
    left: 280px;             /* after sidebar */
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center; /* center inside content area */
    z-index: 4001;
    pointer-events: none;
}

/* Modal itself takes pointer events */
.promo-modal {
    pointer-events: auto;
}

/* Backdrop that dims behind modal but keeps header/sidebar clear visually */
/* Blur only main-content, keep sidebar + header visible */
.promo-modal-backdrop {
    position: fixed;
    top: 80px;               /* below header */
    left: 280px;             /* right of sidebar */
    right: 0;
    bottom: 0;
    background: rgba(2,6,23,0.55);
    backdrop-filter: blur(6px);
    z-index: 4000;
}


/* Glass modal (theme-consistent) */
.promo-modal {
    width: 460px;
    max-width: calc(100% - 40px);
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border-glass);
    border-radius: 14px;
    padding: 1.6rem;
    backdrop-filter: blur(12px);
    box-shadow: 0 15px 40px rgba(2,6,23,0.65);
    color: var(--text-primary);
    z-index: 4001;
    text-align: center;
    animation: slideInUp 0.32s ease;
}

/* Header */
.promo-modal h3 {
    margin: 0 0 0.4rem 0;
    font-size: 1.15rem;
    color: var(--gradient-1);
}

/* Subtext */
.promo-modal p {
    color: var(--text-secondary);
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

/* Input */
.promo-input {
    width: 100%;
    padding: 0.9rem 1rem;
    border-radius: 10px;
    border: 1px solid var(--border-glass);
    background: rgba(255,255,255,0.02);
    color: var(--text-primary);
    margin-bottom: 0.8rem;
    font-size: 1rem;
}

/* Buttons */
.promo-actions {
    display:flex;
    gap:0.6rem;
    justify-content:center;
    margin-top:0.6rem;
}
.promo-btn {
    padding: 0.75rem 1.1rem;
    border-radius: 10px;
    border: none;
    font-weight:600;
    cursor:pointer;
    transition:0.18s ease;
}
.promo-btn.verify {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color:white;
}
.promo-btn.verify[disabled] {
    opacity:0.6; cursor:not-allowed;
}
.promo-btn.cancel {
    background: transparent;
    border: 1px solid var(--border-glass);
    color: var(--text-secondary);
}

/* Error text */
.promo-error {
    color: #ff7b7b;
    font-size: 0.92rem;
    margin-top: 0.4rem;
}

/* Small note */
.promo-note {
    font-size: 0.82rem;
    color: var(--text-secondary);
    margin-top: 0.6rem;
}

/* Keep modal animation consistent with your theme */
@keyframes slideInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 520px) {
    .promo-modal { width: 92%; padding: 1.2rem; }
}
</style>

<!-- Modal markup -->
<?php
// If session not unlocked -> show modal overlay to force authentication
$showModal = empty($_SESSION['promo_unlocked']);
?>
<?php if($showModal): ?>
<div class="promo-modal-overlay" id="promoModalRoot" aria-hidden="false">
    <div class="promo-modal-backdrop" id="promoBackdrop"></div>

    <div class="promo-modal" role="dialog" aria-modal="true" aria-labelledby="promoTitle">
        <h3 id="promoTitle"><i class="fas fa-user-shield"></i> Re-enter Admin Password</h3>
        <p>Please enter your admin login password to unlock Semester Promotion.</p>

        <input type="password" id="promoPassword" class="promo-input" placeholder="Admin password" autocomplete="current-password" />

        <div class="promo-actions">
            <button class="promo-btn verify" id="promoVerifyBtn"><i class="fas fa-lock-open"></i> Verify</button>
            <button class="promo-btn cancel" id="promoCancelBtn"><i class="fas fa-times"></i> Cancel</button>
        </div>

        <div id="promoError" class="promo-error" style="display:none;"></div>

        <div class="promo-note">
            This page contains irreversible actions. Authentication required for security.
        </div>
    </div>
</div>
<?php endif; ?>

<!-- INTERNAL CSS (original page styles kept below) -->
<style>
/* (your original internal CSS — kept as-is) */
.main-content {
  padding: 2rem;
  color: var(--text-primary);
}

.page-title {
  text-align: center;
  color: var(--gradient-1);
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.glass-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 20px;
  padding: 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  margin-bottom: 2rem;
}

.centered-form {
  text-align: center;
  margin: 1.5rem 0;
}

.btn-promote {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  padding: 0.9rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: 0.3s ease;
}
.btn-promote:hover {
  box-shadow: 0 0 12px rgba(118,75,162,0.6);
  transform: scale(1.05);
}
.btn-promote[disabled] { opacity: 0.6; cursor: not-allowed; }

.alert-box {
  background: rgba(255,255,255,0.08);
  border-left: 4px solid #999;
  color: var(--text-secondary);
  padding: 0.8rem 1rem;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 1rem;
}
.alert-box.green {
  border-left: 4px solid #4CAF50;
  color: #4CAF50;
}
.alert-box.gray {
  border-left: 4px solid #aaa;
}

.stats-row {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 1rem;
}
.stat-card {
  background: rgba(255,255,255,0.07);
  border: 1px solid var(--border-glass);
  border-radius: 12px;
  padding: 1.5rem 2rem;
  text-align: center;
  width: 200px;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.stat-card i {
  font-size: 2rem;
  color: var(--gradient-1);
}
.stat-card h3 {
  font-size: 1rem;
  color: var(--text-secondary);
  margin-top: 0.8rem;
}
.stat-card p {
  font-size: 1.4rem;
  font-weight: 600;
  color: #fff;
}

.log-card {
  background: rgba(255,255,255,0.05);
  border-radius: 15px;
  padding: 1.5rem;
  border: 1px solid var(--border-glass);
  backdrop-filter: blur(10px);
}

.log-title {
  margin-bottom: 1rem;
  color: var(--gradient-1);
  text-align: center;
}

.log-table {
  width: 100%;
  border-collapse: collapse;
  text-align: center;
  color: var(--text-primary);
}
.log-table th, .log-table td {
  padding: 0.8rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
.log-table th {
  color: var(--gradient-1);
}
.log-table tr:hover {
  background: rgba(255,255,255,0.05);
}
.no-data {
  text-align: center;
  padding: 1rem;
  color: var(--text-secondary);
}
</style>

<script src="../Assets/JS/universal.js"></script>

<script>
/* Robust modal + verify controller — drop-in replacement for your current script */
(function(){
    const modalRoot = document.getElementById('promoModalRoot'); // overlay wrapper
    const mainContent = document.getElementById('mainContent');
    const verifyBtn = document.getElementById('promoVerifyBtn');
    const cancelBtn = document.getElementById('promoCancelBtn');
    const pwdInput = document.getElementById('promoPassword');
    const promoError = document.getElementById('promoError');
    const btnPromote = document.getElementById('btnPromote');

    // defensive helpers
    const showError = (text) => {
        if(promoError){
            promoError.textContent = text;
            promoError.style.display = 'block';
        } else {
            alert(text);
        }
        console.warn('Promo verify error:', text);
    };

    const clearError = () => {
        if(promoError){
            promoError.textContent = '';
            promoError.style.display = 'none';
        }
    };

    // If modal exists, lock the main content and prepare UI
    if(modalRoot){
        // scroll to top so modal visually centers
        try { window.scrollTo(0,0); } catch(e){/*ignore*/}

        if(mainContent){
            mainContent.classList.add('locked');
        }
        // prevent body scroll while modal open
        document.body.style.overflow = 'hidden';

        // autofocus password input after a tiny delay
        setTimeout(()=> { if(pwdInput) pwdInput.focus(); }, 250);
    }

    // Cancel button: go to AdminHome
    if(cancelBtn){
        cancelBtn.addEventListener('click', function(e){
            e.preventDefault();
            // restore scroll & allow navigation
            document.body.style.overflow = '';
            window.location.href = 'AdminHome.php';
        });
    }

    // Verify handler — required for server check
    if(verifyBtn){
        verifyBtn.addEventListener('click', async function(e){
            e.preventDefault();
            clearError();

            if(!pwdInput){
                showError('Password input not found.');
                return;
            }

            const pwd = pwdInput.value.trim();
            if(!pwd){
                showError('Please enter password.');
                pwdInput.focus();
                return;
            }

            // disable controls while verifying
            verifyBtn.disabled = true;
            const origText = verifyBtn.innerHTML;
            verifyBtn.innerHTML = 'Verifying...';

            try {
                const formData = new FormData();
                formData.append('verify_promo', '1');
                formData.append('promo_password', pwd);

                // Use 'include' to be sure cookies (PHPSESSID) are sent in all environments
                const resp = await fetch(window.location.href, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData,
                    cache: 'no-store'
                });

                // We may get JSON or non-JSON (PHP warnings). Read text first.
                const text = await resp.text();
                let json = null;
                try {
                    json = JSON.parse(text);
                } catch (parseErr){
                    // Not JSON — show server output to help debug
                    console.error('Server returned non-JSON response:', text);
                    showError('Server error: see console (non-JSON).');
                    // restore UI
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = origText;
                    return;
                }

                // Handle JSON response
                if(json.status === 'ok'){
                    // success -> remove modal and blur, enable promote button
                    if(modalRoot && modalRoot.parentNode) modalRoot.parentNode.removeChild(modalRoot);
                    if(mainContent) mainContent.classList.remove('locked');
                    if(btnPromote) btnPromote.removeAttribute('disabled');

                    // restore body scroll
                    document.body.style.overflow = '';

                    // small success console msg
                    console.log('Promo unlocked successfully.');
                } else {
                    // server returned error message
                    showError(json.message || 'Password incorrect.');
                    pwdInput.focus();
                }
            } catch(err){
                console.error('Verification fetch error:', err);
                showError('Verification failed. Check console / network.');
            } finally {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = origText || '<i class="fas fa-lock-open"></i> Verify';
            }
        });
    }

    // Pressing Enter in password input triggers verify
    if(pwdInput){
        pwdInput.addEventListener('keydown', function(e){
            if(e.key === 'Enter'){
                e.preventDefault();
                if(verifyBtn) verifyBtn.click();
            }
        });
    }
})();
</script>
</body>
</html>
