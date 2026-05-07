<?php
include("../Assets/Connection/Connection.php");
session_start();

//header('Content-Type: application/json'); // Allow JSON responses for AJAX

if(isset($_POST["btn_login"])) {
    $email = mysqli_real_escape_string($con, $_POST["txt_email"]);
    $password = mysqli_real_escape_string($con, $_POST["txt_password"]);
    $role = mysqli_real_escape_string($con, $_POST["txt_role"]);

    // Early validation
    if(empty($email) || empty($password) || empty($role)) {
        echo json_encode(["status" => "error", "message" => "Please fill all fields!"]);
        exit();
    }

    if($role === "admin") {
        // -------- Admin Login --------
        $selAdmin = "SELECT * FROM tbl_admin WHERE admin_email='$email' AND admin_password='$password'";
        $resAdmin = mysqli_query($con, $selAdmin);

        if($dataAdmin = mysqli_fetch_assoc($resAdmin)) {
            $_SESSION["admin_id"] = $dataAdmin["admin_id"];
            $_SESSION["admin_name"] = $dataAdmin["admin_name"];
            echo json_encode(["status" => "success", "redirect" => "../Admin/AdminHome.php"]);
            exit();
        }
    } 
    elseif($role === "teacher") {
        // -------- Teacher/HOD Login --------
        $selTeacher = "SELECT * FROM tbl_teacher WHERE teacher_email='$email' AND teacher_password='$password'";
        $resTeacher = mysqli_query($con, $selTeacher);

        if($dataTeacher = mysqli_fetch_assoc($resTeacher)) {
            $teacher_id = $dataTeacher["teacher_id"];
            $teacher_name = $dataTeacher["teacher_name"];
            
            // Check if teacher is HOD
            $hodQry = "SELECT department_id FROM tbl_department WHERE hod_teacher_id='$teacher_id' LIMIT 1";
            $resHod = mysqli_query($con, $hodQry);

            if($hodRow = mysqli_fetch_assoc($resHod)) {
                $_SESSION['is_hod'] = true;
                $_SESSION['hod_id'] = $teacher_id;
                $_SESSION['hod_name'] = $teacher_name;
                $_SESSION['hod_department_id'] = $hodRow['department_id'];
                echo json_encode(["status" => "success", "redirect" => "../HOD/HODHome.php"]);
                exit();
            }

            // Normal teacher
            $_SESSION["teacher_id"] = $dataTeacher["teacher_id"];
            $_SESSION["teacher_name"] = $dataTeacher["teacher_name"];
            $_SESSION["department_id"] = $dataTeacher["department_id"];
            $_SESSION["designation_id"] = $dataTeacher["designation_id"];

            // Check if class teacher
            $classQry = "SELECT class_id FROM tbl_class WHERE teacher_id='$teacher_id' AND is_completed=0 LIMIT 1";
            $resClass = mysqli_query($con, $classQry);

            $_SESSION['is_class_teacher'] = ($classRow = mysqli_fetch_assoc($resClass)) ? true : false;
            if($_SESSION['is_class_teacher']) $_SESSION['class_id'] = $classRow['class_id'];

            echo json_encode(["status" => "success", "redirect" => "../Teacher/TeacherHome.php"]);
            exit();
        }
    } 
    elseif($role === "student") {
        // -------- Student Login --------
        $selStudent = "SELECT s.*, c.course_id, co.department_id 
                       FROM tbl_student s 
                       LEFT JOIN tbl_class c ON s.class_id = c.class_id 
                       LEFT JOIN tbl_course co ON c.course_id = co.course_id 
                       WHERE s.student_email='$email' 
                       AND s.student_password='$password' 
                       AND s.is_active=1";
        $resStudent = mysqli_query($con, $selStudent);

        if($dataStudent = mysqli_fetch_assoc($resStudent)) {
            $_SESSION["student_id"] = $dataStudent["student_id"];
            $_SESSION["student_name"] = $dataStudent["student_name"];
            $_SESSION['class_id'] = $dataStudent["class_id"];
            $_SESSION["department_id"] = $dataStudent["department_id"];
            echo json_encode(["status" => "success", "redirect" => "../Student/StudentHome.php"]);
            exit();
        }
    }

    echo json_encode(["status" => "error", "message" => "Invalid Email or Password!"]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Connect - Login Portal</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ========================
           CSS Variables & Reset
           ======================== */
        :root {
            --primary-bg: #0a0e27;
            --secondary-bg: #151932;
            --card-bg: rgba(21, 25, 50, 0.7);
            --text-primary: #ffffff;
            --text-secondary: #b8c1ec;
            --gradient-1: #667eea;
            --gradient-2: #764ba2;
            --gradient-3: #f093fb;
            --glow-color: rgba(102, 126, 234, 0.5);
            --border-color: rgba(255, 255, 255, 0.1);
            --error-color: #ef4444;
            --success-color: #10b981;
        }

        body.light-mode {
            --primary-bg: #f5f7fa;
            --secondary-bg: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.9);
            --text-primary: #1a202c;
            --text-secondary: #4a5568;
            --glow-color: rgba(102, 126, 234, 0.3);
            --border-color: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--primary-bg);
            color: var(--text-primary);
            overflow: hidden;
            transition: background 0.3s ease;
        }

        /* ========================
           Portal Transition
           ======================== */
        #portal-transition {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            pointer-events: none;
            opacity: 1;
            transition: opacity 1s ease;
        }

        #portal-transition.hidden {
            opacity: 0;
        }

        .portal-circle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--gradient-1), var(--gradient-2), transparent);
            box-shadow: 0 0 100px var(--glow-color), inset 0 0 50px var(--glow-color);
            opacity: 0;
        }

        /* ========================
           Three.js Canvas Container
           ======================== */
        #three-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        /* ========================
           Particles Background
           ======================== */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -2;
        }

        /* ========================
           Login Container
           ======================== */
        .login-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            opacity: 0;
            animation: fadeIn 1s ease forwards 0.5s;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* ========================
           Floating Campus Elements
           ======================== */
        .floating-symbols {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 0;
        }

        .floating-symbol {
            position: absolute;
            opacity: 0.05;
            color: var(--gradient-1);
            font-size: 3rem;
            animation: floatSymbol 20s ease-in-out infinite;
        }

        .floating-symbol:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-symbol:nth-child(2) {
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-symbol:nth-child(3) {
            bottom: 15%;
            left: 15%;
            animation-delay: 4s;
        }

        .floating-symbol:nth-child(4) {
            bottom: 20%;
            right: 10%;
            animation-delay: 6s;
        }

        @keyframes floatSymbol {
            0%, 100% {
                transform: translateY(0) translateX(0) scale(1);
            }
            25% {
                transform: translateY(-30px) translateX(20px) scale(1.1);
            }
            50% {
                transform: translateY(-50px) translateX(-10px) scale(0.9);
            }
            75% {
                transform: translateY(-20px) translateX(30px) scale(1.05);
            }
        }

        /* ========================
           Logo Animation
           ======================== */
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
            opacity: 0;
            animation: logoAppear 1s ease forwards 1s;
        }

        @keyframes logoAppear {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2), var(--gradient-3));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulseGlow 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px var(--glow-color));
        }

        @keyframes pulseGlow {
            0%, 100% {
                filter: drop-shadow(0 0 20px var(--glow-color));
            }
            50% {
                filter: drop-shadow(0 0 40px var(--glow-color));
            }
        }

        .logo-text {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ========================
           Login Card (Glassmorphism)
           ======================== */
        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 30px;
            padding: 3rem;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 40px var(--glow-color);
            transform-style: preserve-3d;
            transition: all 0.3s ease;
            position: relative;
            z-index: 10;
            opacity: 0;
            animation: cardMaterialize 1.5s ease forwards 1.5s;
        }

        @keyframes cardMaterialize {
            0% {
                opacity: 0;
                transform: scale(0.8) rotateY(10deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotateY(0deg);
            }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2), var(--gradient-3));
            border-radius: 30px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-card:hover::before {
            opacity: 0.3;
        }

        /* ========================
           Card Header
           ======================== */
        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .card-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* ========================
           Role Selector
           ======================== */
        .role-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .role-btn {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid var(--border-color);
            background: transparent;
            color: var(--text-secondary);
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .role-btn:hover {
            border-color: var(--gradient-1);
            color: var(--text-primary);
            transform: translateY(-3px);
        }

        .role-btn.active {
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
            border-color: transparent;
            color: white;
            box-shadow: 0 5px 20px var(--glow-color);
        }

        /* ========================
           Form Inputs
           ======================== */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gradient-1);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px var(--glow-color);
        }

        .form-control:focus ~ i {
            color: var(--gradient-1);
        }

        .form-control.success {
            border-color: var(--success-color);
            animation: successPulse 0.5s ease;
        }

        .form-control.error {
            border-color: var(--error-color);
            animation: shake 0.5s ease;
        }

        @keyframes successPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            100% {
                box-shadow: 0 0 0 15px rgba(16, 185, 129, 0);
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
            }
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: var(--gradient-1);
        }

        /* ========================
           Remember & Forgot
           ======================== */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: var(--gradient-1);
        }

        .forgot-link {
            color: var(--gradient-1);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--gradient-2);
        }

        /* ========================
           Error Message
           ======================== */
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error-color);
            color: var(--error-color);
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .error-message.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========================
           Submit Button
           ======================== */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
            border: none;
            border-radius: 15px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px var(--glow-color);
            position: relative;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px var(--glow-color);
        }

        .btn-submit:active::before {
            width: 300px;
            height: 300px;
        }

        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-submit .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        .btn-submit.loading .spinner {
            display: block;
        }

        .btn-submit.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ========================
           Theme Toggle
           ======================== */
        .theme-toggle {
            position: fixed;
            top: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .theme-toggle:hover {
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
            color: white;
            transform: rotate(180deg) scale(1.1);
        }

        .theme-toggle i {
            font-size: 1.3rem;
            color: var(--text-primary);
        }

        .theme-toggle:hover i {
            color: white;
        }

        /* ========================
           Back Button
           ======================== */
        .back-btn {
            position: fixed;
            top: 2rem;
            left: 2rem;
            width: 50px;
            height: 50px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: var(--text-primary);
        }

        .back-btn:hover {
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
            color: white;
            transform: translateX(-5px) scale(1.1);
        }

        .back-btn i {
            font-size: 1.3rem;
        }

        /* ========================
           Responsive Design
           ======================== */
        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
            }

            .logo-icon {
                font-size: 3rem;
            }

            .logo-text {
                font-size: 1.5rem;
            }

            .card-header h2 {
                font-size: 1.5rem;
            }

            .role-selector {
                flex-direction: column;
            }

            .theme-toggle,
            .back-btn {
                width: 40px;
                height: 40px;
            }

            .theme-toggle {
                top: 1rem;
                right: 1rem;
            }

            .back-btn {
                top: 1rem;
                left: 1rem;
            }
        }

        /* ========================
           Custom Scrollbar
           ======================== */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--primary-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
            border-radius: 10px;
        }
    </style>
