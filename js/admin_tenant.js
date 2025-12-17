document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('tenantForm');
    const tableBody = document.querySelector('#tenantTable tbody');
    const messageDiv = document.getElementById('message');

    // Load tenants
    function loadTenants() {
        fetch('admin_tenant.php?action=read')
            .then(res => res.json())
            .then(tenants => {
                tableBody.innerHTML = '';
                if (tenants.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="9">No tenants found.</td></tr>';
                    return;
                }

                tenants.forEach(t => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${t.TenantID}</td>
                        <td>${t.RoleID || '-'}</td>
                        <td>${t.TenantName || '-'}</td>
                        <td>${t.FullName || '-'}</td>
                        <td>${t.Email || '-'}</td>
                        <td>${t.PhoneNo || '-'}</td>
                        <td>${t.Gender || '-'}</td>
                        <td>${t.Country || '-'}</td>
                        <td>
                            <button class="delete-btn" data-id="${t.TenantID}">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });

                // Delete handlers
                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.onclick = function() {
                        if (confirm('Delete this tenant?')) {
                            const fd = new FormData();
                            fd.append('TenantID', this.dataset.id);
                            fetch('admin_tenant.php?action=delete', { method: 'POST', body: fd })
                                .then(res => res.json())
                                .then(() => {
                                    showMessage('Tenant deleted!', 'success');
                                    loadTenants();
                                });
                        }
                    };
                });
            });
    }

    // Add tenant
    form.onsubmit = function(e) {
        e.preventDefault();
        const fd = new FormData(this);

        fetch('admin_tenant.php?action=create', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                showMessage(data.message || 'Success!', data.success ? 'success' : 'error');
                if (data.success) {
                    form.reset();
                    loadTenants();
                }
            });
    };

    function showMessage(text, type) {
        messageDiv.innerHTML = `<div class="alert ${type}">${text}</div>`;
        setTimeout(() => messageDiv.innerHTML = '', 4000);
    }

    loadTenants();
});