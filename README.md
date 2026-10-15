# Bagisto API Platform

Comprehensive REST and GraphQL APIs for seamless e-commerce integration and extensibility.

[![Run in Postman](https://run.pstmn.io/button.svg)](https://www.postman.com/bagisto-apis/bagistoapi)

## Requirements

- [Bagisto](https://github.com/bagisto/bagisto) **v2.5.0**

## Installation

```bash
composer require bagisto/bagisto-api:^2.5
php artisan bagisto-api-platform:install
```

The installer publishes the configuration and assets, runs the migrations, creates a storefront key and writes it to `.env`, clears the caches and caches the configuration and routes again, so the APIs work as soon as it finishes.

It also edits application files: it registers the API Platform providers in `bootstrap/app.php`, writes the `STOREFRONT_*` settings to `.env`, makes core's `packages/Webkul/Core/src/Eloquent/TranslatableModel.php` abstract, and adds the service provider to `bootstrap/providers.php` when package discovery does not register it.

Upgrading from the 2.4 line? See [`UPGRADE.md`](UPGRADE.md).

### Access Points

- **API Landing**: [https://your-domain.com/api](https://api-demo.bagisto.com/api)
- **REST API (Shop)**: [https://your-domain.com/api/shop/](https://api-demo.bagisto.com/api/shop)
- **REST API (Admin)**: [https://your-domain.com/api/admin/](https://api-demo.bagisto.com/api/admin)
- **REST API Docs (Shop)**: [https://your-domain.com/api/shop/docs](https://api-demo.bagisto.com/api/shop/docs)
- **REST API Docs (Admin)**: [https://your-domain.com/api/admin/docs](https://api-demo.bagisto.com/api/admin/docs)
- **GraphQL Playground (Shop)**: [https://your-domain.com/api/graphiql](https://api-demo.bagisto.com/api/graphiql)
- **GraphQL Playground (Admin)**: [https://your-domain.com/api/admin/graphiql](https://api-demo.bagisto.com/api/admin/graphiql)

## Exporting the API Schema

Generate schema files for the shop and admin APIs — OpenAPI JSON (REST) and GraphQL SDL — to import into Postman, a client/code generator, or a mock server without calling a live server:

```bash
php artisan bagisto-api-platform:export-schema
```

The files are written to `schema/generated/`:

| File | Contents |
|---|---|
| `openapi-shop.json` / `openapi-admin.json` | OpenAPI for each REST surface |
| `shop.graphql` / `admin.graphql` | GraphQL SDL for each surface |
| `graphql-operations-shop.json` / `-admin.json` | Every root GraphQL field mapped to its resource tag |

Each spec is scoped to its own surface — the storefront spec carries no admin path, schema or tag, and the reverse — and the command refuses to write one that leaks the other surface or references a definition it does not include.

Options:

- `--path=<dir>` — write elsewhere
- `--transport=all|rest|graphql` — limit to one transport (default `all`)

Re-running overwrites those six files and nothing else. To rebuild the Postman collections from them:

```bash
php schema/tools/build-collection.php
```

## Postman Collections

Run them from the [public workspace](https://www.postman.com/bagisto-apis/bagistoapi), or import the copies that ship with the package:

- `collections/Bagisto-Shop-API.postman_collection.json` — storefront: browsing, cart, checkout, orders, account
- `collections/Bagisto-Admin-API.postman_collection.json` — admin, foldered to mirror the admin sidebar
- `environments/Bagisto.postman_environment.json` — one environment for both

Both transports are covered: requests are foldered `REST/` and `GraphQL/` under the same resource names, with GraphQL split into `Queries/` and `Mutations/`.

### Importing

1. Postman → **Import** → pick a file from `collections/`.
2. Postman → **Environments** → **Import** → pick `environments/Bagisto.postman_environment.json`, select it, then fill in your values.

| Variable | Used by | Description |
|---|---|---|
| `url` | Both | Your Bagisto URL, e.g. `http://localhost:8000` |
| `storefrontKey` | Shop | Storefront API key, from admin → Configuration → API |
| `customerEmail` | Shop | A storefront customer's email |
| `customerPassword` | Shop | That customer's password |
| `customerToken` | Shop | Filled in by the Customer login request |
| `cartToken` | Shop | Filled in by the Create cart token request |
| `adminToken` | Admin | Integration token, from admin → Settings → Integration |
| `locale` | Both | Locale code, defaults to `en` |
| `channel` | Both | Channel code, defaults to `default` |
| `currency` | Shop | Currency code, defaults to `USD` |

Fill in only what the collection you are using needs.

### Authenticating

**Shop** — run **REST → Customer → Customer login**. It stores `customerToken`, which every other request sends as the bearer. For a guest cart, run **Create cart token** instead and set the collection's **Authorization** tab to `{{cartToken}}`; the cart and checkout endpoints serve guests and logged-in customers alike, so the token you send decides whose cart you are working on.

**Admin** — there is no login request. Generate an integration token in the admin panel (see [Admin API Authentication](#admin-api-authentication)) and paste it into `adminToken`.

### Keeping them current

`schema/tools/build-collection.php` regenerates the collections from the exported schemas, so a collection follows the API rather than being maintained by hand. After `bagisto-api-platform:export-schema` writes a new schema, rerun the builder and commit both — **Validate** rebuilds the collections in CI and fails the push if the committed copies no longer match, so a stale collection cannot reach the workspace.

Three workflows keep the published copies in step: **Validate** checks the files and that rebuild on every push, **Push Collections to Postman** publishes them to the official workspace when they change on `main`, and **Sync Collections from Postman** pulls edits made in Postman back into the repository on a release. All three refuse to move a real storefront key.

Publishing needs four repository secrets, and the push and sync jobs skip with a notice while any of them is missing rather than failing the build:

| Secret | Value |
|--------|-------|
| `POSTMAN_API_KEY` | A Postman API key with write access to the workspace |
| `POSTMAN_SHOP_COLLECTION_ID` | UID of the Bagisto Shop API collection |
| `POSTMAN_ADMIN_COLLECTION_ID` | UID of the Bagisto Admin API collection |
| `POSTMAN_ENVIRONMENT_ID` | UID of the Bagisto environment |

Collection and environment UIDs come from Postman — open the item, then **Info → ID**. Validation needs no secret and runs on every push regardless.

Values in the requests are placeholders — replace them with records that exist on your store. Postman's **Auto Fetch** runs a schema introspection query that costs far more than a normal request; switch it off if the GraphQL folders feel slow.

## Admin API Authentication

Admin endpoints (`/api/admin/*` and `/api/admin/graphql`) require an integration-token Bearer header:

Authorization: Bearer id|generated-token


To generate a token:

1. Log into the Bagisto admin panel.
2. Enable the module first: navigate to **Configuration → API → Integration → Module Settings** and turn **Enabled** on. (Without this, the Integration menu stays hidden.)
3. Navigate to **Settings → Integration**.
4. Click **Create**, fill in the name / description / assigned admin / permission mode (`All`, `Custom`, or `Same as Web`) / optional IP allowlist / rate limits / expiry, and save as a draft.
5. Click **Generate**. The plaintext token is shown **once** — copy it immediately. You won't be able to view it again; if lost, use **Regenerate** to issue a new one.

Each token is scoped to a single admin user and inherits that admin's role permissions — so tokens can never do more than their owner could in the admin UI. To issue tokens to multiple admins, create one token per admin (each admin can hold only one active token at a time).

Tokens can be revoked at any time from the same page or via the signed link in the lifecycle notification email sent to the token owner.

## Documentation
- Bagisto API: [Demo Page](https://api-demo.bagisto.com/api)
- API Documentation: [Bagisto API Docs](https://api-docs.bagisto.com/)
- GraphQL Playground (Shop): [Interactive Playground](https://api-demo.bagisto.com/api/graphiql)
- GraphQL Playground (Admin): [Interactive Playground](https://api-demo.bagisto.com/api/admin/graphiql)
- Release history: see [`CHANGELOG.md`](CHANGELOG.md)
- Upgrading from 2.4: see [`UPGRADE.md`](UPGRADE.md)
 
## Support

For issues and questions, please visit:
- [GitHub Issues](https://github.com/bagisto/bagisto-api/issues)
- [Bagisto Documentation](https://bagisto.com/docs)
- [Community Forum](https://forum.bagisto.com)

## 📝 License

The Bagisto API Platform is open-source software licensed under the [MIT license](LICENSE).
