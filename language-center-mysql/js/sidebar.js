document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const toggle = document.querySelector('.sidebar-toggle');
    const STORAGE_KEY = 'lc.sidebar.collapsed';
    const MOBILE_WIDTH = 768;

    // Initialize sidebar state
    function initSidebar() {
        const isMobile = window.innerWidth <= MOBILE_WIDTH;
        if (isMobile) {
            body.classList.remove('sidebar-collapsed');
            body.classList.add('mobile');
        } else {
            if (localStorage.getItem(STORAGE_KEY) === '1') {
                body.classList.add('sidebar-collapsed');
            }
            body.classList.remove('mobile');
        }
    }

    // Toggle sidebar
    if (toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (body.classList.contains('mobile')) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem(STORAGE_KEY, body.classList.contains('sidebar-collapsed') ? '1' : '0');
            }
            // Add rotation animation
            toggle.classList.add('rotate');
            setTimeout(() => toggle.classList.remove('rotate'), 300);
        });
    }

    // Handle resize
    window.addEventListener('resize', function() {
        initSidebar();
    });

    // Initial setup
    initSidebar();

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (body.classList.contains('mobile') && 
            body.classList.contains('sidebar-open') && 
            !e.target.closest('.sidebar') && 
            !e.target.closest('.sidebar-toggle')) {
            body.classList.remove('sidebar-open');
        }
    });
});