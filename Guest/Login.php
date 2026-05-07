<?php
include("../Assets/Connection/Connection.php");
session_start();
if(isset($_POST["btn_login"])) {
    $email = $_POST["txt_email"];
    $password = $_POST["txt_password"];
    // -------- Admin Login --------
    $selAdmin = "SELECT * FROM tbl_admin WHERE admin_email='$email' AND admin_password='$password'";
    $resAdmin = mysqli_query($con, $selAdmin);
    if($dataAdmin = mysqli_fetch_assoc($resAdmin)) {
        $_SESSION["admin_id"] = $dataAdmin["admin_id"];
        $_SESSION["admin_name"] = $dataAdmin["admin_name"];
        header("location: ../Admin/AdminHome.php");
        exit();
    }
    // -------- Teacher/HOD Login --------
    $selTeacher = "SELECT * FROM tbl_teacher WHERE teacher_email='$email' AND teacher_password='$password'";
    $resTeacher = mysqli_query($con, $selTeacher);
    if($dataTeacher = mysqli_fetch_assoc($resTeacher)) {
        $teacher_id = $dataTeacher["teacher_id"];
        $teacher_name = $dataTeacher["teacher_name"];
        // Check if this teacher is HOD
        $hodQry = "SELECT department_id FROM tbl_department WHERE hod_teacher_id='$teacher_id' LIMIT 1";
        $resHod = mysqli_query($con, $hodQry);
        if($hodRow = mysqli_fetch_assoc($resHod)) {
            // Teacher is HOD
            $_SESSION['is_hod'] = true;
            $_SESSION['hod_id'] = $teacher_id;
            $_SESSION['hod_name'] = $teacher_name;
            $_SESSION['hod_department_id'] = $hodRow['department_id'];
            header("location: ../HOD/HODHome.php");
            exit();
        }
        $_SESSION["teacher_id"] = $dataTeacher["teacher_id"];
        $_SESSION["teacher_name"] = $dataTeacher["teacher_name"];
        $_SESSION["department_id"] = $dataTeacher["department_id"];
        $_SESSION["designation_id"] = $dataTeacher["designation_id"];
        // Check if this teacher is a class teacher
        $classQry = "SELECT class_id FROM tbl_class WHERE teacher_id='$teacher_id' AND is_completed=0 LIMIT 1";
        $resClass = mysqli_query($con, $classQry);
        if($classRow = mysqli_fetch_assoc($resClass)) {
            $_SESSION['is_class_teacher'] = true;
            $_SESSION['class_id'] = $classRow['class_id'];
        } else {
            $_SESSION['is_class_teacher'] = false;
        }
        // Redirect normal teacher
        header("location: ../Teacher/TeacherHome.php");
        exit();
    }
    // -------- Student Login --------
    $selStudent = "SELECT * FROM tbl_student WHERE student_email='$email' AND student_password='$password' AND is_active=1";
    $resStudent = mysqli_query($con, $selStudent);
    if($dataStudent = mysqli_fetch_assoc($resStudent)) {
        $_SESSION["student_id"] = $dataStudent["student_id"];
        $_SESSION["student_name"] = $dataStudent["student_name"];
        $_SESSION['class_id'] = $dataStudent["class_id"];
        $_SESSION["department_id"] = $dataStudent["department_id"];
        header("location: ../Student/StudentHome.php");
        exit();
    }
    $error = "Invalid Email or Password!"; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Connect</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ========================
           Global Styles & Variables
           ======================== */
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #f093fb;
            --dark-bg: #0a0e27;
            --card-bg: rgba(21, 25, 50, 0.85);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #b8c1ec;
            --glow: rgba(102, 126, 234, 0.6);
            --success: #10b981;
            --error: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            overflow-x: hidden;
            min-height: 100vh;
            position: relative;
        }

        /* ========================
           Animated Gradient Background
           ======================== */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(135deg, 
                #0a0e27 0%, 
                #1a1f3a 25%, 
                #2a1a40 50%, 
                #1a1f3a 75%, 
                #0a0e27 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* ========================
           Particle Canvas
           ======================== */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }

        /* ========================
           Split Screen Layout
           ======================== */
        .login-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Left Side - Lottie Animation */
        .lottie-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }

        .lottie-container {
            width: 100%;
            max-width: 600px;
            position: relative;
            z-index: 2;
        }

        #mainLottie {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 0 40px rgba(102, 126, 234, 0.3));
            transition: transform 0.3s ease;
        }

        .lottie-side:hover #mainLottie {
            transform: scale(1.05);
        }

        .welcome-text {
            text-align: center;
            margin-top: 2rem;
            z-index: 2;
        }

        .welcome-text h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            animation: textGlow 3s ease-in-out infinite;
        }

        @keyframes textGlow {
            0%, 100% { filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.5)); }
            50% { filter: drop-shadow(0 0 20px rgba(118, 75, 162, 0.8)); }
        }

        .welcome-text p {
            font-size: 1.2rem;
            color: var(--text-secondary);
            font-weight: 300;
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            animation: float 6s ease-in-out infinite;
            opacity: 0.6;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 70%;
            left: 15%;
            animation-delay: 1s;
        }

        .floating-element:nth-child(3) {
            top: 30%;
            right: 10%;
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        /* Right Side - Login Form */
        .form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            background: var(--card-bg);
            backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            animation: slideInRight 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            overflow: hidden;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Animated Border */
        .login-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--primary), var(--secondary), var(--accent), var(--primary));
            background-size: 400% 400%;
            border-radius: 30px;
            z-index: -1;
            animation: borderGlow 3s ease infinite;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-container:hover::before {
            opacity: 1;
        }

        @keyframes borderGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Back Button */
        .back-home {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 100;
        }

        .back-home a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 600;
            padding: 0.8rem 1.5rem;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }

        .back-home a::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
            z-index: -1;
        }

        .back-home a:hover::before {
            width: 300px;
            height: 300px;
        }

        .back-home a:hover {
            transform: translateX(-10px) scale(1.05);
            box-shadow: 0 5px 20px var(--glow);
        }

        /* Logo Section */
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-animation {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .logo-circle {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: logoFloat 3s ease-in-out infinite;
            box-shadow: 0 10px 40px var(--glow);
            transition: all 0.3s ease;
        }

        .logo-circle:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 50px var(--glow);
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .logo-circle i {
            font-size: 3.5rem;
            color: white;
        }

        .logo-circle::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(45deg, var(--primary), var(--accent), var(--secondary));
            border-radius: 30px;
            z-index: -1;
            filter: blur(20px);
            opacity: 0.7;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 1; }
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Role Selection Cards */
        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }

        .role-card {
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }

        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .role-card:hover::before {
            left: 100%;
        }

        .role-card:hover {
            transform: translateY(-10px) scale(1.05);
            border-color: var(--primary);
            background: rgba(102, 126, 234, 0.1);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .role-card.active {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            transform: scale(1.05);
        }

        .role-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 0.8rem;
        }

        .role-card i {
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s ease;
        }

        .role-card:hover i {
            transform: scale(1.2) rotate(10deg);
        }

        .role-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .role-card:hover .role-label,
        .role-card.active .role-label {
            color: var(--primary);
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.8rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .input-wrapper {
            position: relative;
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .input-wrapper:focus-within {
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s ease;
            z-index: 2;
        }

        .input-wrapper:focus-within .input-icon {
            transform: translateY(-50%) scale(1.2);
        }

        .form-control {
            width: 100%;
            padding: 1.1rem 3.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid var(--glass-border);
            border-radius: 15px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            position: relative;
        }

        .form-control:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary);
            box-shadow: 
                0 0 0 4px rgba(102, 126, 234, 0.1),
                0 5px 20px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .form-control:focus + .form-label {
            color: var(--primary);
        }

        .form-control::placeholder {
            color: rgba(184, 193, 236, 0.4);
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--secondary);
            transform: translateY(-50%) scale(1.2);
        }

        /* Error Message */
        .error-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            border: 2px solid rgba(239, 68, 68, 0.3);
            border-radius: 15px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: errorShake 0.5s ease, errorFadeIn 0.3s ease;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes errorFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-message i {
            color: var(--error);
            font-size: 1.5rem;
            animation: errorPulse 1s ease infinite;
        }

        @keyframes errorPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .error-message span {
            color: #fca5a5;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 15px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }

        .btn-login:active {
            transform: translateY(-2px) scale(0.98);
        }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--glass-border);
        }

        .login-footer p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            transition: width 0.3s ease;
        }

        .login-footer a:hover::after {
            width: 100%;
        }

        .login-footer a:hover {
            color: var(--secondary);
        }

        /* Decorative Lottie Elements */
        .mini-lottie {
            position: absolute;
            width: 100px;
            height: 100px;
            opacity: 0.6;
            pointer-events: none;
            animation: miniFloat 4s ease-in-out infinite;
        }

        .mini-lottie-1 {
            top: 5%;
            right: 5%;
            animation-delay: 0s;
        }

        .mini-lottie-2 {
            bottom: 10%;
            left: 5%;
            animation-delay: 1s;
        }

        @keyframes miniFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .login-layout {
                flex-direction: column;
            }

            .lottie-side {
                padding: 2rem;
                min-height: 40vh;
            }

            .welcome-text h1 {
                font-size: 2.5rem;
            }

            .form-side {
                min-height: 60vh;
            }
        }

        @media (max-width: 768px) {
            .role-selector {
                grid-template-columns: 1fr;
            }

            .back-home {
                top: 15px;
                left: 15px;
            }

            .login-container {
                padding: 2rem 1.5rem;
            }

            .welcome-text h1 {
                font-size: 2rem;
            }

            .lottie-container {
                max-width: 400px;
            }
        }

        /* Smooth Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
        }
    </style>
</head>
<body>
    
    <!-- Animated Background -->
    <div class="animated-bg"></div>
    
    <!-- Particle Background -->
    <div id="particles-js"></div>

    <!-- Back to Home -->
    <div class="back-home">
        <a href="../index.php">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>

    <!-- Login Layout -->
    <div class="login-layout">
        
        <!-- Left Side - Lottie Animation -->
        <div class="lottie-side">
            <!-- Floating Elements -->
            <div class="floating-element">
                <i class="fas fa-book" style="font-size: 60px; color: rgba(102, 126, 234, 0.3);"></i>
            </div>
            <div class="floating-element">
                <i class="fas fa-graduation-cap" style="font-size: 70px; color: rgba(118, 75, 162, 0.3);"></i>
            </div>
            <div class="floating-element">
                <i class="fas fa-user-graduate" style="font-size: 65px; color: rgba(240, 147, 251, 0.3);"></i>
            </div>
            
            <!-- Main Lottie Animation -->
            <div class="lottie-container">
                <div id="mainLottie"></div>
            </div>
            
            <!-- Welcome Text -->
            <div class="welcome-text">
                <h1>Campus Connect</h1>
                <p>Connecting Campus, Empowering Voices</p>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="form-side">
            <!-- Mini Decorative Lotties -->
            <div class="mini-lottie mini-lottie-1" id="miniLottie1"></div>
            <div class="mini-lottie mini-lottie-2" id="miniLottie2"></div>
            
            <div class="login-container">
                <!-- Logo -->
                <div class="login-logo">
                    <div class="logo-animation">
                        <div class="logo-circle">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <h2 class="login-title">Welcome Back!</h2>
                    <p class="login-subtitle">Sign in to continue your journey</p>
                </div>

                <!-- Role Selector -->
                <div class="role-selector">
                    <div class="role-card" data-role="student">
                        <div class="role-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="role-label">Student</div>
                    </div>
                    <div class="role-card" data-role="teacher">
                        <div class="role-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="role-label">Teacher</div>
                    </div>
                    <div class="role-card" data-role="admin">
                        <div class="role-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="role-label">Admin</div>
                    </div>
                </div>

                <!-- Error Message -->
                <?php if(isset($error)) { ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php } ?>

                <!-- Login Form -->
                <form method="post" action="" id="loginForm">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                name="txt_email" 
                                class="form-control" 
                                placeholder="Enter your email"
                                required
                                autocomplete="email"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                name="txt_password" 
                                id="password"
                                class="form-control" 
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="btn_login" class="btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <!-- Footer -->
                <div class="login-footer">
                    <p>Need help? <a href="#">Contact Support</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    <script>
        // ========================
        // Particle.js Configuration
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
                    type: ['circle', 'triangle', 'edge'],
                },
                opacity: {
                    value: 0.4,
                    random: true,
                    anim: {
                        enable: true,
                        speed: 1,
                        opacity_min: 0.1,
                        sync: false
                    }
                },
                size: {
                    value: 4,
                    random: true,
                    anim: {
                        enable: true,
                        speed: 2,
                        size_min: 0.1,
                        sync: false
                    }
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#667eea',
                    opacity: 0.3,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: true,
                    straight: false,
                    out_mode: 'out',
                    bounce: false,
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
                            opacity: 0.8
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
        // Lottie Animations
        // ========================
        
        // Main Education Lottie Animation
        const mainLottie = lottie.loadAnimation({
            container: document.getElementById('mainLottie'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: 'https://lottie.host/50f3c9ec-d4ed-4d8c-b3e5-c7e2ea8ac8e9/IYiVVJw1LT.json' // Education/Study animation
        });

        // Mini Lottie 1 - Books
        const miniLottie1 = lottie.loadAnimation({
            container: document.getElementById('miniLottie1'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: 'https://lottie.host/b7e1e9f6-44e0-4e6f-9c17-3c3e9c8a7f5d/x5rYVqMDhM.json' // Books animation
        });

        // Mini Lottie 2 - Graduation
        const miniLottie2 = lottie.loadAnimation({
            container: document.getElementById('miniLottie2'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: 'https://lottie.host/8f6e7d5c-33b2-4a1f-8e0d-2b1a0c9d8e7f/Ab3XyZwPqR.json' // Graduation cap animation
        });

        // Hover effects on Lottie
        document.querySelector('.lottie-container').addEventListener('mouseenter', () => {
            mainLottie.setSpeed(1.5);
        });

        document.querySelector('.lottie-container').addEventListener('mouseleave', () => {
            mainLottie.setSpeed(1);
        });

        // ========================
        // Role Card Selection
        // ========================
        const roleCards = document.querySelectorAll('.role-card');
        
        roleCards.forEach(card => {
            card.addEventListener('click', function() {
                roleCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Trigger confetti effect
                createConfetti(this);
            });

            // Enhanced hover effects
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.08) rotateZ(2deg)';
            });

            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = '';
                }
            });
        });

        // Confetti effect on role selection
        function createConfetti(element) {
            const rect = element.getBoundingClientRect();
            const colors = ['#667eea', '#764ba2', '#f093fb'];
            
            for (let i = 0; i < 20; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.left = rect.left + rect.width / 2 + 'px';
                confetti.style.top = rect.top + rect.height / 2 + 'px';
                confetti.style.width = '8px';
                confetti.style.height = '8px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = '50%';
                confetti.style.pointerEvents = 'none';
                confetti.style.zIndex = '1000';
                confetti.style.transition = 'all 1s ease-out';
                
                document.body.appendChild(confetti);
                
                const angle = (Math.PI * 2 * i) / 20;
                const velocity = 100 + Math.random() * 100;
                
                setTimeout(() => {
                    confetti.style.transform = `translate(${Math.cos(angle) * velocity}px, ${Math.sin(angle) * velocity}px)`;
                    confetti.style.opacity = '0';
                }, 10);
                
                setTimeout(() => {
                    confetti.remove();
                }, 1000);
            }
        }

        // ========================
        // Password Toggle
        // ========================
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if(type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });

        // ========================
        // Form Enhancements
        // ========================
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const inputs = document.querySelectorAll('.form-control');

        // Input focus effects
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-3px)';
                this.parentElement.style.transition = 'all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = '';
            });

            // Floating label effect
            input.addEventListener('input', function() {
                if(this.value.length > 0) {
                    this.style.borderColor = '#667eea';
                } else {
                    this.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                }
            });
        });

        // Form submission
        let isSubmitting = false;
        loginForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            
            isSubmitting = true;
            loginBtn.classList.add('loading');
            loginBtn.innerHTML = '';
            
            // Add sparkle effect
            createSparkles(loginBtn);
        });

        // Sparkle effect
        function createSparkles(element) {
            const rect = element.getBoundingClientRect();
            for (let i = 0; i < 15; i++) {
                const sparkle = document.createElement('div');
                sparkle.style.position = 'fixed';
                sparkle.style.left = rect.left + Math.random() * rect.width + 'px';
                sparkle.style.top = rect.top + Math.random() * rect.height + 'px';
                sparkle.style.width = '4px';
                sparkle.style.height = '4px';
                sparkle.style.backgroundColor = 'white';
                sparkle.style.borderRadius = '50%';
                sparkle.style.pointerEvents = 'none';
                sparkle.style.zIndex = '1001';
                sparkle.style.animation = 'sparkle 0.8s ease-out forwards';
                
                document.body.appendChild(sparkle);
                
                setTimeout(() => sparkle.remove(), 800);
            }
        }

        // Add sparkle animation to CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes sparkle {
                0% {
                    transform: scale(0) translateY(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(1) translateY(-50px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // ========================
        // Mouse Trail Effect
        // ========================
        let mouseTrail = [];
        const maxTrailLength = 10;

        document.addEventListener('mousemove', (e) => {
            mouseTrail.push({
                x: e.clientX,
                y: e.clientY,
                time: Date.now()
            });

            if (mouseTrail.length > maxTrailLength) {
                mouseTrail.shift();
            }

            // Create trail dot
            if (Math.random() > 0.8) {
                const dot = document.createElement('div');
                dot.style.position = 'fixed';
                dot.style.left = e.clientX + 'px';
                dot.style.top = e.clientY + 'px';
                dot.style.width = '4px';
                dot.style.height = '4px';
                dot.style.borderRadius = '50%';
                dot.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                dot.style.pointerEvents = 'none';
                dot.style.zIndex = '9999';
                dot.style.transition = 'all 0.5s ease';
                dot.style.opacity = '0.6';
                
                document.body.appendChild(dot);
                
                setTimeout(() => {
                    dot.style.opacity = '0';
                    dot.style.transform = 'scale(0)';
                }, 100);
                
                setTimeout(() => dot.remove(), 600);
            }
        });

        // ========================
        // Keyboard Navigation Enhancement
        // ========================
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && document.activeElement.tagName !== 'BUTTON') {
                loginBtn.click();
            }
        });

        // ========================
        // Page Load Animation
        // ========================
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.6s ease';
                document.body.style.opacity = '1';
            }, 100);

            // Animate role cards on load
            roleCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 200 + (index * 100));
            });
        });

        // ========================
        // 3D Tilt Effect on Login Container
        // ========================
        const loginContainer = document.querySelector('.login-container');
        
        loginContainer.addEventListener('mousemove', (e) => {
            const rect = loginContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            loginContainer.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
        });
        
        loginContainer.addEventListener('mouseleave', () => {
            loginContainer.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
        });

        // ========================
        // Dynamic Background Color Shift
        // ========================
        let hue = 0;
        setInterval(() => {
            hue = (hue + 1) % 360;
            document.querySelector('.animated-bg').style.filter = `hue-rotate(${hue}deg)`;
        }, 100);

        // ========================
        // Easter Egg - Secret Admin Mode
        // ========================
        let secretCode = [];
        const secret = ['a', 'd', 'm', 'i', 'n'];
        
        document.addEventListener('keypress', (e) => {
            secretCode.push(e.key.toLowerCase());
            if (secretCode.length > secret.length) {
                secretCode.shift();
            }
            
            if (secretCode.join('') === secret.join('')) {
                roleCards[2].click();
                createFireworks();
            }
        });

        function createFireworks() {
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const firework = document.createElement('div');
                    firework.style.position = 'fixed';
                    firework.style.left = Math.random() * window.innerWidth + 'px';
                    firework.style.top = Math.random() * window.innerHeight + 'px';
                    firework.style.width = '10px';
                    firework.style.height = '10px';
                    firework.style.borderRadius = '50%';
                    firework.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
                    firework.style.pointerEvents = 'none';
                    firework.style.zIndex = '10000';
                    firework.style.animation = 'firework 1s ease-out forwards';
                    
                    document.body.appendChild(firework);
                    setTimeout(() => firework.remove(), 1000);
                }, i * 50);
            }
        }

        const fireworkStyle = document.createElement('style');
        fireworkStyle.textContent = `
            @keyframes firework {
                0% {
                    transform: scale(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(3);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(fireworkStyle);

        // ========================
        // Console Welcome
        // ========================
        console.log('%c🎓 Campus Connect - Login Portal', 'color: #667eea; font-size: 20px; font-weight: bold; text-shadow: 2px 2px 4px rgba(102, 126, 234, 0.5);');
        console.log('%c✨ Connecting Campus, Empowering Voices', 'color: #764ba2; font-size: 14px; font-style: italic;');
        console.log('%c💡 Tip: Type "admin" anywhere to activate secret admin mode!', 'color: #f093fb; font-size: 12px;');
    </script>
</body>
</html>