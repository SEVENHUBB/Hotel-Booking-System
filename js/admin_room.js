const ROOM_API = "php/room_crud.php";

function showRoomMessage(text, type="success") {
    const msg = document.getElementById("roomMessage");
    msg.innerHTML = text;
    msg.className = "msg " + type;
    setTimeout(() => msg.innerHTML = "", 5000);
}

// 加载已有酒店到下拉列表
function loadHotelOptions() {
    fetch(`${ROOM_API}?action=hotels`)
        .then(res => res.json())
        .then(data => {
            const select = document.querySelector('select[name="HotelID"]');
            select.innerHTML = '<option value="">Select Hotel</option>';
            data.forEach(h => {
                select.innerHTML += `<option value="${h.HotelID}">${h.HotelName}</option>`;
            });
        })
        .catch(err => console.error("Failed to load hotels:", err));
}

// 加载房间列表
function loadRooms() {
    fetch(`${ROOM_API}?action=read`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector("#roomTable tbody");
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:#999">No rooms found.</td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(r => `
                <tr>
                    <td>${r.RoomID}</td>
                    <td>${r.HotelName}</td>
                    <td>${r.RoomType}</td>
                    <td>${r.RoomPrice}</td>
                    <td>${r.RoomDesc || '-'}</td>
                    <td>${r.RoomStatus}</td>
                    <td>${r.Capacity}</td>
                    <td><span class="delete-btn" style="color:red; cursor:pointer;" data-id="${r.RoomID}">Delete</span></td>
                </tr>
            `).join("");

            document.querySelectorAll(".delete-btn").forEach(btn => {
                btn.onclick = () => deleteRoom(btn.dataset.id);
            });
        })
        .catch(err => console.error("Failed to load rooms:", err));
}

// 提交新增房间表单
document.getElementById("roomForm").addEventListener("submit", function(e){
    e.preventDefault();
    const formData = new FormData(this);

    fetch(`${ROOM_API}?action=create`, { method: "POST", body: formData })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                showRoomMessage("Room added successfully!");
                this.reset();
                loadRooms();
            } else {
                showRoomMessage("Error: " + (result.error || "Could not add room"), "error");
            }
        })
        .catch(err => {
            showRoomMessage("Request failed. Check console.", "error");
            console.error(err);
        });
});

// 删除房间
function deleteRoom(id) {
    if (!confirm("Delete this room?")) return;
    const fd = new FormData();
    fd.append("RoomID", id);

    fetch(`${ROOM_API}?action=delete`, { method: "POST", body: fd })
        .then(res => res.json())
        .then(result => {
            if(result.success) {
                showRoomMessage("Room deleted successfully");
                loadRooms();
            } else {
                showRoomMessage("Delete failed", "error");
            }
        })
        .catch(err => {
            showRoomMessage("Delete failed", "error");
            console.error(err);
        });
}

// 初始化
document.addEventListener("DOMContentLoaded", () => {
    loadHotelOptions();
    loadRooms();
});
