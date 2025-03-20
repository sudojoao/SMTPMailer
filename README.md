# SMTPMailer   / *No libraries No composer only a script!*

A lightweight, dependency-free PHP SMTP mail sender that allows you to send emails without requiring any external libraries or Composer. Perfect for simple PHP projects that need email functionality without the overhead of large email libraries.

### Please star if you find it useful :)

## Features

- 🔌 No external dependencies required
- 📧 Supports both plain text and HTML emails
- 🔒 TLS encryption support
- 🔑 SMTP authentication
- 📝 UTF-8 character encoding
- 🔍 Optional debug mode
- 📨 Multiple recipient support
- ⚡ Lightweight and easy to integrate

## Quick Start

1. Import and use in your project:
```php
require_once 'smtpmail.php';

$mailer = new SMTPMail();
$mailer->createSimpleLetter('Subject', 'Email body', 'html');
$mailer->setRecipient('recipient@example.com', 'Recipient Name');
$mailer->sendLetter();
```
2. Configure your SMTP settings in `config.php`:
```php
define('SMTP_EMAIL', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-specific-password');
define('SMTP_NAME', 'Your Name');
```

## Configuration

The script is pre-configured for Gmail SMTP but can be customized for any SMTP server:

- Default SMTP Host: smtp.gmail.com
- Default Port: 587
- Authentication: Required
- Security: TLS

## Requirements

- PHP 5.6 or higher
- OpenSSL extension enabled
- SMTP server access

## License

MIT License - See LICENSE file for details

## Author

João Vieira <mail@joaovieira.eu>

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
