# Changelog

All notable changes to `bagisto/bagisto-api` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Add request and response examples to the Swagger/OpenAPI docs for every Returns (RMA) and EU Withdrawal endpoint (shop and admin).
- Add the request body schema for creating and updating RMA reasons, rules, statuses and custom fields in the Swagger docs.
- Add `redirect` and `redirectUrl` to the place-order response so clients can tell when the shopper must be sent to a payment page before the order exists.
- Add `minPrice` and `maxPrice` to the storefront category response so REST clients can bound a price-range filter, matching what GraphQL already exposed.

### Changed

- Drop the hard Redis requirement: the API metadata/schema cache and rate-limit counters now follow the application's `CACHE_STORE` (falling back to `file`), so no separate cache service or extra configuration is needed.
- Take the cart file-upload size limit from `php.ini` (`upload_max_filesize`) like the rest of Bagisto instead of a package setting, and default how long a staged upload stays valid to the store's session lifetime.

### Fixed

- Fix GraphQL connection fields (e.g. cart `items { edges }`) failing with `Field "items" of type "Iterable" must not have a sub selection` on some production PHP-FPM servers.
- Fix RMA settings create and update responses showing a success `message`; the confirmation message is now returned only on delete.
- Restore backward compatibility with Bagisto cores below 2.4.5 by conditionally registering the EU Withdrawal endpoints only when the core module is present.
- Fix `paymentGatewayUrl` coming back empty for redirect payment methods (Stripe, Razorpay, PayPal Standard, PhonePe), leaving clients with nowhere to send the shopper.
- Fix selecting PhonePe as the payment method failing with a server error.
- Fix place order creating an unpaid order when a redirect payment method is selected; it now returns the payment redirect, and the order is created once the gateway confirms payment.
- Fix `cartToken` in checkout responses returning the customer id (or an empty value for guests) instead of the cart's guest token; it is now the guest token, or null for a signed-in customer.
- Fix `cartToken` in cart responses returning the cart id instead of the guest token.
- Fix 30 admin endpoints in the Swagger docs asking for a required path parameter the URL does not contain, such as `method` on list payment methods and `id` on list order comments.
- Fix Swagger path parameter descriptions exposing internal resource names (e.g. "AdminCart identifier" now reads "Cart ID").
- Fix the storefront key falling back to a placeholder in the GraphiQL and Swagger docs after `config:cache` or `optimize`; playground settings are now read from configuration, so they survive a cached config.
- Fix Swagger UI ignoring `API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY`, so the key was never pre-filled there even when the setting was enabled.
- Fix product image and video URLs being prefixed with an unset `API_URL`, which produced a doubled host on stores that set it.
- Fix the copy-product endpoint missing its `sourceId` field in the Swagger docs, which left it uncallable from the UI.
- Fix the Swagger docs demanding a request body on action endpoints that take none (cancel, reopen and close a return, revoke a GDPR request, reopen an RMA, resend an EU withdrawal confirmation).

## [2.4.0] - 2026-07-15

### Changed

- Upgrade to Bagisto 2.4.7 / Laravel 12; the package now requires PHP 8.3+.
- Upgrade API Platform to `4.3.x` (`api-platform/laravel` and `api-platform/graphql` at `~4.3.8`), which also resolves the `api-platform/json-api` security advisory.
- Install only `api-platform/laravel` and `api-platform/graphql` — the other `api-platform/*` components are pulled in automatically, so the redundant per-component `composer require` list has been removed.
- Keep every endpoint URL unchanged after the API Platform upgrade: auto-generated paths stay underscored (e.g. `/api/shop/compare_items`), so no client needs to update a URL.

### Fixed

- Fix GraphQL delete mutations (`deleteWishlist`, `deleteCompareItem`, `deleteGdprRequest`) returning only `id` instead of the deleted record's fields.
- Fix storefront place-order (`createCheckoutOrder`) returning an empty payload.
- Fix a request for another customer's return (`customerReturn`) returning a blank record instead of an error.
- Fix `filterableAttributes` missing from the storefront `category` GraphQL type.
- Fix admin user create/edit/delete and downloadable-file download rejecting roles that hold the correct permission, caused by permission keys renamed in Bagisto 2.4.
- Fix storefront contact-us, newsletter, login, logout, forgot-password and token-verification responses returning empty payloads.
- Fix creating a product review over REST (`POST /api/shop/reviews`) failing with a server error.

