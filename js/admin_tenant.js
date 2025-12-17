const API_URL = "php/admin_tenant.php";

function showMessage(text, type = "success") {
    const msgDiv = document.getElementById("message");
    msgDiv.innerHTML = text;
    msgDiv.className = "msg " + type;
    setTimeout(() => msgDiv.innerHTML = "", 5000);
}

function loadTenants() {
    fetch(`${API_URL}?action=read`)
        .then(res => {
            if (!res.ok) throw new Error("Server error");
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#tenantTable tbody");
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:20px; color:#999;">
                    No tenants found. Add one above!
                </td></tr>`;
                return;
            }

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
                    <td data-label="Role ID">${t.RoleID}</td>
                    <td data-label="Action">
                        <span class="delete-btn" style="color:red; cursor:pointer;" data-id="${t.TenantID}">Delete</span>
                    </td>
                </tr>
            `).join("");

            document.querySelectorAll(".delete-btn").forEach(btn => {
                btn.onclick = () => deleteTenant(btn.dataset.id);
            });
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load tenants: " + err.message, "error");
        });
}

// 头像预览
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
            preview.innerHTML = `<img src="${ev.target.result}" alt="Avatar Preview" style="max-width:200px; border-radius:50%;">`;
        };
        reader.readAsDataURL(file);
    }
});

// 表单提交
document.getElementById("tenantForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(`${API_URL}?action=create`, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showMessage("Tenant added successfully!");
            this.reset();
            document.getElementById('imagePreview').innerHTML = '';
            loadTenants();
        } else {
            showMessage("Error: " + (result.error || "Could not add tenant"), "error");
        }
    })
    .catch(err => {
        showMessage("Request failed.", "error");
        console.error(err);
    });
});

function deleteTenant(id) {
    if (!confirm("Are you sure you want to delete this tenant?")) return;

    const formData = new FormData();
    formData.append("TenantID", id);

    fetch(`${API_URL}?action=delete`, { method: "POST", body: formData })
        .then(() => {
            showMessage("Tenant deleted successfully");
            loadTenants();
        })
        .catch(() => showMessage("Delete failed", "error"));
}

document.addEventListener("DOMContentLoaded", loadTenants);