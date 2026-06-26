# Changelog

All notable changes to `bagisto/bagisto-api` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Coming Soon]

## [1.0.6] - 2026-06-26

> **Heads-up — breaking changes.** Several admin GraphQL/REST responses changed shape: the customer `customerGroupId` / `customerGroupName` scalars became a `group { id code name }` object; the channel `localeIds` / `currencyIds` / `inventorySourceIds` int arrays became `locales` / `currencies` / `inventorySources` object collections; the invoice `orderId` scalar became `order { id }` / `order { _id }`; review `productName` / `productSku` / `customerName` / `customerEmail` became nested `product` / `customer` objects. The undocumented `/api/admin/reviews` endpoint was removed. Update field selections accordingly — see the entries below.

### Added

- Storefront GDPR data requests API (REST + GraphQL): a logged-in customer can now raise, list their own, view one, revoke, and delete their own GDPR data requests directly through the customer API (previously this was only possible from the storefront website). Every request is scoped to the signed-in customer — you only ever see your own. The whole feature is gated by the store's GDPR setting: when an administrator has GDPR turned off, these endpoints return a clear "GDPR data requests are disabled" error.
- Admin → Customers (docs): the Create-Order sidebar **Compare Items** endpoint (`GET /api/admin/customers/{customerId}/compare-items` + `adminCustomerCompareItems`) is now documented alongside the cart-items / wishlist-items panels.

### Removed

- Admin: removed the undocumented `/api/admin/reviews` endpoint (`adminReviews` / `adminReview`). It was an internal duplicate of the **Customers → Reviews** menu — use `/api/admin/customers/reviews` (`adminCustomerReviews`) instead, which is the documented, full-featured review surface.

### Changed

- Catalog → **Products** create/update (docs + Swagger, both transports): the examples now cover **every field on the product edit form** for each product type. The Swagger UI request body for both **Create** and **Update** has a product-type dropdown (Simple, Virtual, Downloadable, Grouped, Bundle, Configurable, Booking) — pick a type to see its complete, verified request body. The GraphQL and REST Update pages document the full field list per type, the new-row key prefixes for nested structures (`option_*`, `product_*`, `link_*`, `sample_*`, `price_*`, `ticket_*`), and which fields apply to which type (custom options are simple/virtual only; grouped/configurable products have no own price; a bundle uses dynamic pricing). All examples were run against the live API and show the real response.
- Customers (GraphQL): the customer listing now accepts the four date-range filters that REST already supported — `date_of_birth_from` / `date_of_birth_to` and `created_at_from` / `created_at_to` — so GraphQL and REST filter by the same set. The Customers GraphQL list docs (main, groups, reviews, GDPR) now document their full filter / sort set with a "Filtered + Sorted" example.
- Settings → **Locales** (both transports): the listing now accepts an `id` filter — a single id or a comma-separated list, e.g. `?id=1,10,35` (GraphQL `id: "1,10,35"`) — matching the admin datagrid's ID filter.
- Settings (docs): every Settings listing now documents its full filter, sort, and pagination set for **both** GraphQL and REST, with a "Filtered + Sorted" example (previously the GraphQL list examples only showed pagination). Covers Locales, Currencies, Channels, Exchange Rates, Inventory Sources, Tax Rates, Tax Categories, Roles, Users, Themes, and Data Transfer Imports. No filters changed — the GraphQL filters already existed and are now simply documented.
- Settings (GraphQL): nested data is now field-selectable instead of an opaque JSON blob — **Tax Categories** `taxRates`, **Themes** `translations`, and **Channels** `translations` are GraphQL connections (`{ edges { node { … } } }`); REST keeps returning them as flat arrays.
- Settings → **Channels** (GraphQL): `localeIds` / `currencyIds` / `inventorySourceIds` are **replaced** by `locales` / `currencies` / `inventorySources` connections of objects (REST returns them as arrays of `{ id, code, name, … }`). The channel's SEO is now selectable as `seoMetaTitle` / `seoMetaDescription` / `seoMetaKeywords` (and per-locale under each `translations` node). **Action:** GraphQL clients selecting the old `localeIds`/`currencyIds`/`inventorySourceIds` int arrays must switch to the new object connections.
- Customers (GraphQL): the customer group is now a field-selectable nested object — `group { id code name }` — on both the customer listing and detail (REST returns it as a `group` object too). **BREAKING:** the flat `customerGroupId` / `customerGroupName` fields were removed on both transports; read `group { id code name }` (GraphQL) / `group.id`/`group.code`/`group.name` (REST) instead. Customers with no group return `group: null`.
- Customer Reviews (GraphQL): a review's product, customer, and images are now field-selectable instead of flat scalars — `product { id name sku }` and `customer { id name email }` are nested objects, and `images` is a connection (`{ edges { node { … } } }`); REST returns product/customer/images as objects/arrays. **BREAKING:** the flat `productName` / `productSku` / `customerName` / `customerEmail` fields were removed on both transports; read them from the nested `product { name sku }` / `customer { name email }` objects.
- Much faster API responses (typical GraphQL list ~1.5s → ~0.3s); run `bagisto-api-platform:optimize` after deploy and set `APP_DEBUG=false`.
- `bagisto-api-platform:optimize` now also caches events, fails loudly if route caching fails, and warns when `APP_DEBUG` is on.
- Cache clearing no longer times out on large installations.

