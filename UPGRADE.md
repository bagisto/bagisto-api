# Upgrading to Bagisto API 2.5

This guide moves a store from the 2.4 line of `bagisto/bagisto-api` (up to `v2.4.4`) to the 2.5 release. Everything below was checked against the code difference between `v2.4.4` and this release.

## 1. Before you start

- Upgrade Bagisto itself to **v2.5.0** first and run its migrations (`php artisan migrate`). This release supports Bagisto v2.5.0 only.
- If you changed the published `config/api-platform.php`, keep a copy of it. The install command in step 3 writes a fresh one.

## 2. Update the package

```bash
composer require bagisto/bagisto-api:^2.5
```

The package now declares `php: ^8.4` and `illuminate/support: ^13.0`, as Bagisto v2.5.0 does. Its API Platform and Symfony requirements are the same as in 2.4.4, so Composer installs them for you. If you upgrade Bagisto core in the same Composer run, add `-W` so Composer can move the shared dependencies.

## 3. Run the installer

```bash
php artisan bagisto-api-platform:install
```

You can run it on a store that already has the API. On an existing install it does the following:

- Re-publishes the package assets and writes a fresh `config/api-platform.php`.
- Leaves the service provider to Composer package discovery, and adds it to `bootstrap/providers.php` only when discovery does not register it.
- Re-applies the API Platform wiring in `bootstrap/app.php` when a Bagisto upgrade replaced that file. The root `composer.json` is no longer edited: the package's own `composer.json` now turns off the package discovery of `api-platform/laravel`.
- Runs `migrate`. It creates the `admin_api_sessions`, `admin_api_refresh_tokens`, `admin_api_tokens` and `admin_api_two_factor_challenges` tables, adds `token_kind` and `channel` to `admin_api_audits` (existing rows that carry a token get `token_kind = integration`), and **drops `admin_personal_access_tokens`**, the Integration token table (see [Admin API authentication](#admin-api-authentication)).
- Creates the ES256 key pair that signs admin access tokens and API tokens (`php artisan bagisto-api:jwt-keys`), unless one exists or the keys come from the environment, and prints the admin login endpoint and the *Settings → API Tokens* screen.
- Keeps an existing storefront key and the `STOREFRONT_*` values in `.env`.
- Clears the caches at the end and caches the configuration and routes again (`php artisan optimize`). After a later `.env` change, run `php artisan optimize` again.

No cache command is needed afterwards, and uploaded files stay in the folders 2.4.4 used. A store served by more than one server needs the same JWT keys on every node; see [Signing keys](#signing-keys).

## 4. Breaking changes

### Admin API permissions

In 2.4.4 most admin **read** endpoints only checked that a token was valid. They now need the same ACL key as the matching admin screen. Review every custom role: a request may do exactly what the admin's role allows, and tokens carry no permissions of their own.

| Endpoint | Key now required |
|---|---|
| Any other admin list or read endpoint | The key of the matching admin screen, for example `catalog.products`, `customers.customers`, `marketing.promotions.cart_rules`, `settings.channels`, `reporting.sales`, `dashboard` |
| Order detail, order comments | `sales.orders.view` |
| Invoice list, detail, PDF and export, and the duplicate invoice email | `sales.invoices.view` |
| Shipment list, detail and export | `sales.shipments.view` |
| Refund list, detail and export | `sales.refunds.view` |
| Transaction list, detail and export, and recording a payment | `sales.transactions.view` |
| Invoice mass status update | `sales.invoices.update` (was `sales.invoices.view`) |
| Bookings | `sales.bookings` (was `sales.bookings.view`) |
| GDPR requests | `customers.gdpr_requests` (was `customers.gdpr_requests.view`) |
| Import stats and import download | `settings.data_transfer.imports.import` |

Granting follows the core admin panel:

- A role can only be created or changed with permissions the calling admin holds. Only an all-access admin can create or edit an all-access role.
- An admin user can only be given a role whose permissions the caller holds. A caller cannot edit or delete an admin whose role is wider than their own.
- An API token can only be created for another admin with `settings.api_tokens.manage_others`, and only when every permission of that admin's role is also held by the creator.
- An admin whose role lacks the permission for an order action (cancel, invoice, ship, refund, reorder) gets `403` before any order-state rule is checked, instead of `422`.
- A resource missing from the admin permission map is refused with `403` instead of being open to any signed-in admin.

### Admin API authentication

**Integration tokens stop working at upgrade.** The `<id>|<secret>` tokens from *Settings → Integration* are removed with their screens, permission types (`all`, `custom`, `same_as_web`), per-token abilities, IP allowlist, notification emails and revoke links, and the migration drops their table. Only their hash was stored and they had no domain, so they cannot be converted. Every client has to move to one of the two new credentials before or right after the upgrade:

| Client | Before | After |
|---|---|---|
| An app a person signs in to (web, mobile, POS) | an Integration token pasted into the app | `POST /api/admin/auth/login` with the admin's email and password; send `accessToken` as the Bearer, renew it with `POST /api/admin/auth/refresh`, and log in again on `refresh_token_invalid` |
| A server integration (ERP, PIM, warehouse) | an Integration token | a new **API token** from *Settings → API Tokens* (or `php artisan bagisto-api:api-token:create`), bound to the domain the integration calls from |

Steps for each server integration:

1. Create, or pick, an admin user with a role that holds only what the integration needs. The token's permissions are that role's, read on every request.
2. Create an API token for that admin with the integration's domain. In the default `dns` binding mode the integration must call from an address the domain resolves to; restrict the trusted proxies at the edge so the client address cannot be forged with `X-Forwarded-For`. The `origin` mode (*Configuration → API → Admin Authentication*) checks the `Origin` or `Referer` host instead.
3. Replace the old token in the integration's secret store. On `401` with `api_token_expired` or `api_token_revoked`, create a new token.

The login, refresh and two-factor flow, the API token domain binding and the client implementation notes are explained step by step in [`docs/authentication.md`](docs/authentication.md).

Other changes clients notice:

- The guard answers with a code in the REST `error` field and in GraphQL `extensions.code`: `access_token_expired`, `unauthenticated`, `refresh_token_invalid`, `api_token_expired`, `api_token_revoked`, `api_token_domain_mismatch`. Logout, the sessions list and `PUT /api/admin/account` need a login session and refuse an API token with `403` and `access_token_required`.
- Rate limits apply per admin for login sessions and per API token, from *Configuration → API → Admin Authentication*; the per-token limits of Integration tokens are gone.
- `GET /api/admin/get` and `readAdminProfile` also return `tokenKind`, `tokenId` and `tokenExpiresAt`.
- The audit history moved from *Settings → Integration → History* to *Settings → API Audit*. Roles that held `integration.history` or `integration.history.delete` need `settings.api_audit` and `settings.api_audit.delete`; roles that managed Integration tokens need `settings.api_tokens` and its children, and `settings.api_sessions` to see and revoke login sessions.
- The Postman environment replaces `adminToken` with `adminEmail`, `adminPassword`, `adminAccessToken`, `adminRefreshToken`, `adminAccessTokenExpiresAt` and `adminApiToken`; import it again.

### Signing keys

Access tokens and API tokens are ES256-signed JWTs. The installer creates the key pair under `storage/app/private/bagisto-api/jwt/` (private key `0600`). That directory is per server: on several servers, or a container image rebuilt on deploy, provide the keys through the environment instead, the same on every node:

```dotenv
BAGISTO_API_JWT_PRIVATE_KEY="-----BEGIN EC PRIVATE KEY-----\n...\n-----END EC PRIVATE KEY-----"
BAGISTO_API_JWT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
```

Both accept PEM or base64-encoded PEM, and the key must be on the P-256 curve. If the keys are lost, every access token and API token stops verifying: apps log in again and every API token has to be created again. To rotate stored keys, run `php artisan bagisto-api:jwt-keys --rotate`; issued tokens keep working, and API tokens signed by the old key are listed. `--reissue-api-tokens` signs them again and prints each new token once, for the integrations to switch to. With environment keys, set the new pair and move the old public key into `BAGISTO_API_JWT_PREVIOUS_PUBLIC_KEYS` (a JSON object of key id to PEM).

### Admin dashboard and reports

These `?type=` values were removed: dashboard `total-visitors`, customer report `customers-traffic` and product report `products-with-most-visits`. Core has no method behind them, so they always returned 500. They now answer with the invalid-type error.

### Storefront authentication and cart

- A customer is identified by the Sanctum bearer token from login only. The `customers.token` and `api_token` values are no longer accepted as a bearer token. `customers.token` is core's email-verification token.
- Merging a guest cart needs the guest's `guestCartToken`. A bare `cartId` is not enough.
- Reading a cart uses the cart of the bearer token. A `cartId` without a matching token returns no other cart.
- When email verification is on, registration returns `token: null` and a `message` asking the customer to verify the email.
- Deleting a customer account needs the account password. It is refused while the customer has pending or processing orders.

### Headers, keys and limits

- An unknown `X-Channel` header returns `400` with `error: invalid_channel`. Before, it was ignored.
- A GraphQL `GET` request that carries a `query` needs the storefront key, like `POST`. Opening the playground needs no key.
- The storefront key rate limit is counted per key and per client IP. Configure trusted proxies so the real client IP reaches the application.
- On every list, `first` is limited to 1–50.

### Checkout

- The payment method has to be one the cart is offered. `paypal_smart_button` is refused, because it captures the payment in its own client-side flow.

### Removed fields

- Product `cost`.
- Downloadable link `url` and `fileName`. Downloads go through the download endpoints.
- Channel `allowedIps`.

### Visibility

- Only approved reviews are returned, by the review list and by the single-review lookup on REST and GraphQL. A client `status` filter is ignored.
- A single product (`GET /api/shop/products/{id}`, GraphQL `product`) is returned only when the storefront would show it: assigned to the channel, enabled and visible individually. An enabled variant of such a product can still be opened by id or SKU.
- CMS pages from another channel are not returned. Disabled categories and categories outside the channel root are not listed. The category tree starts at the channel root, and `depth` is limited to 1–6.

## 5. Unchanged for clients

On Bagisto 2.5, flag fields such as customer `status`, `isVerified` and `isSuspended` keep their `0`/`1` values and `String` type, and `dateOfBirth` keeps the `Y-m-d` format. Clients written for 2.4.4 read the same shapes.

## 6. Theme endpoints

On Bagisto v2.5.0 the theme endpoints are `/api/admin/appearance/themes`, `/api/admin/appearance/sections`, `/api/shop/sections` and `/api/shop/theme`, and they need the `appearance.*` permissions. The installer in step 3 clears the caches, so this set is picked up.

## 7. Admin uploads and rich text (unreleased)

- HTML sent to the admin API for CMS pages, email templates, category descriptions and WYSIWYG product attributes is now cleaned with core's `clean_content()` before it is saved. Content that was saved through the API before this change keeps its markup until it is next saved; open and save it again, through the API or the admin panel, to clean it.
- Product images uploaded through the admin API are stored as webp under a generated name in `products/{id}/`, as the admin panel stores them. Images uploaded earlier stay where they are.

## 7a. Configuration clean-up (unreleased)

- The `rate_limit` block of `config/api-platform.php` and the `RATE_LIMIT_*` environment variables are gone. They were read only by a middleware that no route used, so removing them changes no limit. If you set them, delete them from `.env`; the published `config/api-platform.php` may keep the block, which is now ignored.
- The `security_headers` values of `config/api-platform.php` now apply. Check `csp_header` in your published copy: a custom value replaces the default Content-Security-Policy, and `enabled => false` stops the API's own headers (Bagisto core still sends its `X-Frame-Options`, `Referrer-Policy` and `Strict-Transport-Security`).
- The audit retention moved to *Configuration → API → Admin Authentication → Data Retention*. `BAGISTO_API_AUDIT_RETENTION_DAYS` is still used while that field is empty, and `bagisto-api:prune-audits` now runs daily through the scheduler.

## 7b. Storefront customer tokens (unreleased)

Customers no longer authenticate with Laravel Sanctum tokens. The installer's `migrate` deletes the customers' rows from `personal_access_tokens`, so every signed-in customer has to log in again.

- Login, registration and social login return `accessToken` (a JWT, 15 minutes by default) and `refreshToken` (30 days by default). Send the access token as `Authorization: Bearer <accessToken>`; before it expires, or when a request answers `401 access_token_expired`, call `POST /api/shop/customer/refresh` with the refresh token and store both new tokens. A refresh token works once: sending a used one ends the session.
- `token` in the login answer is the access token, kept for one release so old clients keep working until they refresh; switch to `accessToken`.
- A refused login is now an HTTP error (`401 invalid_credentials`, `403 account_inactive` or `account_unverified`, `429 too_many_login_attempts`) instead of `201` with `success: false`.
- An access token is tied to the channel it was issued on.
- A request with an expired access token answers `401 access_token_expired` on every storefront operation, and one with a revoked session or another channel's token `401 unauthenticated`, before the operation runs. Refresh on the first, log in again on the second.
- To keep a guest's cart, send its cart token as `guest_cart_token` on the login; the answer's `cartId` is the customer's cart. `POST /api/shop/merge-carts` still works.
- Re-import the Postman environment: `customerToken` is now `customerAccessToken`, with `customerRefreshToken` and `customerAccessTokenExpiresAt`.
- The lifetimes and limits are set under *Configuration → API → Customer Authentication*.
- `POST /api/admin/customers/{id}/impersonate` and `createAdminCustomerImpersonate` are removed. An admin views and ends a customer's sessions under *Customers → API Sessions*, or through `/api/admin/customers/api-sessions`; grant roles `customers.api_sessions` and `customers.api_sessions.delete` for it.

## 8. Admin RMA message attachments

Earlier versions saved the files an admin attached to an RMA message through `POST /api/admin/rma/messages` on the default disk, which is `public` on a standard store, under `rma-conversation/{message id}/`. Anyone with the link can download those files from `/storage/rma-conversation/...`. New uploads go to the `private` disk under `rmas/{rma id}/conversations/{message id}/`, as the admin panel stores them.

The upgrade does not move the old files. They keep working: `GET /api/admin/rma/messages/{id}/attachment` reads the private disk first and falls back to the default disk, as core does. To stop serving them publicly, move them to the private disk during a maintenance window. The stored paths stay valid when the folder keeps its relative path, so no database change is needed:

```bash
mkdir -p storage/app/private
mv storage/app/public/rma-conversation storage/app/private/
```

On a store whose disks are not local (for example S3), copy the `rma-conversation/` prefix from the default disk to the private disk the same way and delete the public copies.

The same folder also holds the files customers attached through the storefront API (`POST /api/shop/return-messages`), which this release still stores publicly. Their `attachmentUrl` in the storefront return messages is a `/storage/...` link, so a moved customer file stops opening from that link. Move the folder only if the storefront clients do not rely on those links.
