<?php

namespace Webkul\BagistoApi\OpenApi;

class ApiGuide
{
    /**
     * The Markdown guide at the top of the admin OpenAPI document.
     */
    public const ADMIN = <<<'MARKDOWN'
Administrative REST API of Bagisto: every admin panel menu, under `/api/admin`. The same operations are available over GraphQL at `POST /api/admin/graphql`.

## Authentication

Every operation takes `Authorization: Bearer <token>`, except the five public sign-in operations (`POST /auth/login`, `/auth/two-factor`, `/auth/refresh`, `/auth/forgot-password` and `/auth/reset-password`). There are two kinds of token, and each operation lists the ones it accepts under **security**:

| Token | For | How to get it | Lifetime |
|---|---|---|---|
| `adminAccessToken` | apps that sign in: web, mobile, POS, desktop | `POST /auth/login`, then `POST /auth/refresh` | 15 minutes by default, renewed with a refresh token (30 days by default) |
| `adminApiToken` | server integrations: ERP, PIM, warehouse | *Settings → API Tokens* in the admin panel, or `php artisan bagisto-api:api-token:create` | until its expiry date, or no expiry |

Nothing about permissions is in a token: every request is allowed or refused by the admin's **role permissions**, read on every request, as in the admin panel. The lifetimes, the rate limits and the revocation rules are set in the admin panel under **Configuration → API → Admin Authentication**.

### Apps: log in, call, refresh

1. `POST /auth/login` with `email`, `password` and optionally `device_name` and `client_type` (`web`, `mobile`, `pos`, `desktop`, `other`). The answer (`200`) carries `accessToken`, `expiresIn` (seconds), `accessTokenExpiresAt`, `refreshToken`, `refreshTokenExpiresAt`, the `session`, the `admin` and the role's `permissions`. Each login is its own session.
2. Send `Authorization: Bearer <accessToken>` on every request.
3. Before `expiresIn` runs out, or when a request answers `401` with `error: access_token_expired`, call `POST /auth/refresh` with `{ "refresh_token": "<refreshToken>" }` and retry. Every refresh returns a **new pair**: store both, as the old refresh token stops working, and presenting a used refresh token again revokes the whole session.
4. When the refresh answers `401` with `error: refresh_token_invalid` (expired, used or revoked, or the session reached its absolute lifetime of 90 days by default), log in again.
5. `POST /auth/logout` ends the session, `POST /auth/logout-all` every session of the admin. `GET /auth/sessions` lists the admin's live sessions and `DELETE /auth/sessions/{id}` revokes one.

Keep both tokens in secure storage, and let only one refresh run at a time: two parallel refreshes with the same refresh token count as reuse and end the session.

### Two-factor authentication

An admin with two-factor authentication enabled gets `202` from the login, with `twoFactorRequired: true`, a single-use `challengeToken` valid for 5 minutes, and every token field `null`. Send `{ "challenge_token": "<challengeToken>", "code": "123456" }` (or a `backup_code` instead of `code`) to `POST /auth/two-factor`; its `200` answer is the same token pair as a login. Five wrong codes revoke the challenge (`401 two_factor_challenge_invalid`): log in again.

### Server integrations: API tokens

An API token acts as one admin user with that admin's role permissions, and works only from the **domain** it is bound to. In the default `dns` mode the request must come from an IP address the domain resolves to; the `origin` mode compares the `Origin` or `Referer` host instead. A request from elsewhere is refused with `403 api_token_domain_mismatch`. There is no login and no refresh: on `401 api_token_expired` or `api_token_revoked`, create a new token. The API lists, reads and revokes API tokens under `/settings/api-tokens` but never creates them. API tokens cannot call the session operations (`/auth/logout`, `/auth/logout-all`, `/auth/sessions`, `/account`), which answer `403 access_token_required`.

### Sessions and revocation

`GET /settings/api-sessions` lists every admin's live sessions, `GET /settings/users/{id}/sessions` one admin's, and `POST /settings/users/{id}/revoke-sessions` ends them all. A password change through `PUT /account` revokes the admin's other sessions when *Revoke Sessions on Password Change* is on (the default), and every API token when *Revoke API Tokens on Password Change* is on. A password reset revokes every session.

## Errors

Authentication errors carry a machine-readable code in `error` (`extensions.code` over GraphQL) next to a translated `message`. Other errors are problem documents with `type`, `title`, `status` and `detail`.

| Status | `error` | Meaning | What to do |
|---|---|---|---|
| 401 | `access_token_expired` | The access token passed its expiry | Refresh, then retry |
| 401 | `refresh_token_invalid` | The refresh token is expired, used or revoked, or the session ended | Log in again |
| 401 | `unauthenticated` | No token, an invalid token, a revoked session or an inactive admin | Log in again |
| 401 | `invalid_credentials` | Wrong email or password | Ask again; the message never says which |
| 401 | `invalid_two_factor_code` / `two_factor_challenge_invalid` | Wrong code / the challenge is used, expired or revoked | Retry the code / log in again |
| 401 | `api_token_expired` / `api_token_revoked` | The API token can no longer be used | Create a new API token |
| 403 | `account_inactive` | The admin is inactive | An administrator must activate the account |
| 403 | `api_token_domain_mismatch` | The API token was used from another domain | Call from the bound domain |
| 403 | `access_token_required` | An API token called a session operation | Use a login session's access token |
| 403 | — | The admin's role lacks the permission (problem document) | Grant the permission to the role |
| 422 | `invalid_current_password`, `password_reset_token_invalid` | A wrong current password, or a reset token that cannot be used | Correct the input |
| 422 | — | Validation failure (problem document, first message in `detail`) | Correct the input |
| 429 | `too_many_login_attempts`, `too_many_password_requests`, `rate_limit_exceeded` | Too many failed logins or codes (5 a minute), password requests (5 a minute), or requests (per admin or API token, 60 a minute by default) | Wait for the `Retry-After` seconds |
MARKDOWN;

