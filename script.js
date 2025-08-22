// script.js - Updated version
let cart = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeQuantityControls();
    initializeAddToCartButtons();
    initializeCheckoutButton();
    initializeModals();
    initializeOrderForm();
    updateCartDisplay();
});

function initializeQuantityControls() {
    document.querySelectorAll('.quantity-controls').forEach(control => {
        const input = control.querySelector('.quantity');
        
        control.querySelector('.increase').addEventListener('click', function() {
            input.value = parseInt(input.value) + 1;
        });
        
        control.querySelector('.decrease').addEventListener('click', function() {
            if (parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
}

function initializeAddToCartButtons() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const quantityInput = this.closest('.bread-info').querySelector('.quantity');
            const quantity = parseInt(quantityInput.value);
            
            if (quantity > 0) {
                checkStock(this.dataset.id, quantity)
                    .then(hasStock => {
                        if (hasStock) {
                            const bread = {
                                id: this.dataset.id,
                                name: this.dataset.name,
                                price: parseFloat(this.dataset.price),
                                image: this.dataset.image,
                                quantity: quantity
                            };
                            
                            addToCart(bread);
                            quantityInput.value = 0;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking stock:', error);
                        alert('Error checking product availability. Please try again.');
                    });
            }
        });
    });
}

function initializeCheckoutButton() {
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            if (cart.length > 0) {
                checkLoginStatus()
                    .then(loggedIn => {
                        if (loggedIn) {
                            const modal = document.getElementById('orderModal');
                            if (modal) {
                                modal.style.display = 'block';
                                populateUserInfo();
                            }
                        } else {
                            alert('Please log in to proceed with checkout');
                            window.location.href = 'login.php';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking login status:', error);
                    });
            }
        });
    }
}

function initializeModals() {
    // Order modal
    const orderModal = document.getElementById('orderModal');
    if (orderModal) {
        orderModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
        
        const closeBtn = orderModal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                orderModal.style.display = 'none';
            });
        }
    }
    
    // Thank you modal
    const thankYouModal = document.getElementById('thankYouModal');
    if (thankYouModal) {
        thankYouModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
        
        const closeThankYou = document.getElementById('closeThankYou');
        if (closeThankYou) {
            closeThankYou.addEventListener('click', function() {
                thankYouModal.style.display = 'none';
                window.location.reload();
            });
        }
    }
}

function initializeOrderForm() {
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        // Order type change handler
        document.querySelectorAll('input[name="orderType"]').forEach(radio => {
            radio.addEventListener('change', function() {
                toggleOrderTypeFields(this.value);
            });
        });
        
        // Form submission
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            processOrderForm();
        });
        
        // Cancel button
        const cancelOrderBtn = document.getElementById('cancelOrder');
        if (cancelOrderBtn) {
            cancelOrderBtn.addEventListener('click', function() {
                document.getElementById('orderModal').style.display = 'none';
            });
        }
    }
}

function toggleOrderTypeFields(orderType) {
    // Hide all order type fields
    document.querySelectorAll('.order-type-fields').forEach(field => {
        field.style.display = 'none';
        field.querySelectorAll('input, textarea').forEach(input => {
            input.removeAttribute('required');
        });
    });
    
    // Show relevant fields
    if (orderType === 'delivery') {
        const deliveryFields = document.getElementById('deliveryFields');
        if (deliveryFields) {
            deliveryFields.style.display = 'block';
            deliveryFields.querySelectorAll('input, textarea').forEach(input => {
                input.setAttribute('required', 'required');
            });
        }
    } else if (orderType === 'pickup') {
        const pickupFields = document.getElementById('pickupFields');
        if (pickupFields) {
            pickupFields.style.display = 'block';
            pickupFields.querySelectorAll('input, textarea').forEach(input => {
                input.setAttribute('required', 'required');
            });
        }
    } else if (orderType === 'preorder') {
        const preorderFields = document.getElementById('preorderFields');
        if (preorderFields) {
            preorderFields.style.display = 'block';
            preorderFields.querySelectorAll('input, textarea').forEach(input => {
                input.setAttribute('required', 'required');
            });
        }
    }
}

function processOrderForm() {
    const orderType = document.querySelector('input[name="orderType"]:checked');
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
    
    if (!orderType) {
        alert('Please select an order type (Delivery, Pickup, or Pre-order)');
        return;
    }
    
    if (!paymentMethod) {
        alert('Please select a payment method');
        return;
    }
    
    const formData = new FormData(document.getElementById('orderForm'));
    const orderData = {
        cart: cart,
        total: parseFloat(document.getElementById('orderTotal').textContent),
        orderType: orderType.value,
        paymentMethod: paymentMethod.value
    };
    
    formData.forEach((value, key) => {
        orderData[key] = value;
    });
    
    fetch('process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('orderModal').style.display = 'none';
            document.getElementById('orderNumber').textContent = data.orderNumber;
            document.getElementById('thankYouModal').style.display = 'block';
            
            // Open receipt in a new tab
            if (data.orderId) {
                window.open('generate_receipt.php?order_id=' + encodeURIComponent(data.orderId), '_blank');
            }
            
            cart = [];
            updateCartDisplay();
        } else {
            alert('Error: ' + (data.message || 'Failed to process order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error processing your order. Please try again.');
    });
}

function checkLoginStatus() {
    return fetch('check_login.php')
        .then(response => response.json())
        .then(data => data.logged_in)
        .catch(error => {
            console.error('Error checking login status:', error);
            return false;
        });
}

function checkStock(productId, quantity) {
    return fetch('check_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.message);
            return false;
        }
        return true;
    });
}

function addToCart(item) {
    const existingItem = cart.find(cartItem => cartItem.id === item.id);
    
    if (existingItem) {
        existingItem.quantity += item.quantity;
    } else {
        cart.push(item);
    }
    
    updateCartDisplay();
}

function updateCartDisplay() {
    const orderItems = document.getElementById('orderItems');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const orderTotal = document.getElementById('orderTotal');
    
    if (!orderItems) return;
    
    if (cart.length === 0) {
        orderItems.innerHTML = '<div class="empty-cart-message"><i class="fas fa-shopping-basket"></i><p>Your cart is empty</p></div>';
        if (checkoutBtn) checkoutBtn.disabled = true;
        if (orderTotal) orderTotal.textContent = '0.00';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += `
            <div class="order-item">
                <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                <div class="item-info">
                    <h4>${item.name}</h4>
                    <p>$${item.price.toFixed(2)} x ${item.quantity}</p>
                </div>
                <div class="item-total">$${itemTotal.toFixed(2)}</div>
                <button class="remove-item" data-id="${item.id}">&times;</button>
            </div>
        `;
    });
    
    orderItems.innerHTML = html;
    if (orderTotal) orderTotal.textContent = total.toFixed(2);
    if (checkoutBtn) checkoutBtn.disabled = false;
    
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            removeFromCart(this.dataset.id);
        });
    });
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartDisplay();
}

function populateUserInfo() {
    const today = new Date().toISOString().split('T')[0];
    const now = new Date();
    now.setHours(now.getHours() + 1);
    const timeString = now.toTimeString().substring(0, 5);
    
    const dateFields = ['deliveryDate', 'pickupDate', 'preorderDate'];
    const timeFields = ['deliveryTime', 'pickupTime', 'preorderTime'];
    
    dateFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) element.value = today;
    });
    
    timeFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) element.value = timeString;
    });
}