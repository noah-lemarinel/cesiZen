# API Authentication (JWT) — setup instructions

This project adds an `/api/login` endpoint that returns a JSON Web Token (JWT) for native/mobile clients.

What was added in the codebase:

- `config/packages/lexik_jwt_authentication.yaml` — configuration for LexikJWTAuthenticationBundle
- New firewall `api` in `config/packages/security.yaml` that protects routes under `/api` and expects JWT tokens
- `src/Controller/ApiAuthController.php` — `/api/login` POST endpoint accepting JSON `{ "email": "...", "password": "..." }` and returning `{ "token": "...", "user": { ... } }` on success

Important: the repository does not (yet) include the Lexik JWT bundle. You must install it and generate keys.

Quick install and setup

1) Install Lexik JWT bundle via Composer

```bash
composer require "lexik/jwt-authentication-bundle:^2.20"
```

(Version may vary; use a version compatible with your Symfony version.)

2) Generate the JWT keys

Create a `config/jwt` directory and generate an RSA keypair. Pick a secure passphrase.

```bash
mkdir -p config/jwt
# generate a 4096-bit private key (recommended) and a public key
# you'll be asked for a passphrase (use something secure) — we'll store it in JWT_PASSPHRASE
openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
# secure permissions
chmod 640 config/jwt/private.pem
chmod 644 config/jwt/public.pem
```

If you prefer to generate the private key with a passphrase using openssl directly:

```bash
openssl genpkey -algorithm RSA -aes256 -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

3) Set the passphrase in your environment

Add the following to your `.env.local` (do NOT commit this file):

```ini
JWT_PASSPHRASE="your-secret-passphrase-here"
```

4) Clear cache / warmup

```bash
php bin/console cache:clear
```

5) Usage (login)

Request a token by POSTing JSON to `/api/login`:

```bash
curl -X POST https://your-host.example/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"thepassword"}'
```

Success response (HTTP 200):

```json
{
  "token": "eyJ0eXAi...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "name": "..."
  }
}
```

6) Use the token on subsequent requests

Include it in the `Authorization` header:

```bash
curl -H "Authorization: Bearer <token>" https://your-host.example/api/some-protected-resource
```

In a browser-based SPA you may use `credentials: 'include'` to reuse session cookies, but for native mobile apps you should persist the JWT on the device and send it as a `Authorization: Bearer` header on requests.

Notes & troubleshooting

- If you get an error about the lexik service not found, ensure the bundle is installed and registered (Symfony Flex should do this automatically when installing the bundle).
- If you prefer not to use Lexik, you can implement your own JWT creation and verification using `firebase/php-jwt`, but Lexik integrates with Symfony security and is recommended.
- For long-lived refresh flows, consider implementing refresh tokens or short TTLs and a refresh endpoint.

Security reminders

- Do not commit `config/jwt/private.pem` or your passphrase to the repository.
- Use HTTPS in production for all API requests.
- Consider rotating keys periodically.

If you want, I can also:
- Add a `/api/refresh` endpoint and refresh-token storage
- Add example React Native or React fetch code that obtains and stores the token
- Add tests for the `/api/login` endpoint

Tell me which follow-up you'd like next.

