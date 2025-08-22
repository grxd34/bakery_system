<?php
require 'config.php';

header('Content-Type: application/json');

$isLoggedIn = isset($_SESSION['user_id']);

$response = [
    'logged_in' => $isLoggedIn
];

if ($isLoggedIn) {
    $response['user'] = [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? null,
        'is_admin' => $_SESSION['is_admin'] ?? false
    ];
}

echo json_encode($response);
?>


