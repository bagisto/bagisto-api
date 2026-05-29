# Changelog

All notable changes to `bagisto/bagisto-api` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.4]: https://github.com/bagisto/bagisto-api/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/bagisto/bagisto-api/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/bagisto/bagisto-api/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/bagisto/bagisto-api/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/bagisto/bagisto-api/releases/tag/v1.0.0
