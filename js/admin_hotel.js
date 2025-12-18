const API_URL = "php/admin_hotel.php";

function showMessage(text, type = "success") {
    const msgDiv = document.getElementById("message");
    msgDiv.innerHTML = text;
    msgDiv.className = "msg " + type;
    setTimeout(() => msgDiv.innerHTML = "", 5000);
}

let editingHotelId = null;

function loadHotels() {
    fetch(`${API_URL}?action=read`)
        .then(res => {
            if (!res.ok) throw new Error("Server error");
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#hotelTable tbody");
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:60px; color:#999;">
                    No hotels found. Add one above!
                </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(h => `
                <tr>
                    <td data-label="ID">${h.HotelID}</td>
                    <td data-label="Image">
                        ${h.ImagePath 
                            ? `<img src="${h.ImagePath}" alt="${h.HotelName}" onerror="this.src='https://via.placeholder.com/100x70/eeeeee/999999?text=No+Image'">`
                            : '<small style="color:#999">No image</small>'
                        }
                    </td>
                    <td data-label="Name"><strong>${h.HotelName}</strong></td>
                    <td data-label="City">${h.City}</td>
                    <td data-label="Country">${h.Country}</td>
                    <td data-label="Rooms">${h.NumRooms || "-"}</td>
                    <td data-label="Rating">${"★".repeat(h.StarRating || 0)}${"☆".repeat(5 - (h.StarRating || 0))}</td>
                    <td data-label="Action" style="text-align:center;">
                        <button class="action-btn edit-btn" onclick="editHotel(${h.HotelID})">
                            Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteHotel(${h.HotelID})">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join("");
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load hotels", "error");
        });
}

function editHotel(id) {
    fetch(`${API_URL}?action=read`)
        .then(res => res.json())
        .then(data => {
            const hotel = data.find(h => h.HotelID == id);
            if (!hotel) return showMessage("Hotel not found", "error");

            editingHotelId = id;

            // Change form to edit mode
            document.getElementById('formTitle').textContent = "Edit Hotel";
            document.getElementById('submitBtn').textContent = "Update Hotel";
            document.getElementById('cancelBtn').style.display = "inline-block";
            document.getElementById('hotelIdInput').readOnly = true; // Prevent changing ID
            document.getElementById('imageLabel').textContent = "Change Hotel Image (optional)";

            // Fill form
            document.getElementById('editHotelID').value = hotel.HotelID;
            document.getElementById('hotelIdInput').value = hotel.HotelID;
            document.querySelector('input[name="HotelName"]').value = hotel.HotelName || '';
            document.querySelector('textarea[name="Description"]').value = hotel.Description || '';
            document.querySelector('input[name="Address"]').value = hotel.Address || '';
            document.querySelector('input[name="City"]').value = hotel.City || '';
            document.querySelector('input[name="Country"]').value = hotel.Country || '';
            document.querySelector('input[name="NumRooms"]').value = hotel.NumRooms || '';
            document.querySelector('input[name="Category"]').value = hotel.Category || '';
            document.querySelector('input[name="StarRating"]').value = hotel.StarRating || '';

            // Show current image
            const currentDiv = document.getElementById('currentImage');
            if (hotel.ImagePath) {
                currentDiv.innerHTML = `<img src="${hotel.ImagePath}" alt="Current" style="max-width:400px; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">`;
            } else {
                currentDiv.innerHTML = '<small style="color:#999;">No current image</small>';
            }

            document.getElementById('imagePreview').innerHTML = '';
            document.querySelector('input[name="hotel_image"]').value = '';

            // Scroll to form
            document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
        });
}

function cancelEdit() {
    editingHotelId = null;
    document.getElementById('formTitle').textContent = "Add New Hotel";
    document.getElementById('submitBtn').textContent = "Add Hotel";
    document.getElementById('cancelBtn').style.display = "none";
    document.getElementById('hotelIdInput').readOnly = false;
    document.getElementById('imageLabel').textContent = "Hotel Image (optional)";
    document.getElementById('currentImage').innerHTML = '';
    document.getElementById('hotelForm').reset();
    document.getElementById('imagePreview').innerHTML = '';
}

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

// Image preview
document.querySelector('input[name="hotel_image"]').addEventListener('change', function(e) {
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
            preview.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="max-width:400px; border-radius:8px;">`;
        };
        reader.readAsDataURL(file);
    }
});

// Form submit - handles both add and update
document.getElementById("hotelForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    const url = editingHotelId ? `${API_URL}?action=update` : `${API_URL}?action=create`;

    fetch(url, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showMessage(editingHotelId ? "Hotel updated successfully!" : "Hotel added successfully!");
            cancelEdit();
            loadHotels();
        } else {
            showMessage("Error: " + (result.error || "Operation failed"), "error");
        }
    })
    .catch(err => {
        showMessage("Request failed.", "error");
        console.error(err);
    });
});

document.getElementById("cancelBtn").addEventListener("click", cancelEdit);

document.addEventListener("DOMContentLoaded", loadHotels);