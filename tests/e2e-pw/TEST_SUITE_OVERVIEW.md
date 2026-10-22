# Bagisto Playwright Test Suite Overview

This project contains Playwright-based API automation for the Bagisto GraphQL shop API.

The goal of the project is to:
- keep GraphQL query coverage organized
- validate real API behavior
- handle auth-required and guest flows cleanly
- make the suite easier to maintain as Bagisto docs and schema change

## Main Structure

### `tests/api/automation`
These files contain the actual Playwright test cases.

In simple terms:
- `*.spec.ts` files are the test files
- each file usually focuses on one feature area
- examples:
  - product tests
  - customer tests
  - cart tests
  - locale/currency/channel/category tests

These files are where requests are sent and assertions are made.

## `tests/graphql/Queries`
These files contain reusable GraphQL queries and mutations.

In simple terms:
- they store the raw GraphQL operations
- test files import them instead of writing long queries again and again
- each file is grouped by feature

Examples:
- product query file contains product-related queries
- customer query file contains customer-related queries
- currency query file contains currency-related queries

This keeps the specs cleaner and makes query updates easier when docs or schema change.

## `tests/graphql/assertions`
These files contain reusable validation logic for API responses.

In simple terms:
- they check response structure and important fields
- they reduce repeated `expect(...)` blocks in test files
- they help keep validation consistent across tests

So instead of rewriting the same checks in many specs, common assertions are kept here.

## `tests/graphql/helpers`
These files contain shared utilities used by the tests.

### `graphqlClient.ts`
This is the common request sender for GraphQL.

What it does:
- sends GraphQL requests to the Bagisto API
- attaches common headers like `X-STOREFRONT-KEY`
- uses the configured base URL and endpoint

Why it is used:
- avoids repeating request setup in every test
- keeps API calls consistent across the suite

### `testSupport.ts`
This contains common helper functions for reading and validating GraphQL responses.

What it does:
- safely reads nested response values
- extracts GraphQL error messages
- prints real API messages in terminal output
- provides reusable success/auth-aware assertions

Why it is used:
- GraphQL responses often have nested data and errors
- many tests need the same response handling
- this makes tests shorter, cleaner, and more consistent

## `tests/config`
These files contain configuration helpers used across the suite.

### `env.ts`
This reads values from `.env`.

What it does:
- loads project environment values like:
  - Bagisto base URL
  - storefront key
  - optional customer credentials
  - optional booking test values

Why it is used:
- keeps configuration in one place
- avoids hardcoding environment-specific values inside tests

### `auth.ts`
This contains reusable auth helpers.

What it does:
- creates customer auth headers when customer credentials are available
- creates guest cart auth headers for guest checkout/cart flows

Why it is used:
- many queries need auth
- some tests need guest auth, some need customer auth
- this avoids duplicating login/cart-token setup in many spec files

## `.env`
This file contains environment-specific values for running the suite locally.

Typical values include:
- Bagisto URL
- storefront access key
- optional customer login credentials
- optional booking product/date values

This file allows the same test suite to run in different environments with minimal code change.

## `playwright.config.ts`
This is the main Playwright configuration file.

What it does:
- tells Playwright where the tests are
- configures reporter, workers, retries, and base settings
- controls how the suite runs

## How The Flow Works

In general, the project works like this:
1. A spec file imports a query from `tests/graphql/Queries`
2. The spec sends the request using `graphqlClient.ts`
3. Auth helpers are used when required
4. Response helpers/assertions validate the result
5. Real API messages are logged where useful

## Why The Project Is Organized This Way

This structure is used to make the suite:
- easier to read
- easier to update when Bagisto docs change
- easier to debug
- less repetitive
- more reusable across many GraphQL features

## Short Summary

If someone is new to this repo:
- `tests/api/automation` = test cases
- `tests/graphql/Queries` = GraphQL queries/mutations
- `tests/graphql/assertions` = reusable validations
- `tests/graphql/helpers` = shared test utilities
- `tests/config` = environment and auth setup
- `.env` = local configuration values
- `playwright.config.ts` = Playwright runtime config

That is the basic structure of the project.

---

## Auto-booted backend server (APP_ENV=testing)

