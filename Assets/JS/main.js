// ========================
// Initialize AOS safely
// ========================
if (window.AOS) {
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });
}

// ========================
// Particle.js Configuration (safe for all pages)
// ========================
if (window.particlesJS && document.getElementById('particles-js')) {
    particlesJS('particles-js', {
        particles: {
            number: { value: 80, density: { enable: true, value_area: 800 } },
            color: { value: '#667eea' },
            shape: { type: 'circle' },
            opacity: { value: 0.5 },
            size: { value: 3, random: true },
            line_linked: {
                enable: true,
                distance: 150,
                color: '#667eea',
                opacity: 0.4,
                width: 1
            },
            move: { enable: true, speed: 2, out_mode: 'out' }
        },
        interactivity: {
            detect_on: 'canvas',
            events: {
                onhover: { enable: true, mode: 'grab' },
                onclick: { enable: true, mode: 'push' },
                resize: true
            },
            modes: {
                grab: { distance: 140, line_linked: { opacity: 1 } },
                push: { particles_nb: 4 }
            }
        },
        retina_detect: true
    });
}

// ========================
// Dark/Light Mode Toggle (global + synced)
// ========================
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

const currentTheme = localStorage.getItem('theme') || 'dark';
body.classList.toggle('light-mode', currentTheme === 'light');
updateThemeIcon();

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('light-mode');
        const theme = body.classList.contains('light-mode') ? 'light' : 'dark';
        localStorage.setItem('theme', theme);
        updateThemeIcon();
        updateParticlesColor(theme);
    });
}

function updateThemeIcon() {
    if (!themeToggle) return;
    const icon = themeToggle.querySelector('i');
    if (!icon) return;
    if (body.classList.contains('light-mode')) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }
}

function updateParticlesColor(theme) {
    const particleColor = theme === 'light' ? '#667eea' : '#667eea';
    if (window.pJSDom && window.pJSDom[0]) {
        window.pJSDom[0].pJS.particles.color.value = particleColor;
        window.pJSDom[0].pJS.particles.line_linked.color = particleColor;
        if (window.pJSDom[0].pJS.fn && typeof window.pJSDom[0].pJS.fn.particlesRefresh === 'function') {
            window.pJSDom[0].pJS.fn.particlesRefresh();
        }
    }
}

// ========================
// Navbar Scroll Effect
// ========================
const nav = document.querySelector('.glass-nav');
if (nav) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            nav.style.padding = '0.5rem 0';
            nav.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
        } else {
            nav.style.padding = '1rem 0';
            nav.style.boxShadow = 'none';
        }
    });
}

// ========================
// Smooth Scrolling for Anchor Links
// ========================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href && href.startsWith('#')) {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offset = 80;
                const targetPosition = target.offsetTop - offset;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                const navCollapse = document.querySelector('.navbar-collapse');
                if (navCollapse && navCollapse.classList.contains('show')) {
                    navCollapse.classList.remove('show');
                }
            }
        }
    });
});

// ========================
// Counter Animation
// ========================
const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 16);
    }

    const observerOptions = { threshold: 0.5 };
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counters = entry.target.querySelectorAll('.stat-number');
                counters.forEach(counter => {
                    if (counter.textContent === '0') animateCounter(counter);
                });
                counterObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    counterObserver.observe(statsSection);
}

// ========================
// Swiper Slider (safe for missing elements)
// ========================
if (typeof Swiper !== 'undefined' && document.querySelector('.reviewSwiper')) {
    new Swiper('.reviewSwiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        breakpoints: { 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
    });
}

// ========================
// Active Navigation Link on Scroll
// ========================
const sections = document.querySelectorAll('section[id]');
if (sections.length > 0) {
    window.addEventListener('scroll', () => {
        const scrollY = window.pageYOffset;
        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 100;
            const sectionId = section.getAttribute('id');
            const navLink = document.querySelector(`.navbar-nav a[href="#${sectionId}"]`);
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                document.querySelectorAll('.navbar-nav .nav-link').forEach(link => link.classList.remove('active'));
                if (navLink) navLink.classList.add('active');
            }
        });
    });
}

// ========================
// Floating Animation Enhancement
// ========================
document.querySelectorAll('.floating-card').forEach((card, i) => {
    card.style.animationDelay = `${i * 0.5}s`;
});

// ========================
// Parallax Effect on Hero Section
// ========================
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const heroContent = document.querySelector('.hero-content');
    const heroIllustration = document.querySelector('.hero-illustration');
    if (heroContent) heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
    if (heroIllustration) heroIllustration.style.transform = `translateY(${scrolled * 0.2}px)`;
});

// ========================
// Scroll to Top Button (Optional)
// ========================
const scrollToTopBtn = document.createElement('button');
scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
scrollToTopBtn.className = 'scroll-to-top';
scrollToTopBtn.style.cssText = `
    position: fixed; bottom: 30px; right: 30px; width: 50px; height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none; border-radius: 50%; color: white; font-size: 1.2rem;
    cursor: pointer; display: none; align-items: center; justify-content: center;
    z-index: 1000; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.5);
    transition: all 0.3s ease;
`;
document.body.appendChild(scrollToTopBtn);

window.addEventListener('scroll', () => {
    scrollToTopBtn.style.display = window.pageYOffset > 300 ? 'flex' : 'none';
});
scrollToTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// ========================
// Mobile Menu Close on Outside Click
// ========================
document.addEventListener('click', (e) => {
    const navCollapse = document.querySelector('.navbar-collapse');
    const navToggler = document.querySelector('.navbar-toggler');
    if (navCollapse && navCollapse.classList.contains('show')) {
        if (!navCollapse.contains(e.target) && !navToggler.contains(e.target)) {
            navCollapse.classList.remove('show');
        }
    }
});
