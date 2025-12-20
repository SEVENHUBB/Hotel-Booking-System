// Payment Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize payment page
    loadCartItems();
    initPaymentMethods();
    initCardValidation();
    initFormSubmission();
});

// Load cart items from the server
async function loadCartItems() {
    const orderItems = document.getElementById('orderItems');
    
    try {
        const response = await fetch('php/get_cart_items.php');
        const data = await response.json();
        
        if (data.success && data.items.length > 0) {
            renderCartItems(data.items);
            updateTotals(data.subtotal, data.tax, data.total);
            
            // Set hidden form values
            document.getElementById('bookingIds').value = data.booking_ids.join(',');
            document.getElementById('hiddenTotal').value = data.total;
            
            // Pre-fill email and phone if available
            if (data.user_email) {
                document.getElementById('email').value = data.user_email;
            }
            if (data.user_phone) {
                document.getElementById('phone').value = data.user_phone;
            }
        } else {
            showEmptyCart();
        }
    } catch (error) {
        console.error('Error loading cart:', error);
        showError('Failed to load cart items. Please try again.');
    }
}

// Render cart items in the order panel
function renderCartItems(items) {
    const orderItems = document.getElementById('orderItems');
    orderItems.innerHTML = '';
    
    items.forEach((item, index) => {
        const itemHtml = `
            <div class="order-item" style="animation-delay: ${index * 0.1}s">
                <div class="item-header">
                    <div>
                        <div class="hotel-name">${escapeHtml(item.hotel_name)}</div>
                        <div class="room-type">${escapeHtml(item.room_type)}</div>
                    </div>
                    <div class="item-price">RM ${formatNumber(item.subtotal)}</div>
                </div>
                <div class="item-details">
                    <div class="detail-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        ${item.check_in} - ${item.check_out}
                    </div>
                    <div class="detail-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        ${item.quantity} room(s) × ${item.days} night(s)
                    </div>
                </div>
            </div>
        `;
        orderItems.insertAdjacentHTML('beforeend', itemHtml);
    });
}

// Update total amounts
function updateTotals(subtotal, tax, total) {
    document.getElementById('subtotal').textContent = `RM ${formatNumber(subtotal)}`;
    document.getElementById('tax').textContent = `RM ${formatNumber(tax)}`;
    document.getElementById('totalAmount').textContent = `RM ${formatNumber(total)}`;
    document.getElementById('buttonAmount').textContent = `RM ${formatNumber(total)}`;
}

// Show empty cart message
function showEmptyCart() {
    const orderItems = document.getElementById('orderItems');
    orderItems.innerHTML = `
        <div class="empty-cart">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any rooms yet.</p>
            <a href="/Hotel_Booking_System/php/index.php" class="btn-browse">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Browse Hotels
            </a>
        </div>
    `;
    
    // Disable payment form
    document.getElementById('payButton').disabled = true;
}

// Initialize payment method selection
function initPaymentMethods() {
    const methods = document.querySelectorAll('.method-option');
    const cardForm = document.getElementById('cardForm');
    const fpxForm = document.getElementById('fpxForm');
    
    methods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove active class from all
            methods.forEach(m => m.classList.remove('active'));
            // Add active class to clicked
            this.classList.add('active');
            
            const selectedMethod = this.dataset.method;
            
            // Toggle form visibility
            if (selectedMethod === 'fpx') {
                cardForm.style.display = 'none';
                fpxForm.style.display = 'block';
                // Update required attributes
                setCardFieldsRequired(false);
                document.getElementById('bankSelect').required = true;
            } else {
                cardForm.style.display = 'block';
                fpxForm.style.display = 'none';
                setCardFieldsRequired(true);
                document.getElementById('bankSelect').required = false;
            }
        });
    });
}

// Set card fields required attribute
function setCardFieldsRequired(required) {
    const cardFields = ['cardName', 'cardNumber', 'expiry', 'cvv'];
    cardFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.required = required;
        }
    });
}

