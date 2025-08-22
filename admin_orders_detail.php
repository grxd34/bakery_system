<?php
require 'config.php';

// Authentication check - must be logged in AND be admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Optional: Verify the user is still an admin in the database
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$orderId = $_GET['id'] ?? 0;
if (!$orderId) {
    header('Location: admin_orders.php');
    exit;
}

$order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$order->execute([$orderId]);
$order = $order->fetch();

if (!$order) {
    die("Order not found");
}

$items = $pdo->prepare("
    SELECT oi.*, b.name, b.image 
    FROM order_items oi 
    JOIN breads b ON oi.bread_id = b.id 
    WHERE oi.order_id = ?
");
$items->execute([$orderId]);
$items = $items->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $orderId]);
    $order['status'] = $_POST['status'];
    
    // Here you would add email notification logic
    $success = "Order status updated successfully";
}

// Check for low stock items
$lowStockItems = $pdo->query("SELECT COUNT(*) FROM breads WHERE stock < 5")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .order-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .order-info {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .order-items {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        .item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .item-img {
            width: 60px;
            height: 60px;
            border-radius: 4px;
            background-size: cover;
            background-position: center;
        }
        .status-form {
            margin-top: 2rem;
        }
        .status-form select, .status-form button {
            padding: 10px;
            margin-right: 10px;
        }
        .success {
            color: green;
            margin: 1rem 0;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 4px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
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
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header>
    <div class="logo">
        <i class="fas fa-bread-slice"></i>
        <h1>Bakery Admin</h1>
    </div>
    <nav>
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="admin_orders.php">Orders</a></li>
            <li><a href="admin_products.php">Products</a></li>
            <?php if ($lowStockItems > 0): ?>
                <li><a href="admin_products.php" style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> Low Stock (<?= $lowStockItems ?>)</a></li>
            <?php endif; ?>
            <li><span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

    <div class="order-container">
        <div class="order-header">
            <h2>Order #<?= htmlspecialchars($order['order_number']) ?></h2>
            <span class="status-badge status-<?= $order['status'] ?>">
                Status: <?= ucfirst($order['status']) ?>
            </span>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="order-info">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Instructions:</strong> <?= htmlspecialchars($order['instructions']) ?></p>
            <p><strong>Order Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
            <p><strong>Order Type:</strong> <?= ucfirst($order['order_type']) ?></p>
            <p><strong>Payment Method:</strong> <?= ucfirst($order['payment_method']) ?></p>
            <p><strong>Total:</strong> $<?= number_format($order['total'], 2) ?></p>
        </div>
        
        <div class="order-items">
            <h3>Order Items</h3>
            <?php foreach ($items as $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <div class="item-img" style="background-image: url('<?= htmlspecialchars($item['image']) ?>')"></div>
                        <div>
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <p>$<?= number_format($item['price'], 2) ?> each</p>
                        </div>
                    </div>
                    <div>
                        <p>Quantity: <?= $item['quantity'] ?></p>
                        <p>Subtotal: $<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <form method="POST" class="status-form">
            <label for="status">Update Status:</label>
            <select name="status" id="status">
                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="baking" <?= $order['status'] === 'baking' ? 'selected' : '' ?>>Baking</option>
                <option value="ready" <?= $order['status'] === 'ready' ? 'selected' : '' ?>>Ready for Pickup</option>
                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn">Update Status</button>
        </form>
    </div>
</body>
</html>