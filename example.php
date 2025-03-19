<?php

require_once("smtpmail.php");

$recipients = array(
    array('email' => 'example@mail.eu', 'name' => 'Recipient 1'),
    // Add as needed
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $smtp = new SMTPMail();
    $smtp->setDebugPrint(true);
    
    foreach ($recipients as $recipient) {
        $smtp->setRecipient($recipient['email'], $recipient['name']);
    }
    
    $smtp->createSimpleLetter(
        "Hope you enjoy this test email!", 
        "This is a test email sent from the PHP SMTP Mail Sender! <h1>Test</h1>", 
        "html"
    );
    
    $result = $smtp->sendLetter();
    $message = $result ? "Email sent successfully!" : "Failed to send email.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SMTPMailer</title>
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
            margin: 20px 0;
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
    <h1>SMTPMailer</h1>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo $result ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <button type="submit" class="button">Send Email</button>
    </form>
</body>
</html>

