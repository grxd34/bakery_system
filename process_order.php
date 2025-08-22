<?php
// process_order.php - Updated version
require 'config.php';
require 'functions.php';

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid input data');
    }
    
    // Save user phone and address for future orders
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
        $stmt->execute([
            $data['phone'] ?? '',
            $data['deliveryAddress'] ?? $data['address'] ?? '',
            $_SESSION['user_id']
        ]);
    }
    
    // Prepare order data based on order type
    $orderData = [
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'address' => $data['deliveryAddress'] ?? $data['address'] ?? null,
        'instructions' => $data['instructions'] ?? null,
        'order_type' => $data['orderType'],
        'payment_method' => $data['paymentMethod'],
        'delivery_date' => $data['deliveryDate'] ?? null,
        'delivery_time' => $data['deliveryTime'] ?? null,
        'pickup_date' => $data['pickupDate'] ?? null,
        'pickup_time' => $data['pickupTime'] ?? null,
        'preorder_date' => $data['preorderDate'] ?? null,
        'preorder_time' => $data['preorderTime'] ?? null,
        'preorder_notes' => $data['preorderNotes'] ?? null,
        'user_id' => $_SESSION['user_id'] ?? null
    ];
    
    $orderInfo = saveOrder($orderData, $data['cart'], $data['total']);
    
    echo json_encode([
        'success' => true,
        'orderNumber' => $orderInfo['orderNumber'],
        'orderId' => $orderInfo['orderId'],
        'paymentMethod' => $data['paymentMethod']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>