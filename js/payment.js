document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const method = document.getElementById('paymentMethod').value;
    if (!method) {
        alert('Please select a payment method.');
        e.preventDefault();
    }
});