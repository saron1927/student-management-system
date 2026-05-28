// assets/js/scripts.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('UniTrack SMS Initialized - UI Dynamics Active');
    
    // Auto-hide alert badges after 5 seconds
    const alerts = document.querySelectorAll('.badge-danger, .badge-success, .badge-warning');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'scale(0.9)';
            setTimeout(() => {
                // If it's a block level alert message
                if(alert.parentElement.tagName.toLowerCase() === 'div' && alert.parentElement.style.padding) {
                   alert.parentElement.style.display = 'none';
                }
            }, 500);
        }, 5000);
    });

    // Add staggered fade-in animations to tables rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.animation = `fadeIn 0.4s ease-out forwards`;
        row.style.animationDelay = `${(index * 0.05) + 0.2}s`;
    });

    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            let x = e.clientX - e.target.getBoundingClientRect().left;
            let y = e.clientY - e.target.getBoundingClientRect().top;
            let ripples = document.createElement('span');
            
            ripples.style.left = x + 'px';
            ripples.style.top = y + 'px';
            ripples.classList.add('ripple-wave');
            this.appendChild(ripples);
            
            setTimeout(() => {
                ripples.remove()
            }, 600);
        });
    });

    // Handle header scroll effect for landing page
    const header = document.querySelector('.landing-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
});
