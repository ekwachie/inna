# Mailer

Inna includes a `Mailer` utility class for sending emails using PHPMailer.

## Basic Usage

### Sending Email

```php
use app\Core\Utils\Mailer;

$mailer = new Mailer();

$mailer->sendMail(
    'recipient@example.com',  // To email
    'John Doe',                // Recipient name
    'Welcome!',                 // Subject
    'Welcome to our platform!'  // Message
);
```

## Configuration

The Mailer class uses PHPMailer and needs to be configured. Check `app/Core/Utils/Mailer.php` for SMTP settings.

### SMTP Configuration

Edit the Mailer class to configure:

```php
$mail->Host = "smtp.gmail.com";
$mail->SMTPAuth = true;
$mail->Username = "your-email@gmail.com";
$mail->Password = "your-password";
$mail->SMTPSecure = "tls";
$mail->Port = 587;
```

## Email Templates

The Mailer class includes email template support. Templates are located in `app/templates/email/`.

### Using Templates

The Mailer automatically loads templates from `app/templates/email/default.html`.

## Complete Example

```php
use app\Core\Utils\Mailer;

public function sendWelcomeEmail($userEmail, $userName)
{
    $mailer = new Mailer();
    
    $subject = 'Welcome to Our Platform!';
    $message = "Hi $userName, welcome to our platform!";
    
    if ($mailer->sendMail($userEmail, $userName, $subject, $message)) {
        echo "Email sent successfully";
    } else {
        echo "Email failed to send";
    }
}
```

## Best Practices

1. **Use environment variables**: Store SMTP credentials in `.env`
2. **Handle errors**: Always check return value
3. **Use templates**: Use HTML email templates for better formatting
4. **Test emails**: Test email sending in development
5. **Queue emails**: Consider queuing emails for better performance

## Next Steps

- [Configuration](/getting-started/configuration.md) - Learn about configuration

