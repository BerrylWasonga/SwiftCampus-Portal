
// ========== DESKTOP SIDEBAR COLLAPSE TOGGLE ==========
const toggleSidebarBtn = document.getElementById('toggleSidebarDesktop');
const sidebarDesktop = document.querySelector('.sidebar-desktop');

if (toggleSidebarBtn && sidebarDesktop) {
    // Load collapsed state from localStorage
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        document.body.classList.add('sidebar-collapsed');
        sidebarDesktop.classList.add('sidebar-collapsed');
    }

    toggleSidebarBtn.addEventListener('click', function() {
        // Toggle on both body and sidebar
        document.body.classList.toggle('sidebar-collapsed');
        sidebarDesktop.classList.toggle('sidebar-collapsed');
        
        // Save state to localStorage
        const collapsed = document.body.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', collapsed);
    });
}

// ========== MOBILE MENU TOGGLE ==========
const toggleMobileBtn = document.getElementById('toggleMobileMenu');
const mobileMenu = document.getElementById('mobileMenu');
const mainContent = document.getElementById('mainContent');
const footer = document.getElementById('footer');

if (toggleMobileBtn) {
    toggleMobileBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
        
        // On tablet/landscape, shift content; on small portrait phones, content goes below
        if (window.innerWidth >= 576) {
            mainContent.classList.toggle('shifted');
            footer.classList.toggle('shifted');
        }
    });
}

// Handle window resize - hide menu if window becomes large
window.addEventListener('resize', function() {
    if (window.innerWidth >= 992) {
        mobileMenu.classList.add('hidden');
        mainContent.classList.remove('shifted');
        footer.classList.remove('shifted');
    }
});

// Optional: Close mobile menu when clicking a link on very small screens
const mobileMenuLinks = mobileMenu.querySelectorAll('.nav-link');
mobileMenuLinks.forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth < 576) {
            mobileMenu.classList.add('hidden');
        }
    });
});