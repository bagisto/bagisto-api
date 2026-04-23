# Changelog

All notable changes to `bagisto/bagisto-api` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.3]: https://github.com/bagisto/bagisto-api/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/bagisto/bagisto-api/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/bagisto/bagisto-api/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/bagisto/bagisto-api/releases/tag/v1.0.0
