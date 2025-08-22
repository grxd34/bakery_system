<?php
require 'config.php';

$success = null;
$error = null;
$generatedLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = 'Email is required';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Generic success message
        $success = 'If that email exists, a reset link is ready below.';

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 60 * 60); // 1 hour

            $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
            $stmt->execute([$token, $expiresAt, $user['id']]);

            // Compose reset link and show it immediately (no email sent)
            $resetLink = sprintf('%sreset_password.php?token=%s',
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/',
                $token
            );
            $generatedLink = $resetLink;
            $_SESSION['last_reset_link'] = $resetLink;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container { max-width: 420px; margin: 4rem auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,.1);}        
        .muted { color: #6c757d; font-size: .9rem; }
        .msg { margin:.75rem 0; }
        .success { color: #155724; background:#d4edda; padding:.5rem .75rem; border-radius:4px; }
        .error { color: #721c24; background:#f8d7da; padding:.5rem .75rem; border-radius:4px; }
    </style>
?</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if ($error): ?><div class="msg error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="msg success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($generatedLink): ?>
            <p>
                <a href="<?= htmlspecialchars($generatedLink) ?>" class="btn" target="_blank">Open reset link</a>
            </p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Your account email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn" style="width:100%">Send Reset Link</button>
        </form>
        <p class="muted" style="margin-top:1rem"><a href="login.php">Back to login</a></p>
    </div>
</body>
</html>

