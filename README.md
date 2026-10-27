# Bagisto API Platform

Comprehensive REST and GraphQL APIs for seamless e-commerce integration and extensibility.

[![Run in Postman](https://run.pstmn.io/button.svg)](https://www.postman.com/bagisto-apis/bagistoapi)

## Requirements

- [Bagisto](https://github.com/bagisto/bagisto) **v2.5.0**

## Installation

```bash
composer require bagisto/bagisto-api
php artisan bagisto-api-platform:install
```

The installer publishes the configuration and assets, runs the migrations, creates a storefront key and writes it to `.env`, clears the caches and caches the configuration and routes again, so the APIs work as soon as it finishes.

It also edits application files: it registers the API Platform providers in `bootstrap/app.php`, writes the `STOREFRONT_*` settings to `.env`, makes core's `packages/Webkul/Core/src/Eloquent/TranslatableModel.php` abstract, and adds the service provider to `bootstrap/providers.php` when package discovery does not register it.

Upgrading from the 2.4 line? See [`UPGRADE.md`](UPGRADE.md).

### Access Points

- **API Landing**: [https://your-domain.com/api](https://api-demo.bagisto.com/api)
- **REST API (Shop)**: [https://your-domain.com/api/shop/](https://api-demo.bagisto.com/api/shop)
- **REST API (Admin)**: [https://your-domain.com/api/admin/](https://api-demo.bagisto.com/api/admin)
- **REST API Docs (Shop)**: [https://your-domain.com/api/shop/docs](https://api-demo.bagisto.com/api/shop/docs)
- **REST API Docs (Admin)**: [https://your-domain.com/api/admin/docs](https://api-demo.bagisto.com/api/admin/docs)
- **GraphQL Playground (Shop)**: [https://your-domain.com/api/graphiql](https://api-demo.bagisto.com/api/graphiql)
- **GraphQL Playground (Admin)**: [https://your-domain.com/api/admin/graphiql](https://api-demo.bagisto.com/api/admin/graphiql)

## Exporting the API Schema

Generate schema files for the shop and admin APIs — OpenAPI JSON (REST) and GraphQL SDL — to import into Postman, a client/code generator, or a mock server without calling a live server:

```bash
php artisan bagisto-api-platform:export-schema
```

The files are written to `schema/generated/`:

| File | Contents |
|---|---|
| `openapi-shop.json` / `openapi-admin.json` | OpenAPI for each REST surface |
| `shop.graphql` / `admin.graphql` | GraphQL SDL for each surface |
| `graphql-operations-shop.json` / `-admin.json` | Every root GraphQL field mapped to its resource tag |

Each spec is scoped to its own surface — the storefront spec carries no admin path, schema or tag, and the reverse — and the command refuses to write one that leaks the other surface or references a definition it does not include.

Options:

- `--path=<dir>` — write elsewhere
- `--transport=all|rest|graphql` — limit to one transport (default `all`)

Re-running overwrites those six files and nothing else. To rebuild the Postman collections from them:

```bash
php schema/tools/build-collection.php
```

## Postman Collections

Run them from the [public workspace](https://www.postman.com/bagisto-apis/bagistoapi), or import the copies that ship with the package:

- `collections/Bagisto-Shop-API.postman_collection.json` — storefront: browsing, cart, checkout, orders, account
- `collections/Bagisto-Admin-API.postman_collection.json` — admin, foldered to mirror the admin sidebar
- `environments/Bagisto.postman_environment.json` — one environment for both

Both transports are covered: requests are foldered `REST/` and `GraphQL/` under the same resource names, with GraphQL split into `Queries/` and `Mutations/`.

### Importing

1. Postman → **Import** → pick a file from `collections/`.
2. Postman → **Environments** → **Import** → pick `environments/Bagisto.postman_environment.json`, select it, then fill in your values.

| Variable | Used by | Description |
|---|---|---|
| `url` | Both | Your Bagisto URL, e.g. `http://localhost:8000` |
| `storefrontKey` | Shop | Storefront API key: `STOREFRONT_PLAYGROUND_KEY` in `.env` (written by the installer), or a key from `php artisan bagisto-api:generate-key` |
| `customerEmail` | Shop | A storefront customer's email |
| `customerPassword` | Shop | That customer's password |
| `customerAccessToken` | Shop | Filled in by the customer login, registration, social login and refresh requests |
| `customerRefreshToken` | Shop | Filled in by the same requests |
| `customerAccessTokenExpiresAt` | Shop | Filled in by the same requests |
| `cartToken` | Shop | Filled in by the Create cart token request |
| `adminEmail` | Admin | An admin user's email, for the login request |
| `adminPassword` | Admin | That admin's password |
| `adminAccessToken` | Admin | Filled in by the login, two-factor and refresh requests |
| `adminRefreshToken` | Admin | Filled in by the same requests |
| `adminAccessTokenExpiresAt` | Admin | Filled in by the same requests |
| `adminApiToken` | Admin | Optional API token from admin → Settings → API Tokens; when set it is sent instead of the login session |
| `locale` | Both | Locale code, defaults to `en` |
| `channel` | Both | Channel code, defaults to `default` |
| `currency` | Shop | Currency code, defaults to `USD` |

The admin collection also keeps one collection variable, `adminChallengeToken`, which a login that needs a second factor fills in for the two-factor request.

Fill in only what the collection you are using needs.

### Authenticating

Both flows are explained step by step, with diagrams and curl and GraphQL examples, in [`docs/authentication.md`](docs/authentication.md).

**Shop** — fill in `customerEmail` and `customerPassword` and run **REST → Customer Auth → Customer login**. It stores `customerAccessToken`, `customerRefreshToken` and `customerAccessTokenExpiresAt`, every other request sends the access token as the bearer, and before each request the collection refreshes it when it expires within 60 seconds. For a guest cart, run **Create cart token** instead and set the collection's **Authorization** tab to `{{cartToken}}`; the cart and checkout endpoints serve guests and logged-in customers alike, so the token you send decides whose cart you are working on.

**Admin** — fill in `adminEmail` and `adminPassword` and run **REST → Auth → Log in with email and password**. It stores `adminAccessToken`, `adminRefreshToken` and `adminAccessTokenExpiresAt`, and before each request the collection refreshes the access token when it expires within 60 seconds. For an admin with two-factor authentication the login stores a challenge token instead: enter the code in **REST → Auth → Finish a login with a two-factor code** and send it. The **Auth** folder lists its requests in the order a client uses them. To call the API as a server integration instead, paste an API token into `adminApiToken`; it is then sent on every request and nothing is refreshed. See [Admin API Authentication](#admin-api-authentication).

### Keeping them current

`schema/tools/build-collection.php` regenerates the collections from the exported schemas, so a collection follows the API rather than being maintained by hand. After `bagisto-api-platform:export-schema` writes a new schema, rerun the builder and commit both — **Validate** rebuilds the collections in CI and fails the push if the committed copies no longer match, so a stale collection cannot reach the workspace.

Three workflows keep the published copies in step: **Validate** checks the files and that rebuild on every push, **Push Collections to Postman** publishes them to the official workspace when they change on `main`, and **Sync Collections from Postman** pulls edits made in Postman back into the repository on a release. All three refuse to move a real storefront key.

Publishing needs four repository secrets, and the push and sync jobs skip with a notice while any of them is missing rather than failing the build:

| Secret | Value |
|--------|-------|
| `POSTMAN_API_KEY` | A Postman API key with write access to the workspace |
| `POSTMAN_SHOP_COLLECTION_ID` | UID of the Bagisto Shop API collection |
| `POSTMAN_ADMIN_COLLECTION_ID` | UID of the Bagisto Admin API collection |
| `POSTMAN_ENVIRONMENT_ID` | UID of the Bagisto environment |

Collection and environment UIDs come from Postman — open the item, then **Info → ID**. Validation needs no secret and runs on every push regardless.

Values in the requests are placeholders — replace them with records that exist on your store. Postman's **Auto Fetch** runs a schema introspection query that costs far more than a normal request; switch it off if the GraphQL folders feel slow.

## Admin API Authentication

The complete guide to both surfaces — the admin login, two-factor, refresh and reuse detection, sessions, API tokens, the storefront key, the customer access and refresh tokens and the guest cart token — is [`docs/authentication.md`](docs/authentication.md). This section is the summary.

Admin endpoints (`/api/admin/*` and `/api/admin/graphql`) take a Bearer token, and every request is allowed or refused by the admin user's **role permissions**, the same permissions the admin panel uses, read on every request. There are two kinds of token, and both work over REST and GraphQL alike.

| | Apps (web, mobile, POS, desktop) | Server integrations (ERP, PIM, warehouse) |
|---|---|---|
| Credential | email and password, through login | an **API token** created in the admin panel |
| Bearer token | a JWT **access token**, 15 minutes by default | the API token, until its expiry date or with no expiry |
| Renewal | a **refresh token**, 30 days by default, exchanged for a new pair | none: create a new token when it expires or is revoked |
| Bound to | a session, listed and revoked on its own | one domain |

Lifetimes, rate limits and the revocation rules are set in **Configuration → API → Admin Authentication**.

### Apps: log in and refresh

```http
POST /api/admin/auth/login
Content-Type: application/json

{ "email": "admin@example.com", "password": "secret", "device_name": "iPhone", "client_type": "mobile" }
```

The answer carries `accessToken`, `accessTokenExpiresAt`, `refreshToken`, `refreshTokenExpiresAt`, the `session`, the `admin` and the role's `permissions`. Send the access token as `Authorization: Bearer <accessToken>`. `client_type` is one of `web`, `mobile`, `pos`, `desktop` or `other`; each login is its own session, so the same admin can be signed in on several devices at once.

A client should:

1. Store both tokens in secure storage (the Keychain or Keystore on mobile), never in plain local storage.
2. Before a request, read `exp` from the access token (or `accessTokenExpiresAt`) and, when it is about to pass, call `POST /api/admin/auth/refresh` with `{ "refresh_token": "<refreshToken>" }`. The refresh token is read from the body only. Every refresh returns a new pair; the old refresh token stops working, and presenting it again revokes the whole session. Let only one refresh run at a time: two parallel refreshes with the same token count as reuse.
3. On `401` with `access_token_expired`, refresh once and retry the request.
4. On `401` with `refresh_token_invalid` (expired, used or revoked, or the session reached its absolute lifetime of 90 days by default), log in again.

A minimal client (the guide has a single-flight version that also refreshes ahead of expiry):

```js
async function adminFetch(path, options = {}) {
  let response = await send(path, options, tokens.accessToken);

  if (response.status === 401 && (await response.clone().json()).error === 'access_token_expired') {
    const refreshed = await fetch(`${baseUrl}/api/admin/auth/refresh`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ refresh_token: tokens.refreshToken }),
    });

    if (!refreshed.ok) {
      return redirectToLogin();
    }

    tokens = await saveTokens(await refreshed.json());
    response = await send(path, options, tokens.accessToken);
  }

  return response;
}
```

An admin with two-factor authentication enabled gets `202` with `twoFactorRequired: true` and a `challengeToken` instead of tokens; send it with the 6-digit `code` (or a `backup_code`) to `POST /api/admin/auth/two-factor` within 5 minutes (configurable) to receive the pair. `POST /api/admin/auth/logout` ends the current session, `POST /api/admin/auth/logout-all` every session of the admin, `GET /api/admin/auth/sessions` lists them and `DELETE /api/admin/auth/sessions/{id}` revokes one. `PUT /api/admin/account` updates the admin's own name, email and password. These five need a login session's access token; an API token gets `403` with `access_token_required`. Forgotten passwords go through `POST /api/admin/auth/forgot-password` and `POST /api/admin/auth/reset-password`, which, like login, two-factor and refresh, need no token. The GraphQL equivalents are `createAdminLogin`, `createAdminTwoFactorVerify`, `createAdminTokenRefresh`, `createAdminLogout`, `createAdminLogoutAll`, `adminSessions`, `deleteAdminSession`, `createAdminAccountUpdate`, `createAdminForgotPassword` and `createAdminResetPassword`, on `/api/admin/graphql`.

### Server integrations: API tokens

1. Create a dedicated admin user for the integration, with a role that holds only the permissions it needs.
2. In the admin panel, open **Settings → API Tokens → Create**, choose that admin, a name, the **domain** the integration calls from (for example `erp.example.com`) and an expiry date or no expiry. The CLI does the same: `php artisan bagisto-api:api-token:create --admin=<id|email> --name=ERP --domain=erp.example.com [--expires=YYYY-MM-DD|never]`.
3. Copy the token, which is shown **once**, into the integration's secret store, and send it as `Authorization: Bearer <api token>`. There is no login and no refresh.
4. On `401` with `api_token_expired` or `api_token_revoked`, create a new token and replace the old one.

The domain binding is checked on every request. In the default `dns` mode the request must come from an address the domain's A or AAAA records point to; the token's page shows the addresses it resolved. `dns` mode trusts the client address Laravel reports, and Bagisto trusts every proxy by default, so on a server that can be reached without its proxy restrict the trusted proxies at the edge, or `X-Forwarded-For` can be forged. The `origin` mode, chosen in the configuration, compares the `Origin` or `Referer` host instead and only means something for browser callers. A request from elsewhere gets `403` with `api_token_domain_mismatch`.

API tokens can be listed, read and revoked through `GET`/`DELETE /api/admin/settings/api-tokens[/{id}]` (GraphQL `adminApiTokens`, `adminApiToken`, `deleteAdminApiToken`) but are created only in the panel or the CLI. Login sessions of every admin are listed and revoked under **Settings → API Sessions**, and every authentication event is recorded under **Settings → API Audit**.

### Errors

| Status and `error` | Meaning | What to do |
|---|---|---|
| `401 access_token_expired` | The access token passed its expiry | Refresh, then retry |
| `401 refresh_token_invalid` | The refresh token is expired, used or revoked, or the session ended | Log in again |
| `401 unauthenticated` | No token, an invalid token, a revoked session or an inactive admin | Log in again |
| `401 invalid_credentials` | Wrong email or password at login | Ask again |
| `401 invalid_two_factor_code` / `two_factor_challenge_invalid` | Wrong code / the challenge is used, expired or revoked | Retry the code / log in again |
| `401 api_token_expired` / `api_token_revoked` | The API token can no longer be used | Create a new API token |
| `403 api_token_domain_mismatch` | The API token was used from another domain | Call from the bound domain |
| `403 access_token_required` | An API token called logout, sessions or the account update | Use a login session's access token |
| `403 account_inactive` | The admin is inactive | Activate the admin |
| `403` | The admin's role lacks the permission | Grant the permission to the role |
| `429 too_many_login_attempts` / `too_many_password_requests` / `rate_limit_exceeded` | Too many failed logins or codes, password requests, or requests | Wait for the `Retry-After` seconds |

REST bodies carry the code in `error`; GraphQL errors carry it in `extensions.code`. The full table, with the body shapes, is in [`docs/authentication.md`](docs/authentication.md#errors).

### Customers

Storefront customers follow the same model through `/api/shop`: `POST /api/shop/customer/login` (GraphQL `createCustomerLogin`), registration and social login return an access token and a refresh token, `POST /api/shop/customer/refresh` renews them, and `logout`, `logout-all` and `GET`/`DELETE /api/shop/customer/sessions` end and list sessions. Login takes an optional `guest_cart_token` that merges the guest's cart into the customer's. Every request also carries the storefront key (`X-STOREFRONT-KEY`). Lifetimes and limits are set in **Configuration → API → Customer Authentication**; administrators list and end customer sessions under **Customers → API Sessions**. See [`docs/authentication.md`](docs/authentication.md#customer-login).

### Signing keys

Access tokens and API tokens are ES256-signed JWTs. `bagisto-api-platform:install` creates the key pair with `php artisan bagisto-api:jwt-keys` under `storage/app/private/bagisto-api/jwt/`. On several servers, every node needs the same keys: set `BAGISTO_API_JWT_PRIVATE_KEY` and `BAGISTO_API_JWT_PUBLIC_KEY` (PEM or base64-encoded PEM) instead. `bagisto-api:jwt-keys --rotate` makes a new signing key and keeps the old public key, so issued tokens keep working; API tokens signed by the old key are listed, and `--reissue-api-tokens` signs them again and prints each new token once.

Keys set through the environment are rotated in the environment, not with `--rotate`:

1. Run `php artisan bagisto-api:jwt-keys` and note the current key id it prints. That is `BAGISTO_API_JWT_KID` when you set one, otherwise the key's SHA-256 thumbprint.
2. Set the new pair in `BAGISTO_API_JWT_PRIVATE_KEY` and `BAGISTO_API_JWT_PUBLIC_KEY`, and change `BAGISTO_API_JWT_KID` too if you use it.
3. Move the old public key into `BAGISTO_API_JWT_PREVIOUS_PUBLIC_KEYS`, a JSON object of key id to PEM (or base64-encoded PEM). Several old keys can be listed.
4. Clear and rebuild the config cache (`php artisan bagisto-api-platform:optimize`) on every node.

```dotenv
BAGISTO_API_JWT_PREVIOUS_PUBLIC_KEYS='{"3sKcJ9vV0yWq4mXo8hTzN2aLpE6rUbFd1gYiQ7cHk5s":"LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0K..."}'
```

API tokens signed by an old key keep verifying while its public key is listed there; run `bagisto-api:jwt-keys --reissue-api-tokens` to sign them with the new key, then remove the old entry. Access tokens signed by an old environment key are refused straight away, so admin and customer apps get `401` once and recover by calling refresh, because refresh tokens are not signed. Removing an entry stops every token signed by that key.

## Documentation
- Authentication guide: [`docs/authentication.md`](docs/authentication.md)
- Bagisto API: [Demo Page](https://api-demo.bagisto.com/api)
- API Documentation: [Bagisto API Docs](https://api-docs.bagisto.com/)
- GraphQL Playground (Shop): [Interactive Playground](https://api-demo.bagisto.com/api/graphiql)
- GraphQL Playground (Admin): [Interactive Playground](https://api-demo.bagisto.com/api/admin/graphiql)
- Release history: see [`CHANGELOG.md`](CHANGELOG.md)
- Upgrading from 2.4: see [`UPGRADE.md`](UPGRADE.md)
 
## Support

For issues and questions, please visit:
- [GitHub Issues](https://github.com/bagisto/bagisto-api/issues)
- [Bagisto Documentation](https://bagisto.com/docs)
- [Community Forum](https://forum.bagisto.com)

## 📝 License

The Bagisto API Platform is open-source software licensed under the [MIT license](LICENSE).
