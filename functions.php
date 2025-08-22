
<?php
require 'config.php';

function getBreads() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM breads WHERE stock > 0');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function saveOrder($customerData, $cartItems, $total) {
    global $pdo;
    
    // First check stock availability
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("SELECT stock FROM breads WHERE id = ?");
        $stmt->execute([$item['id']]);
        $bread = $stmt->fetch();
        
        if (!$bread || $bread['stock'] < $item['quantity']) {
            throw new Exception('Not enough stock for ' . $item['name']);
        }
    }
    
    $orderNumber = 'ORD-' . mt_rand(100000, 999999);
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO orders 
                          (order_number, customer_name, email, phone, address, instructions, 
                          total, status, order_type, payment_method, delivery_date, delivery_time, 
                          pickup_date, pickup_time, preorder_date, preorder_time, preorder_notes, user_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $orderNumber,
            $customerData['name'],
            $customerData['email'],
            $customerData['phone'],
            $customerData['address'] ?? '',
            $customerData['instructions'] ?? '',
            $total,
            $customerData['order_type'],
            $customerData['payment_method'],
            $customerData['delivery_date'] ?? null,
            $customerData['delivery_time'] ?? null,
            $customerData['pickup_date'] ?? null,
            $customerData['pickup_time'] ?? null,
            $customerData['preorder_date'] ?? null,
            $customerData['preorder_time'] ?? null,
            $customerData['preorder_notes'] ?? null,
            $customerData['user_id'] ?? null
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, bread_id, quantity, price) 
                              VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Update stock
            $stmt = $pdo->prepare("UPDATE breads SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
        }
        
        $pdo->commit();
        return ['orderNumber' => $orderNumber, 'orderId' => $orderId];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Function to generate receipt
function generateReceipt($orderId) {
    global $pdo;
    
    // Get order details
    $order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $order->execute([$orderId]);
    $order = $order->fetch();
    
    if (!$order) {
        return false;
    }
    
    // Get order items
    $items = $pdo->prepare("
        SELECT oi.*, b.name, b.image 
        FROM order_items oi 
        JOIN breads b ON oi.bread_id = b.id 
        WHERE oi.order_id = ?
    ");
    $items->execute([$orderId]);
    $items = $items->fetchAll();
    
    // Generate HTML receipt
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Order Receipt #'.$order['order_number'].'</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
            h1 { color: #333; text-align: center; }
            .header { text-align: center; margin-bottom: 30px; }
            .order-info { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f5f5f5; }
            .total { font-weight: bold; font-size: 1.2em; text-align: right; }
            .footer { margin-top: 30px; text-align: center; font-size: 0.9em; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Gold Label Bakeshoppe</h1>
            <p>123 Bakery Street, City, Country</p>
            <p>Phone: (123) 456-7890 | Email: info@goldlabelbakeshoppe.com</p>
        </div>
        
        <div class="order-info">
            <h2>Order Receipt #'.$order['order_number'].'</h2>
            <p><strong>Date:</strong> '.date('F j, Y, g:i a', strtotime($order['created_at'])).'</p>
            <p><strong>Customer:</strong> '.htmlspecialchars($order['customer_name']).'</p>
            <p><strong>Email:</strong> '.htmlspecialchars($order['email']).'</p>
            <p><strong>Phone:</strong> '.htmlspecialchars($order['phone']).'</p>';
            
    if ($order['order_type'] == 'delivery') {
        $html .= '<p><strong>Delivery Address:</strong> '.htmlspecialchars($order['address']).'</p>';
    }
    
    $html .= '<p><strong>Order Type:</strong> '.ucfirst($order['order_type']).'</p>
            <p><strong>Payment Method:</strong> '.ucfirst($order['payment_method']).'</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($items as $item) {
        $html .= '
                <tr>
                    <td>'.htmlspecialchars($item['name']).'</td>
                    <td>$'.number_format($item['price'], 2).'</td>
                    <td>'.$item['quantity'].'</td>
                    <td>$'.number_format($item['price'] * $item['quantity'], 2).'</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="total">
            <p>Total: $'.number_format($order['total'], 2).'</p>
        </div>
        
        <div class="footer">
            <p>DONT FORGET TO TAKE A SCREENSHOT OF YOUR RECEIPT</p>
            <p>Thank you for your order!</p>
            <p>For any questions, please contact us at info@goldlabelbakeshoppe.com</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

// Function to get user order history
function getUserOrderHistory($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get order details
function getOrderDetails($orderId) {
    global $pdo;
    
    // Get order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        return false;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, b.name, b.image 
        FROM order_items oi 
        JOIN breads b ON oi.bread_id = b.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    return [
        'order' => $order,
        'items' => $items
    ];
}
?>
