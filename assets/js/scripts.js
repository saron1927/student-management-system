// assets/js/scripts.js
document.addEventListener('DOMContentLoaded', function() {
    // Basic interaction script
    console.log('UniTrack SMS Initialized');
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.badge-danger, .badge-success');
    alerts.forEach(alert => {
        setTimeout(() => {
            // alert.style.opacity = '0';
        }, 5000);
    });
});
