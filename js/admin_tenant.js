const API_URL = "php/admin_tenant.php";

function showMessage(text, type = "success") {
    const msgDiv = document.getElementById("message");
    msgDiv.innerHTML = text;
    msgDiv.className = "msg " + type;
    setTimeout(() => msgDiv.innerHTML = "", 5000);
}

let editingTenantId = null;
const editCard = document.getElementById('editCard');
const tenantForm = document.getElementById('tenantForm');
const cancelBtn = document.getElementById('cancelBtn');

function loadTenants() {
    fetch(`${API_URL}?action=read`)
        .then(res => {
            if (!res.ok) throw new Error("Server error");
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#tenantTable tbody");
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:60px; color:#999; font-size:1.2em;">
                    No tenants found.
                </td></tr>`;
                return;
            }

            // IMPORTANT: Use BACKTICKS ` for template literals
            tbody.innerHTML = data.map(t => `
                <tr>
                    <td data-label="ID">${t.TenantID}</td>
                    <td data-label="Avatar">
                        ${t.ImagePath 
                            ? `<img src="${t.ImagePath}" alt="${t.FullName || t.TenantName}" onerror="this.src='https://via.placeholder.com/70x70/eeeeee/999999?text=No+Image'">`
                            : '<small style="color:#999">No avatar</small>'
                        }
                    </td>
                    <td data-label="Full Name"><strong>${t.FullName || "-"}</strong></td>
                    <td data-label="Username">${t.TenantName}</td>
                    <td data-label="Email">${t.Email || "-"}</td>
                    <td data-label="Phone">${t.PhoneNo || "-"}</td>
                    <td data-label="Gender">${t.Gender || "-"}</td>
                    <td data-label="Country">${t.Country || "-"}</td>
                    <td data-label="Role ID">${t.RoleID || "null"}</td>
                    <td data-label="Action" style="text-align:center;">
                        <button class="action-btn edit-btn" onclick="editTenant(${t.TenantID})">
                            Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteTenant(${t.TenantID})">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join("");

            // No need for extra event binding – onclick is directly on the buttons
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load tenants", "error");
        });
}

function editTenant(id) {
    fetch(`${API_URL}?action=read`)
        .then(res => res.json())
        .then(data => {
            const tenant = data.find(t => t.TenantID == id);
            if (!tenant) return showMessage("Tenant not found", "error");

            editingTenantId = id;
            editCard.style.display = "block";
            editCard.scrollIntoView({ behavior: 'smooth' });

            document.getElementById('editTenantID').value = tenant.TenantID;
            document.querySelector('input[name="RoleID"]').value = tenant.RoleID || '';
            document.querySelector('input[name="TenantName"]').value = tenant.TenantName || '';
            document.querySelector('input[name="FullName"]').value = tenant.FullName || '';
            document.querySelector('input[name="Email"]').value = tenant.Email || '';
            document.querySelector('input[name="PhoneNo"]').value = tenant.PhoneNo || '';
            document.querySelector('input[name="Gender"]').value = tenant.Gender || '';
            document.querySelector('input[name="Country"]').value = tenant.Country || '';

            const currentAvatarDiv = document.getElementById('currentAvatar');
            if (tenant.ImagePath) {
                currentAvatarDiv.innerHTML = `<img src="${tenant.ImagePath}" alt="Current Avatar" style="width:150px;height:150px;object-fit:cover;border-radius:50%;border:4px solid #ddd;">`;
            } else {
                currentAvatarDiv.innerHTML = '<small style="color:#999;">No current avatar</small>';
            }

            document.getElementById('imagePreview').innerHTML = '';
            document.querySelector('input[name="tenant_image"]').value = '';
        });
}

function cancelEdit() {
    editingTenantId = null;
    editCard.style.display = "none";
    tenantForm.reset();
    document.getElementById('currentAvatar').innerHTML = '';
    document.getElementById('imagePreview').innerHTML = '';
}

function deleteTenant(id) {
    if (!confirm("Are you sure you want to delete this tenant? This action cannot be undone.")) return;

    const formData = new FormData();
    formData.append("TenantID", id);

    fetch(`${API_URL}?action=delete`, { method: "POST", body: formData })
        .then(() => {
            showMessage("Tenant deleted successfully");
            loadTenants();
        })
        .catch(() => showMessage("Delete failed", "error"));
}

// Image preview for new avatar
document.querySelector('input[name="tenant_image"]').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        if (file.size > 5 * 1024 * 1024) {
            preview.innerHTML = '<small style="color:red;">Image too large! Max 5MB</small>';
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="max-width:300px;max-height:300px;border-radius:12px;">`;
        };
        reader.readAsDataURL(file);
    }
});

// Form submit (update)
tenantForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(`${API_URL}?action=update`, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showMessage("Tenant updated successfully!");
            cancelEdit();
            loadTenants();
        } else {
            showMessage("Error: " + (result.error || "Update failed"), "error");
        }
    })
    .catch(err => {
        showMessage("Request failed.", "error");
        console.error(err);
    });
});

cancelBtn.addEventListener("click", cancelEdit);

// Load tenants when page loads
document.addEventListener("DOMContentLoaded", loadTenants);