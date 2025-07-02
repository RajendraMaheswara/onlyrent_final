document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.add('active');
    document.querySelector('.mobile-overlay').classList.add('active');
});

// Close Sidebar
document.querySelector('.mobile-overlay').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('active');
    this.classList.remove('active');
});

// Toggle Mobile Search
document.querySelector('.search-toggle').addEventListener('click', function() {
    document.querySelector('.mobile-search-box').classList.toggle('d-none');
});

// Close sidebar when clicking on nav links (for mobile)
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('active');
        document.querySelector('.mobile-overlay').classList.remove('active');
    });
});

// Responsive adjustments
function handleResponsive() {
    const screenWidth = window.innerWidth;
    const userDropdown = document.querySelector('.dropdown .btn');
    
    if (screenWidth < 576) {
        // Hide button text on extra small screens
        document.querySelectorAll('.btn-text').forEach(text => {
            text.classList.add('d-none');
        });
    } else {
        // Show button text on larger screens
        document.querySelectorAll('.btn-text').forEach(text => {
            text.classList.remove('d-none');
        });
    }
}

// Run on load and resize
window.addEventListener('load', handleResponsive);
window.addEventListener('resize', handleResponsive);