`playwright.config.ts` now includes a `webServer` block that auto-boots `php artisan serve` with `APP_ENV=testing` against the same DB as `APP_ENV=local` (no separate `.env.testing` / `database.connections.testing`, so the runtime DB is unchanged).

Why: `VerifyStorefrontKey::handle()` + `ApiKeyService::validate()` shortcircuit test-prefixed keys (`pk_test_*` / `sk_test_*`) and skip rate limiting **only when** `app()->environment('testing')` is true. With the previous `APP_ENV=local` serve, tests had to honor production-style rate limits — the workaround was bumping `STOREFRONT_DEFAULT_RATE_LIMIT` in `.env` by hand.

Behavior:
- **CI** (`process.env.CI` set): `reuseExistingServer: false` — a clean server is booted every run.
- **Locally**: `reuseExistingServer: true` — if you already have `php artisan serve` running on `:8000`, Playwright reuses it. **If that existing server runs with `APP_ENV=local`, the test-key bypass will NOT fire.** Stop your local serve before running Playwright when you need the bypass.


---

## Storefront specs for the unreleased API changes

| Spec | Covers |
|---|---|
| `tests/graphQL/api/automation/downloadableProductDownloadLink.spec.ts` | `createDownloadableProductDownloadLink` refuses an anonymous caller and reports a purchased link that does not exist. |
| `tests/restAPI/api/automation/customerDownloads.spec.ts` | `GET /api/shop/customer-downloadable-products/{id}/download` answers 401 without a customer token and 404 for a link that is not the customer's purchase. |
| `tests/graphQL/api/automation/checkoutAddress.spec.ts` | `readCheckoutAddress` returns the billing address saved on the bearer token's customer cart, returns a guest cart with no address before one is saved, and refuses a call without a token. |
| `tests/graphQL/api/automation/wishlist.spec.ts`, `tests/restAPI/api/automation/wishlist.spec.ts` | Wishlist add and toggle refuse an anonymous caller before the product is looked up (even for a product id that does not exist); an authenticated customer still gets "Product not found" and can add a product. |
| `tests/graphQL/api/automation/bookingSlots.spec.ts` | Every `bookingSlots` entry has the id `/api/shop/booking-slots/{bookingProductId}-{date}-{slotId}`, unique within the response. Needs `BAGISTO_BOOKING_PRODUCT_ID` and a `BAGISTO_BOOKING_DATE` that has slots. |

Attribute flags, category `status`, section `status` and unsigned integer columns (`_lft`, `_rgt`, `categoryId`, `channelId`, `sectionId`, `countryId`) are asserted with the type the schema declares on every database: booleans for flags, numbers for integers.

Not covered, because the suite has no way to create a purchased, invoiced or shipped order through the API: the successful `createDownloadableProductDownloadLink` and download (and that the download counts exactly once), the pending, limit-exceeded and not-owner refusals, and `customerOrderShipmentItems` nested under a shipment.

---

## Admin specs for the unreleased API changes