// Initialize card input validation and formatting
function initCardValidation() {
    const cardNumber = document.getElementById('cardNumber');
    const expiry = document.getElementById('expiry');
    const cvv = document.getElementById('cvv');
    const cvvToggle = document.getElementById('cvvToggle');
    
    // Card number formatting
    cardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        e.target.value = value.substring(0, 19);
        
        // Detect card type
        detectCardType(value.replace(/\s/g, ''));
    });
    
    // Expiry date formatting
    expiry.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
    
    // CVV - numbers only
    cvv.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
    });
    
    // CVV toggle visibility
    cvvToggle.addEventListener('click', function() {
        const cvvInput = document.getElementById('cvv');
        const type = cvvInput.type === 'password' ? 'text' : 'password';
        cvvInput.type = type;
        
        // Toggle icon
        this.innerHTML = type === 'password' 
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
               </svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
               </svg>`;
    });
}

// Detect card type based on number
function detectCardType(number) {
    const visa = document.getElementById('visaIcon');
    const mc = document.getElementById('mcIcon');
    
    // Reset
    visa.classList.remove('active');
    mc.classList.remove('active');
    
    if (number.length === 0) return;
    
    // Visa starts with 4
    if (number.charAt(0) === '4') {
        visa.classList.add('active');
    }
    // Mastercard starts with 51-55 or 2221-2720
    else if (/^5[1-5]/.test(number) || /^2[2-7]/.test(number)) {
        mc.classList.add('active');
    }
}

// Initialize form submission
function initFormSubmission() {
    const form = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Show loading state
        setLoadingState(true);
        
        // Submit form
        try {
            const formData = new FormData(form);
            const response = await fetch('php/payment_logic.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Redirect to success page
                window.location.href = `php/payment_success.php?payment_id=${result.payment_id}`;
            } else {
                showError(result.message || 'Payment failed. Please try again.');
                setLoadingState(false);
            }
        } catch (error) {
            console.error('Payment error:', error);
            showError('An error occurred. Please try again.');
            setLoadingState(false);
        }
    });
}

// Validate form fields
function validateForm() {
    const errors = [];
    
    // Check terms
    if (!document.getElementById('terms').checked) {
        errors.push('Please agree to the Terms & Conditions');
    }
    
    // Get payment method
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'FPX') {
        // Validate bank selection
        if (!document.getElementById('bankSelect').value) {
            errors.push('Please select your bank');
        }
    } else {
        // Validate card fields
        const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
        if (cardNumber.length < 16) {
            errors.push('Please enter a valid card number');
        }
        
        const expiry = document.getElementById('expiry').value;
        if (!/^\d{2}\/\d{2}$/.test(expiry)) {
            errors.push('Please enter a valid expiry date (MM/YY)');
        } else {
            // Check if card is not expired
            const [month, year] = expiry.split('/');
            const expDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
            if (expDate < new Date()) {
                errors.push('Your card has expired');
            }
        }
        
        const cvv = document.getElementById('cvv').value;
        if (cvv.length < 3) {
            errors.push('Please enter a valid CVV');
        }
    }
    
    // Validate billing info
    const email = document.getElementById('email').value;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push('Please enter a valid email address');
    }
    
    const phone = document.getElementById('phone').value;
    if (phone.length < 8) {
        errors.push('Please enter a valid phone number');
    }
    
    if (errors.length > 0) {
        showError(errors[0]);
        return false;
    }
    
    return true;
}

// Show error message
function showError(message) {
    // Remove existing error
    const existing = document.querySelector('.error-message');
    if (existing) existing.remove();
    
    const errorHtml = `
        <div class="error-message">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            ${escapeHtml(message)}
        </div>
    `;
    
    const form = document.getElementById('paymentForm');
    form.insertAdjacentHTML('afterbegin', errorHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const error = document.querySelector('.error-message');
        if (error) {
            error.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => error.remove(), 300);
        }
    }, 5000);
}

// Set loading state
function setLoadingState(loading) {
    const button = document.getElementById('payButton');
    const buttonText = button.querySelector('.button-text');
    const buttonAmount = button.querySelector('.button-amount');
    const buttonLoader = button.querySelector('.button-loader');
    
    if (loading) {
        button.disabled = true;
        buttonText.style.display = 'none';
        buttonAmount.style.display = 'none';
        buttonLoader.style.display = 'flex';
    } else {
        button.disabled = false;
        buttonText.style.display = 'inline';
        buttonAmount.style.display = 'inline';
        buttonLoader.style.display = 'none';
    }
}

// Helper: Format number with 2 decimal places
function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Helper: Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}