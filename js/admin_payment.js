const API_URL = "php/admin_payment.php";

function showMessage(text, type = "info") {
    const msgDiv = document.getElementById("message");
    msgDiv.innerHTML = text;
    msgDiv.className = "msg " + type;
    setTimeout(() => msgDiv.innerHTML = "", 6000);
}

function loadPayments() {
    fetch(`${API_URL}?action=read`)
        .then(res => {
            if (!res.ok) throw new Error("Server error");
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#paymentTable tbody");
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#999;">
                    No payments recorded.
                </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(p => `
                <tr>
                    <td data-label="Payment ID">${p.PaymentID}</td>
                    <td data-label="Booking ID">${p.BookingID || "-"}</td>
                    <td data-label="Tenant">${p.TenantName || "Unknown"}</td>
                    <td data-label="Amount">$${parseFloat(p.Amount || 0).toFixed(2)}</td>
                    <td data-label="Method">${p.PaymentMethod || "-"}</td>
                    <td data-label="Status">
                        <span style="padding:4px 8px; border-radius:4px; font-size:0.9em; 
                            background:${p.PaymentStatus === 'Paid' ? '#d4edda' : '#f8d7da'};
                            color:${p.PaymentStatus === 'Paid' ? '#155724' : '#721c24'};">
                            ${p.PaymentStatus || "Pending"}
                        </span>
                    </td>
                    <td data-label="Stay Period">
                        ${p.CheckInDate || "?"} → ${p.CheckOutDate || "?"}
                    </td>
                </tr>
            `).join("");
        })
        .catch(err => {
            console.error(err);
            showMessage("Failed to load payments: " + err.message, "error");
        });
}

document.addEventListener("DOMContentLoaded", loadPayments);