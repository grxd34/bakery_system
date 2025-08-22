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

// Get all orders
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();

// Check for low stock items
$lowStockItems = $pdo->query("SELECT COUNT(*) FROM breads WHERE stock < 5")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: var(--dark-color);
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
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
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
        }
        .view-btn {
            background-color: var(--secondary-color);
            color: white;
        }
        .view-btn:hover {
            background-color: var(--accent-color);
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

    <div class="orders-container">
        <h2>Manage Orders</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Type</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    <td>$<?= number_format($order['total'], 2) ?></td>
                    <td><?= ucfirst($order['order_type']) ?></td>
                    <td><?= ucfirst($order['payment_method']) ?></td>
                    <td>
                        <span class="status-badge status-<?= $order['status'] ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="admin_orders_detail.php?id=<?= $order['id'] ?>" class="action-btn view-btn">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>