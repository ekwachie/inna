# Authentication and Authorization

Authentication identifies an actor. Authorization decides whether that actor may perform an action on a particular resource.

## Session applications

Use AuthMiddleware for signed-in pages and AdminMiddleware for admin-only actions. The session user can be a flat array or a legacy one-row array. It must contain a non-empty id or username; admin access requires an explicit admin role.

After password verification, regenerate the session ID and store only public identity fields:

```php
use app\Core\Utils\Session;

Session::regenerate();
Session::set('user', ['id' => $userId, 'username' => $username, 'role' => 'user']);
```

Never accept roles or user IDs supplied by an untrusted request. Reload access state for sensitive operations when roles may have changed.

## Passwords

Use DUtil::passHash and DUtil::passVerify, which wrap PHP password APIs. Validate a password without HTML-escaping or modifying it. Never store plaintext passwords or include hashes in sessions and JSON responses.

## APIs

The starter exposes POST /api/login, GET /api/profile, GET /api/admin/users and POST /api/refresh using the bundled users schema. Login requires JSON, checks is_active/is_suspended, and returns a JWT. Admin listing and refresh recheck current database account state.

These are starter endpoints, not a complete account system: add rate limiting at your edge or application, recovery, verification, MFA and audit policy as your product requires. Do not assume the legacy AuthController has complete routed login/register views.

## Access boundaries

Every private download must check ownership before reading a storage ID. A random ID is not permission. Use [CSRF protection](security/middleware.md) for session-authenticated writes and [JWT](security/jwt.md) for Bearer APIs. Signed webhooks use a separate verification mechanism.

Do not publish live keys or signing secrets in templates, browser JavaScript, logs, examples or repository files.

