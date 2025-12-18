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
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:40px; color:#999; font-size:1.1em;">
                    No tenant accounts found.
                </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(t => `
                <tr>
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
                    <td data-label="Action">
                        <span class="delete-btn" style="color:red; cursor:pointer;" data-id="${t.TenantID}">Delete</span>
                    </td>
                </tr>
            `).join("");

            // Attach delete handlers
            document.querySelectorAll(".delete-btn").forEach(btn => {
                btn.onclick = () => deleteTenant(btn.dataset.id);
            });
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load tenants: " + err.message, "error");
        });
}

function deleteTenant(id) {
    if (!confirm("Are you sure you want to permanently delete this tenant account?")) return;

    const formData = new FormData();
    formData.append("TenantID", id);

    fetch(`${API_URL}?action=delete`, { method: "POST", body: formData })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                showMessage("Tenant account deleted successfully");
                loadTenants();
            } else {
                showMessage("Delete failed", "error");
            }
        })
        .catch(() => showMessage("Delete request failed", "error"));
}

// Load tenants when page loads
document.addEventListener("DOMContentLoaded", loadTenants);