## [2.3.1] - 2026-07-07

### Fixed

- Fix file-type customizable options not being usable on add to cart for simple and virtual products (`POST /api/shop/customizable-option-files`).
- Fix required customizable options not being enforced on add to cart.

## [2.3.0] - 2026-07-02

### Added

- Add product video endpoints: upload a video (`POST /api/admin/catalog/products/{id}/videos`, REST multipart) and delete one (`DELETE /api/admin/catalog/products/{id}/videos/{id}` / `deleteAdminCatalogProductVideo`).

### Fixed

- Fix the storefront `filterableAttributes` price range: `maxPrice` now reflects the authenticated customer's customer-group pricing, and `minPrice` now returns the actual lowest product price.
- Fix admin tax-rate update leaving orphaned zip fields when switching between specific-zip and zip-range modes.
- Fix the storefront (shop) REST API documentation: correct the authentication endpoint paths, remove the non-existent reset-password page, and replace placeholder request/response examples with real ones.
- Update the translations for all non-English locales.

## [2.2.0] - 2026-07-02

### Added

- Add downloadable-product file upload (REST): store the binary for a file-type link or sample and get back its path — `POST /api/admin/catalog/products/{id}/downloadable-links/upload` and `.../downloadable-samples/upload`.
- Add downloadable-product file download (REST): stream a stored file — `GET /api/admin/catalog/products/{id}/downloadable/{attributeId}/download`.

### Changed

- Change admin marketing campaign update to be partial — send only the fields you want to change (previously every field was required).

### Fixed

- Fix the campaign-send, coupon-generate, coupon-mass-delete, and sitemap-generate mutations returning null over GraphQL for `campaignId` / `cartRuleId` / `sitemapId` and the generated sitemap file paths and timestamp; these fields now resolve. REST was unaffected.
- Fix admin customer-address update / delete / set-default over GraphQL not verifying the owner — `customerId` is now required, so an address can no longer be changed or removed without identifying its customer.

## [2.1.0] - 2026-07-01

### Added

- Add the "Record Payment" action for invoices (`POST /api/admin/transactions` + `createAdminTransaction`): record a full or partial payment against an invoice; the invoice is marked paid and the order advances once payments cover its total.

### Fixed

- Fix the dashboard date range returning `null` over GraphQL (REST was unaffected).
- Fix admin API token rate limiting counting the per-minute and per-day caps against one shared counter; the two are now independent (previously the per-minute cap tripped at half its configured value and the per-day cap was not enforced).

## [2.0.0] - 2026-06-26

**Major release.** Contains **breaking changes to the Admin API** (some response shapes changed). The **Shop (storefront) API is fully backward compatible** — storefront integrations need no changes.

### BC breaks

#### Admin API

- Change `adminCustomers` / `adminCustomer` (`GET /api/admin/customers`) to return the customer group as a nested object instead of the flat `customerGroupId` / `customerGroupName`.
- Change `adminCustomerReviews` / `adminCustomerReview` (`GET /api/admin/customers/reviews`) to return product, customer, and images as nested objects instead of flat scalars.
- Change `adminSettingsChannels` / `adminSettingsChannel` (`GET /api/admin/settings/channels`) to return locales, currencies, and inventory sources as object collections instead of int arrays.
- Change `adminInvoices` / `adminInvoice` (`GET /api/admin/invoices`) to return the order as a nested object instead of the flat `orderId`.
- Change `adminSettingsTaxCategories` / `adminSettingsTaxCategory` and `adminSettingsThemes` / `adminSettingsTheme` to return nested data as field-selectable objects instead of an opaque JSON blob.
- Remove `adminReviews` / `adminReview` (`GET /api/admin/reviews`); use `adminCustomerReviews` / `adminCustomerReview` (`GET /api/admin/customers/reviews`) instead.

### Added

- Add storefront GDPR data requests (REST + GraphQL): a logged-in customer can raise / list / view / revoke / delete their own requests; scoped per-customer and gated by the store's GDPR setting.
- Add automated field-completeness test guards (read + create-mutation sweeps) that catch any admin field that fails to resolve.
- Add the Compare Items endpoint and 31 Shop API overview pages to the docs (Shop docs now match the Admin docs for menu parity).

