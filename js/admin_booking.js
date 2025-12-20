const API_URL = "php/admin_booking.php";

function showMessage(text, type = "info") {
    const msgDiv = document.getElementById("message");
    msgDiv.innerHTML = text;
    msgDiv.className = "msg " + type;
    setTimeout(() => msgDiv.innerHTML = "", 6000);
}

function deleteBooking(bookingID) {
    if (!confirm("Are you sure you want to delete this booking? This action cannot be undone.")) {
        return;
    }

    fetch(`${API_URL}?action=delete`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ bookingID: bookingID })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || "Booking deleted successfully", "info");
            loadBookings(); // Refresh the list
        } else {
            showMessage(data.message || "Failed to delete booking", "error");
        }
    })
    .catch(err => {
        console.error(err);
        showMessage("Network error while deleting", "error");
    });
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
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#999;">
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
                    <td data-label="Actions">
                        <button class="delete-btn" onclick="deleteBooking(${b.BookingID})">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join("");
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load bookings: " + err.message, "error");
        });
}

document.addEventListener("DOMContentLoaded", loadBookings);