document.addEventListener('DOMContentLoaded', function() {

    // 1. DELETE CONFIRMATION
    const deleteLinks = document.querySelectorAll('.delete-link');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm("⚠️ Are you sure you want to delete this student?")) {
                e.preventDefault();
            }
        });
    });

    // 2. FIXED AUTO-HIDE LOGIC
    // This looks for any <p> tag that is used for messages
    const messages = document.querySelectorAll('.container p');
    
    messages.forEach(msg => {
        // Only target paragraphs that look like our status messages (green, red, or blue)
        if (msg.style.backgroundColor || msg.style.border) {
            
            // Set the transition property via JS to ensure it exists
            msg.style.transition = "opacity 0.8s ease, transform 0.8s ease, margin-top 0.8s ease";

            setTimeout(() => {
                msg.style.opacity = "0";
                msg.style.transform = "translateY(-20px)";
                msg.style.marginTop = "-40px"; // This slides the table up smoothly
                
                // Remove from DOM after animation finishes
                setTimeout(() => {
                    msg.remove();
                }, 800);
            }, 3000); // 3 seconds delay
        }
    });
});