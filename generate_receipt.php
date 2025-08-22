
<?php
require 'functions.php';

$orderId = $_GET['order_id'] ?? '';

if (empty($orderId)) {
    die('Order ID is required');
}

// Generate receipt
$receipt = generateReceipt($orderId);

// Output the receipt
header('Content-Type: text/html');
echo $receipt;
?>
