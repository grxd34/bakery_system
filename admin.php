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

// Get orders and bread counts
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();
$breadCount = $pdo->query("SELECT COUNT(*) FROM breads")->fetchColumn();
$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$revenue = $revenue ? $revenue : 0;

// Check for low stock items
$lowStockItems = $pdo->query("SELECT COUNT(*) FROM breads WHERE stock < 5")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            font-size: 2rem;
            color: var(--secondary-color);
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: var(--dark-color);
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-pending { color: #FFA500; }
        .status-baking { color: #FF6347; }
        .status-ready { color: #32CD32; }
        .status-delivered { color: #008000; }
        .status-cancelled { color: #dc3545; }
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

    <div class="admin-container">
        <h2>Dashboard Overview</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><?= $breadCount ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p><?= $orderCount ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p>$<?= number_format($revenue, 2) ?></p>
            </div>
            <?php if ($lowStockItems > 0): ?>
            <div class="stat-card" style="grid-column: span 3; background-color: #fff3cd;">
                <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Items</h3>
                <p style="color: #856404;"><?= $lowStockItems ?> products need restocking</p>
                <a href="admin_products.php" class="btn" style="margin-top: 10px;">Manage Products</a>
            </div>
            <?php endif; ?>
        </div>

        <h3>Recent Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
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
                    <td class="status-<?= $order['status'] ?>">
                        <?= ucfirst($order['status']) ?>
                    </td>
                    <td>
                        <a href="admin_orders_detail.php?id=<?= $order['id'] ?>" class="btn">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>