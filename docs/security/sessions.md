# Sessions

Session::init runs during application construction. It configures a one-hour cookie lifetime and garbage-collection lifetime, HttpOnly cookies, SameSite=Lax and strict session mode. Secure follows the server's HTTPS flag.

## Read and write

```php
use app\Core\Utils\Session;

Session::set('cart', ['project' => 1]);
$cart = Session::get('cart');
$exists = Session::issert('cart');
Session::unsert('cart');
```

get returns null for absent values. issert is the existing helper name, not a typo for isset in your application code.

## Login and logout

Call Session::regenerate() after a successful login and before storing identity. Call Session::destroy() to remove data and expire the session cookie. Do not store password hashes, provider keys or file encryption keys in the session.

The one-hour settings are not a guaranteed idle-session cutoff; enforce inactivity and absolute expiry explicitly when your product needs them. Use shared session storage or sticky routing deliberately on multiple servers.

## Reverse proxies

Terminate HTTPS correctly and configure the web server to convey trusted HTTPS state. Do not let a client-supplied forwarding header determine security-sensitive settings. TRUSTED_PROXY_IPS applies to client-IP extraction, not automatic web-server HTTPS configuration.

