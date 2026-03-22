document.querySelectorAll('.delete-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!confirm("Are you sure you want to delete this student?")) {
            e.preventDefault();
        }
    });
});