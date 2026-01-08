# Email Templates

This directory contains HTML email templates used by the `Mailer` class.

## Template Files

### `default.html`
The default email template with Tailwind-inspired design. This template uses placeholder variables that are replaced with actual values when the email is sent.

## Available Placeholders

The following placeholders can be used in email templates:

- `{{APP_NAME}}` - Application name (from `APP_NAME` env variable or default: "Inna Framework")
- `{{FNAME}}` - Recipient's first name
- `{{MESSAGE}}` - Email message content (automatically converts newlines to `<br>` tags)
- `{{MAIL_TO}}` - Recipient's email address
- `{{CURRENT_YEAR}}` - Current year (e.g., 2026)
- `{{APP_URL}}` - Application base URL (from `BASE_URL` constant)

## Usage

The `Mailer::getEmailTemplate()` method automatically:
1. Loads the template from `app/templates/email/default.html`
2. Replaces all placeholders with actual values
3. Applies HTML escaping for security
4. Returns the final HTML string

## Creating Custom Templates

To create a custom email template:

1. Create a new HTML file in this directory (e.g., `welcome.html`, `notification.html`)
2. Use the same placeholder format: `{{PLACEHOLDER_NAME}}`
3. Update the `Mailer::getEmailTemplate()` method to load your custom template, or add a parameter to specify which template to use

## Template Design Guidelines

- Use inline CSS (email clients don't support external stylesheets)
- Use table-based layouts for better email client compatibility
- Test templates in multiple email clients (Gmail, Outlook, Apple Mail, etc.)
- Keep images small and use absolute URLs for images
- Use web-safe fonts with fallbacks

## Example

```html
<p>Hello {{FNAME}},</p>
<p>{{MESSAGE}}</p>
<p>Best regards,<br>The {{APP_NAME}} Team</p>
```

