document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const tenantId = this.getAttribute('data-id');
            const confirmDelete = confirm('Are you sure you want to delete this tenant? This action cannot be undone.');

            if (confirmDelete) {
                window.location.href = `admin_tenant.php?delete=${tenantId}`;
            }
        });
    });
});