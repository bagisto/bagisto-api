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
- Runs `migrate`. This release adds no table and no column. The one migration that changed (`2026_05_13_130000_align_admin_personal_access_tokens_fks`) is a no-op on a store that already has the foreign keys. It now also works when `DB_PREFIX` is set.
- Keeps an existing storefront key and the `STOREFRONT_*` values in `.env`.
- Clears the caches at the end and caches the configuration and routes again (`php artisan optimize`). After a later `.env` change, run `php artisan optimize` again.

No `.env` edit and no cache command is needed afterwards. No data has to be migrated, and uploaded files stay in the folders 2.4.4 used.

## 4. Breaking changes

### Admin API permissions

In 2.4.4 most admin **read** endpoints only checked that a token was valid. They now need the same ACL key as the matching admin screen. Review every custom role and every integration token with **Custom** abilities.

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
- An integration token can only be created, edited, generated, regenerated or revoked for an admin whose role the caller could grant.
- Custom token abilities must be real ACL keys.

The integration screens now have ACL entries for saving a new or edited token and for the history detail and clean-up pages. Custom roles that hold `integration.create`, `integration.edit`, `integration.history.view` or `integration.history.delete` can use those pages now.

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
