<?php
// index.php
require_once 'includes/auth.php';
require_once 'config/database.php';

redirectIfLoggedIn();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = trim($_POST['username']);
    $password = $_POST['password'];

    // Support signing in by either Username OR Email address!
    if (filter_var($username_or_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    }
    $stmt->execute([$username_or_email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        header("Location: " . $user['role'] . "/dashboard.php");
        exit();
    } else {
        $error = "Invalid username/email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ST Thresa School - Empowering Academic Excellence</title>
    
    <!-- Fonts & FontAwesome Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/landing.css?v=3.0">
</head>
<body>

    <!-- Sticky Header Navigation -->
    <header class="landing-header" id="navbar">
        <a href="#hero" class="brand">
            <i class="fas fa-graduation-cap" style="color: var(--accent);"></i>
            <span>ST Thresa School</span>
        </a>
        <ul class="nav-links">
            <li><a href="#about" class="nav-link">About</a></li>
            <li><a href="#features" class="nav-link">Features</a></li>
            <li><a href="#courses" class="nav-link">Courses</a></li>
            <li><a href="#gallery" class="nav-link">Gallery</a></li>
            <li><a href="#testimonials" class="nav-link">Testimonials</a></li>
            <li><a href="#contact" class="nav-link">Contact</a></li>
        </ul>
        <a href="login.php" class="nav-cta"><i class="fas fa-sign-in-alt"></i> Portal Sign-In</a>
    </header>

    <!-- Hero Section & Integrated Login Card -->
    <section class="hero-section" id="hero" style="position: relative; overflow: hidden; display: flex; align-items: flex-end; padding-bottom: 8rem; justify-content: flex-start; text-align: left; padding-left: 10px;">
        <!-- Video Background -->
        <video autoplay muted loop playsinline style="position: absolute; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; z-index: 0; transform: translateX(-50%) translateY(-50%); object-fit: cover; opacity: 0.9;">
            <source src="assets/images/hero_bg.mp4?v=2" type="video/mp4">
        </video>
        
        <div class="hero-content" style="position: relative; z-index: 2; width: 100%; max-width: 800px; display: flex; flex-direction: column; align-items: flex-start;">
            <span class="hero-tag"><i class="fas fa-star"></i> Ranked #1 Academic Institution</span>
            <h1 class="hero-title">Empowering Minds,<br><span>Shaping Dynamic Futures</span></h1>
            
            <div class="hero-actions" style="margin-top: 2rem;">
                <a href="#courses" class="btn btn-primary" style="width: auto; text-decoration: none;">
                    Explore Courses <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#about" class="btn" style="width: auto; background: #F5EEEE; color: var(--accent); border: 1px solid #EADAD9; text-decoration: none;">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- School Features Section -->
    <section class="section section-bg" id="features">
        <div class="section-header">
            <span class="section-tag">Key Features</span>
            <h2 class="section-title">Why Choose ST Thresa School?</h2>
            <p class="section-desc">We combine academic rigour with professional career preparation and virtual operations to build the modern leader.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3 class="feature-title">Smart Labs</h3>
                <p class="feature-desc">Equipped with ultra-fast computers and high-end scientific testing benches to encourage discovery and deep learning.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background: #d1fae5; color: #059669;">
                    <i class="fas fa-globe-americas"></i>
                </div>
                <h3 class="feature-title">Global Curriculum</h3>
                <p class="feature-desc">Aligned with leading international educational frameworks to ensure students are prepared to compete on the world stage.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 class="feature-title">Expert Faculty</h3>
                <p class="feature-desc">Taught by highly credentialed scholars and industry experts committed to fostering creativity and academic success.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background: #fee2e2; color: #dc2626;">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <h3 class="feature-title">Secure Portal</h3>
                <p class="feature-desc">Access our robust role-based secure virtual platform to track grades, enrollments, and daily attendance seamlessly.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section" id="about">
        <div class="about-grid">
            <div class="about-graphics">
                <!-- Authentic ST Thresa School Student Photos -->
                <img src="assets/images/photo1.jpg" alt="ST Thresa Students Group" class="about-img large">
                <img src="assets/images/photo2.jpg" alt="ST Thresa Student" class="about-img">
                <img src="assets/images/photo3.jpg" alt="ST Thresa Students Walking" class="about-img">
            </div>
            <div>
                <span class="section-tag" style="text-align: left; margin: 0 0 0.75rem 0;">Our Story & Mission</span>
                <h2 class="section-title" style="text-align: left; margin-bottom: 1.5rem;">Fostering a Culture of Innovation & Integrity</h2>
                <p class="about-tagline">"We don't just teach courses; we guide tomorrow's global trailblazers to fulfill their maximum capability."</p>
                <p class="about-p">ST Thresa School was founded with the singular goal of integrating technology and top-tier pedagogical practices. We provide a space where curiosity is celebrated and students have access to operational virtual platforms to manage their academic careers.</p>
                
                <div class="about-values">
                    <div class="value-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h5>Excellence</h5>
                            <p>We push academic benchmarks further each year.</p>
                        </div>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h5>Integrity</h5>
                            <p>Operating with transparency and moral responsibility.</p>
                        </div>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h5>Leadership</h5>
                            <p>Building characters capable of guiding complex environments.</p>
                        </div>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h5>Collaboration</h5>
                            <p>Fostering cooperative learning models across departments.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section -->
    <section class="section section-bg" id="courses">
        <div class="section-header">
            <span class="section-tag">Our Programs</span>
            <h2 class="section-title">Popular Academic Courses</h2>
            <p class="section-desc">Explore a selection of our premium engineering and computer science programs led by our highly qualified instructors.</p>
        </div>
        <div class="courses-grid">
            <div class="course-card">
                <div class="course-img-wrapper">
                    <img src="assets/images/photo4.jpg" alt="ST Thresa Student Profile" class="course-img">
                    <span class="course-badge">Computer Science</span>
                </div>
                <div class="course-body">
                    <a href="#hero" class="course-title">Web Development (CS202)</a>
                    <p class="course-desc">A deep dive into advanced frontend architectures, databases, PHP backend operations, and modern CSS3 styles.</p>
                    <div class="course-meta">
                        <span><i class="fas fa-graduation-cap"></i> Saron Welyu</span>
                        <span style="font-weight: 700; color: var(--accent);"><i class="fas fa-star"></i> 4 Credits</span>
                    </div>
                </div>
            </div>
            <div class="course-card">
                <div class="course-img-wrapper">
                    <img src="assets/images/photo6.jpg" alt="ST Thresa Student" class="course-img">
                    <span class="course-badge">Data Systems</span>
                </div>
                <div class="course-body">
                    <a href="#hero" class="course-title">Database Systems (CS301)</a>
                    <p class="course-desc">Analyze relational databases, design robust entities, write advanced MySQL queries, and study index structures.</p>
                    <div class="course-meta">
                        <span><i class="fas fa-graduation-cap"></i> Saron Welyu</span>
                        <span style="font-weight: 700; color: var(--accent);"><i class="fas fa-star"></i> 3 Credits</span>
                    </div>
                </div>
            </div>
            <div class="course-card">
                <div class="course-img-wrapper">
                    <img src="assets/images/photo7.jpg" alt="ST Thresa Student" class="course-img">
                    <span class="course-badge">Engineering</span>
                </div>
                <div class="course-body">
                    <a href="#hero" class="course-title">Software Engineering (SE101)</a>
                    <p class="course-desc">Discover complex project management cycles, unit-testing, model structures, and responsive design patterns.</p>
                    <div class="course-meta">
                        <span><i class="fas fa-graduation-cap"></i> Not Assigned</span>
                        <span style="font-weight: 700; color: var(--accent);"><i class="fas fa-star"></i> 3 Credits</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section (TikTok Videos) -->
    <section class="section" id="gallery">
        <div class="section-header">
            <span class="section-tag">Campus Life</span>
            <h2 class="section-title">Featured School Moments</h2>
            <p class="section-desc">Take a look inside our dynamic campus life, student activities, and school events through our featured TikTok videos.</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 2.5rem; justify-content: center; align-items: flex-start;">
            <!-- TikTok Video 1 -->
            <div style="width: 100%; max-width: 330px; border-radius: 1rem; overflow: hidden; box-shadow: 0 10px 25px rgba(130, 15, 54, 0.1);">
                <blockquote class="tiktok-embed" cite="https://vt.tiktok.com/ZSx6bpb2x/" data-video-id="" style="max-width: 605px;min-width: 325px; margin: 0;">
                    <section></section>
                </blockquote>
            </div>
            
            <!-- TikTok Video 2 -->
            <div style="width: 100%; max-width: 330px; border-radius: 1rem; overflow: hidden; box-shadow: 0 10px 25px rgba(130, 15, 54, 0.1);">
                <blockquote class="tiktok-embed" cite="https://vt.tiktok.com/ZSx6gJFWT/" data-video-id="" style="max-width: 605px;min-width: 325px; margin: 0;">
                    <section></section>
                </blockquote>
            </div>
        </div>
        <!-- TikTok Embed Script loaded asynchronously -->
        <script async src="https://www.tiktok.com/embed.js"></script>
    </section>

    <!-- Testimonials Section -->
    <section class="section section-bg" id="testimonials">
        <div class="section-header">
            <span class="section-tag">Testimonials</span>
            <h2 class="section-title">What Our Community Says</h2>
            <p class="section-desc">Read honest perspectives from our enrolled students, alumni, and parents regarding our educational quality.</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <p class="testimonial-quote">"The digital student portal is a game changer. I can log in, view my attendance logs, and check my grading scores instantly. The courses are rich and teachers are top-tier."</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">AJ</div>
                    <div>
                        <h5>Alice Johnson</h5>
                        <p>Computer Science Student</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-quote">"As an instructor, having dual transactional double-insert profiles for registering students, tracking attendance, and uploading marks lets me focus on what's most important: high quality teaching!"</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">JS</div>
                    <div>
                        <h5>Dr. John Smith</h5>
                        <p>Senior Faculty Teacher</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-quote">"ST Thresa School gave my daughter the exact skills she needed to land a job at a global technology firm. The school environment is supportive and modern."</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">MP</div>
                    <div>
                        <h5>Marcus Peters</h5>
                        <p>Parent of Graduate Student</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & Map Section -->
    <section class="section" id="contact">
        <div class="contact-grid">
            <div>
                <span class="section-tag" style="text-align: left; margin: 0 0 0.75rem 0;">Get In Touch</span>
                <h2 class="section-title" style="text-align: left; margin-bottom: 1.5rem;">Contact Our Admissions Office</h2>
                <p class="about-p" style="margin-bottom: 2rem;">Have questions about admission processes, portal registration, or general curricula? Send our office a letter or reach out directly via telephone.</p>
                
                <ul class="contact-list">
                    <li class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h6>Campus Location</h6>
                            <p>102 University Avenue, Academic Square, NY 10012</p>
                        </div>
                    </li>
                    <li class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <h6>Admissions Hotline</h6>
                            <p>+1 (555) 839-2910 | Mon - Fri, 8:00 AM - 5:00 PM</p>
                        </div>
                    </li>
                    <li class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h6>Admissions Support Email</h6>
                            <p>admissions@stthresaschool.edu | info@stthresa.com</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Message Submission Card -->
            <div class="contact-card">
                <h4>Send Us a Direct Message</h4>
                <form method="GET" action="#contact" onsubmit="alert('Thank you for contacting admissions! We will reply to your inquiry shortly.'); return true;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 0.8rem; font-weight: 600;">Full Name</label>
                            <input type="text" class="form-control" placeholder="John Doe" required style="padding: 0.6rem;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-size: 0.8rem; font-weight: 600;">Email Address</label>
                            <input type="email" class="form-control" placeholder="john@example.com" required style="padding: 0.6rem;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size: 0.8rem; font-weight: 600;">Subject Topic</label>
                        <select class="form-control" required style="padding: 0.6rem;">
                            <option value="">-- Select Inquiry Topic --</option>
                            <option value="admissions">Admissions & Tuition</option>
                            <option value="portal">Student/Teacher Portal Support</option>
                            <option value="other">General Academic Questions</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size: 0.8rem; font-weight: 600;">Message Content</label>
                        <textarea class="form-control" rows="4" placeholder="How can our admissions office assist you?" required style="padding: 0.6rem; resize: none;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Submit Inquiry Message <i class="far fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Corporate Footer -->
    <footer class="landing-footer">
        <div class="footer-top">
            <div>
                <div class="footer-brand">
                    <i class="fas fa-graduation-cap" style="color: var(--accent);"></i>
                    <span>ST Thresa School</span>
                </div>
                <p class="footer-desc">Bridging scientific study, academic excellence, and modern virtual portal services to nurture tomorrow's leading figures.</p>
                <div class="footer-socials">
                    <a href="#" class="footer-social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="footer-social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="footer-social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="footer-social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <h5 class="footer-title">Inquiry Nav</h5>
                <ul class="footer-links">
                    <li class="footer-link-item"><a href="#about" class="footer-link">About Academy</a></li>
                    <li class="footer-link-item"><a href="#features" class="footer-link">Core Features</a></li>
                    <li class="footer-link-item"><a href="#courses" class="footer-link">Department Courses</a></li>
                    <li class="footer-link-item"><a href="#gallery" class="footer-link">Photo Gallery</a></li>
                </ul>
            </div>
            <div>
                <h5 class="footer-title">Digital Portal</h5>
                <p class="footer-desc" style="margin-bottom: 1rem;">Access your academic reports from any device. Try standard seeded profiles in the portal card at the top-right:</p>
                <ul style="list-style: none; font-size: 0.8rem; color: #94a3b8;">
                    <li style="margin-bottom: 0.25rem;"><strong style="color: #cbd5e1;">Admin:</strong> admin / 123</li>
                    <li style="margin-bottom: 0.25rem;"><strong style="color: #cbd5e1;">Teacher:</strong> teacher1 / password</li>
                    <li style="margin-bottom: 0.25rem;"><strong style="color: #cbd5e1;">Student:</strong> student1 / password</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> ST Thresa School Student Management System. All rights reserved. Designed with premium educational standards.</p>
        </div>
    </footer>

    <!-- Dynamic Header Scroll Micro-interaction -->
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
