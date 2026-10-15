# Changelog

All notable changes to `bagisto/bagisto-api` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.5.0] - 2026-10-15

### Added
- Cart payload (REST `POST /api/shop/cart` and every cart mutation, GraphQL `readCart` and the cart mutations) now carries `miniCartOfferInfoEnabled` and `miniCartOfferInfo`, the offer text set under Sales, Checkout, Mini Cart. `miniCartOfferInfoEnabled` is true only when the mini cart is shown and the text is not empty. The text follows the request locale.
- Store settings for storefronts and apps: `GET /api/shop/store-config` and the GraphQL query `storeConfig` return the public settings of the current channel — guest checkout, minimum order amount and message, review rules, compare, wishlist, buy now and image search switches, listing defaults, email verification, newsletter, enabled social login providers, tax display, and the GDPR and EU withdrawal switches. Only a fixed list of non-secret settings is returned.
- Bagisto 2.5.0 support (Laravel 13, PHP 8.4). This release requires Bagisto v2.5.0: the package now declares `php: ^8.4` and `illuminate/support: ^13.0`.
- PostgreSQL support. Every list, search, sort and export that relied on MySQL-only SQL now works on PostgreSQL 16 as well, and the full suite passes on both databases with identical results.
- Romanian (`ro`) translations, and the new admin and installer messages in every shipped locale.

### Fixed
- Product searches made through the API (REST `?query=` and GraphQL `products(query:)`) are now recorded in the admin Search Terms list, as core does for the web storefront. Only a plain first-page search with no filters is recorded.
- On Bagisto 2.5, flag fields on attributes, channels, bookings, bundle and customizable options, wishlists, categories and sections keep the plain `0`/`1` values and the `String` type that Bagisto 2.4 returned, instead of switching to `true`/`false`.
- Searching and filtering by text was case-sensitive on PostgreSQL. It is case-insensitive on every database.
- Customer, order, refund, shipment, return and GDPR lists failed on PostgreSQL when building full names, and the shipment, CMS page, sitemap and returnable-order lists failed on functions PostgreSQL does not have.
- Product sorting and counting by name or price failed on PostgreSQL.
- Marketing campaign list and create failed on Bagisto 2.5, which no longer has the campaign `type` and `mail_to` columns. They are used only when the store has them.
- Copying a cart rule failed on Bagisto 2.5, which renamed the channel and customer group relations.
- Customer `status`, `isVerified` and `isSuspended` kept their `0`/`1` values on Bagisto 2.5, and `dateOfBirth` kept the `Y-m-d` format, so existing clients see no change.
- Updating a customer profile without `subscribedToNewsLetter` no longer writes an empty value on PostgreSQL.
- The Integration screens in the admin panel rendered a few classes unstyled on the Tailwind 4 admin build.
- The `2026_05_13_130000_align_admin_personal_access_tokens_fks` migration works when `DB_PREFIX` is set, and its rollback drops only the foreign keys that exist.
- The installer no longer edits the root `composer.json`, so the committed `composer.lock` stays in sync. The package's own `composer.json` now turns off the package discovery of `api-platform/laravel`.
- The installer no longer stops `migrate` after 60 seconds, so a long pending core migration is not cut off.
- GraphQL `customerOrderShipmentItems` limits `first` to 1–50 like every other list, so `first: 0` no longer fails.
- The OpenAPI documents report the package version `2.5.0`.

### Security
- Admin roles can only be created, changed or deleted with permissions the calling admin holds, and only an all-access admin can create or edit an all-access role, as in the core admin panel.
- An admin user can only be given a role whose permissions the caller holds, and a caller cannot edit or delete an admin whose role is wider than their own.
- An integration token can only be created, edited, generated, regenerated or revoked for an admin whose role the caller could grant. The integration screens now have ACL entries for saving a token and for the history detail and clean-up pages.
- Order, invoice, shipment and refund detail endpoints, the invoice PDF and order comments need the matching `.view` permission, and the invoice mass status update needs `sales.invoices.update`.
- A customer is identified only by the Sanctum bearer token from login. Core's email-verification token (`customers.token`) and `api_token` are no longer accepted as a bearer token by the cart and checkout.
- GraphQL `customerOrderShipmentItems` and `customerOrderShipmentItem` return only the calling customer's shipment items.
- GraphQL lookups by id return only approved reviews, enabled categories and products the storefront would show.

### Removed
- The report types dashboard `total-visitors`, customer report `customers-traffic` and product report `products-with-most-visits`. Core has no method behind them, so they returned 500. They now answer with the invalid-type error.

### Changed

- Deleting a customer account now needs the account password and is refused while the customer has pending or processing orders, as the storefront does.
- Registering with email verification on no longer returns a token for the unverified account. The response carries `token: null` and a `message` asking the customer to verify the email.
- Guest EU withdrawal lookups are limited to five attempts per minute for each address and order number.
- The public review list and single-review lookup return approved reviews only. A `status` filter from the client is ignored.
- A newsletter address that unsubscribed earlier can subscribe again.
- A throttled password reset reports that it is throttled instead of "email not found".
- Google social login is refused when no Google client id is configured, instead of skipping the audience check.
- Cart `taxAmount`, `baseTaxAmount` and `formattedTaxAmount` returned 0 and now carry the cart's tax.
- A coupon that reached its usage limit is removed from the cart and reported when the order is placed.
- Cancelling, closing or reopening a return follows the same rules as the storefront, including the return window.
- A role or admin update cannot remove the last all-access administrator, and changing an admin password fires the same event as the admin panel.
- Import stats and download use the `settings.data_transfer.imports.import` permission, as core does.
- Configuration values saved through the API are no longer written in clear text to the audit log, and any audited field whose name contains secret, password, key or token is redacted.
- The email link that revokes an integration token now opens a confirmation page and revokes only after a confirming click, so link scanners cannot revoke a token.
- The installer reports when it could not register the service provider instead of printing success.
- Request logging honours its configuration, uploaded multipart temp files are removed after the request, and query logging no longer records bindings or logs on local environments by default.
- Static customer `api_token` and `token` values are no longer accepted by the download mutation. Use the bearer token.
- The `first` argument is limited to 1–50 on every list. `first: 0` and `first: -1` return a one-item page, and a larger value is capped at 50.
- Pages assigned to another channel are no longer returned, and categories that are disabled or outside the channel root are no longer listed. The category tree starts at the channel's root category, and `depth` is limited to 1–6.
- The channel `allowedIps` field is no longer exposed.
- The product `cost` field and the downloadable link `url` and `fileName` fields are no longer exposed.
- A single product (REST `GET /api/shop/products/{id}`, GraphQL `product(id:)`, `product(sku:)`, `product(urlKey:)`) is returned only when the store front would show it: assigned to the channel, enabled, and visible individually. A product that is hidden from the catalog answers 404 and `product not found`. An enabled variant of a product that passes these checks can still be opened by id or SKU, so clients that fetch variants keep working. Variants are never listed.
- Sorting products by name works when a channel is passed.
- Internal clean-up with no change to any response: variables use full words, deeply nested methods were split, and the largest classes (cart processor, service provider, product model) were divided into focused parts.
- Removed an unused product provider that no endpoint called.