</head>
<body class="dark-mode">
    
    <!-- Portal Transition Effect -->
    <div id="portal-transition">
        <div class="portal-circle"></div>
    </div>

    <!-- Particle Background -->
    <div id="particles-js"></div>

    <!-- Three.js Container -->
    <div id="three-container"></div>

    <!-- Floating Campus Symbols -->
    <div class="floating-symbols">
        <i class="floating-symbol fas fa-graduation-cap"></i>
        <i class="floating-symbol fas fa-book-open"></i>
        <i class="floating-symbol fas fa-university"></i>
        <i class="floating-symbol fas fa-pencil-alt"></i>
    </div>

    <!-- Back Button -->
    <a href="../index.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
    </a>

    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Login Container -->
    <div class="login-wrapper">
        <div class="login-card" id="loginCard">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="logo-text">
                    Campus<span class="gradient-text">Connect</span>
                </h1>
            </div>

            <!-- Card Header -->
            <div class="card-header">
                <h2>Welcome Back</h2>
                <p>Enter your credentials to access the portal</p>
            </div>

            <!-- Error Message -->
            <div class="error-message" id="errorMessage"></div>

            <!-- Role Selector -->
            <div class="role-selector">
                <button type="button" class="role-btn" data-role="student">
                    <i class="fas fa-user-graduate"></i>
                    <span>Student</span>
                </button>
                <button type="button" class="role-btn active" data-role="teacher">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Faculty</span>
                </button>
                <button type="button" class="role-btn" data-role="admin">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin</span>
                </button>
            </div>

            <!-- Login Form -->
            <form id="loginForm" method="post">
                <input type="hidden" name="txt_role" id="selectedRole" value="teacher">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="txt_email" 
                            class="form-control" 
                            placeholder="Enter your email"
                            required
                        >
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="txt_password" 
                            class="form-control" 
                            placeholder="Enter your password"
                            required
                        >
                        <i class="fas fa-lock"></i>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

               <!-- <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div> -->

                <button type="submit" name="btn_login" class="btn-submit" id="submitBtn">
                    <span class="btn-text">Login to Portal</span>
                    <div class="spinner"></div>
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <script>
        // ========================
        // Portal Transition Animation
        // ========================
        window.addEventListener('load', () => {
            const portal = document.querySelector('.portal-circle');
            const transition = document.getElementById('portal-transition');

            gsap.to(portal, {
                scale: 50,
                opacity: 1,
                duration: 1.5,
                ease: "power2.inOut",
                onComplete: () => {
                    setTimeout(() => {
                        transition.classList.add('hidden');
                        setTimeout(() => {
                            transition.style.display = 'none';
                        }, 1000);
                    }, 500);
                }
            });
        });

        // ========================
        // Particles.js Background
        // ========================
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 80,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: ['#667eea', '#764ba2', '#f093fb']
                },
                shape: {
                    type: 'circle'
                },
                opacity: {
                    value: 0.5,
                    random: true
                },
                size: {
                    value: 3,
                    random: true
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#667eea',
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: true,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: {
                        enable: true,
                        mode: 'grab'
                    },
                    onclick: {
                        enable: true,
                        mode: 'push'
                    },
                    resize: true
                },
                modes: {
                    grab: {
                        distance: 200,
                        line_linked: {
                            opacity: 0.5
                        }
                    },
                    push: {
                        particles_nb: 4
                    }
                }
            },
            retina_detect: true
        });

        // ========================
        // Three.js Background
        // ========================
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.getElementById('three-container').appendChild(renderer.domElement);

        // Create geometric shapes
        const geometry1 = new THREE.TorusGeometry(10, 3, 16, 100);
        const geometry2 = new THREE.OctahedronGeometry(8, 0);
        const geometry3 = new THREE.IcosahedronGeometry(6, 0);

        const material = new THREE.MeshBasicMaterial({
            color: 0x667eea,
            wireframe: true,
            transparent: true,
            opacity: 0.1
        });

        const torus = new THREE.Mesh(geometry1, material);
        const octahedron = new THREE.Mesh(geometry2, material);
        const icosahedron = new THREE.Mesh(geometry3, material);

        torus.position.set(-20, 10, -50);
        octahedron.position.set(20, -10, -50);
        icosahedron.position.set(0, 15, -60);

        scene.add(torus);
        scene.add(octahedron);
        scene.add(icosahedron);

        camera.position.z = 30;

        function animate() {
            requestAnimationFrame(animate);

            torus.rotation.x += 0.005;
            torus.rotation.y += 0.005;
            octahedron.rotation.x += 0.003;
            octahedron.rotation.y += 0.007;
            icosahedron.rotation.x += 0.004;
            icosahedron.rotation.y += 0.006;

            renderer.render(scene, camera);
        }

        animate();

        // Resize handler
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // ========================
        // Card 3D Tilt Effect
        // ========================
        const loginCard = document.getElementById('loginCard');

        loginCard.addEventListener('mousemove', (e) => {
            const rect = loginCard.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;

            loginCard.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
        });

        loginCard.addEventListener('mouseleave', () => {
            loginCard.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
        });

        // ========================
        // Role Selection
        // ========================
        const roleButtons = document.querySelectorAll('.role-btn');
        const roleInput = document.getElementById('selectedRole');

        roleButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                roleButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                roleInput.value = btn.dataset.role;
            });
        });

        // ========================
        // Password Toggle
        // ========================
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });

        // ========================
        // Form Validation & Submit
        // ========================
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');

        // Real-time validation
        emailInput.addEventListener('blur', () => {
            if (emailInput.value && validateEmail(emailInput.value)) {
                emailInput.classList.add('success');
                emailInput.classList.remove('error');
            } else if (emailInput.value) {
                emailInput.classList.add('error');
                emailInput.classList.remove('success');
            }
        });

        passwordInput.addEventListener('blur', () => {
            if (passwordInput.value && passwordInput.value.length >= 2) {
                passwordInput.classList.add('success');
                passwordInput.classList.remove('error');
            } else if (passwordInput.value) {
                passwordInput.classList.add('error');
                passwordInput.classList.remove('success');
            }
        });

        // Email validation
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Show error message
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.add('show');
            
            gsap.fromTo(errorMessage, 
                { opacity: 0, y: -10 },
                { opacity: 1, y: 0, duration: 0.3 }
            );

            setTimeout(() => {
                errorMessage.classList.remove('show');
            }, 5000);
        }

        // Form submission with AJAX
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const email = emailInput.value;
            const password = passwordInput.value;

            // Validate
            if (!validateEmail(email)) {
                emailInput.classList.add('error');
                emailInput.classList.remove('success');
                gsap.to(emailInput, { x: -10, duration: 0.1, yoyo: true, repeat: 5 });
                showError('Please enter a valid email address');
                return;
            }

            if (password.length < 2) {
                passwordInput.classList.add('error');
                passwordInput.classList.remove('success');
                gsap.to(passwordInput, { x: -10, duration: 0.1, yoyo: true, repeat: 5 });
                showError('Password must be at least 2 characters');
                return;
            }

            // Loading state
            submitBtn.classList.add('loading');
            errorMessage.classList.remove('show');

            // Data pulse animation
            gsap.to(loginCard, {
                scale: 1.05,
                duration: 0.3,
                yoyo: true,
                repeat: 1,
                ease: "power2.inOut"
            });

            // Create ripple effect
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.width = '10px';
            ripple.style.height = '10px';
            ripple.style.background = 'rgba(102, 126, 234, 0.5)';
            ripple.style.borderRadius = '50%';
            ripple.style.top = '50%';
            ripple.style.left = '50%';
            ripple.style.transform = 'translate(-50%, -50%)';
            ripple.style.pointerEvents = 'none';
            ripple.style.zIndex = '1000';
            loginCard.appendChild(ripple);

            gsap.to(ripple, {
                width: '1000px',
                height: '1000px',
                opacity: 0,
                duration: 1,
                ease: "power2.out",
                onComplete: () => {
                    ripple.remove();
                }
            });

            // Submit form via AJAX
            const formData = new FormData(loginForm);

            fetch('Login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.classList.remove('loading');

                if (data.status === 'success') {
                    // Success animation
                    gsap.to(loginCard, {
                        scale: 0.95,
                        opacity: 0,
                        duration: 0.5,
                        ease: "power2.in",
                        onComplete: () => {
                            window.location.href = data.redirect;
                        }
                    });
                } else {
                    // Error animation
                    showError(data.message);
                    gsap.to(loginCard, { x: -10, duration: 0.1, yoyo: true, repeat: 5 });
                }
            })
            .catch(error => {
                submitBtn.classList.remove('loading');
                showError('An error occurred. Please try again.');
                console.error('Error:', error);
            });
        });

        // ========================
        // Theme Toggle
        // ========================
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        
        // Check saved theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }

        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                localStorage.setItem('theme', 'dark');
            }
        });

        // ========================
        // Keyboard Shortcuts
        // ========================
        document.addEventListener('keydown', (e) => {
            // Alt + T for theme toggle
            if (e.altKey && e.key === 't') {
                e.preventDefault();
                themeToggle.click();
            }
            
            // Escape to go back
            if (e.key === 'Escape') {
                window.location.href = '../index.php';
            }
        });

        // ========================
        // Prevent zoom on double tap (mobile)
        // ========================
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (e) => {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // ========================
        // Loading Screen Enhancement
        // ========================
        window.addEventListener('load', () => {
            // Add subtle entrance animations
            gsap.from('.floating-symbol', {
                opacity: 0,
                scale: 0,
                duration: 1,
                stagger: 0.2,
                ease: "back.out(1.7)"
            });
        });

        // ========================
        // Console Easter Egg
        // ========================
        console.log('%c🎓 Campus Connect', 'font-size: 24px; font-weight: bold; color: #667eea;');
        console.log('%cConnecting Campus, Empowering Voices', 'font-size: 14px; color: #764ba2;');
        console.log('%c\n👨‍💻 Interested in the code? Check out our GitHub!', 'font-size: 12px; color: #b8c1ec;');
    </script>
</body>
</html>