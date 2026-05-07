<?php
include("Assets/Connection/Connection.php");

// Fetch live statistics from database
$q_students = mysqli_query($con, "SELECT COUNT(*) AS total FROM tbl_student WHERE is_active=1");
$q_teachers = mysqli_query($con, "SELECT COUNT(*) AS total FROM tbl_teacher");
$q_resolved = mysqli_query($con, "SELECT COUNT(*) AS total FROM tbl_complaint WHERE complaint_status='Resolved'");
$q_notices = mysqli_query($con, "SELECT COUNT(*) AS total FROM tbl_notice");

$students = mysqli_fetch_assoc($q_students)['total'];
$teachers = mysqli_fetch_assoc($q_teachers)['total'];
$resolved = mysqli_fetch_assoc($q_resolved)['total'];
$notices = mysqli_fetch_assoc($q_notices)['total'];
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Connect - Connecting Campus, Empowering Voices</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="Assets/CSS/style.css">
</head>
<body class="dark-mode">
    
    <!-- Particle Background -->
    <div id="particles-js"></div>
    
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top glass-nav" data-aos="fade-down">
        <div class="container">
            <a class="navbar-brand logo-text" href="#">
                <i class="fas fa-graduation-cap"></i> Campus<span class="gradient-text">Connect</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reviews">Reviews</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="Guest/Login.php" class="btn btn-glow">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <button class="theme-toggle" id="themeToggle">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Campus<span class="gradient-text">Connect</span>
                        </h1>
                        <p class="hero-subtitle">Connecting Campus, Empowering Voices.</p>
                        <p class="hero-description">
                            Your all-in-one digital platform for seamless campus management. 
                            Submit complaints, view notices, and access study materials - all in one place.
                        </p>
                        <div class="hero-buttons">
                            <a href="Guest/Login.php" class="btn btn-primary btn-glow btn-lg me-3">
                                <i class="fas fa-rocket"></i> Login Now
                            </a>
                            <a href="#about" class="btn btn-outline-light btn-lg smooth-scroll">
                                <i class="fas fa-info-circle"></i> Know More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000">
                    <div class="hero-illustration">
                        <div class="floating-card card-1">
                            <i class="fas fa-comments"></i>
                            <span>Complaints</span>
                        </div>
                        <div class="floating-card card-2">
                            <i class="fas fa-bell"></i>
                            <span>Notices</span>
                        </div>
                        <div class="floating-card card-3">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Attendance</span>
                        </div>
                        <div class="floating-card card-4">
                            <i class="fas fa-book"></i>
                            <span>Resources</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-padding">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="section-title">About <span class="gradient-text">Campus Connect</span></h2>
                <p class="section-subtitle">Revolutionizing Campus Management</p>
            </div>
            <div class="row mt-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="about-content">
                        <p class="lead">
                            Campus Connect is an all-in-one digital platform designed to streamline communication 
                            and management within your college community.
                        </p>
                        <p>
                            From submitting complaints to accessing study materials, viewing notices to tracking attendance - 
                            everything you need is just a click away. Built by students, for students.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-4">
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <h4>Submit Complaints</h4>
                                <p>Raise your concerns directly to class teachers, HODs, or admin</p>
                            </div>
                        </div>
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <h4>View Notices</h4>
                                <p>Stay updated with all campus announcements and notices</p>
                            </div>
                        </div>
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-download"></i>
                                </div>
                                <h4>Study Materials</h4>
                                <p>Access and download course materials anytime, anywhere</p>
                            </div>
                        </div>
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4>Track Attendance</h4>
                                <p>Monitor your attendance records in real-time</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Statistics Section -->
    <section class="stats-section section-padding">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="section-title">Live <span class="gradient-text">Statistics</span></h2>
                <p class="section-subtitle">Real-time campus data</p>
            </div>
            <div class="row mt-5 g-4">
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $students; ?>">0</h3>
                        <p class="stat-label">Registered Students</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $teachers; ?>">0</h3>
                        <p class="stat-label">Faculty Members</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $resolved; ?>">0</h3>
                        <p class="stat-label">Complaints Resolved</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $notices; ?>">0</h3>
                        <p class="stat-label">Notices Posted</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Zone (Flip Cards) -->
    <section id="features" class="section-padding">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="section-title">Access <span class="gradient-text">Portals</span></h2>
                <p class="section-subtitle">Choose your role to continue</p>
            </div>
            <div class="row mt-5 g-4">
                <div class="col-lg-4 col-md-6" data-aos="flip-left" data-aos-delay="100">
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="zone-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <h3>Student Zone</h3>
                                <p>Access your portal</p>
                            </div>
                            <div class="flip-card-back">
                                <h4>Student Portal</h4>
                                <p>View notices, submit complaints, check attendance, and download study materials</p>
                                <a href="Guest/Login.php" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-right"></i> Go
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="flip-left" data-aos-delay="200">
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="zone-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <h3>Faculty Zone</h3>
                                <p>Manage your classes</p>
                            </div>
                            <div class="flip-card-back">
                                <h4>Faculty Portal</h4>
                                <p>Post notices, manage complaints, mark attendance, and upload study materials</p>
                                <a href="Guest/Login.php" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-right"></i> Go
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="flip-left" data-aos-delay="300">
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="zone-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h3>Admin Zone</h3>
                                <p>Control panel</p>
                            </div>
                            <div class="flip-card-back">
                                <h4>Admin Portal</h4>
                                <p>Manage users, departments, courses, and oversee all campus operations</p>
                                <a href="Guest/Login.php" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-right"></i> Go
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="section-padding reviews-section">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="section-title">What Users <span class="gradient-text">Say</span></h2>
                <p class="section-subtitle">Testimonials from our community</p>
            </div>
            <div class="swiper reviewSwiper mt-5" data-aos="fade-up">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="review-card">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="review-text">
                                "Campus Connect has made my college life so much easier! I can now submit complaints 
                                and get responses quickly. The interface is super clean and modern."
                            </p>
                            <div class="reviewer">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="reviewer-info">
                                    <h5>Liam Brown</h5>
                                    <span>BCA Student</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="review-card">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <p class="review-text">
                                "As a teacher, this platform helps me manage attendance and communicate with students 
                                effortlessly. Highly recommend for educational institutions!"
                            </p>
                            <div class="reviewer">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="reviewer-info">
                                    <h5>Adithyan V.S</h5>
                                    <span>Faculty Member</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="review-card">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="review-text">
                                "The notice board feature is fantastic! No more missing important announcements. 
                                Everything is organized and accessible from my phone."
                            </p>
                            <div class="reviewer">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="reviewer-info">
                                    <h5>Emma Smith</h5>
                                    <span>BCA Student</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="review-card">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="review-text">
                                "Managing the entire campus has never been this simple. The admin dashboard 
                                gives me complete control and insights into all operations."
                            </p>
                            <div class="reviewer">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="reviewer-info">
                                    <h5>Austin Shibu</h5>
                                    <span>System Administrator</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="wave-animation">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
            </svg>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="footer-title">
                        <i class="fas fa-graduation-cap"></i> Campus<span class="gradient-text">Connect</span>
                    </h4>
                    <p class="footer-text">
                        Connecting Campus, Empowering Voices. Your all-in-one platform for seamless campus management.
                    </p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-heading">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#reviews">Reviews</a></li>
                        <li><a href="Guest/Login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-heading">Contact Info</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-envelope"></i> support@campusconnect.com</li>
                        <li><i class="fas fa-phone"></i> +91 9778149090</li>
                        <li><i class="fas fa-map-marker-alt"></i> Kerala, India</li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-bottom">
                <p>&copy; 2025 Campus Connect | All Rights Reserved</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="Assets/JS/main.js"></script>
</body>
</html>