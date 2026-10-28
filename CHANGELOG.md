# Changelog

All notable changes to `bagisto/bagisto-api` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.4.10] - 2026-10-15

Security and correctness release from the Bagisto v2.4.10 API audit. Tested against Bagisto v2.4.10.

### Added

- `createCheckoutOrder` and `POST /api/shop/checkout-orders` return `incrementId`: the order number shown to the shopper, with the configured prefix, length and suffix, as on the storefront order success page. It is null when `redirect` is true, because no order exists yet.
- Admin Create Order can add booking products, as the admin panel does in 2.4.10. `POST /api/admin/carts/{id}/items` and `addItemAdminCart` take a `booking` object (`type`, `date`, `slot`, and the rental, table or event fields).
- `GET /api/admin/booking-products/{productId}/config` (`configAdminBookingProduct`): what the Create Order booking drawer shows — sub-type, availability window, calendar, and the rental, table or event-ticket data.
- `GET /api/admin/booking-products/{productId}/slots?date=` (`adminBookingProductSlots`): the slots a booking product offers on one date.
- `GET /api/admin/marketing/cart-rules/condition-attributes` (`adminMarketingCartRuleConditionAttributes`) and `GET /api/admin/marketing/catalog-rules/condition-attributes` (`adminMarketingCatalogRuleConditionAttributes`): every attribute a rule condition can test, with its type and options, and the operators each type accepts — the lists the admin rule forms show. A client can now build `conditions` without guessing codes.
- The image download step of a products import with `image_source=url`: `POST /api/admin/settings/data-transfer/imports/{id}/download-images` (one wave), `POST .../download-images/queue` and `GET .../download-images/status`, with `downloadAdminSettingsDataTransferImportImages`, `queueAdminSettingsDataTransferImportImages` and `adminSettingsDataTransferImportImages`. Run it after validate and before start, as the admin panel does. Before, a URL import through the API created the products without their images.
- `GET /api/shop/store-config` and the `storeConfig` query: the public storefront settings of the current channel, so an app can decide what to render before it calls an endpoint that might refuse. Only a storefront key is needed. Send `X-Channel` or `X-Locale` to read another channel or language.
  - **Checkout:** `guestCheckoutEnabled`, `cartCrossSellEnabled`, `estimateShippingEnabled`, `reorderEnabled`, `cartSummary`, `cartPageEnabled`, `miniCartEnabled`, `miniCartOfferInfo`, `backordersEnabled`.
  - **Minimum order:** `minimumOrderEnabled`, `minimumOrderAmount`, `minimumOrderIncludesDiscount`, `minimumOrderIncludesTax`, `minimumOrderMessage`.
  - **Order number:** `orderNumberPrefix`, `orderNumberLength`, `orderNumberSuffix` — the format of order numbers (Sales → Order Settings → Order Number); each is null when not set. `orderNumberGenerator` is the configured generator class, null when the default is used.
  - **Order settings:** `orderMaxRetryAttempts` (order creation retries, default 3) and `adminReorderEnabled` (whether reorder is on in the admin panel; `reorderEnabled` is the storefront switch).
  - **Reviews:** `customerReviewEnabled`, `guestReviewEnabled`, `reviewerNameCensored`, `reviewSummary`.
  - **Products:** `compareEnabled`, `wishlistEnabled`, `imageSearchEnabled`, `socialShareEnabled`, `productsPerPage`, `listingMode`, `defaultSortBy`, `buyNowButtonEnabled`, `relatedProductsLimit`, `upSellProductsLimit`, `crossSellProductsLimit`, `searchMinQueryLength`, `searchMaxQueryLength`, `socialShareProviders`, `socialShareMessage`.
  - **Layout and content:** `categoryView`, `breadcrumbsEnabled`, `headerOfferTitle`, `headerOfferLinkTitle`, `headerOfferLink`, `footerCopyright`, `weightUnit`, `customCss`, `customJavascript`, `speculationRules` (the ready-to-use speculation rules JSON, null when switched off), `sitemapEnabled`, `contactName`, `contactEmail`.
  - **SEO:** `richSnippets` (which product and category structured-data blocks are switched on).
  - **Customer:** `emailVerificationRequired`, `newsletterEnabled`, `captchaEnabled`, `captchaSiteKey` (the public site key only), `signupNewsletterEnabled`, `loginRedirectTo` (`home` or `account`, the page a customer lands on after signing in), `socialLoginProviders` (the providers that are switched on and that social login accepts: `google`, `facebook`, `linkedin`).
  - **Addresses:** `addressCountryRequired`, `addressStateRequired`, `addressPostcodeRequired`, `addressStreetLines`.
  - **Tax display:** `taxCartDisplayPrices`, `taxCartDisplaySubtotal`, `taxCartDisplayShipping`, `taxCartShowBreakdown`, `taxOrderDisplayPrices`, `taxOrderDisplaySubtotal`, `taxOrderDisplayShipping`.
  - **Tax calculation:** `taxCalculationBasedOn`, `taxProductPrices`, `taxShippingPrices`, `taxApplyOn`, `taxDefaultCountry`, `taxDefaultState`, `taxDefaultPostcode`, and the tax categories `taxShippingCategoryId` and `taxProductCategoryId` (null when set to None).
  - **Store information:** `storeInformation` (store name, address, VAT number, contact and bank details, as printed on customer documents).
  - **Privacy:** `gdprEnabled`, `gdprAgreementEnabled`, `gdprAgreementLabel`, `gdprAgreementContent`, `cookieConsentEnabled`, `cookieConsentPosition`, `cookieConsentDescription`, `cookieConsentStaticBlockIdentifier`, `cookieConsentCategories`.
  - **Returns:** `returnWindowDays`, `returnPolicy`, `returnAllowedFileTypes`, `returnAllowedProductTypes`, `returnAfterCancelledAllowed`, `returnAfterDeclinedAllowed`.
  - **Optional features:** `euWithdrawalEnabled`, `aiImageSearchEnabled`, `aiReviewTranslationEnabled`, `aiCheckoutMessageEnabled` (each is true only when Magic AI itself is also on).
  - Secrets are never returned: API keys, client secrets, mail and payment credentials stay server-side.