### CLI

- `bagisto-api-platform:optimize` — single post-deploy command that caches config, events, and routes and warms the API Platform metadata + GraphQL schema. Cuts typical response times ~5× (GraphQL list ~1.5s → ~0.3s). Fails loudly if route caching fails and warns when `APP_DEBUG` is on; pair with `APP_DEBUG=false`. Run it after every deploy or endpoint change.

### Changed

- Change Settings GraphQL nested data (`taxRates`, `translations`) to be field-selectable instead of an opaque JSON blob.
- Document the full filter / sort / pagination set on every Settings & Catalog listing, and every field per product type on the Product create/update Swagger.

### Fixed

- Fix every admin GraphQL delete mutation (Customers, Customer Groups, Reviews, GDPR, Catalog Rules, Catalog sub-resources, Settings, CMS) returning "Internal server error" when selecting `id` — now returns a snapshot of the deleted record plus a `message`.
- Fix admin listing 500s: Orders (tax / incl-tax / `isGift`), Marketing Campaigns / Search Terms / Subscribers (nested `channel` / `customerGroup` / `marketingTemplate`), Customers (`group.isUserDefined`), Invoices (nested `order`).
- Fix `adminMarketingCartRule` partial update wrongly demanding `couponCode` — it now preserves the existing coupon.
- Fix catalog-rule mass-delete to return the `skipped` ids.
- Fix Catalog GraphQL attribute-option create/update, product inventory bulk-update, category update, and product customer-group-price fields.
- Fix admin mass-delete results being wrapped in a `{ data, meta }` envelope — now a plain list of ids.
- Fix `adminCustomer` REST detail returning 500 when a date of birth is set.
- Fix customer-nested REST endpoints (impersonate, gdpr-download, notes, addresses, draft-carts) requiring the customer id twice in Swagger.
- Fix tokens with the Users permission being wrongly denied (403) on user create/update/delete.
- Fix cache clearing timing out on large installations.
- Add `sort` / `order` to the Wishlist and Customer Address listings.
- Remove the stray History tab from the Integration token Create/Edit pages.

## [1.0.5] - 2026-06-10

### Added

**Integration menu**
- API change history (audit trail): every admin-API create/update/delete (REST and GraphQL) is recorded with actor, token, time, IP, and before/after field values, viewable on a new **Integration → History** screen with diff and version history.
- History cleanup tools (permission-gated): mass-delete, "delete logs older than N days", a `bagisto-api:prune-audits` command, and a retention config. Sensitive fields are redacted; the feature can be switched off.

**Sales menu**
- CSV export for the Orders, Invoices, Shipments, Transactions and Bookings datagrids (`?format=csv`, honours listing filters; REST only).
- Shipment detail now includes the order's payment & shipping panel.
- Booking detail now embeds the order's addresses, payment/shipping info, and invoice/shipment/refund summaries.

**Catalog menu**
- Products listing now includes the special-price columns.
- CSV export for the Products datagrid.

**CMS menu**
- CSV export for the Pages datagrid.
- Pages listing now includes the remaining page columns; each page now carries a `previewUrl`.

**Settings menu**
- CSV export for the Tax Rates datagrid.
- Exchange Rates auto-sync (the admin "Update Rates" action).
- Admin self-account deletion (`delete-self`, password-confirmed).
- Data Transfer → Imports: full import lifecycle (create/update upload, validate/start/link/index, stats, and file/error/sample downloads).

**Reporting menu**
- "View Details" (detailed table form) for every reporting panel.
- CSV export for each reporting sub-page.

### Fixed

**Installation**
- Fixed the `composer require bagisto/bagisto-api` failure on a fresh install — the API Platform dependencies are now pinned to a consistent, tested set so installation completes cleanly.

**Configuration menu**
- Configuration schema now returns human-readable, translated field labels instead of raw translation keys.

**Settings menu**
- Multi-word fields across every Settings resource now resolve over GraphQL (previously returned `null`; REST unchanged).

**Reporting menu**
- `dateRange` now resolves over GraphQL on every reporting query.

