<?php
require 'config.php';
require 'functions.php';

header('Content-Type: application/json');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['id']) || !isset($data['quantity'])) {
        throw new Exception('Invalid input data');
    }
    
    $breadId = $data['id'];
    $quantity = $data['quantity'];
    
    $stmt = $pdo->prepare("SELECT name, stock FROM breads WHERE id = ?");
    $stmt->execute([$breadId]);
    $bread = $stmt->fetch();
    
    if (!$bread) {
        throw new Exception('Product not found');
    }
    
    if ($bread['stock'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Not enough stock for ' . $bread['name'] . '. Only ' . $bread['stock'] . ' left in stock.'
        ]);
    } else {
        echo json_encode([
            'success' => true
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}