# API Authentication

## Obtain a token

POST JSON to /api/login with username and password for an existing active user in the bundled schema. The response contains data.token and a limited public user record.

```bash
curl http://127.0.0.1:8080/api/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"ama","password":"your-password"}'
```

The framework does not seed credentials or automatically create a user. Add rate limiting before exposing login.

## Send Bearer authentication

```bash
curl http://127.0.0.1:8080/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN"
```

GET /api/admin/users also requires an admin role and rechecks the current account. POST /api/refresh issues another access token only for a valid, active account. It is not a durable refresh-token mechanism.

## Different credentials

JWT identifies your application's user. A Payperlez X-API-Key identifies a business and test/live environment to the external API. A webhook signing secret authenticates inbound events. These are different credentials and must not be substituted for each other.

See [JWT](security/jwt.md) and [Payperlez services](services/configuration.md).

