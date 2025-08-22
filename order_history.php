<?php
require 'config.php';
require 'functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user's order history
$orders = getUserOrderHistory($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Gold Label Bakeshoppe</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .order-history-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1rem;
        }
        
        .order-history-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .order-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .order-header {
            background-color: var(--dark-color);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-body {
            padding: 1.5rem;
        }
        
        .order-items {
            margin-bottom: 1rem;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-baking {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-ready {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .empty-history {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-history i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        .view-receipt-btn {
            background-color: var(--secondary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        
        .view-receipt-btn:hover {
            background-color: var(--accent-color);
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
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#menu">Our Breads</a></li>
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

    <div class="order-history-container">
        <div class="order-history-header">
            <h2>Your Order History</h2>
            <p>View all your past orders with us</p>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-history">
                <i class="fas fa-shopping-bag"></i>
                <h3>No orders yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                <a href="index.php#menu" class="btn">Browse Our Breads</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): 
                $orderDetails = getOrderDetails($order['id']);
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Order #<?= htmlspecialchars($order['order_number']) ?></h3>
                            <p>Placed on <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        </div>
                        <span class="status-badge status-<?= $order['status'] ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-items">
                            <?php foreach ($orderDetails['items'] as $item): ?>
                                <div class="order-item">
                                    <span><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
                                    <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div>
                            <p><strong>Order Type:</strong> <?= ucfirst($order['order_type']) ?></p>
                            <p><strong>Payment Method:</strong> <?= ucfirst($order['payment_method']) ?></p>
                            <?php if ($order['order_type'] === 'delivery' && !empty($order['address'])): ?>
                                <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-footer">
                        <div>
                            <strong>Total: $<?= number_format($order['total'], 2) ?></strong>
                        </div>
                        <div>
                            <a href="generate_receipt.php?order_id=<?= $order['id'] ?>" target="_blank" class="view-receipt-btn">
                                <i class="fas fa-receipt"></i> View Receipt
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>