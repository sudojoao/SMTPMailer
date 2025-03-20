<?php
require_once('smtpmail.php');
$mailer = new SMTPMail();

function sendSimpleEmailWithAttachment() {
    global $mailer;
    
    $mailer->clearRecipients();
    $mailer->clearAttachments();
    
    $subject = "Your Invoice #12345";
    $body = "<h2>Invoice Attached</h2>
            <p>Dear Customer,</p>
            <p>Thank you for your recent purchase. Please find your invoice attached to this email.</p>
            <p>If you have any questions, please don't hesitate to contact us.</p>
            <p>Best regards,<br>Your Company</p>";
    
    $mailer->createSimpleLetter($subject, $body, 'html');
    $mailer->setRecipient("example@example.com", "Customer Name");
    
    $mailer->addAttachment("pdf\example.pdf");
    
    if($mailer->sendLetter()) {
        return "Invoice email sent successfully!";
    } else {
        return "Failed to send invoice email.";
    }
}

$mail = '';
if(isset($_POST['send_email'])) {
    $mail = sendSimpleEmailWithAttachment();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <h1>SMTP-MAILER Test Page</h1>
    <form method="POST">
        <button type="submit" name="send_email" class="button">Send Test Email</button>
    </form>
    <?php if($mail): ?>
        <div class="message <?php echo strpos($mail, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($mail); ?>
        </div>
    <?php endif; ?>
</body>
</html>