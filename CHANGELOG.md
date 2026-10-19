# Changelog

All notable changes to `bagisto/bagisto-api` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- RMA file downloads for the admin API, as the admin panel serves them: REST `GET /api/admin/rma/requests/images/{id}` streams a return image inline and REST `GET /api/admin/rma/messages/{id}/attachment` downloads a message attachment under its original name. Both need `sales.rma.requests`, are REST-only binary responses (`Accept: application/octet-stream`), and also serve files stored on the public disk by earlier versions. The `images[].url` of `GET /api/admin/rma/requests/{id}` (GraphQL `adminReturn`) and the `attachmentUrl` of the RMA messages now point at these endpoints instead of a `/storage/...` link that returned 404 for privately stored files; the field names are unchanged.
- The RMA create form's order picker through REST `GET /api/admin/rma/requests/orders` and GraphQL `adminReturnableOrders`, reusing core's `OrderRMADataGrid` query: orders that can still have a return opened, with id, increment id, customer name, status, payment method, grand total and date filters. Needs `sales.rma.requests.create`, as core's create route does.

### Changed
- On MySQL and MariaDB, model properties that the model does not cast now have the type PostgreSQL gives them and the published schema documents: `int`/`int unsigned` columns are `Int`/`integer` (they were `String` for unsigned columns), `tinyint(1)` columns are `Boolean`/`boolean` (they were `String`), and `decimal`, `timestamp`/`datetime`, `bigint` and `double` columns are `String`/`string` (decimals were `Float`/`number` and timestamps `date-time`). REST bodies are unchanged; GraphQL now serialises these fields by the declared type, so on MySQL a flag that came back as `"1"` comes back as `true`.
- Flag fields on attributes (`isRequired`, `isUnique`, `isFilterable`, `isConfigurable`, `isVisibleOnFront`, `isComparable`, `isUserDefined`, `valuePerLocale`, `valuePerChannel`, `enableWysiwyg`), categories (`status`, and `position` as an integer), bundle options (`isRequired`), bundle option products (`isUserDefined`, `isDefault`) and customizable options (`isRequired`) are cast as core casts them, so they are `true`/`false` on every database. On MySQL the GraphQL `Boolean!` fields returned the string `"1"` and REST returned `1`/`0`; PostgreSQL already returned booleans. This replaces the 2.5.0 entry that kept `0`/`1` for these resources.

### Fixed
- GraphQL `createDownloadableProductDownloadLink` failed, because it wrote to a `downloadable_product_download_links` table that no migration creates. It now checks the purchase is the customer's, invoiced and within its download limits, and returns the purchase `id` and the `url` of `GET /api/shop/customer-downloadable-products/{id}/download`. The mutation no longer counts a download itself; following the URL counts exactly one, as core's storefront download does. The unrouted token download controller is removed.
- GraphQL `readCheckoutAddress` returns the addresses of the bearer token's cart instead of failing to resolve an item.
- GraphQL `customerOrderShipmentItems` nested under a shipment returns only that shipment's items, not every shipment item of the customer.
- Updating an admin role or admin user checks that the caller may grant the role before the last all-access role and admin guards, as core does, so a caller without that right gets 403 instead of a message about the last all-access admin.
- The admin GraphiQL playground (`/api/admin/graphiql`) and the API documentation (`/api/docs`) are served as HTML instead of being forced to a JSON content type.
- The generated OpenAPI documents, GraphQL schemas and Postman collections are regenerated from the current resources.
- The API works with `symfony/property-info` 8, which removed the legacy `Symfony\Component\PropertyInfo\Type` class: the package's property metadata factories read only the native `symfony/type-info` type, so a fresh install that resolves Symfony 8 no longer fails with `Class "Symfony\Component\PropertyInfo\Type" not found`.
- Adding or toggling a wishlist item checks the customer token before it looks up the product, so an anonymous caller gets 401/403 instead of a product-not-found or product-disabled error.
- The exported schema (`php artisan bagisto-api-platform:export-schema`) is identical on MySQL, MariaDB and PostgreSQL, so the committed `schema/generated/` files and the Postman collections no longer go stale when they are regenerated on another database. The GraphQL SDL prints the fields of every type in alphabetical order; field order carries no meaning in GraphQL.

