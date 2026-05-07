/* ========================
   CAMPUS CONNECT UNIVERSAL JAVASCRIPT
   For: Admin, HOD, Teacher, Student Modules
   ======================== */

// ========================
// Initialize on DOM Load
// ========================
document.addEventListener('DOMContentLoaded', function() {
    initializeParticles();
    initializeSidebar();
    initializeHeader();
    initializeThemeToggle();
    initializeAnimations();
    initializeDropdowns();
    initializeTables();
    initializeForms();
    initializeTabs();
    initializeTooltips();
    initializeFileUpload();
    initializeCharts();
});

// ========================
// Particle Background Animation
// ========================
function initializeParticles() {
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles-background', {
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
                    type: ['circle', 'triangle'],
                },
                opacity: {
                    value: 0.3,
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
    }
}

// ========================
// Sidebar Management
// ========================
function initializeSidebar() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    
    // Mobile menu toggle
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            this.innerHTML = sidebar.classList.contains('active') 
                ? '<i class="fas fa-times"></i>' 
                : '<i class="fas fa-bars"></i>';
        });

        // Close sidebar on link click (mobile)
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('active');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        });

        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            }
        });
    }

    // Active link highlighting
    const currentPath = window.location.pathname;
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.classList.add('active');
        }
    });

    // Smooth link hover effects
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        });
    });
}

// ========================
// Header Scroll Effects
// ========================
function initializeHeader() {
    const header = document.querySelector('.main-header');
    let lastScroll = 0;

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });

    // User profile dropdown
    const userProfile = document.querySelector('.user-profile');
    if (userProfile) {
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            this.parentElement.classList.toggle('active');
        });
    }
}

// ========================
// Theme Toggle (Dark/Light Mode)
// ========================
function initializeThemeToggle() {
    const themeToggle = document.querySelector('.theme-toggle-btn');
    const body = document.body;

    // Check for saved theme
    const currentTheme = localStorage.getItem('campusConnectTheme') || 'dark';
    if (currentTheme === 'light') {
        body.classList.add('light-mode');
        updateThemeIcon(themeToggle, true);
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            body.classList.toggle('light-mode');
            const isLight = body.classList.contains('light-mode');
            localStorage.setItem('campusConnectTheme', isLight ? 'light' : 'dark');
            updateThemeIcon(this, isLight);
            
            // Update particles color
            updateParticlesTheme(isLight);
        });
    }
}

function updateThemeIcon(button, isLight) {
    const icon = button.querySelector('i');
    if (icon) {
        icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
    }
}

function updateParticlesTheme(isLight) {
    if (window.pJSDom && window.pJSDom[0]) {
        const color = isLight ? '#667eea' : '#667eea';
        window.pJSDom[0].pJS.particles.color.value = color;
        window.pJSDom[0].pJS.particles.line_linked.color = color;
        window.pJSDom[0].pJS.fn.particlesRefresh();
    }
}

// ========================
// Animation on Scroll (AOS Alternative)
// ========================
function initializeAnimations() {
    const animatedElements = document.querySelectorAll('.stat-card, .glass-card, .quick-action-btn');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    animatedElements.forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
}

// ========================
// Dropdown Menus
// ========================
function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        
        if (trigger) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close other dropdowns
                dropdowns.forEach(d => {
                    if (d !== dropdown) d.classList.remove('active');
                });
                
                dropdown.classList.toggle('active');
            });
        }
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    });
}

// ========================
// Table Enhancements
// ========================
function initializeTables() {
    const tables = document.querySelectorAll('.glass-table');

    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach((row, index) => {
            // Staggered fade-in animation
            row.style.opacity = '0';
            row.style.animation = `fadeInUp 0.4s ease forwards ${index * 0.05}s`;

            // Row click effect
            row.addEventListener('click', function() {
                this.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });

            // Ripple effect on row click
            row.addEventListener('mousedown', function(e) {
                createRipple(e, this);
            });
        });
    });

    // Table search functionality
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const tableId = this.getAttribute('data-table-search');
            const table = document.getElementById(tableId);
            filterTable(table, this.value);
        });
    });
}

