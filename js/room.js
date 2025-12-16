let allRooms = [];

// Load rooms on page load
document.addEventListener('DOMContentLoaded', function () {
    loadRooms();
});

// Show alert message
function showAlert(message, type) {
    const alert = document.getElementById('alert');
    alert.textContent = message;
    alert.className = 'alert alert-' + type + ' show';

    setTimeout(() => {
        alert.classList.remove('show');
    }, 5000);
}

// Show add form
function showAddForm() {
    document.getElementById('formTitle').textContent = 'Add New Room';
    document.getElementById('roomFormElement').reset();
    document.getElementById('roomId').value = '';
    document.getElementById('roomForm').classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Hide form
function hideForm() {
    document.getElementById('roomForm').classList.remove('active');
    document.getElementById('roomFormElement').reset();
}

// Load all rooms
function loadRooms() {
    const formData = new FormData();
    formData.append('action', 'read');

    fetch('room_operations.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRooms = data.data;
                displayRooms(allRooms);
            } else {
                showAlert(data.message, 'error');
                displayEmptyState();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading rooms', 'error');
            displayEmptyState();
        });
}

// Display rooms in table
function displayRooms(rooms) {
    const tbody = document.getElementById('roomsTableBody');

    if (rooms.length === 0) {
        displayEmptyState();
        return;
    }

    tbody.innerHTML = rooms.map(room => `
        <tr>
            <td><strong>${room.RoomID}</strong></td>

            <td class="room-image-cell">
                ${room.RoomImage ?
                    `<img src="${room.ImagePath}" alt="${room.RoomType}">` :
                    '<div class="no-image">No Image</div>'
                }
            </td>

            <td><strong>${room.RoomType}</strong></td>
            <td>RM ${parseFloat(room.RoomPrice).toFixed(2)}</td>
            <td><span class="status-badge status-${room.RoomStatus.toLowerCase()}">${room.RoomStatus}</span></td>
            <td>${room.RoomDesc || 'N/A'}</td>

            <td>${room.Capacity}</td>
            <td>
                <button onclick = "editRoom(${room.RoomID})">Edit</button>
                <button onclick = "deleteRoom(${room.RoomID})">Delete</button>
            </td>
        </tr>
    `).join('');

    // if (room.ImagePath) {
    //     document.getElementById('currentImg').src = room.ImagePath;
    //     document.getElementById('currentImage').style.display = 'block';
    // } else {
    //     document.getElementById('currentImage').style.display = 'none';
    // }
}

// new
function previewImage(event) {
    const file = event.target.files[0];
    const previewDiv = document.getElementById('imagePreview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewDiv.innerHTML = `
                <img src="${e.target.result}" alt="Preview" style="width:100%; border-radius:8px; border:2px solid #e5e7eb;">
                <button type="button" onclick="removeImagePreview()" style="margin-top:10px; padding:5px 15px; background:#ef4444; color:white; border:none; border-radius:5px; cursor:pointer;">
                    ✖ Remove Image
                </button>
            `;
            previewDiv.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

function removeImagePreview() {
    document.getElementById('roomImage').value = '';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('imagePreview').style.display = 'none';
}

// Display empty state
function displayEmptyState() {
    const tbody = document.getElementById('roomsTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <div class="empty-state-icon">🏨</div>
                    <h3>No rooms found</h3>
                    <p>Start by adding your first room using the "Add New Room" button above.</p>
                </div>
            </td>
        </tr>
    `;
}

// Save room (create or update)
function saveRoom(event) {
    event.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const roomId = document.getElementById('roomId').value;
    const formData = new FormData();

    formData.append('action', roomId ? 'update' : 'create');
    if (roomId) formData.append('roomId', roomId);

    // if not add this files always be null
    const imageInput = document.getElementById('roomImage');
    if (imageInput.files.length > 0) {
        formData.append('roomImage', imageInput.files[0]);
    }
    
    // formData.append('hotelId', document.getElementById('hotelId').value);
    // formData.append('tenantId', document.getElementById('tenantId').value);
    formData.append('roomType', document.getElementById('roomType').value);
    formData.append('roomPrice', document.getElementById('roomPrice').value);
    formData.append('roomDesc', document.getElementById('roomDesc').value);
    formData.append('roomStatus', document.getElementById('roomStatus').value);
    formData.append('capacity', document.getElementById('capacity').value);

    fetch('room_operations.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = '💾 Save Room';

            if (data.success) {
                showAlert(data.message, 'success');
                hideForm();
                loadRooms();
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.textContent = '💾 Save Room';
            console.error('Error:', error);
            showAlert('Error saving room', 'error');
        });
}

// Edit room
function editRoom(roomId) {
    const formData = new FormData();
    formData.append('action', 'get_single');
    formData.append('id', roomId);

    fetch('room_operations.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const room = data.data;

                document.getElementById('formTitle').textContent = 'Edit Room';
                document.getElementById('roomId').value = room.RoomID;
                document.getElementById('hotelId').value = room.HotelID;
                document.getElementById('tenantId').value = room.TenantID || '';
                document.getElementById('roomType').value = room.RoomType;
                document.getElementById('roomPrice').value = room.RoomPrice;
                document.getElementById('roomDesc').value = room.RoomDesc || '';
                document.getElementById('roomStatus').value = room.RoomStatus;
                document.getElementById('capacity').value = room.Capacity;

                document.getElementById('roomForm').classList.add('active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                showAlert('Error loading room data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading room data', 'error');
        });
}

// Delete room
function deleteRoom(roomId) {
    if (!confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', roomId);

    fetch('room_operations.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadRooms();
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting room', 'error');
        });
}

// Filter rooms
function filterRooms() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();

    const filteredRooms = allRooms.filter(room => {
        return room.RoomType.toLowerCase().includes(searchTerm) ||
            room.RoomStatus.toLowerCase().includes(searchTerm) ||
            room.RoomPrice.toString().includes(searchTerm) ||
            room.RoomID.toString().includes(searchTerm) ||
            (room.RoomDesc && room.RoomDesc.toLowerCase().includes(searchTerm));
    });

    displayRooms(filteredRooms);
}