| Spec | Covers |
|---|---|
| `tests/restAPI/api/automation/admin/catalog/productImages.spec.ts` | A real PNG and JPEG upload is stored re-encoded as `products/{id}/<40 random chars>.webp` and listed on the product; an SVG, a JPEG named `.php` or `.html`, an HTML file named `.png` and an undecodable PNG are refused with 422 and nothing is stored; a missing file is 422, an unknown product 404; delete, reorder and not-found paths run against a product the test creates. |
| `tests/restAPI/api/automation/admin/catalog/productVideos.spec.ts` | An MP4 upload is stored under a generated `products/{id}/<40 random chars>.mp4` name and can be deleted; an MP4 named `.php` or `.html` and an HTML file named `.mp4` are refused with 422 and nothing is stored. |
| `tests/graphQL/api/automation/admin/catalog/productImages.spec.ts` | `createAdminCatalogProductImage` still refuses the upload with the REST-only message; reorder and delete refuse ids the product does not have. |
| `tests/restAPI/api/automation/admin/cms/cmsPages.spec.ts`, `tests/graphQL/api/automation/admin/cms/cmsPages.spec.ts` | Page `html_content` sent on create and update has its script tags, Blade echoes, `@php` and `<?php` stripped and keeps the safe markup. |
| `tests/restAPI/api/automation/admin/marketing/templates.spec.ts`, `tests/graphQL/api/automation/admin/marketing/templates.spec.ts` | The same for email template `content`. |
| `tests/restAPI/api/automation/admin/catalog/categories.spec.ts`, `tests/graphQL/api/automation/admin/catalog/categories.spec.ts` | The same for category `description`, on create and on the locale block of an update. |
| `tests/restAPI/api/automation/admin/catalog/products.spec.ts`, `tests/graphQL/api/automation/admin/catalog/products.spec.ts` | The same for the WYSIWYG `description` attribute on product update. |
| `tests/restAPI/api/automation/admin/settings/roles.spec.ts`, `tests/graphQL/api/automation/admin/settings/roles.spec.ts`, `tests/restAPI/api/automation/admin/settings/users.spec.ts` | A role or user update is validated (422) before the last all-access guard runs, and the record is left unchanged. |
| `tests/restAPI/api/automation/admin/sales/returnFiles.spec.ts` | `GET /api/admin/rma/requests/images/{id}` and `GET /api/admin/rma/messages/{id}/attachment` answer 401 without a token and 404 with no file for an unknown id; where the database already holds returns with images or attachments, their `url`/`attachmentUrl` point at these endpoints and the file is served. |
| `tests/restAPI/api/automation/admin/sales/returnableOrders.spec.ts`, `tests/graphQL/api/automation/admin/sales/returnableOrders.spec.ts` | The RMA order picker (`GET /api/admin/rma/requests/orders`, `adminReturnableOrders`) refuses a call without a token, returns its `{ data, meta }` envelope or cursor connection with only orders that still have a quantity to return, caps the page size at 50, and filters by increment id and order id. |
| `tests/restAPI/api/automation/admin/apiDocs.spec.ts` | `/api/admin/graphiql` and `/api/docs` are served as HTML; `/api/admin/docs` stays JSON. |

Shared admin helpers: `sendAdminMultipartRequest` in `tests/restAPI/rest/helpers/adminClient.ts`, image and video bytes in `tests/restAPI/rest/helpers/mediaFixtures.ts`, and `adminSanitisedHtml.assertions.ts` under both assertion directories.

Not covered: the 403 a caller gets before the last all-access 400 when it may not grant a role (the shared session belongs to a single all-access admin), the last all-access 400 itself (it depends on how many all-access admins the shared database holds), the import images archive rules (the suite does not create imports), and an RMA image or attachment uploaded and downloaded end to end (the suite cannot create an order a return can be opened for).

## Admin authentication

The admin specs authenticate the way an app does. `tests/fixtures/globalSetup.ts` logs in once with `ADMIN_EMAIL` and `ADMIN_PASSWORD` through `POST /api/admin/auth/login` and writes the access and refresh tokens to the git-ignored `.admin-session.json`. `tests/fixtures/adminSession.ts` holds that session for every worker: `sendAdminRequest` (REST, `tests/restAPI/rest/helpers/adminClient.ts`) and `sendAdminGraphQLRequest` (GraphQL, `tests/graphQL/graphql/helpers/adminGraphqlClient.ts`) send the access token, and on a `401` with `access_token_expired` refresh once and retry; when the refresh token is refused (`refresh_token_invalid`) or the session was revoked they log in again. A call that passes its own `Authorization` header is sent as it is.

| Variable | Used for |
|---|---|
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | The shared login session; required |
| `ADMIN_API_TOKEN` | An API token bound to `localhost`; the API token specs skip without it |
| `ADMIN_API_TOKEN_OTHER_DOMAIN` | An API token bound to `example.com`, refused with `api_token_domain_mismatch` |
| `ADMIN_API_TOKEN_REVOCABLE_REST`, `ADMIN_API_TOKEN_REVOCABLE_GRAPHQL` | One API token each spec revokes, since the API cannot create API tokens |

CI creates the API tokens with `php artisan bagisto-api:api-token:create --only-token`.