### Security
- Admin product image uploads (REST `POST /api/admin/catalog/products/{productId}/images`) are accepted only when both the type detected from the file's contents and the extension it was sent with are an allowed image type (bmp, jpeg, jpg, png, webp), so a JPEG named `x.php` or `x.html`, an SVG, or an HTML file named `x.png` is refused with 422. The image is re-encoded to webp and stored under a generated name in core's product directory (`products/{id}/<random>.webp`), as the admin panel stores it; a file that cannot be decoded as an image is refused with 422. The response fields are unchanged; only the stored `path` and `url` now end in `.webp`.
- Admin product video uploads (REST `POST /api/admin/catalog/products/{productId}/videos`) need both an allowed detected type and an allowed extension (mp4, webm, ogg, mov), and the file is stored under a generated name with the extension taken from its contents, never from the client, in core's product directory.
- HTML sent through the admin API is now run through core's `clean_content()` before it is saved, as the admin panel does: CMS page `html_content` (create, and the requested locale on update), email template `content` (create and update), category `description` (create and update, top level and every locale block) and every WYSIWYG textarea product attribute on product update. Script tags, Blade echoes and directives, and raw PHP are stripped over REST and GraphQL alike.
- The images archive of an admin import (REST `POST`/`PUT /api/admin/settings/data-transfer/imports`, `upload_images`) is no longer extracted whole. As in core, only image entries (bmp, gif, jpeg, jpg, png, webp) are unpacked, entry by entry, and entries whose names contain `..`, a backslash or a leading `/` are skipped. An archive with more than 5,000 entries or more than 1 GB unpacked is refused with 422 before the import is created.
- Files an admin attaches to an RMA message through REST `POST /api/admin/rma/messages` are stored on the `private` disk under `rmas/{rma}/conversations/{message}`, as core's admin panel stores them. They were saved on the default (public) disk under `rma-conversation/{message}`, where anyone could fetch them from `/storage/...`. Files stored by earlier versions stay where they are; see `UPGRADE.md`.

## [2.5.0] - 2026-10-15

