<?php
// mailer.php
// Drop PHPMailer files into lib/PHPMailer/src and fill in credentials below.

// SMTP settings (set these)
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587; // 587 (TLS) or 465 (SSL)
const SMTP_ENCRYPTION = 'tls'; // 'tls' or 'ssl'
const SMTP_USERNAME = 'your_gmail_address@gmail.com'; // replace
const SMTP_PASSWORD = 'your_app_password_here'; // use Gmail App Password
const FROM_EMAIL = 'your_gmail_address@gmail.com';
const FROM_NAME = 'Gold Label Bakeshoppe';

function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    // Try PHPMailer if present
    $phpMailerPath = __DIR__ . '/lib/PHPMailer/src/PHPMailer.php';
    $smtpPath = __DIR__ . '/lib/PHPMailer/src/SMTP.php';
    $exceptionPath = __DIR__ . '/lib/PHPMailer/src/Exception.php';

    if (file_exists($phpMailerPath) && file_exists($smtpPath) && file_exists($exceptionPath)) {
        require_once $exceptionPath;
        require_once $phpMailerPath;
        require_once $smtpPath;

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($toEmail, $toName ?: $toEmail);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            return $mail->send();
        } catch (Throwable $e) {
            error_log('Email send failed: ' . $e->getMessage());
            return false;
        }
    }

    // Fallback: try PHP mail() (usually not configured on XAMPP)
    $headers = "MIME-Version: 1.0\r\n" .
               "Content-type:text/html;charset=UTF-8\r\n" .
               'From: ' . FROM_NAME . ' <' . FROM_EMAIL . ">\r\n";
    $ok = @mail($toEmail, $subject, $htmlBody, $headers);
    if (!$ok) {
        error_log('Email send skipped or failed (no PHPMailer, mail() likely not configured).');
    }
    return $ok;
}

function sendPasswordResetEmail(string $toEmail, string $resetLink): bool {
    $subject = 'Reset your password';
    $html = '<p>We received a request to reset your password.</p>' .
            '<p><a href="' . htmlspecialchars($resetLink) . '">Click here to reset your password</a></p>' .
            '<p>This link will expire in 1 hour.</p>';
    return sendEmail($toEmail, '', $subject, $html);
}

?>


