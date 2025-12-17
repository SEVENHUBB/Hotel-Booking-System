const API_URL = "php/admin_booking.php";

function showMessage(text, type = "info") {
    const msgDiv = document.getElementById("message");
    msgDiv.innerHTML = text;
    msgDiv.className = "msg " + type;
    setTimeout(() => msgDiv.innerHTML = "", 6000);
}

function loadBookings() {
    fetch(`${API_URL}?action=read`)
        .then(res => {
            if (!res.ok) throw new Error("Server error");
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#bookingTable tbody");
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:30px; color:#999;">
                    No bookings found.
                </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(b => `
                <tr>
                    <td data-label="Booking ID">${b.BookingID}</td>
                    <td data-label="Room ID">${b.RoomID || "-"}</td>
                    <td data-label="Tenant">${b.TenantName || "Unknown"}<br><small>${b.TenantEmail || "-"}</small></td>
                    <td data-label="Check-in">${b.CheckInDate || "-"}</td>
                    <td data-label="Check-out">${b.CheckOutDate || "-"}</td>
                    <td data-label="Guests">${b.NumberOfTenant || "-"}</td>
                    <td data-label="Booking Date">${new Date(b.BookingDate).toLocaleString()}</td>
                </tr>
            `).join("");
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load bookings: " + err.message, "error");
        });
}

document.addEventListener("DOMContentLoaded", loadBookings);