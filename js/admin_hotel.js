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
        <td data-label="Image">
            ${h.ImagePath 
                ? `<img src="${h.ImagePath}" alt="${h.HotelName}" onerror="this.src='https://via.placeholder.com/70x50/eeeeee/999999?text=No+Image'">`
                : '<small style="color:#999">No image</small>'
            }
        </td>
        <td data-label="Name"><strong>${h.HotelName}</strong></td>
        <td data-label="City">${h.City}</td>
        <td data-label="Country">${h.Country}</td>
        <td data-label="Rooms">${h.NumRooms || "-"}</td>
        <td data-label="Rating">${"★".repeat(h.StarRating || 0)}${"☆".repeat(5 - (h.StarRating || 0))}</td>
        <td data-label="Action">
            <span class="delete-btn" style="color:red; cursor:pointer;" data-id="${h.HotelID}">Delete</span>
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

// Image Preview (small size)
document.querySelector('input[name="hotel_image"]').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        
        // Optional: limit file size (e.g. max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            preview.innerHTML = '<small style="color:red;">Image too large! Max 5MB</small>';
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.innerHTML = `<img src="${ev.target.result}" alt="Hotel Preview">`;
        };
        reader.readAsDataURL(file);
    }
});

// Updated form submit with file support
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
            document.getElementById('imagePreview').innerHTML = '';
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