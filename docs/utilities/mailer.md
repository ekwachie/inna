# Mail

Mailer sends an HTML email through PHPMailer and the bundled app/templates/email/default.html template. Configure SMTP before calling it; merely booting Inna does not send mail.

## Configure

Set SMTP_HOST, SMTP_PORT=587, SMTP_USERNAME, SMTP_PASSWORD, SMTP_FROM_ADDRESS and SMTP_FROM_NAME in .env. The helper uses STARTTLS and authenticates when a username is configured. Use an authorized sender address. It does not disable certificate verification.

## Send

```php
use app\Core\Utils\Mailer;

$sent = (new Mailer())->sendMail(
    'ama@example.com',
    'Ama',
    'Project created',
    'Your project is ready.'
);
```

The template substitutes the application name, recipient, greeting, message, year and base URL. Recipient/name/message content is HTML-escaped; messages preserve line breaks. sendMail returns a boolean on completion, but PHPMailer/configuration errors can throw. Handle them at the application boundary without exposing credentials.

## Delivery workflows

This helper sends synchronously and does not provide a queue, idempotency or delivery receipts. For important notifications, persist a notification/outbox record and process it with your own worker. Avoid sending mail inside a long-running database transaction.

Customize the HTML template in app/templates/email/default.html and verify it in the email clients your application supports.

