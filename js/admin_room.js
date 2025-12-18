const ROOM_API = "php/room_crud.php";

function showRoomMessage(text, type = "success") {
    const msg = document.getElementById("roomMessage");
    msg.innerHTML = text;
    msg.className = "msg " + type;
    setTimeout(() => msg.innerHTML = "", 5000);
}

let editingRoomId = null;

// Load hotels for dropdown
function loadHotelOptions() {
    fetch(`${ROOM_API}?action=hotels`)
        .then(res => res.json())
        .then(data => {
            const select = document.querySelector('select[name="HotelID"]');
            select.innerHTML = '<option value="">Select Hotel</option>';
            data.forEach(h => {
                select.innerHTML += `<option value="${h.HotelID}">${h.HotelName}</option>`;
            });
        });
}

// Load rooms list
function loadRooms() {
    fetch(`${ROOM_API}?action=read`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector("#roomTable tbody");
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:60px; color:#999;">
                    No rooms found.
                </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(r => `
                <tr>
                    <td>${r.RoomID}</td>
                    <td>${r.HotelName}</td>
                    <td>${r.RoomImage ? `<img src="${r.RoomImage}" alt="Room">` : '<small>No image</small>'}</td>
                    <td>${r.RoomType}</td>
                    <td>$${Number(r.RoomPrice).toFixed(2)}</td>
                    <td>${r.RoomDesc || '-'}</td>
                    <td><span class="status-badge status-${r.RoomStatus.toLowerCase()}">${r.RoomStatus}</span></td>
                    <td>${r.Capacity}</td>
                    <td>${r.RoomQuantity}</td>
                    <td style="text-align:center;">
                        <button class="action-btn edit-btn" onclick="editRoom(${r.RoomID})">Edit</button>
                        <button class="action-btn delete-btn" onclick="deleteRoom(${r.RoomID})">Delete</button>
                    </td>
                </tr>
            `).join("");
        });
}

function editRoom(id) {
    fetch(`${ROOM_API}?action=read`)
        .then(res => res.json())
        .then(data => {
            const room = data.find(r => r.RoomID == id);
            if (!room) return showRoomMessage("Room not found", "error");

            editingRoomId = id;

            document.getElementById('formTitle').textContent = "Edit Room";
            document.getElementById('submitBtn').textContent = "Update Room";
            document.getElementById('cancelBtn').style.display = "inline-block";
            document.getElementById('imageLabel').textContent = "Change Room Image (optional)";

            document.getElementById('editRoomID').value = room.RoomID;
            document.querySelector('select[name="HotelID"]').value = room.HotelID;
            document.querySelector('select[name="RoomType"]').value = room.RoomType;
            document.querySelector('input[name="RoomPrice"]').value = room.RoomPrice;
            document.querySelector('input[name="Capacity"]').value = room.Capacity;
            document.querySelector('input[name="RoomQuantity"]').value = room.RoomQuantity;
            document.querySelector('input[name="RoomDesc"]').value = room.RoomDesc || '';
            document.querySelector('select[name="RoomStatus"]').value = room.RoomStatus;

            const currentDiv = document.getElementById('currentImage');
            if (room.RoomImage) {
                currentDiv.innerHTML = `<img src="${room.RoomImage}" alt="Current" style="max-width:400px; border-radius:12px; box-shadow:0 8px 25px rgba(0,0,0,0.15);">`;
            } else {
                currentDiv.innerHTML = '<small style="color:#999;">No current image</small>';
            }

            document.getElementById('imagePreview').innerHTML = '';
            document.querySelector('input[name="room_image"]').value = '';

            document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
        });
}

function cancelEdit() {
    editingRoomId = null;
    document.getElementById('formTitle').textContent = "Add New Room";
    document.getElementById('submitBtn').textContent = "Add Room";
    document.getElementById('cancelBtn').style.display = "none";
    document.getElementById('imageLabel').textContent = "Room Image (optional)";
    document.getElementById('currentImage').innerHTML = '';
    document.getElementById('roomForm').reset();
    document.getElementById('imagePreview').innerHTML = '';
}

function deleteRoom(id) {
    if (!confirm("Are you sure you want to delete this room?")) return;

    const fd = new FormData();
    fd.append("RoomID", id);

    fetch(`${ROOM_API}?action=delete`, { method: "POST", body: fd })
        .then(() => {
            showRoomMessage("Room deleted successfully");
            loadRooms();
        })
        .catch(() => showRoomMessage("Delete failed", "error"));
}

// Image preview
document.querySelector('input[name="room_image"]').addEventListener('change', function(e) {
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
            preview.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="max-width:400px; border-radius:12px;">`;
        };
        reader.readAsDataURL(file);
    }
});

// Form submit (add or update)
document.getElementById("roomForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const url = editingRoomId ? `${ROOM_API}?action=update` : `${ROOM_API}?action=create`;

    fetch(url, { method: "POST", body: formData })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                showRoomMessage(editingRoomId ? "Room updated successfully!" : "Room added successfully!");
                cancelEdit();
                loadRooms();
            } else {
                showRoomMessage("Error: " + (result.error || "Operation failed"), "error");
            }
        })
        .catch(() => showRoomMessage("Request failed", "error"));
});

document.getElementById("cancelBtn").addEventListener("click", cancelEdit);

document.addEventListener("DOMContentLoaded", () => {
    loadHotelOptions();
    loadRooms();
});