- `miniCartOfferInfoEnabled` and `miniCartOfferInfo` on every cart payload: the cart token, read cart, add, update and remove item, coupon, merge and move-to-wishlist responses, on both REST and GraphQL. They carry the mini cart offer from **Sales → Checkout → Mini Cart**, so an app can show it in the cart header. `miniCartOfferInfoEnabled` is true when the mini cart is switched on and an offer text is set.
- `POST /api/shop/reset-passwords` and the `createResetPassword` mutation: set a new customer password with the token from the forgot-password email, so an app can finish a password reset without the storefront page. Sends `token`, `email`, `password` and `passwordConfirmation`. A token works once; an invalid or expired token returns 422.
- `POST /api/shop/verify-accounts` (`createVerifyAccount`) and `POST /api/shop/verification-emails` (`createVerificationEmail`): verify a customer account with the token from the verification email, and send that email again. Before this, an app could not finish email verification, and unverified customers could not sign in.
- `POST /api/shop/estimate-shippings` and `POST /api/shop/move-to-wishlists`: REST routes for estimating shipping on the cart page and moving cart items to the wishlist. Both were GraphQL only.
- Estimate shipping (REST and `createEstimateShipping`) now also returns `shippingMethods`, the methods available for the estimate address, as the storefront does.
- `createCustomerReturn` (GraphQL) and the JSON body of `POST /api/shop/returns` take evidence `images` as base64 data URIs, the same format as the profile image, max 5 MB each. Before, a return with images needed a REST multipart request. The real file type is checked against Configuration → Sales → RMA → Allowed file extension.
- `POST /api/shop/newsletters/unsubscribe` (`createNewsletterUnsubscribe`): unsubscribe with the token from the newsletter email link, as the storefront does.
- `GET /api/shop/url-resolver?path=` (`urlResolver(path:)`): tells a headless storefront what a path is, exactly as the storefront's catch-all route decides it: a category slug, a product URL key, the last segment of a nested category path, or a URL rewrite with its 301/302 target.
- `GET /api/shop/return-messages` accepts `limit` and `page`, as the storefront's message list does.
- `POST /api/shop/image-searches` (`createImageSearch`): search by image, as the storefront camera search does. Takes a multipart file or a base64 data URI; with Magic AI image search on it returns keywords to search with.
- `GET /api/shop/search-redirect?query=` (`searchRedirect`): the redirect URL set on a search term in Marketing → Search Terms, which the storefront follows before searching.
- `GET /api/shop/gdpr-data` (`gdprData`) and `GET /api/shop/gdpr-data/pdf`: the signed-in customer's personal data (profile, saved addresses, orders) as JSON or as the PDF the storefront GDPR page downloads. Needs GDPR switched on.
- Online payments over the API. `POST /api/shop/checkout-payments/initiate` (`createCheckoutPaymentInitiation`) starts payment for the gateway saved on the cart and says how to hand the customer over: `redirect` (Stripe, PhonePe, PayGlocal), `form` (PayPal Standard, PayU) or `sdk` (Razorpay). `POST /api/shop/checkout-payments/confirm` (`createCheckoutPaymentConfirmation`) places the order after a Razorpay or PayPal Standard payment; the Razorpay signature is verified and the Razorpay order must belong to the cart and its amount. Before, place-order only returned the storefront redirect page, which reads the cart from the browser session, so only offline methods and PayU could complete an order through the API.
- `GET /api/shop/cart/cross-sells` and the `cartCrossSellProducts` query: the cross-sell products the storefront cart page shows, without the products already in the cart.
- `GET /api/shop/pages?url_key=`: look up a CMS page by its URL key over REST, matching the GraphQL `pageByUrlKeypages` query.
- Admin orders: quick search `?query=` / `adminOrders(query:)` matching customer email, status, customer name or the exact order number, as the admin orders search does; and `?customer_id=` / `customerId` to list one customer's orders.
- Admin invoices: `?customer_id=` / `adminInvoices(customerId:)` lists one customer's invoices.
- Admin customers: quick search `?query=` / `adminCustomers(query:)` matching email or full name.
- `GET /api/admin/bookings/calendar?start=&end=` / `getAdminBookingCalendar(start:, end:)`: every booking in a date window (current week by default) with formatted slot times and booking attributes, as the admin bookings calendar shows.
- Datagrid exports as csv, xls or xlsx, honouring the listing filters, like the admin Export button: `GET /api/admin/eu-withdrawals/export`, `GET /api/admin/customers/export`, `GET /api/admin/customers/gdpr-requests/export`, and a cart rule's coupons at `GET /api/admin/marketing/cart-rules/{cartRuleId}/coupons/export` (REST only).
- Admin Create Order sidebar: remove an item from the customer's active storefront cart, wishlist or compare list with `DELETE /api/admin/customers/{customerId}/cart-items|wishlist-items|compare-items/{itemId}`, or `removeAdminCustomerCartItem` / `removeAdminCustomerWishlistItem` / `removeAdminCustomerCompareItem` (input `id` = `/api/admin/customers/{customerId}/<list>/{itemId}`).
- Admin returns created over GraphQL (`createAdminReturn`) now save custom field answers sent as `customAttributes`.
- Admin product search (`/api/admin/products`, `adminProducts`) accepts `exclude_customizable_products` (with `type=simple`) and `customerId` (limits to the customer's channel), as the admin product-link and Create Order pickers do.
- Product videos can be reordered: `PUT /api/admin/catalog/products/{productId}/videos/reorder` / `reorderAdminCatalogProductVideo`.
- `GET /api/admin/catalog/products/{productId}/cart-options` / `adminCatalogProductCartOptions(productId:)`: the add-to-cart options the admin Create Order drawer needs for a product (configurable variants and prices, bundle options, grouped products, downloadable links, customizable options).
- `GET /api/admin/acl` / `getAdminAcl`: the permission tree the role screen shows, so a client can see every valid permission key.
- `POST /api/admin/configuration/cache` / `createAdminConfigurationCache`: the admin Cache Management actions (clear or build config, routes, views, events, compiled files, application cache), limited to core's action list.
- `GET /api/admin/configuration/search?query=` / `searchAdminConfiguration(query:)`: search the configuration screens, returning each match's title, admin URL and slug.
- `GET /api/admin/configuration/download?code=`: download the file stored for a file or image configuration field (REST only).
- Category logo and banner can be uploaded (`POST /api/admin/catalog/categories/{id}/media`, multipart `logo` or `banner`, with optional `alt_text`, `file_name`, `locale`) and removed (`DELETE …/media/{logo|banner}`), stored as the admin panel stores them (REST).
- Categories carry per-locale `logoAlt` and `bannerAlt` on create, update and detail, over REST and GraphQL.
- Attribute options: upload an image swatch (`POST /api/admin/catalog/attributes/{attributeId}/options/{optionId}/swatch`, multipart `swatch_value`, REST), and `swatchAlt` / `swatchFileName` on option create, update and read (REST + GraphQL).
- Admin user avatar upload and removal (`POST`/`DELETE /api/admin/settings/users/{id}/image`, REST).
- Channel logo and favicon upload and removal (`POST`/`DELETE /api/admin/settings/channels/{id}/logo` and `/favicon`, REST).
- Locale logo upload and removal (`POST`/`DELETE /api/admin/settings/locales/{id}/logo`, REST).
- Magic AI for admins: generate text (`POST /api/admin/magic-ai/content`, `createAdminMagicAiContent`) and images (`POST /api/admin/magic-ai/image`, `createAdminMagicAiImage`) with the configured AI provider, refused while the feature is switched off.
- My account: edit your own name, email and password with your current password (`PUT /api/admin/account`, `updateAdminProfile`), and upload or remove your avatar (`POST`/`DELETE /api/admin/account/image`, REST). The profile read now includes `imageUrl`.
- Two-factor authentication for the token's admin: set up (`GET /api/admin/account/two-factor`, `setupAdminTwoFactor`), enable (`POST …/enable`, `enableAdminTwoFactor`) and disable (`POST …/disable`, `disableAdminTwoFactor`); disabling needs a current code or a backup code.
- Notifications: the bell list with per-status counts and the unread total (`GET /api/admin/notifications`, `adminNotifications`, `adminNotification(id:)`, `summaryAdminNotification`), mark one order's notification read (`POST /api/admin/notifications/{orderId}/read`, `readAdminNotification`) and mark all read (`POST /api/admin/notifications/read-all`, `readAllAdminNotification`).
- Large import files can be validated window by window (`POST /api/admin/settings/data-transfer/imports/{id}/validate-chunk`, `chunkAdminSettingsDataTransferImportValidation`) or as a queued background batch (`POST .../validate/queue`, `queueAdminSettingsDataTransferImportValidation`), with `GET .../validate/status` / `adminSettingsDataTransferImportValidation(importId:)` reporting progress and, once done, the verdict and the import, as the admin panel does.
- `GET /api/admin/settings/data-transfer/imports/sample-images/{type}` downloads an importer's sample images ZIP (REST only).
- `GET /api/shop/categories/attributes` (with `category_id` or `category_slug`) and a `categoryId` argument on `categoryAttributeFilters`: a category's filterable attributes, or the store-wide filterable attributes when no category is given, as the storefront filter panel does.
- Attribute options can be searched by label (`search`) and scoped to one attribute (`attribute_id` / `attributeId`) on `GET /api/shop/attribute-options` and `attributeOptions`. Boolean attributes return their No (0) and Yes (1) options.
- Shop search runs on Elasticsearch when the store's search engine is set to Elasticsearch, including the "did you mean" correction. Send `suggest=0` (REST) or `suggest: false` (GraphQL) to search the exact term. `GET /api/shop/search-redirect` / `searchRedirect` gains a `suggestion` field (null on the database engine).
- `GET /api/shop/compare-attributes` and `compareAttributes`: the attributes the storefront compare page shows.
- PayPal Smart Button checkout. `checkout-payments/initiate` creates the PayPal order and returns its id, client id and currency for the PayPal JS SDK (`type: sdk`). `checkout-payments/confirm` with `paypal_order_id` captures the approved payment and places a paid, invoiced order, as the storefront does. The PayPal order must match the cart's total and currency. Placing the order directly (`checkout-orders`) still refuses this method with a 422 pointing to the initiate/confirm flow.
- `GET /api/shop/customer-refunds[/{id}]` (`customerRefunds`, `customerRefund`): the signed-in customer's refunds with their items and totals, as the storefront order page shows them. Filter by order with `orderId`.
- The Romanian (`ro`) translation of the API messages.
- The Postman collections and the exported OpenAPI and GraphQL schemas include the new endpoint and fields.

### Security

- Guest EU withdrawal now needs proof that the guest owns the order email, as on the storefront. `POST /api/shop/eu-withdrawals/guest/lookup` (`createGuestEuWithdrawalLink`) emails a signed link valid for 24 hours, and `POST /api/shop/eu-withdrawals/guest` (`createGuestEuWithdrawal`) now takes that `link` instead of the order number and email. Before, anyone who knew an order number and its email could withdraw it. Both steps are rate-limited per IP like the storefront (5 and 20 a minute). **Breaking:** clients that sent `order_increment_id` and `email` to the guest create must switch to the two-step flow.

- Admin read endpoints now enforce the same ACL key as the matching admin screen. A `permission_type=custom` token without the key gets `403`, where it previously read the data.
- A disabled admin's integration token is rejected.
- A token's abilities are capped by its owner's role. A custom token holding `*` gets exactly the role's permissions, never more.
- The IP allowlist on an integration token no longer admits `127.0.0.1` when it is not in the list.
- A customer token is fully verified. A forged token that carries a valid id is rejected.
- GraphQL queries need a storefront key unless they are pure introspection. Queries sent as a GET, as a raw GraphQL body, mixed with introspection, or named after a public operation are no longer let through.
- An expired or deleted storefront key stops working at once, including a key already cached. Rate limits apply per client IP, and the GraphQL rate limit is enforced.
- Customers can read only their own invoices, downloadable products and carts. A guest or another customer gets an error.
- A downloadable link no longer exposes the paid file or URL before purchase.
- A review ignores a client-sent `status` and owner name.
- Product `cost` is no longer exposed on the storefront.
- Products that are disabled on the channel, or not assigned to it, are no longer served.
- The impersonate endpoint requires the core "login as customer" permission. The GDPR download-data endpoint requires the core GDPR key.
- API and GraphQL responses are never stored in the full-page cache.
- Social login (`POST /api/shop/customers/social-login`, `createSocialLogin`) accepts a token only when it was issued to the store's own app, as the redirect sign-in does. Google ID and access tokens are checked against the Google client id, Facebook tokens with Facebook's token check, and LinkedIn tokens with LinkedIn's token introspection. A provider without a configured client id now answers `PROVIDER_NOT_CONFIGURED`; before, a Google token from any app signed the customer in.

### Fixed

- The `options` of an attribute over GraphQL — on `categoryAttributeFilters` (the layered-navigation filters) and on `attribute(id:)` — list that attribute's own options again, as the storefront filters do. Before, every attribute returned the same first options of all attributes, so Color, Size and Brand could not be filtered from an app. REST was not affected.
- `attributeOptions(first: 0)` returns an empty page with the total count. Before, it failed with an internal server error.
- Return messages (`createCustomerReturnMessage`, `POST /api/shop/return-messages` with a JSON body) accept an attachment as a base64 data URI in `attachment`, with an optional `attachmentName`, so an app can attach a file as the storefront conversation does. Before, only a multipart REST request could attach a file.
- Returns (RMA) now refuse what the storefront refuses: a solved, declined, received or item-canceled return can no longer be canceled, a return can only be closed while core's `canCloseRma` allows it, and closing or reopening is refused once the return window has passed. Before, the API canceled or closed a return in any state.
- Updating the profile now applies the storefront rules to the fields sent: email unique within the channel (it was checked across all channels), the core phone format (a leading `+` is now accepted) and phone uniqueness, a birth date in the past, and a new password of at least 6 characters with `confirmPassword`. A password change now fires `customer.password.update.after`, so the "password changed" email is sent.
- Customer addresses (`/api/shop/customer-addresses`) now apply the storefront address rules: `phone` (core format) and `email` are required, and country, state and postcode follow the store's required-field settings, with the core postcode and VAT rules.
- Newsletter subscribe (`POST /api/shop/newsletters`, `createNewsletter`) now works like the storefront: an email that unsubscribed can subscribe again, `customer.subscription.before`/`after` fire, a signed-in customer's newsletter flag is set, and the current channel is always used.
- When a coupon reaches its usage limit at place order (or payment confirm), the coupon is now removed from the cart and the totals recollected, as the storefront does, so the retry succeeds. Before, every retry failed with a generic error.
- **Breaking:** Deleting your own account (`POST /api/shop/customer-profile-deletes/{id}`, `createCustomerProfileDelete`) now requires `password`, as the storefront does, and is refused while an order is pending or processing. Before, the bearer token alone deleted the account.
- Customer login now looks the email up in the current channel only, as the storefront does. On a multi-channel store the same email can belong to two accounts, and login could pick the other channel's. Login and logout now fire `customer.after.login` and `customer.after.logout`.
- When the store turns reCAPTCHA on, login, registration, forgot password and contact-us now require a valid token, as the storefront does. Send it as `recaptchaToken` (GraphQL input), `recaptcha_token` (REST body) or the `X-Recaptcha-Token` header. Before, the API skipped the check.
- **Breaking:** Product reviews (`GET /api/shop/reviews`, `/products/{productId}/reviews`, `productReviews`) now list approved reviews only; the `status` filter is gone. `GET /api/shop/reviews/{id}` and `productReview` return an approved review or the caller's own review. Reviewer names are censored when Configuration → Catalog → Products → Review → Censor reviewer name is on. Before, any caller could read pending and rejected reviews.
- CMS pages (`GET /api/shop/pages`, `/pages/{id}`, `pages`, `page`) now return only pages assigned to the current channel. The REST list also accepts `per_page`.
- Updating a channel (`PUT /api/admin/settings/channels/{id}`, `updateAdminSettingsChannel`) no longer deletes its logo and favicon. Any update that did not send them removed both files.
- Updating a category (`PUT /api/admin/catalog/categories/{id}`, `updateAdminCategory`) no longer deletes its logo and banner. Any update that did not send them removed both images.
- Admin users and roles now have core's lockout guards (422 on REST, an `errors[]` entry on GraphQL). The only active admin with full access cannot make their own account inactive, and the last admin with full access cannot be moved to another role. A role cannot be changed from all access to custom while it gives the last admin full access.
- Adding an item to an admin draft cart over GraphQL no longer stores the GraphQL query and variables on the cart item. The order item kept them as extra options.
- Changing an admin user's password through the API now fires `admin.password.update.after`, as the admin panel does.
- Product search (REST `?query=` and GraphQL `products(query:)`) now works like the storefront search: names also match the terms in Marketing → Search Synonyms, and a plain search is recorded in Marketing → Search Terms with its result count.
- CMS page lookup by URL key (REST `?url_key=` and GraphQL `pageByUrlKeypages`) now works like the storefront page: only pages assigned to the current channel are returned, and a CMS URL rewrite for an old key is followed. Before, GraphQL also returned pages of other channels.
- Raising a return (`POST /api/shop/returns`) now stores the evidence image when it is sent as a single `images` field. It was accepted with 201 but silently dropped, because only `images[]` was read back. Accepted file types follow Configuration → Sales → RMA → Allowed file extension, as in the storefront (JPG, PNG and WEBP by default; core has no video option).
- Registering or updating a profile through the shop API now keeps the newsletter subscriber list in step, as the storefront does: a sign-up with the newsletter creates the subscriber, an earlier guest subscription is linked to the new account, and turning the newsletter off unsubscribes it. Before, API customers never appeared in Marketing → Newsletter Subscriptions.
- A shop profile update that does not send `subscribedToNewsLetter` no longer unsubscribes the customer.
- Moving cart items to the wishlist now requires a signed-in customer. A guest cart previously created wishlist rows with no owner.

- Remove the `total-visitors` dashboard stat and the `customers-traffic` and `products-with-most-visits` reporting stats. Bagisto v2.4.10 no longer tracks visitors, so these types returned a server error; they now return the invalid-type error like any unknown type.
- Checkout accepts only payment methods that are enabled and available for the cart. Smart-button methods are refused.
- Fix Cash on Delivery missing from `GET /api/shop/payment-methods` and `collectionPaymentMethods` even when it is enabled. The list is now worked out against the caller's cart, as web checkout does, so Cash on Delivery shows whenever the cart holds only physical items.
- Checkout enforces guest checkout, stock, the minimum order amount and suspended accounts, as the storefront does.
- Checkout validates the address like the storefront form, with a `422` for a missing field or an invalid phone. Saving an address again resets the chosen shipping method.
- An order carries the customer's name, email, guest flag and currency codes.
- A cart rule for the customer's group applies to a Bearer request.
- Buy Now leaves one active cart that holds only the bought product.
- Merging carts requires the guest cart token, and the guest cart is not left active afterwards.
- `X-Channel` is applied to the request. An unknown channel returns `400`, and `X-Locale` and `X-Currency` are checked against that channel.
- The product price filter reads the price index for the customer's group, converts the range to the base currency, and accepts `price=a,b`. Configurable products now match.
- Unknown query parameters on the product listing are ignored instead of filtering the list.
- The downloadable link price is converted only once.
- Product listings show only products that are visible individually. A single product lookup by id, SKU or URL key still returns a variant or child product.
- ACL keys now match core: `sales.bookings`, `customers.gdpr_requests`, and `settings.data_transfer.imports.import` for running an import and for reading its stats and downloads.
- A customer's orders, invoices, shipments and invoice PDFs are now found by the customer id alone, as the storefront does. When a package replaces the customer model, orders stored with that model's class were missing from `customerOrders`, `customerInvoices` and `customerOrderShipments`.
- The `addresses` connection on `customerInvoice` / `customerInvoices` now returns the order's billing and shipping addresses over GraphQL; it was always empty.
- The `/api` landing page now comes from the `GET /api` route, so an add-on that registers its own landing page (for example with an extra API card) is shown. Before, the page always listed Shop and Admin only. Its links now include the install path, so they work when the store runs in a sub-folder.
- Saving a channel or a configuration in the admin panel no longer empties the API's metadata cache. The full-page cache flush wiped the whole `file` cache store, so the next API request rebuilt the metadata and could time out with a 500. With a file-backed cache the metadata now lives in its own directory, `storage/framework/cache/bagisto-api`.
- Social login now finds an existing account by email within the current channel only, as customer login does, and fires `customer.after.login` like the storefront.
- A signed-in customer now sees their own customer group's product prices on product listings and product detail (REST and GraphQL). Before, the bearer token was not applied to catalog reads, so they saw guest prices while the cart charged their group price.
- `minPrice` and `maxPrice` on category filters are returned in the requested currency (`X-Currency`) instead of the base currency.
- Compare add, remove and clear-all fire the storefront's `customer.compare.*` events, and creating a product review fires `customer.review.create.before`/`after`, so listeners run on API requests too.
- The wishlist listing drops items whose product has been disabled, as the storefront does.
- Shop reorder skips booking items and says so in the response `message`.
- A Razorpay payment confirmed through the API now records the paid amount on its transaction; it was always 0.
- **Breaking:** shop reorder returns 422 when reorder is switched off for the storefront in store settings, or when the order cannot be reordered (a guest order, or an item no longer for sale). Before, items no longer for sale were skipped silently.
- **Breaking:** a GDPR request `message` is limited to 500 characters, and a request can be revoked only while `pending`, as on the storefront.
- **Breaking:** wishlist endpoints return 403 when the wishlist is switched off (Configuration → Customer → Settings → Wishlist), and compare endpoints and `compare-attributes` return 403 when product comparison is switched off (Configuration → Catalog → Products → Settings).
- **Breaking:** Contact Us rejects a `contact` value that is not a phone number (an optional `+` followed by digits), as the storefront does.
- The quantity of an appointment or event booking item in the cart can be changed (`POST /api/shop/update-cart-item`, `createUpdateCartItem`), as on the storefront cart page. The API refused it even though the cart reported `canChangeQty: true`. Core's availability check still applies.

## [2.4.4] - 2026-09-14

### Added

- One package now serves Bagisto 2.4.10 and the releases before it. The theme surface follows the store it is installed on: a 2.4.10 store gets the Appearance API, an older store keeps the theme customization endpoints it already had. Nothing else in the API changes shape between them.
- `GET /api/shop/features` and the `storefrontFeature` query — which optional features this channel has switched on, so a storefront can decide what to render before calling something that might refuse. Covers GDPR data requests and the EU right of withdrawal.
- `GET /api/shop/returnable-orders` and the `returnableOrders` query — the customer's orders a return can still be raised against, which is the list the New Request flow opens with. Filter by order number or status; sort by date, order number or total.
- Appearance API for Bagisto 2.4.10: theme gallery, impact report, activation per channel, and section management for the theme a channel runs.
- Section draft workflow: stage content per locale, stage status and order, then publish or discard in one call.
- Section preview endpoint, rendering a theme with its staged edits applied.
- Section fields endpoint, returning the field schema for a section's type plus its current values.
- Section media upload (REST only) and section copy.
- `GET /api/shop/theme` and the `theme` query — the theme the current channel runs and the section types it holds.
- Sitemap channels: a sitemap now carries `channels`, returns the index URL per channel, and reports `generatedFiles`.
- Product image `alt_text`, settable on upload and editable via `PUT /api/admin/catalog/products/{productId}/images/{id}`.
- Product import image sources — `image_source` of `url`, `upload` or `directory`, with `upload_images` for a ZIP.
- `manageStock` on the product listing.

### Fixed

- Section update dropped `options` and `locale`, so content could not be published through it.
- Section update erased a locale's content when `options` were omitted.
- Section create ignored the `channel` it named.
- Copying a footer links section produced a second one; it is refused, as in the admin.
- Section media upload of an unsupported type failed with a server error; it is refused with the admin's types and 50 MB limit.
- Reordering a subset of sections reshuffled the published order; a reorder must carry every section of one theme and channel.
- Storefront sections returned only ten per page; the default is now 50.
- Exported GraphQL schemas dropped every array-valued field.
- Creating or updating a sitemap failed outright, and a channel-less sitemap generated nothing.
- Product listing worked out image, quantity, category and family on the fly; they are read as the admin reads them.
- Storefront product images came back in arbitrary order; they are in gallery order.
- Deleting an email template or marketing event still used by a campaign is refused.
- An attribute `regex` the storefront cannot compile is refused.
- Customer email is unique per channel, not per store.
- Customer registration demanded `status`, `isVerified`, `isSuspended` and `subscribedToNewsLetter`, none of which a registering customer should send. They are optional now, and the account's state is decided by the store as it is on the storefront: a new account is active and unsuspended, and starts verified only when email verification is switched off. A value sent for any of them is accepted and ignored, so existing clients keep working.
- Installing the package added an autoload entry for a `Webkul\GraphQL` package that does not exist and that nothing in the API refers to. It is no longer added. A store installed before this release still carries the entry; delete that line from `composer.json` and run `composer dump-autoload` to clear it.
- Attaching a file to a return message was impossible: the endpoint documented a `file` upload but rejected the multipart request it needed. `POST /api/shop/return-messages` now accepts `multipart/form-data` and stores the attachment.
- A customer registered with a `status` of `"active"` was created inactive, because the value was written to the account's active flag as-is. The flag is no longer taken from the request at all — a new account is always active.
- A new account was created unverified unless the request said otherwise, so a customer who sent nothing could not sign in afterwards. Verification now follows the store's email verification setting, as the storefront sign-up does: verified while that setting is off, awaiting confirmation while it is on.
- A customer registered through the API never received a working verification link, because the account was created without the token the verification email carries.

### Changed

- On a Bagisto 2.4.10 store, storefront theme customizations are sections: `/api/shop/theme-customizations[/{id}]` and `themeCustomization(s)` become `/api/shop/sections[/{id}]` and `section`/`sections`, and translations carry `sectionId`. On an older store the previous endpoints stay exactly as they were — upgrading the store is what moves them.
- On a Bagisto 2.4.10 store, storefront sections return only the published sections of the channel's active theme.
- On a Bagisto 2.4.10 store, admin theme customizations move out of Settings: `/api/admin/settings/themes` and `adminSettingsTheme*` become `/api/admin/appearance/themes` and `/api/admin/appearance/sections`, under `appearance.*` permissions, and bulk status becomes the staged status plus publish rather than mass-update-status. On an older store the Settings endpoints and their `settings.themes.*` permissions are unchanged.
- On a Bagisto 2.4.10 store, deleting an attribute family is refused for the default family; on an older store it is refused for the last remaining one, as before.
- CI runs against Bagisto v2.4.10, and a failing Pest suite now fails the build.

## [2.4.3] - 2026-08-26

### Added

- Add social login — sign in or sign up with a Google, Facebook or LinkedIn token the app or web already holds (no redirect), returning a Bearer token. `POST /api/shop/customers/social-login` and the `createSocialLogin` mutation.

- Add a GraphQL operation map to the export (`graphql-operations-shop.json` / `-admin.json`) mapping each root field to its resource tag, so tooling can group GraphQL the way REST is grouped.
- Add a re-seed tool that rebuilds the collections from the exported schemas.
- Add **View** permissions for Integration and API Change History, so read access to those screens can be granted or withheld separately from creating, editing and deleting.

### Fixed

- Fix a fatal error at boot after installing on a newer Symfony release.
- Fix an inactive customer being able to log in; inactive accounts are now rejected.
- Fix a suspended customer being blocked at login; they can now log in and browse, and are stopped only at checkout.
- Fix a customer logging in without verifying their email when email verification is required.
- Honour permissions on the Integration and API Change History screens.
- Scope each exported schema to its own surface.
- Restore tag descriptions in the exported schemas and the interactive docs (the tag list and folders were unlabelled).
- Write a placeholder storefront key into the exported storefront schema instead of the configured key (it resolves from the Postman environment).
- Include two missing storefront endpoints in the export: the invoice PDF and the downloadable-product download.
- Make the exported schemas import cleanly into Postman — they now carry a title and a configurable server URL.

### Changed

- `bagisto-api-platform:export-schema` now writes to `schema/generated/` instead of `schema/`, leaving the hand-maintained collections and environments untouched; `--path` is unchanged.
- The export now refuses to write a schema that leaks another surface, references a missing definition, carries an unused definition, or contains a storefront key.

## [2.4.2] - 2026-08-17

### Added

- Add `POST /api/admin/marketing/search-terms` (and `createAdminMarketingSearchTerm`) to create a search term.
- Add `bagisto-api-platform:export-schema`, which writes the REST (OpenAPI JSON) and GraphQL (SDL) schemas to files for Postman and code generators. Defaults to the package's `schema/` folder; override with `--path`.

### Changed

- Update the bundled translations across all supported locales.
- Group the admin draft-cart and place-order endpoints under `Admin: Customer Order creation` in the Swagger docs.
- Speed up every request by splitting the service provider and deferring state bindings, so a request only loads what it uses.

### Fixed

- Fix newsletter subscription rejecting guests with `Unauthenticated`; a visitor can subscribe with the storefront key alone, as on the storefront.
- Fix deleting a customer account (`POST /api/shop/customer-profile-deletes/{id}`) doing nothing over REST, and drop the `password` field the docs wrongly marked required.
- Fix product 404s returning the raw text `bagistoapi::app.graphql.product.not-found` instead of "Product not found", on both transports.
- Fix `adminProfile` returning `roleId` and `roleName` as null over GraphQL.
- Fix the admin product copy mutation returning `sourceId` as null over GraphQL.
- Fix `adminCatalogProduct`'s `superAttributes { options }` returning null, and its `relatedProducts` / `upSells` / `crossSells` returning storefront-shaped nodes.
- Fix `createAdminCatalogProduct` / `updateAdminCatalogProduct` returning an unusable `id` and connections listing unrelated products.
- Fix the invoice PDF endpoint (`GET /api/admin/invoices/{id}/print`) returning a 500 (`View [sales.invoices.pdf] not found`) on Bagisto 2.4.
- Fix `PUT /api/admin/settings/data-transfer/imports/{id}` as `multipart/form-data` failing with "The type field is required"; multipart fields are now read on PUT/PATCH as on POST, and the file stays optional.
- Fix `POST /api/admin/eu-withdrawals/{id}/resend-confirmation` returning 422 when a body was sent; it is now a true empty-body action.
- Fix `DELETE /api/admin/rma/custom-fields/{id}` returning 204 with no confirmation; it now returns 200 with a message and the deleted record.
- Fix customer registration not setting the channel and default customer group, which left `channelId` and `customerGroupId` null and blocked storefront sign-in.
- Fix the order-comments endpoints documenting a required `id` path parameter that the URL does not contain.
- Fix malformed JSON returning a terse "Syntax error" (and a 500 with debug on); it is now a `400` with "The request body contains invalid JSON."
- Fix a duplicate attribute-group name returning a 500 that leaked a database error; it is now a `422`.
- Fix cursor-paginated GraphQL collections erroring on `first: 0`; they now return an empty connection.
- Fix `PUT /api/admin/carts/{id}/items` returning `200` without applying a rejected quantity; it now returns `422` with the reason.
- Fix REST error responses that returned no message on customer profile and address endpoints.
- Fix admin and shop pages hanging (sometimes a 500) for 30+ seconds right after caches are cleared.

## [2.4.1] - 2026-07-22

### Added

- Add request and response examples to the Swagger/OpenAPI docs for every Returns (RMA) and EU Withdrawal endpoint (shop and admin).
- Add the request body schema for creating and updating RMA reasons, rules, statuses and custom fields in the Swagger docs.
- Add `redirect` and `redirectUrl` to the place-order response so clients can tell when the shopper must be sent to a payment page before the order exists.
- Add `minPrice` and `maxPrice` to the storefront category response so REST clients can bound a price-range filter, matching what GraphQL already exposed.
- Add `xls` and `xlsx` to every admin export endpoint (`?format=`), matching the formats the admin panel offers; exported values are now guarded against spreadsheet formula injection.

### Changed

- Drop the hard Redis requirement: the API metadata/schema cache and rate-limit counters now follow the application's `CACHE_STORE` (falling back to `file`), so no separate cache service or extra configuration is needed.
- Take the cart file-upload size limit from `php.ini` and the staged-upload lifetime from the session lifetime, instead of package settings.

### Fixed

- Fix GraphQL connection fields (e.g. cart `items { edges }`) failing with `Field "items" of type "Iterable" must not have a sub selection` on some production PHP-FPM servers.
- Fix RMA settings create and update responses showing a success `message`; the confirmation message is now returned only on delete.
- Restore backward compatibility with Bagisto cores below 2.4.5 by conditionally registering the EU Withdrawal endpoints only when the core module is present.
- Fix `paymentGatewayUrl` coming back empty for redirect payment methods (Stripe, Razorpay, PayPal Standard, PhonePe), leaving clients with nowhere to send the shopper.
- Fix selecting PhonePe as the payment method failing with a server error.
- Fix place order creating an unpaid order when a redirect payment method is selected; it now returns the payment redirect, and the order is created once the gateway confirms payment.
- Fix `cartToken` in cart and checkout responses returning an id instead of the cart's guest token; it is now the guest token, or null for a signed-in customer.
- Fix 30 admin endpoints in the Swagger docs asking for a required path parameter the URL does not contain, such as `method` on list payment methods and `id` on list order comments.
- Fix Swagger path parameter descriptions exposing internal resource names (e.g. "AdminCart identifier" now reads "Cart ID").
- Fix the storefront key falling back to a placeholder in the GraphiQL and Swagger docs after `config:cache` or `optimize`; playground settings are now read from configuration, so they survive a cached config.
- Fix Swagger UI ignoring `API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY`, so the key was never pre-filled there even when the setting was enabled.
- Fix product image and video URLs being prefixed with an unset `API_URL`, which produced a doubled host on stores that set it.
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

[2.4.3]: https://github.com/bagisto/bagisto-api/compare/v2.4.2...v2.4.3
[2.4.2]: https://github.com/bagisto/bagisto-api/compare/v2.4.1...v2.4.2
[2.4.1]: https://github.com/bagisto/bagisto-api/compare/v2.4.0...v2.4.1
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
