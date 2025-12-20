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
                        ${t.profile_image 
                            ? `<img src="${t.profile_image}" alt="${t.FullName || t.TenantName}" onerror="this.src='https://via.placeholder.com/70x70/eeeeee/999999?text=No+Image'">`
                            : '<small style="color:#999">No avatar</small>'
                        }
                    </td>
                    <td data-label="Full Name"><strong>${t.FullName || "-"}</strong></td>
                    <td data-label="Username">${t.TenantName}</td>
                    <td data-label="Email">${t.Email || "-"}</td>
                    <td data-label="Phone">${t.PhoneNo || "-"}</td>
                    <td data-label="Action" style="text-align:center;">
                        <button class="action-btn delete-btn" onclick="deleteTenant(${t.TenantID})">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join("");
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load tenants", "error");
        });
}

function deleteTenant(id) {
    if (!confirm("Are you sure you want to permanently delete this tenant account?")) return;

    const formData = new FormData();
    formData.append("TenantID", id);
    formData.append("action", "delete");

    fetch(API_URL, {
        method: "POST",
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error("Server error");
        return res.json();
    })
    .then(result => {
        if (result.success) {
            showMessage("Tenant account deleted successfully!");
            loadTenants(); // Refreshes list instantly — perfect!
        } else {
            showMessage("Delete failed: " + (result.error || "Unknown error"), "error");
        }
    })
    .catch(err => {
        console.error(err);
        showMessage("Network/request error", "error");
    });
}

// Load tenants on page load
document.addEventListener("DOMContentLoaded", loadTenants);