### Added
- Product image search via REST multipart `POST /api/shop/image-searches`, using core image storage and optional Magic AI analysis with TensorFlow fallback. The uploaded file is checked against core's rules (`image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048`) by its real content, so a file of another type is refused whatever name or MIME type it is sent with.
- Approved product review translation via REST `/api/shop/products/{productId}/reviews/{reviewId}/translation` and GraphQL `productReviewTranslation`, gated by both Magic AI settings and limited to 10 requests per minute per IP, as core's route is.
- AI checkout success message on demand through REST `GET /api/shop/checkout-messages/{orderId}` and GraphQL `checkoutMessage(orderId)`, for an order placed with the bearer token. It is generated only when asked for, so placing an order never waits on Magic AI or fails because of it; the message is null when the Magic AI checkout-message feature is off or generation fails, and requests are limited to 10 per minute per IP.
- Public download of file/image product attributes via REST `GET /api/shop/products/{productId}/attribute-files/{attributeId}`, limited to storefront-visible products.
- REST `GET /api/shop/pages?url_key=` filtering, matching pages by URL key across translations while retaining the current-channel scope.
- Newsletter unsubscribe via REST `POST /api/shop/newsletters/unsubscribe` and GraphQL `createNewsletterUnsubscription`, using the email token and returning generic success for unknown tokens.
- Customer GDPR data export via REST/GraphQL `gdprData` and authenticated PDF download at `GET /api/shop/gdpr-data/pdf`; both require GDPR to be enabled and return only the authenticated customer's data. The JSON export carries each saved address (street, city, state, country and the rest) and each order with its items.
- GraphQL twins of the four binary shop endpoints: `createImageSearch` (upload, base64 image), `createCustomizableOptionFile` (upload, base64 file), `productAttributeFile(productId, attributeId)` (download, base64 content) and `gdprDataPdf` (download, base64 PDF). File bytes travel as base64 text so either transport handles them, and the REST upload and download responses are unchanged. The GDPR PDF download now shares one service method between its REST controller and its GraphQL query.
- Guest EU withdrawal confirmation lookup via REST `GET /api/shop/eu-withdrawals/guest/{uuid}?email=` and GraphQL `guestEuWithdrawalConfirmation`. Matching guest-order email is required; mismatch, missing withdrawal, and disabled feature return a generic not-found response, with per-IP/UUID throttling.
- REST `POST /api/shop/estimate-shippings` now mirrors the existing GraphQL shipping estimate using the bearer cart, address fields, and optional shipping method.
- REST `POST /api/shop/move-to-wishlists` now mirrors the existing GraphQL cart-to-wishlist mutation, with cart-scoped item IDs and optional per-item quantities.
- Resending the account verification email through REST `POST /api/shop/verification-emails` and GraphQL `createResendVerificationEmail`. A fresh 64-character token is written and core's `EmailVerificationNotification` is queued; unknown addresses get the same success response so the endpoint cannot be used to discover registered emails. Limited to 6 requests per minute per IP, as core's route is.
- Resetting a customer password with the emailed token through REST `POST /api/shop/reset-passwords` and GraphQL `createResetPassword` (`password_confirmation`, `passwordConfirmation` in GraphQL). The reset is scoped to the current channel as core's is, and it revokes every API token of the customer and signs out their storefront sessions.
- Verifying a customer account with the emailed token through REST `POST /api/shop/verify-accounts` and GraphQL `createVerifyAccount`, as core's verify link does, sending the registration email when that setting is on.
- Headless Stripe checkout. REST `POST /api/shop/checkout-payments/initiate` and GraphQL `createCheckoutPaymentSession` create the Stripe Checkout Session for the bearer cart (cart token or customer token) and return its `sessionId` and `redirectUrl`. REST `POST /api/shop/checkout-payments/confirm` and GraphQL `createCheckoutPaymentConfirmation` then verify the session with Stripe (paid, same cart, same amount and currency) and place the order through core's Stripe settlement. Confirming is repeatable: when the webhook or an earlier call already placed the order, that order is returned and no second one is created. Other redirect gateways have no headless initiate step yet.
- Cart cross-sells through REST `GET /api/shop/cart/cross-sells` and GraphQL `cartCrossSells`, using the current bearer cart and the storefront's configured cross-sell rules.
- Cart payload (REST `POST /api/shop/cart` and every cart mutation, GraphQL `readCart` and the cart mutations) now carries `miniCartOfferInfoEnabled` and `miniCartOfferInfo`, the offer text set under Sales, Checkout, Mini Cart. `miniCartOfferInfoEnabled` is true only when the mini cart is shown and the text is not empty. The text follows the request locale.
- Store settings for storefronts and apps: `GET /api/shop/store-config` and the GraphQL query `storeConfig` return the public settings of the current channel — guest checkout, minimum order amount and message, review rules, compare, wishlist, buy now and image search switches, listing defaults, email verification, newsletter, enabled social login providers, tax display, and the GDPR and EU withdrawal switches. Only a fixed list of non-secret settings is returned.
- Bagisto 2.5.0 support (Laravel 13, PHP 8.4). This release requires Bagisto v2.5.0: the package now declares `php: ^8.4` and `illuminate/support: ^13.0`.
- PostgreSQL support. Every list, search, sort and export that relied on MySQL-only SQL now works on PostgreSQL 16 as well, and the full suite passes on both databases with identical results.
- Attribute translations through GraphQL `attributeTranslations` and `attributeTranslation`, mirroring the existing REST `/api/shop/attribute_translations` collection and item endpoints.
- Romanian (`ro`) translations, and the new admin and installer messages in every shipped locale.

### Fixed
- The installer always caches the configuration and routes at the end (`php artisan optimize`), so a fresh store does not rebuild the API routes on every request.
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
- Forgot password sends the reset link for the account on the current channel, as core does, so an email registered on two channels resets the right account.
- The Bagisto Shop API Postman collection groups the newsletter-unsubscribe GraphQL mutation under the `Newsletter` folder, so every GraphQL folder has a REST counterpart.
- GraphQL `deleteAdminCustomerAddress` always failed with "customerId is required". The customer is read from the address IRI (or the address itself), the ownership and permission checks still apply, and the deleted address is returned.
- GraphQL `updateAdminCatalogProductImage` returned `productId` and `altText` as null after a correct save, and the image and video mutations returned `productId` as null. The REST responses are unchanged.
- GraphQL `readGetCustomerAddress` returned an empty object without checking ownership, and `readCustomerAddressToken` always failed. Both now return the calling customer's address (id, name, street lines, city, country, default flag) and refuse another customer's address.
- GraphQL `collectionCustomerAddressToken` is removed: it could never return data. Use `customerAddresses` or `getCustomerAddresses` to list the customer's addresses.
- The root GraphQL queries `productBundleOptionProducts`, `productDownloadableLinks`, `productDownloadableSamples` and `productGroupedProducts` failed; they now return a cursor-paginated list like `productImages`. The fields nested under a product or bundle option are unchanged.
- GraphQL `collectionDefaultChannels` returned null for every multi-word field (`isMaintenanceOn`, `rootCategoryId`, `logoUrl`, `defaultLocale` and the rest).

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

