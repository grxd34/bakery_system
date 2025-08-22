<?php
require 'config.php';
require 'functions.php';

$breads = getBreads();

// Get user information if logged in
$userInfo = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username, email, phone, address FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Bread Bakery - Order Online</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* User dropdown styles */
        .user-info {
            position: relative;
            cursor: pointer;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-dropdown i {
            font-size: 1.5rem;
            color: var(--secondary-color);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 120px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            overflow: hidden;
        }

        .dropdown-content a {
            color: var(--dark-color);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: var(--light-color);
        }

        .user-info:hover .dropdown-content {
            display: block;
        }
        
        nav ul li a.btn {
            padding: 8px 16px;
            margin-left: 10px;
            background-color: var(--primary-color);
        }

        nav ul li a.btn:hover {
            background-color: var(--secondary-color);
        }
        
        .hero {
            background-image: url('https://tse3.mm.bing.net/th/id/OIP.NEVv_xJYl8d3e3Wcdj2YggHaEK?pid=Api&P=0&h=220');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 500px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 0 20px;
        }

        .hero h2 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }
        
        /* Order type specific fields */
        .order-type-fields {
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .order-type-fields > div {
            margin-bottom: 1rem;
        }
        
        /* NEW PROFESSIONAL ORDER FORM STYLES */
        .order-form-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .order-form-header {
            background: linear-gradient(135deg, var(--dark-color), #2a4a6d);
            color: white;
            padding: 1.5rem 2rem;
            position: relative;
        }

        .order-form-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .order-form-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }

        .order-form-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .form-section-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-group {
            flex: 1 0 calc(50% - 20px);
            margin: 0 10px 1.5rem;
            min-width: 250px;
        }

        .form-group-full {
            flex: 1 0 calc(100% - 20px);
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #444;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f9fafb;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(247, 114, 128, 0.1);
            background-color: white;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .order-type-options {
            display: flex;
            gap: 15px;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .order-type-option {
            flex: 1;
            min-width: 150px;
        }

        .order-type-option input[type="radio"] {
            display: none;
        }

        .order-type-option label {
            display: block;
            padding: 15px;
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .order-type-option input[type="radio"]:checked + label {
            border-color: var(--secondary-color);
            background-color: rgba(247, 114, 128, 0.1);
            color: var(--secondary-color);
            font-weight: 600;
        }

        .order-type-option label i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .payment-options {
            display: flex;
            gap: 15px;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .payment-option {
            flex: 1;
            min-width: 120px;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option label {
            display: block;
            padding: 12px;
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option input[type="radio"]:checked + label {
            border-color: var(--secondary-color);
            background-color: rgba(247, 114, 128, 0.1);
            color: var(--secondary-color);
            font-weight: 600;
        }

        .payment-option label i {
            margin-right: 5px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 2rem;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .form-required {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-hint {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 calc(100% - 20px);
            }
            
            .order-type-options, .payment-options {
                flex-direction: column;
            }
            
            .order-form-body {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* Order summary in modal */
        .order-summary-modal {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .order-summary-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }

        .order-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .order-summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <header>
    <div class="logo">
        <i class="fas fa-bread-slice"></i>
        <h1>Gold Label Bakeshoppe</h1>
    </div>
    <nav>
        <ul>
            <li><a href="#menu">Our Breads</a></li>
            <li><a href="#about">About Us</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="user-info">
                    <div class="user-dropdown">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <div class="dropdown-content">
                            <a href="order_history.php">Order History</a>
                            <?php if ($_SESSION['is_admin']): ?>
                                <a href="admin.php">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn">Create Account</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h2>Gold Label Bakeshoppe Bread Made Daily</h2>
                <p>Order fresh, handcrafted bread delivered to your door</p>
                <a href="#menu" class="btn">Order Now</a>
            </div>
        </section>

        <section id="menu" class="menu-section">
            <h2>Our Bread Selection</h2>
            <div class="bread-grid" id="breadMenu">
                <?php foreach ($breads as $bread): ?>
                <div class="bread-item">
                    <div class="bread-img" style="background-image: url('<?= htmlspecialchars($bread['image']) ?>')"></div>
                    <div class="bread-info">
                        <h3><?= htmlspecialchars($bread['name']) ?></h3>
                        <p><?= htmlspecialchars($bread['description']) ?></p>
                        <span class="bread-price">$<?= number_format($bread['price'], 2) ?></span>
                        <div class="quantity-controls">
                            <button class="decrease">-</button>
                            <input type="number" min="0" value="0" class="quantity">
                            <button class="increase">+</button>
                        </div>
                        <button class="add-to-cart" data-id="<?= $bread['id'] ?>" 
                                data-name="<?= htmlspecialchars($bread['name']) ?>" 
                                data-price="<?= $bread['price'] ?>" 
                                data-image="<?= htmlspecialchars($bread['image']) ?>">
                            Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="order-section">
    <h2>Your Order</h2>
    <div class="order-summary">
        <div class="order-items" id="orderItems">
            <div class="empty-cart-message">
                <i class="fas fa-shopping-basket"></i>
                <p>Your cart is empty</p>
            </div>
        </div>
        <div class="order-total">
            <div>
                <h3>Order Summary</h3>
                <p>Total: $<span id="orderTotal">0.00</span></p>
            </div>
            <button id="checkoutBtn" class="btn" disabled>Proceed to Checkout</button>
        </div>
    </div>
</section>

        <section id="about" class="about-section">
            <h2>About Our Bakery</h2>
            <p>We've been crafting Goldlabel bread using traditional methods since 1986. Our bread is made with locally sourced, organic ingredients and baked fresh daily.</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 Fresh Bread Bakery. All rights reserved.</p>
    </footer>

    <!-- Order Modal with Professional Design -->
    <div class="modal" id="orderModal">
        <div class="order-form-container">
            <div class="order-form-header">
                <h2>Complete Your Order</h2>
                <p>Please provide your details to complete your purchase</p>
                <button class="close" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            
            <div class="order-form-body">
                <form id="orderForm">
                   
                    
                    <!-- Order Type Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-truck"></i>
                            Order Type
                        </div>
                        
                        <div class="order-type-options">
                            <div class="order-type-option">
                                <input type="radio" id="preorderType" name="orderType" value="preorder">
                                <label for="preorderType">
                                    <i class="fas fa-calendar-check"></i>
                                    Pre-order
                                </label>
                            </div>
                            <div class="order-type-option">
                                <input type="radio" id="deliveryType" name="orderType" value="delivery">
                                <label for="deliveryType">
                                    <i class="fas fa-home"></i>
                                    Delivery
                                </label>
                            </div>
                            <div class="order-type-option">
                                <input type="radio" id="pickupType" name="orderType" value="pickup">
                                <label for="pickupType">
                                    <i class="fas fa-store"></i>
                                    Pickup
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-credit-card"></i>
                            Payment Method
                        </div>
                        
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="codPayment" name="paymentMethod" value="cod">
                                <label for="codPayment">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Cash on Delivery
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="onlinePayment" name="paymentMethod" value="online">
                                <label for="onlinePayment">
                                    <i class="fas fa-globe"></i>
                                    Online Payment
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-user"></i>
                            Personal Information
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name <span class="form-required">*</span></label>
                                <input type="text" id="name" name="name" class="form-input" required 
                                       placeholder="Your full name" value="<?= isset($userInfo['username']) ? htmlspecialchars($userInfo['username']) : '' ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address <span class="form-required">*</span></label>
                                <input type="email" id="email" name="email" class="form-input" required 
                                       placeholder="your@email.com" value="<?= isset($userInfo['email']) ? htmlspecialchars($userInfo['email']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number <span class="form-required">*</span></label>
                                <input type="tel" id="phone" name="phone" class="form-input" required 
                                       placeholder="+1 (123) 456-7890" value="<?= isset($userInfo['phone']) ? htmlspecialchars($userInfo['phone']) : '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Fields -->
                    <div id="deliveryFields" class="form-section order-type-fields" style="display: none;">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Delivery Information
                        </div>
                        
                        <div class="form-group form-group-full">
                            <label for="deliveryAddress" class="form-label">Delivery Address <span class="form-required">*</span></label>
                            <textarea id="deliveryAddress" name="deliveryAddress" class="form-textarea" required 
                                      placeholder="Street address, city, zip code"><?= isset($userInfo['address']) ? htmlspecialchars($userInfo['address']) : '' ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="deliveryDate" class="form-label">Delivery Date <span class="form-required">*</span></label>
                                <input type="date" id="deliveryDate" name="deliveryDate" class="form-input" required>
                                <div class="form-hint">Please allow at least 2 hours for delivery</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="deliveryTime" class="form-label">Delivery Time <span class="form-required">*</span></label>
                                <input type="time" id="deliveryTime" name="deliveryTime" class="form-input" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pickup Fields -->
                    <div id="pickupFields" class="form-section order-type-fields" style="display: none;">
                        <div class="form-section-title">
                            <i class="fas fa-store"></i>
                            Pickup Information
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="pickupDate" class="form-label">Pickup Date <span class="form-required">*</span></label>
                                <input type="date" id="pickupDate" name="pickupDate" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="pickupTime" class="form-label">Pickup Time <span class="form-required">*</span></label>
                                <input type="time" id="pickupTime" name="pickupTime" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="form-group form-group-full">
                            <div class="form-hint">
                                <i class="fas fa-info-circle"></i> Our bakery is located at 123 Bakery Street. Please arrive within 15 minutes of your selected pickup time.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preorder Fields -->
                    <div id="preorderFields" class="form-section order-type-fields" style="display: none;">
                        <div class="form-section-title">
                            <i class="fas fa-calendar-alt"></i>
                            Preorder Information
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="preorderDate" class="form-label">Desired Date <span class="form-required">*</span></label>
                                <input type="date" id="preorderDate" name="preorderDate" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="preorderTime" class="form-label">Desired Time <span class="form-required">*</span></label>
                                <input type="time" id="preorderTime" name="preorderTime" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="form-group form-group-full">
                            <label for="preorderNotes" class="form-label">Special Instructions</label>
                            <textarea id="preorderNotes" name="preorderNotes" class="form-textarea" 
                                      placeholder="Any special requests or notes for your order"></textarea>
                        </div>
                    </div>
                    
                    <!-- Additional Instructions -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-sticky-note"></i>
                            Additional Instructions
                        </div>
                        
                        <div class="form-group form-group-full">
                            <label for="instructions" class="form-label">Order Notes</label>
                            <textarea id="instructions" name="instructions" class="form-textarea" 
                                      placeholder="Any additional notes for your order (allergies, special requests, etc.)"></textarea>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelOrder">Cancel</button>
                        <button type="submit" class="btn">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <div class="modal" id="thankYouModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Thank You for Your Order!</h2>
        <p>Your bread will be freshly baked and delivered to you soon.</p>
        <p>Order #: <span id="orderNumber"></span></p>
        <button class="btn" id="closeThankYou">Close</button>
    </div>
</div>

    <script src="script.js"></script>
    <script>
        // Additional JavaScript for the enhanced order form
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today for all date fields
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('deliveryDate').min = today;
            document.getElementById('pickupDate').min = today;
            document.getElementById('preorderDate').min = today;
            
            // Set default time to current time + 1 hour
            const now = new Date();
            now.setHours(now.getHours() + 1);
            const timeString = now.toTimeString().substring(0, 5);
            document.getElementById('deliveryTime').value = timeString;
            document.getElementById('pickupTime').value = timeString;
            document.getElementById('preorderTime').value = timeString;
            
            // Order type change handler
            document.querySelectorAll('input[name="orderType"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const orderType = this.value;
                    
                    // Hide all order type fields
                    document.querySelectorAll('.order-type-fields').forEach(field => {
                        field.style.display = 'none';
                        field.querySelectorAll('input, textarea').forEach(input => {
                            input.removeAttribute('required');
                        });
                    });
                    
                    // Show relevant fields
                    if (orderType === 'delivery') {
                        document.getElementById('deliveryFields').style.display = 'block';
                        document.getElementById('deliveryFields').querySelectorAll('input, textarea').forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                        
                        // For delivery, show COD as default
                        document.getElementById('codPayment').checked = true;
                    } else if (orderType === 'pickup') {
                        document.getElementById('pickupFields').style.display = 'block';
                        document.getElementById('pickupFields').querySelectorAll('input, textarea').forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                        
                        // For pickup, show online payment as default
                        document.getElementById('onlinePayment').checked = true;
                    } else if (orderType === 'preorder') {
                        document.getElementById('preorderFields').style.display = 'block';
                        document.getElementById('preorderFields').querySelectorAll('input, textarea').forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    }
                });
            });
            
            // Cancel button handler
            document.getElementById('cancelOrder').addEventListener('click', function() {
                document.getElementById('orderModal').style.display = 'none';
            });
            
            // Close modal when clicking outside
            document.getElementById('orderModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
            
            // Close button
            document.querySelector('#orderModal .close').addEventListener('click', function() {
                document.getElementById('orderModal').style.display = 'none';
            });
        });
    </script>
</body>
</html>