**Sales menu**
- Cancel-order and add-comment now return usable fields over GraphQL (previously only `id` was exposed).
- GraphQL cart-write / draft-cart / place-order docs now select the result fields (`cartId`, `orderId`, …) instead of the non-selectable `id`.
- Create-Order draft cart is no longer destroyed when adding an unavailable product — it now returns a clear error and keeps the cart intact.
- Orders overview docs now include an order-lifecycle guide.
- Orders listing/export date presets now match the admin datagrid (`last_three_months` / `last_six_months`).
- Orders listing/export grand-total filter now matches the datagrid (base grand total, with `_from`/`_to` range).
- Orders listing over GraphQL (`adminOrders`) now accepts its filter arguments (previously rejected as unknown).
- Create-Order place-order now rejects a cart below the configured minimum order amount with a `422`.
- Create-Order save-address now validates required address fields, returning `422` when one is missing.
- Order detail now embeds refunds, the comment thread, total due, payment-method code, and each address's `vatId`.
- Create-shipment now validates composite products (bundle/configurable/grouped) per component, preventing over-quantity shipments.
- Create-Order add-to-cart over GraphQL now supports every product type (configurable, downloadable, grouped, bundle); booking stays blocked by design.
- Invoice `state`: removed the non-existent `refunded` value from the filter, OpenAPI enum, and docs.

**Catalog menu**
- Admin product search over GraphQL now accepts its filter arguments and resolves `formattedPrice` / `baseImageUrl` / `isSaleable` (were `null`).
- Attribute detail now returns `isComparable`, `enableWysiwyg`, and `regex` (could be set but not read back).
- Attribute Families GraphQL docs: corrected the example record-id path.

**CMS menu**
- Page fields (`pageTitle`, `urlKey`, `htmlContent`, `metaTitle`, `previewUrl`, timestamps) now resolve over GraphQL (were `null`).

**GraphQL node ids**
- Catalog Products, CMS Pages and Sales Refunds node `id`s returned the export path instead of the per-record IRI once a CSV export endpoint was added; corrected.

### Changed

**Sales menu**
- Swagger: all order-menu endpoints are now grouped under a single `Admin Sales: Orders` tag (the former `Admin Orders` / `Admin Order Actions` / `Admin Carts` tags were retired).
- Transactions is a listing + detail menu only (standalone detail/overview doc pages removed).
- Invoices overview now documents each action and the payment-due countdown.
- Cart endpoints are documented as part of the Orders menu (Create-Order flow), not a standalone "Cart" menu.

**Catalog menu**
- The full Products datagrid is now the canonical "List Products" page; the slim `adminProducts` picker is repositioned as the Create-Order "Add Product" search.

## [1.0.4] - 2026-05-29

### Added

**Customer REST API (parity with existing GraphQL)**
- Customer auth: login, logout, register, forgot password, verify token.
- Customer profile: get, update, delete.
- Customer address full CRUD.
- Cart: read, add item, update item, remove item(s), apply / remove coupon, merge cart, cart token bootstrap.
- Checkout: save address, list / select shipping method, list / select payment method, place order.
- Wishlist: collection, single, create, delete, toggle, move-to-cart, delete-all.
- Compare items: collection, single, create, delete, delete-all.
- Contact Us submission.
- Newsletter subscription.
- Customer orders listing + detail with fully embedded items / addresses / payment / shipments (no dangling IRIs to follow).
- Customer reviews collection + single.
- Booking slot collection.
- Category list + detail (status-filtered) and Category Tree.
- Channel, Country, Country State, and Locale collections.
- Product list (shape matches the existing GraphQL search response) and product detail with embedded categories, channels, variants, super-attributes, and bundle options.

**Admin REST + GraphQL API (new surface)**
- Dedicated admin GraphQL POST entrypoint, separate from shop GraphQL (own middleware stack — no storefront key required, Bearer token only).
- Admin GraphiQL playground (browseable UI for admin GraphQL) with AES-GCM encrypted token storage and isolated browser storage so admin and shop sessions don't leak across each other.
- Admin authentication via integration tokens with per-token rate limits (per-minute and per-day), IP allowlist (IPv4 / IPv6 / CIDR), three permission modes (`all`, `custom`, `same_as_web`), configurable expiry, regeneration, and revocation. Lifecycle email notifications on generate / regenerate / revoke, including a signed login-free revoke link sent to the token owner.
- Admin panel **Integration** plugin (Settings → Integration) for token management: list / create / edit / generate / regenerate / revoke flows. Module enable/disable toggle under **Configuration → API → Integration**.

