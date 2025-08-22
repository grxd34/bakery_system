<?php
require 'config.php';

$token = $_GET['token'] ?? '';
$error = null;
$success = null;
$valid = false;

if ($token) {
    $stmt = $pdo->prepare('SELECT id, reset_expires FROM users WHERE reset_token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        if ($user['reset_expires'] && strtotime($user['reset_expires']) > time()) {
            $valid = true;
            $userId = $user['id'];
        } else {
            $error = 'Reset link has expired. Please request a new one.';
        }
    } else {
        $error = 'Invalid reset token.';
    }
} else {
    $error = 'Missing reset token.';
}

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $stmt->execute([$hash, $userId]);
        $success = 'Password updated. You can now login.';
        $valid = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container { max-width: 420px; margin: 4rem auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,.1);}        
        .msg { margin:.75rem 0; }
        .success { color: #155724; background:#d4edda; padding:.5rem .75rem; border-radius:4px; }
        .error { color: #721c24; background:#f8d7da; padding:.5rem .75rem; border-radius:4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if ($error): ?><div class="msg error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="msg success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($valid): ?>
        <form method="POST">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" required>
            </div>
            <button type="submit" class="btn" style="width:100%">Update Password</button>
        </form>
        <?php else: ?>
            <p><a href="forgot_password.php">Request a new reset link</a></p>
            <p><a href="login.php">Back to login</a></p>
        <?php endif; ?>
    </div>
</body>
</html>