    /**
     * The Markdown guide at the top of the storefront OpenAPI document.
     */
    public const SHOP = <<<'MARKDOWN'
Customer-facing REST API of Bagisto: catalog, cart, checkout and the customer account, under `/api/shop`. The same operations are available over GraphQL at `POST /api/graphql`.

## Authentication

Each operation lists what it needs under **security**:

| Scheme | Header | Needed by |
|---|---|---|
| `storefrontKey` | `X-STOREFRONT-KEY: <key>` | every operation |
| `customerToken` | `Authorization: Bearer <customer token>` | the customer account: profile, addresses, orders, invoices, downloads, returns, reviews of your own, wishlist, compare list, GDPR and EU withdrawals |
| `guestCartToken` | `Authorization: Bearer <cart token>` | the cart and checkout of a guest, as the alternative to `customerToken` |

### Storefront key

Every request carries the storefront key in `X-STOREFRONT-KEY`. Create a key with `php artisan bagisto-api:generate-key` and manage it with `php artisan bagisto-api:key:manage`. A missing key is answered with `401 missing_key`, an invalid or inactive key, or one used from an IP address it does not allow, with `403 invalid_key`, and a key over its rate limit with `429 rate_limit_exceeded` and `Retry-After`.

### Customers

`POST /customer/login` with `email` and `password` (and optionally `device_name` and `client_type`) starts a session on the current channel and returns `accessToken`, a JWT that lasts 15 minutes by default, and `refreshToken`. Registration through `POST /customers` and `POST /customers/social-login` return a pair too. Send the access token as `Authorization: Bearer <accessToken>`. Before it expires, or when a request answers `401` with `access_token_expired`, exchange the refresh token at `POST /customer/refresh` for a new pair: each refresh token works once, and presenting a used one ends the whole session. `POST /customer/logout` ends this session, `POST /customer/logout-all` every session, `GET /customer/sessions` lists them and `DELETE /customer/sessions/{id}` ends one. A failed login answers `401 invalid_credentials`, an inactive or unverified account `403 account_inactive` or `account_unverified`, and too many failed logins `429 too_many_login_attempts` with `Retry-After`. `token` in the login answer repeats the access token for clients of the old flow and will be removed. A request whose customer access token is expired, revoked or forged answers `401` with `access_token_expired` or `unauthenticated` before the operation runs; the login, registration, refresh and password operations never refuse a stale bearer.

### Guests: the cart token

`POST /cart-tokens` without an `Authorization` header creates a guest cart and returns its `cartToken`. Send it as `Authorization: Bearer <cartToken>` on the cart and checkout operations, which accept either a guest cart token or a customer token: the bearer decides whose cart you are working on. Called with a customer token, `POST /cart-tokens` returns the customer's active cart instead. When the guest logs in, send the cart token as `guest_cart_token` on `POST /customer/login` to merge the guest cart into the customer's in the same step (`cartId` in the answer); `POST /merge-carts` with the customer token and the guest's cart token does the same afterwards.

## Errors

Storefront key errors carry a code in `error` (`missing_key`, `invalid_key`, `rate_limit_exceeded`) next to `message`. Other errors are problem documents with `type`, `title`, `status` and `detail`: `401` for a missing or invalid bearer token, `403` when the caller may not act on the resource (some customer operations also answer a missing customer token with `403`), `404` for an unknown or foreign record, `400` or `422` for invalid input.
MARKDOWN;
}