**Admin API endpoints by menu**
- **Authentication:** read admin profile.
- **Dashboard:** stats endpoint with seven stat groups.
- **Reporting:** overview, sales, customers, and products stat groups.
- **Sales:** Orders listing + detail; Cancel order; add / list order comments; create, get, and print invoice (PDF); create and get shipment; create, preview, and get refund. Standalone listings for Invoices, Shipments, Refunds, Transactions, and Bookings.
- **Customers:** full CRUD plus mass-delete and mass-update-status; Customer Groups CRUD plus mass-delete; Customer Addresses CRUD (ownership-checked); Customer Notes (append-only); Customer Impersonate (issues a short-lived customer token for the target customer); Customer Reviews moderation (list, detail, update-status, delete plus mass variants); Customer GDPR Requests (list, detail, update, delete, process, download data).
- **Catalog Products:** DataGrid-parity listing (11 filters, 9 sort columns); type-aware detail; create across all seven product types (simple, virtual, downloadable, grouped, bundle, configurable, booking); update (free-shape pass-through with a `_warnings` array surfacing dropped sub-resource fields); delete; copy; mass-delete; mass-update-status. Sub-resources: images (multipart upload, reorder, delete), inventories (list plus bulk update with `meta.totalQty`), and customer-group prices (full CRUD with composite uniqueness).
- **Catalog Categories:** listing, nested tree, detail with all-locale translations, create, update (locale-nested payload), delete (root-category guarded), mass-delete, mass-update-status.
- **Catalog Attributes:** listing, detail with translations and options, full CRUD, attribute-options CRUD, mass-delete.
- **Catalog Attribute Families:** listing, detail with attribute groups and per-group attributes, full CRUD with nested writes, delete guarded against last-family and products-attached conditions.
- **CMS Pages:** listing, detail with translations and channels, create (top-level broadcast across locales), update (locale-nested payload), delete, mass-delete.
- **Marketing:** Cart Rules (CRUD plus mass-delete) and Cart Rule Coupons (list, single create, bulk generate, delete, mass-delete); Catalog Rules (CRUD plus mass-delete); Email Templates (CRUD); Marketing Events (CRUD); Campaigns (CRUD plus manual send); Newsletter Subscribers (list, detail, toggle, delete — toggling mirrors the flag onto the linked customer); Search Terms (list, detail, update, delete, mass-delete); Search Synonyms (CRUD plus mass-delete); URL Rewrites (CRUD plus mass-delete); Sitemaps (CRUD plus a synchronous generate action that writes the XML files to the public disk).
- **Settings:** Currencies (CRUD plus mass-delete; last-currency and channel-base-currency guards); Channels (CRUD with translatable payload and multi-pivot sync; default-channel guard); Exchange Rates (CRUD plus mass-delete); Locales (CRUD plus mass-delete; last-locale and channel-default-locale guards); Inventory Sources (CRUD plus mass-delete); Tax Rates (CRUD with conditional zip rules); Tax Categories (CRUD; delete refused when rates still attached); Data Transfer Imports (list, detail, cancel, delete — create is deferred); Roles (CRUD with in-use and last-role guards); Users (CRUD with self-delete and last-admin guards); Themes (CRUD plus mass-delete and mass-update-status).
- **Create-Order flow:** customer-nested draft-cart bootstrap; cart-keyed cart endpoints (add / update / remove items, save addresses, apply / remove coupon); shipping and payment selection; place-order. Every step gated by cart-state sequence checks so callers get a precise error on out-of-order calls instead of a generic 500.
- **Configuration:** schema-tree endpoint, per-section values endpoint, and an update endpoint with anti-scope-escape guard and server-side validation.

**Infrastructure**
- Standard `{ data, meta }` envelope on every admin paginated collection.
- Pagination response headers `X-Total-Count`, `X-Page`, `X-Per-Page`, `X-Total-Pages` on every paginated REST endpoint (CORS-exposed so JavaScript clients can read them).
- Empty `application/json` request bodies on admin endpoints no longer return 500 — accepted as `{}` automatically.
- Comprehensive Pest test coverage across the new admin REST and GraphQL surface, plus regression coverage for every shop fix in this release.