### Fixed

- Admin API test coverage: added two automated field-completeness guards — a read sweep (`FieldCompletenessTest`) that queries every admin list/detail field and a create-mutation sweep (`MutationCompletenessTest`) that verifies every result field of representative create mutations resolves. These caught (and now lock in) the listing/detail and mutation-response field-resolution fixes below. (Both run serially — invoke them on their own, not under `--parallel`.)
- Admin → Customers (REST): `GET /api/admin/customers/{id}` no longer returns "Internal server error" for a customer who has a date of birth set — the detail now returns correctly with `dateOfBirth` as `YYYY-MM-DD`. (Customers without a date of birth were unaffected; GraphQL was unaffected.)
- Admin listings (GraphQL): several listing rows returned "Internal server error" when you selected certain fields. **Orders** — `isGift` and the tax / tax-inclusive amounts (`shippingTaxAmount`, `subTotalInclTax`, `shippingAmountInclTax`, and their `base*` / `*Refunded` variants) now resolve. **Marketing → Campaigns** — `type`, `mailTo`, and the `channel` / `customerGroup` / `marketingTemplate` objects now resolve (the objects are populated on the detail query; on the listing row they come back `null`). **Marketing → Search Terms** and **Newsletter Subscribers** — `displayInSuggestedTerms` / the `channel` object now resolve. **Customers** — the nested `group { isUserDefined }` now resolves on the listing. **Invoices** — selecting nested `order { … }` fields on the listing no longer errors (the nested order object is detail-only on the listing and resolves `null`; use the flat `orderIncrementId` scalar there, or the detail query for the full order). Found via an automated read-only field sweep across every admin GraphQL query.
- Marketing → Cart Rules (both transports): a **partial update** of a coupon-bearing rule no longer fails with "The coupon code field is required." Previously, sending only a few fields (e.g. `name` + `discountAmount`) on a rule that has a specific coupon code wrongly demanded `couponCode` again, because the rule's existing coupon code wasn't being read back. The update now preserves the existing coupon code when you don't change it — send only the fields you want to update.
- Marketing → Catalog Rules (both transports): **mass-delete** now returns the `skipped` field. It lists the ids that didn't exist and were skipped (alongside `deleted` = the ids actually removed); previously `skipped` was documented but never present in the response, so selecting it over GraphQL returned nothing.
- Marketing → Catalog Rules (GraphQL): **delete** (`deleteAdminMarketingCatalogRule`) no longer returns "Internal server error" when you select `_id` (or `id`) on the result. It now returns a snapshot of the just-deleted rule plus a `message` field (`"Catalog rule deleted successfully."`) — selecting `id`, `_id`, `name`, scalar fields, and `message` works and confirms exactly what was removed. Failures still surface in the top-level `errors` array. (REST delete still returns its `{ message }` confirmation.)
- Catalog → **Products** update (docs, both transports): the **Bundle** update example was wrong — its bundle-option products were keyed `p1` / `p2`, which the store rejects ("No query results"). New rows inside an option must be keyed `product_*`; the example is corrected and a key-prefix reference table was added so the same mistake can't be made for grouped links, downloadable links/samples, custom-option prices, or event tickets.
- Customers (REST / Swagger): the customer-nested action and create endpoints no longer ask for the customer id twice. `POST /customers/{customerId}/impersonate`, `…/gdpr-download-data`, `…/notes`, `…/addresses`, and `…/draft-carts` previously listed a second, redundant required `id` (or `cartId`) path parameter — now they take `customerId` only. The two no-body actions (impersonate, gdpr-download-data) also no longer show a stray `"string"` request-body example (they take no body — the customer is identified by the path).
- Customer Reviews (GraphQL): the reviews listing no longer returns "Internal server error" for a guest review (one with no customer) or a review whose customer or product has since been removed — those entries now list cleanly with the missing customer/product returned as `null`.
- Customers (GraphQL): the **delete** mutations for Customers, Customer Groups, Customer Reviews, and GDPR requests no longer return "Internal server error" when you select `id` on the result. Each now returns a snapshot of the just-deleted record plus a `message` success field — selecting `id`, `_id`, the record's scalar fields (e.g. `status`, `firstName`, `code`, `type`), and `message` works and confirms exactly what was removed. Failures still surface in the top-level `errors` array. REST delete responses were unaffected.
- Settings (GraphQL): every Settings **delete** mutation now exposes a `message` field — selecting `message` on the result returns the success confirmation (e.g. `"Locale deleted successfully."`) so a client can tell the delete succeeded; on read / list / create / update it is `null`, and a refused delete still surfaces in the top-level `errors` array. Covers Locales, Currencies, Channels, Exchange Rates, Inventory Sources, Tax Rates, Tax Categories, Roles, Users, Themes, and Data Transfer Imports. REST delete is unchanged (still returns `{ "message": … }`).
- Customers (GraphQL): the "Login as Customer" (impersonate) and the GDPR process / data-download actions no longer return `null` for multi-word fields (e.g. `customerId`, `customerEmail`, `customerName`, `expiresAt`, `processedAt`, `generatedAt`) — these now resolve correctly. REST was unaffected.
- CMS Pages (GraphQL): **update** (`updateAdminCmsPage`) no longer returns "Internal server error" — editing a page's `urlKey` now works over GraphQL (the storefront redirect it triggers was failing on this transport). REST was unaffected.
- CMS Pages (GraphQL): **delete** (`deleteAdminCmsPage`) now returns a snapshot of the just-deleted page plus a `message` field (`"CMS page deleted successfully."`) — selecting `id`, `_id`, `urlKey`, `pageTitle`, `message`, and the other scalar fields works and tells the client exactly what was removed and that it succeeded. Failures still surface in the top-level `errors` array. Previously the result came back `null`. (REST delete still returns 204.)
- Catalog → Product customer-group prices (GraphQL): the list no longer returns `null` for `productId`, `valueType`, `customerGroupId`, and `customerGroupName` — these fields now resolve correctly. REST was unaffected.
- Catalog → Categories (GraphQL): **update** no longer returns "Internal server error" — editing a category over GraphQL now works (the storefront redirect it triggers was failing on this transport). REST was unaffected.
- Catalog → Attribute options (GraphQL): **create** and **update** now work correctly — previously create saved nothing and update could not be called. REST was unaffected.
- Catalog → Product inventory (GraphQL): bulk inventory **update** no longer returns "Internal server error" — it now returns the updated source so you can read back `sourceId`, `sourceCode`, `sourceName`, and `qty` (the full per-source list with totals stays available via the inventory list query). REST was unaffected.
- Catalog (GraphQL): **delete** mutations for product customer-group prices, categories, attributes, attribute options, and attribute families now return a snapshot of the just-deleted record — selecting `id` / `_id` and the record's fields works and tells you exactly what was removed. Previously the result came back empty. REST delete responses were unaffected.
- Admin GraphQL: bulk **mass-delete** (and mass-update-status) results now return a plain list of affected ids again, instead of being wrapped in an unexpected `{ data, meta }` pagination envelope.
- Settings (GraphQL): **delete** mutations now return a snapshot of the just-deleted record — selecting `id`, `_id` and the record's fields works and tells you exactly what was removed. Previously selecting `id` errored out the whole response. (REST delete still returns its `{ message }` confirmation.)
- Admin Users: a token with the **Users** permission is no longer wrongly denied (403) on create/update/delete.
- Wishlist and Customer Address listings now accept `sort`/`order` (e.g. `?sort=created_at&order=desc`).
- Integration: the token Create/Edit pages no longer show an unrelated **History** tab.
- Hardened tests that assumed fixed seed values (ids, counts, structure) so they pass on any database.

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

[1.0.4]: https://github.com/bagisto/bagisto-api/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/bagisto/bagisto-api/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/bagisto/bagisto-api/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/bagisto/bagisto-api/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/bagisto/bagisto-api/releases/tag/v1.0.0