function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        top: ${y}px;
        left: ${x}px;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        transform: scale(0);
        animation: rippleEffect 0.6s ease-out;
        pointer-events: none;
    `;

    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);

    setTimeout(() => ripple.remove(), 600);
}

// Add ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes rippleEffect {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ========================
// Form Enhancements
// ========================
function initializeForms() {
    const inputs = document.querySelectorAll('.form-control');

    inputs.forEach(input => {
        // Floating label effect
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
            this.parentElement.style.transition = 'all 0.3s ease';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = '';
        });

        // Input validation feedback
        input.addEventListener('input', function() {
            if (this.validity.valid) {
                this.style.borderColor = 'var(--success)';
            } else if (this.value.length > 0) {
                this.style.borderColor = 'var(--error)';
            } else {
                this.style.borderColor = 'var(--border-glass)';
            }
        });
    });

    // Form submission with loading state
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
            }
        });
    });
}

// ========================
// Tabs Functionality
// ========================
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabGroup = this.closest('[data-tabs]');
            const targetId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs
            tabGroup.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            tabGroup.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            const targetContent = tabGroup.querySelector(`#${targetId}`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
}

// ========================
// Tooltips
// ========================
function initializeTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');
    
    elements.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const text = this.getAttribute('data-tooltip');
            const tooltip = createTooltip(text);
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            
            setTimeout(() => tooltip.style.opacity = '1', 10);
            
            this.addEventListener('mouseleave', function() {
                tooltip.style.opacity = '0';
                setTimeout(() => tooltip.remove(), 300);
            }, { once: true });
        });
    });
}

function createTooltip(text) {
    const tooltip = document.createElement('div');
    tooltip.style.cssText = `
        position: fixed;
        background: var(--card-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--border-glass);
        color: var(--text-primary);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        z-index: 10000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        box-shadow: var(--shadow-md);
    `;
    tooltip.textContent = text;
    return tooltip;
}

// ========================
// File Upload Drag & Drop
// ========================
function initializeFileUpload() {
    const uploadAreas = document.querySelectorAll('.upload-area');
    
    uploadAreas.forEach(area => {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            area.addEventListener(eventName, () => {
                area.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, () => {
                area.classList.remove('dragover');
            }, false);
        });

        area.addEventListener('drop', handleDrop, false);
    });
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDrop(e) {
    const files = e.dataTransfer.files;
    handleFiles(files);
}

function handleFiles(files) {
    [...files].forEach(file => {
        console.log('File uploaded:', file.name);
        showToast(`File "${file.name}" uploaded successfully!`, 'success');
    });
}

// ========================
// Toast Notifications
// ========================
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    const icon = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    }[type];
    
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.4s ease forwards';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// Add slide out animation
const toastStyle = document.createElement('style');
toastStyle.textContent = `
    @keyframes slideOutRight {
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(toastStyle);

// ========================
// Counter Animation (for stat cards)
// ========================
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

// Observe stat cards and animate counters
const statValues = document.querySelectorAll('.stat-card-value');
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const target = parseInt(entry.target.textContent);
            entry.target.textContent = '0';
            animateCounter(entry.target, target);
            counterObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

statValues.forEach(value => counterObserver.observe(value));

// ========================
// Charts Initialization (if Chart.js is loaded)
// ========================
function initializeCharts() {
    if (typeof Chart !== 'undefined') {
        const chartElements = document.querySelectorAll('[data-chart]');
        
        chartElements.forEach(canvas => {
            const type = canvas.getAttribute('data-chart');
            // Chart initialization would go here based on data attributes
            // This is a placeholder for actual chart implementation
        });
    }
}

// ========================
// Modal Management
// ========================
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => modal.style.opacity = '1', 10);
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

// Close modal on outside click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.opacity = '0';
        setTimeout(() => e.target.style.display = 'none', 300);
    }
});

// ========================
// Search Functionality
// ========================
const headerSearch = document.querySelector('.header-search input');
if (headerSearch) {
    headerSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        // Implement global search logic here
        console.log('Searching for:', searchTerm);
    });
}

// ========================
// Page Load Animation
// ========================
window.addEventListener('load', function() {
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease';
        document.body.style.opacity = '1';
    }, 100);
});

// ========================
// Utility Functions
// ========================

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    });
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ========================
// Export functions for global use
// ========================
window.CampusConnect = {
    showToast,
    showModal,
    hideModal,
    copyToClipboard,
    confirmAction,
    formatDate,
    animateCounter
};

// ========================
// Console Welcome Message
// ========================
console.log('%c🎓 Campus Connect Dashboard', 'color: #667eea; font-size: 20px; font-weight: bold;');
console.log('%c✨ Universal Theme Loaded Successfully', 'color: #764ba2; font-size: 14px;');
console.log('%cConnecting Campus, Empowering Voices', 'color: #f093fb; font-size: 12px; font-style: italic;');