| Spec | Covers |
|---|---|
| `tests/restAPI/api/automation/admin/auth.spec.ts`, `tests/graphQL/api/automation/admin/auth.spec.ts` | Login shape and the profile's token kind; a wrong password and an unknown email get the same answer; an inactive admin is refused; refresh rotates the pair and a replayed refresh token revokes the session; a refresh token is not a bearer token; logout and logout-all; two logins with different client types are two sessions and revoking one keeps the other; another admin's sessions listed and revoked through the settings endpoints; a limited role gets 403 where it lacks the permission and 200 where it has it; the GraphQL public allowlist refuses a document that mixes the login mutation with a protected field. Each test creates its own admin and role and deletes them. |
| `tests/restAPI/api/automation/admin/apiTokens.spec.ts`, `tests/graphQL/api/automation/admin/apiTokens.spec.ts` | The `localhost` API token reads the profile and admin data; the `example.com` token is refused; tokens are listed and read without their string; a revoked token gets `api_token_revoked`; an API token cannot create API tokens or use the login session endpoints. |

Not covered end to end: an expired access token. The shortest access token lifetime is 5 minutes, so the refresh-and-retry path is exercised only when a real token expires during a run, and the expiry itself is covered by the Pest suites with a moved clock. Two-factor login is covered by Pest only, since the suite has no authenticator.

## Seeded fixtures (`tests/fixtures/`)

`playwright.config.ts` runs `tests/fixtures/globalSetup.ts` once per run (once per shard in CI). It seeds the data that order-based specs need **through the API itself** and writes it to `.e2e-fixtures.json` (git-ignored). Specs read it through `e2eFixtures()` from `tests/fixtures/e2eFixtures.ts` and skip with `missingFixtureReason(...)` when a fixture is absent.

| Fixture | What `globalSetup` creates |
|---|---|
| `customer`, `otherCustomer` | two registered customers (unique `@example.com` emails); `otherCustomer` is used to prove one customer cannot reach another's data |
| `order` | the customer orders 10 units of a simple product (flat rate, cash on delivery or money transfer); the admin invoices and ships it. RMA is enabled for the product so returns are allowed |
| `guestOrder` | a guest checkout order |
| `downloadable` | a downloadable product ordered and invoiced, with its purchased link |
| `rma` | a return on `order` with an image, and a message with an attachment |
| `booking` | a booking product and the first date in the next 21 days (in `APP_TIMEZONE`) that has slots |

`globalSetup` never throws: a failed step is logged as `[e2e fixtures] <step>: not seeded: <method path status body>` and only its fixture is left out. It also switches EU withdrawal on for the run if it is off.

## Full API coverage (`coverage/` folders)

Every REST operation in `schema/generated/openapi-{shop,admin}.json` and every GraphQL operation in `schema/generated/graphql-operations-{shop,admin}.json` is called by at least one spec. The coverage specs live in:

- `tests/restAPI/api/automation/coverage/` and `tests/graphQL/api/automation/coverage/` — one spec per area (`shopCart*`, `shopCheckout*`, `shopAccount*`, `shopCatalog*`, `shopCms*`, `adminSales*`, `adminCart*`, `adminCustomer*`, `adminRma*`, `adminCatalog*`, `adminSettings*`, `adminReporting*`, `adminMenu*`, `adminWithdrawals`, `restGaps*`);
- `tests/graphQL/graphql/Queries/coverage/` — their GraphQL documents;
- `tests/restAPI/rest/endpoints/coverage/` — their path constants and expected messages;
- `tests/restAPI/rest/helpers/coverage/` — data-building helpers (customers, carts, orders, invoices, imports, admin products).

Each operation has a test that asserts the status and the body (for binary responses: content type, disposition and bytes), and refusal tests (no credentials, another customer's record, unknown id, invalid input with the exact message from the package lang file). Every test creates and cleans up its own records; writes never touch shared rows.

Not coverable through the API, and therefore covered by refusal tests only: the Stripe payment confirmation success path, social login success, the emailed password-reset and verification tokens, Magic AI review translation, and file-type product attributes (no API stores one).

## Type check

```bash
npx --yes -p typescript@5.6.3 tsc -p tsconfig.json
```

CI runs this before the suite; it must report no errors.
