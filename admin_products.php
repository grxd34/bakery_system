<?php
require 'config.php';

// Authentication check - must be logged in AND be admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
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

// Handle product deletion
if (isset($_GET['delete'])) {
    // First delete related order items
    $pdo->prepare("DELETE FROM order_items WHERE bread_id = ?")->execute([$_GET['delete']]);
    // Then delete the bread
    $pdo->prepare("DELETE FROM breads WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: admin_products.php?deleted=1");
    exit;
}

// Handle product addition/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $stock = $_POST['stock'];
    
    if ($id > 0) {
        // Update existing product
        $stmt = $pdo->prepare("UPDATE breads SET name = ?, description = ?, price = ?, image = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $image, $stock, $id]);
        $message = "Product updated successfully";
    } else {
        // Add new product
        $stmt = $pdo->prepare("INSERT INTO breads (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $image, $stock]);
        $message = "Product added successfully";
    }
}

$products = $pdo->query("SELECT * FROM breads ORDER BY name")->fetchAll();

// Check for low stock items
$lowStockItems = $pdo->query("SELECT COUNT(*) FROM breads WHERE stock < 5")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .products-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }
        .product-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }
        .stock-out {
            background-color: #dc3545;
        }
        .stock-low {
            background-color: #ffc107;
            color: #000;
        }
        .stock-ok {
            background-color: #28a745;
        }
        .product-img {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        .product-info {
            padding: 1rem;
        }
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        .message {
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .alert {
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
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

    <div class="products-container">
        <h2>Manage Products</h2>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="message warning">Product deleted successfully</div>
        <?php endif; ?>
        
        <?php if (isset($message)): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="product-form">
            <h3><?= isset($_GET['edit']) ? 'Edit Product' : 'Add New Product' ?></h3>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $_GET['edit'] ?? 0 ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required 
                               value="<?= isset($_GET['edit']) ? htmlspecialchars($products[array_search($_GET['edit'], array_column($products, 'id'))]['name']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                               value="<?= isset($_GET['edit']) ? htmlspecialchars($products[array_search($_GET['edit'], array_column($products, 'id'))]['price']) : '' ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="image">Image URL:</label>
                        <input type="url" id="image" name="image" required
                               value="<?= isset($_GET['edit']) ? htmlspecialchars($products[array_search($_GET['edit'], array_column($products, 'id'))]['image']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock Quantity:</label>
                        <input type="number" id="stock" name="stock" min="0" required
                               value="<?= isset($_GET['edit']) ? htmlspecialchars($products[array_search($_GET['edit'], array_column($products, 'id'))]['stock']) : '10' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?= isset($_GET['edit']) ? htmlspecialchars($products[array_search($_GET['edit'], array_column($products, 'id'))]['description']) : '' ?></textarea>
                </div>
                <button type="submit" class="btn"><?= isset($_GET['edit']) ? 'Update Product' : 'Add Product' ?></button>
                <?php if (isset($_GET['edit'])): ?>
                    <a href="admin_products.php" class="btn" style="background-color: #6c757d;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <h3>Current Products</h3>
        <div class="product-grid">
            <?php foreach ($products as $product): 
                $stockClass = '';
                if ($product['stock'] <= 0) {
                    $stockClass = 'stock-out';
                    $stockText = 'Out of Stock';
                } elseif ($product['stock'] < 5) {
                    $stockClass = 'stock-low';
                    $stockText = 'Low Stock';
                } else {
                    $stockClass = 'stock-ok';
                    $stockText = 'In Stock';
                }
            ?>
                <div class="product-card">
                    <div class="stock-badge <?= $stockClass ?>"><?= $stockText ?> (<?= $product['stock'] ?>)</div>
                    <div class="product-img" style="background-image: url('<?= htmlspecialchars($product['image']) ?>')"></div>
                    <div class="product-info">
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                        <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
                        <div class="product-actions">
                            <a href="admin_products.php?edit=<?= $product['id'] ?>" class="btn">Edit</a>
                            <a href="admin_products.php?delete=<?= $product['id'] ?>" class="btn" style="background-color: #dc3545;" 
                               onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>