const API_URL = "php/admin_hotel.php";

function showMessage(text, type = "success") {
    const msgDiv = document.getElementById("message");
    msgDiv.innerHTML = text;
    msgDiv.className = "msg " + type;
    setTimeout(() => msgDiv.innerHTML = "", 5000);
}

function loadHotels() {
    fetch(`${API_URL}?action=read`)
        .then(res => {
            if (!res.ok) throw new Error("Server error: " + res.status);
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#hotelTable tbody");
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:20px; color:#999;">
                    No hotels found. Add one above!
                </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(h => `
                <tr>
                    <td data-label="ID">${h.HotelID}</td>
                    <td data-label="Name">${h.HotelName}</td>
                    <td data-label="City">${h.City}</td>
                    <td data-label="Country">${h.Country}</td>
                    <td data-label="Rooms">${h.NumRooms || "-"}</td>
                    <td data-label="Rating">${"★".repeat(h.StarRating || 0)}${"☆".repeat(5 - (h.StarRating || 0))}</td>
                    <td data-label="Action">
                        <span class="delete-btn" data-id="${h.HotelID}">Delete</span>
                    </td>
                </tr>
            `).join("");

            // Attach delete events safely
            document.querySelectorAll(".delete-btn").forEach(btn => {
                btn.onclick = () => deleteHotel(btn.dataset.id);
            });
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load hotels: " + err.message, "error");
        });
}

document.getElementById("hotelForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(`${API_URL}?action=create`, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showMessage("Hotel added successfully!");
            this.reset();
            loadHotels();
        } else {
            showMessage("Error: " + (result.error || "Could not add hotel"), "error");
        }
    })
    .catch(err => {
        showMessage("Request failed. Check console.", "error");
        console.error(err);
    });
});

function deleteHotel(id) {
    if (!confirm("Are you sure you want to delete this hotel?")) return;

    const formData = new FormData();
    formData.append("HotelID", id);

    fetch(`${API_URL}?action=delete`, { method: "POST", body: formData })
        .then(() => {
            showMessage("Hotel deleted successfully");
            loadHotels();
        })
        .catch(() => showMessage("Delete failed", "error"));
}

// Load hotels when page opens
document.addEventListener("DOMContentLoaded", loadHotels);