### Changed
- Admin GraphQL traffic moved to its own dedicated POST endpoint. Shop GraphQL no longer accepts admin Bearer tokens — clean transport split, no shared back door.
- `X-STOREFRONT-KEY` is no longer required on admin endpoints. Admin auth is Bearer-only; the storefront key remains required on shop endpoints.
- Nested item lists on admin detail responses (order items, invoice items, shipment items, refund items, order invoices, order shipments, etc.) render as plain JSON arrays in both REST and GraphQL. Query the fields directly — there is no GraphQL cursor-connection wrapper to unwrap.
- Order listings now fall back to the raw numeric amount when the order's snapshot currency code no longer exists in the currencies table (previously these orders returned 500).
- Checkout place-order response now populates `success` and `message` fields, matching the other checkout endpoints.
- OpenAPI version bumped to `1.0.4` in Swagger / API Platform configuration.

### Fixed
- Shop `toggleWishlist` mutation no longer fails with "Internal server error" when a downstream listener misbehaves — the toggle completes successfully and listener failures are silently swallowed.
- Shop `removeCartItem`, `applyCoupon`, and `removeCoupon` GraphQL responses now carry the correct `success` and `message` fields. Apply-coupon verifies the code actually applied before reporting success.
- Shop `readCart` now returns the applied `couponCode` (previously always null after applying a coupon).
- Shop customer-profile-update REST now accepts `currentPassword` for password changes (previously rejected as missing).
- Shop locale collection rows always include `logoPath` and `logoUrl` (null when unset) so clients can rely on field presence.
- Shop `createNewsletter` GraphQL mutation now correctly accepts `customerEmail` (previously rejected as missing).
- Admin reporting GraphQL queries (overview / sales / customers / products) no longer return "Internal server error".
- Admin dashboard `top-selling-products` and reporting `top-selling-products-by-revenue` / `by-quantity` no longer return "Internal server error".
- Admin transaction detail endpoint no longer returns "Internal server error".
- Admin settings-channel partial updates preserve existing `locales` / `currencies` / `inventory_sources` when those fields are omitted from the request body.
- Admin Bearer tokens now correctly identify the calling admin on every request (previously the first request's admin could leak into subsequent requests under specific deployments).


---


## [1.0.3]

### Added

**Customer account APIs**
- Customer orders list / detail endpoints (`CustomerOrderProvider`).
- Customer order shipments (`CustomerOrderShipmentProvider`).
- Customer invoices (`CustomerInvoiceProvider`) and invoice PDF download (`InvoicePdfController`).
- Customer reviews list (`CustomerReviewProvider`).
- Customer downloadable products listing (`CustomerDownloadableProductProvider`) and purchased-downloads download endpoint (`DownloadablePurchasedController`).
- Cancel order (`CancelOrderProcessor` + `CancelOrderInput` DTO).
- Reorder (`ReorderProcessor` + `ReorderInput` DTO).
- Customer profile output resource (`CustomerProfileOutput`) and profile helper.
- Customer address DTO + processor updates for address CRUD.

**Catalog & storefront APIs**
- REST endpoints for Locale, Category tree, and Theme Customization.
- CMS page lookup by URL key (`PageProvider`, GraphQL `PageByUrlKeyResolver` tagged as collection query resolver).
- Channel endpoint (`ChannelProvider`).
- Product API now exposes query fields for dynamic currency.
- Booking product slot provider (`BookingSlotProvider`) and mutations for Booking / Event Booking product types.
- Downloadable product sample download (`DownloadSampleController`).
- More precise product search by title.
- Contact Us submission (`ContactUsProcessor` + `ContactUsInput`/`ContactUsOutput` DTOs).

**Cart, wishlist & compare**
- Merge cart API with configurable product support (`CartTokenProcessor` extended).
- Compare item CRUD (`CompareItemProvider`/`CompareItemProcessor`) + delete-all (`DeleteAllCompareItemsProcessor`).
- Wishlist CRUD (`WishlistProvider`/`WishlistProcessor`) + delete-all (`DeleteAllWishlistsProcessor`).
- Move wishlist item to cart (`MoveWishlistToCartProcessor` + input/output DTOs).

**Infrastructure**
- `php artisan bagisto-api-platform:cache:clear` (`ClearApiPlatformCacheCommand`).
- `CursorAwareCollectionProvider` for cursor-based pagination.
- `FixedSerializerContextBuilder` to patch API Platform serializer context handling.
- `SnakeCaseLinksHandler` for consistent snake_case link rendering.
- Push Notification integration.
- Extensive Pest feature test coverage: GraphQL product/cart/checkout/customer/wishlist/compare/booking/reorder, REST customer orders/invoices/reviews/downloadable/CMS pages, customer auth and address flows, locale + channel + currency headers.

### Changed
- Cart price conversion now respects the active currency.
- Translation fallback for products and product variants based on active status.
- Translations extended across 21 locales (`en/app.php` + 20 locale files: `ar, bn, ca, de, es, fa, fr, he, hi_IN, id, it, ja, nl, pl, pt_BR, ru, sin, tr, uk, zh_CN`) including Event Booking product type strings.
- Shipping rates now expose `formattedPrice` (`ShippingRateOutput` updated).
- GraphQL Playground controller refreshed (~460 lines) with updated endpoints and UX.
- `InstallApiPlatformCommand` now publishes vendor config.
- `api-platform/laravel` and `api-platform/graphql` pinned to specific versions in `composer.json`.
- OpenAPI `info.version` bumped to `1.0.3` in `config/api-platform.php`, `config/api-platform-vendor.php`, and the `SwaggerUIController` error fallback.
- Add-to-cart error response updated with clearer payload.
- Rate-limit enforcement tightened for storefront endpoints.

### Fixed
- Disabled products can no longer be added to the wishlist.
- Moving a wishlist item to the cart now increments the cart quantity when the same product is moved again.
- `attributeValues` key resolved correctly in product query data.
- `formattedPrice` field for downloadable and Event Booking product types.
- Cart merge behaviour for configurable products.
- Translation fallback for products and cart price conversion.
- Order, customer, and wishlist edge cases reported during QA.
- README + `api-platform-vendor.php` newline hygiene.

### Documentation
- README: fixed step numbering (Step 9 → Step 6), stray backtick on the GraphQL endpoint URL, and the `graphqli` → `graphiql` typo in the GraphQL Playground link.
- Added `CHANGELOG.md` (this file).

---

## [1.0.2] - 2026-01-23

### Added
- `PageProvider` for CMS page API resource.
- Combination and super-attribute options on the configurable product API.
- Vendor config publishing and install URL in `InstallApiPlatformCommand`.

### Changed
- Updated checkout address handling and playground controller endpoints.
- Reworked add-to-cart token flow for guest users.
- Product provider refinements.
- Install command success / failure messaging.

### Fixed
- Read-cart issue and attribute IRI resolution.
- Cache clear commands (`cache(...)` → correct artisan command).

---

## [1.0.1] - 2026-01-19

### Added
- `bagisto-api-platform:install` artisan command (installation command for the platform).

### Changed
- Install command now auto-registers the service provider via `bootstrap/providers.php`.
- Install command wired into `post-autoload-dump` composer hook.
- Translations for install command output.

### Fixed
- Provider registration string formatting and indentation in `InstallApiPlatformCommand`.
- Storefront key generation: bounded retry logic to respect max-request ceilings and improved key management.

---

## [1.0.0] - 2026-01-10

### Added
- Initial release of `bagisto/bagisto-api`.
- REST and GraphQL API surface built on top of API Platform v4.1 (REST) and v4.2 (GraphQL) for Bagisto.
- Storefront key–based authentication and rate limiting.
- Swagger / OpenAPI documentation at `/api/docs` and GraphQL playground at `/graphiql`.
- Initial documentation and demo links in the README.

[2.4.0]: https://github.com/bagisto/bagisto-api/compare/v2.3.1...v2.4.0
[2.3.1]: https://github.com/bagisto/bagisto-api/compare/v2.3.0...v2.3.1
[2.3.0]: https://github.com/bagisto/bagisto-api/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/bagisto/bagisto-api/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/bagisto/bagisto-api/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/bagisto/bagisto-api/compare/v1.0.5...v2.0.0
[1.0.5]: https://github.com/bagisto/bagisto-api/compare/v1.0.4...v1.0.5
[1.0.4]: https://github.com/bagisto/bagisto-api/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/bagisto/bagisto-api/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/bagisto/bagisto-api/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/bagisto/bagisto-api/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/bagisto/bagisto-api/releases/tag/v1.0.0
