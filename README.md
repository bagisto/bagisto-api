# Bagisto API Platform

Comprehensive REST and GraphQL APIs for seamless e-commerce integration and extensibility.

## Requirements

- PHP 8.3+
- [Bagisto](https://github.com/bagisto/bagisto) **v2.4.7** (the version this package is tested against in CI)
- Composer 2
- MySQL 8.0+ or PostgreSQL 14+
- API Platform for Laravel — `api-platform/laravel` and `api-platform/graphql` (`~4.3.8`), which bring in the remaining `api-platform/*` components at a matching version, installed automatically via `composer require`

## Installation

### Method 1: Quick Start (Composer Installation – Recommended)

The fastest way to get started:

```bash
composer require bagisto/bagisto-api
php artisan bagisto-api-platform:install
```

Your APIs are now ready! Access them at:
- **API Landing**: `https://your-domain.com/api`
- **REST API Docs (Shop)**: `https://your-domain.com/api/shop/docs`
- **REST API Docs (Admin)**: `https://your-domain.com/api/admin/docs`
- **GraphQL Playground (Shop)**: `https://your-domain.com/api/graphiql`
- **GraphQL Playground (Admin)**: `https://your-domain.com/api/admin/graphiql`
 
### Method 2: Manual Installation

Use this method if you need more control over the setup.

#### Step 1: Download and Extract

1. Download the BagistoApi package from [GitHub](https://github.com/bagisto/bagisto-api)
2. Extract it to: `packages/Webkul/BagistoApi/`

#### Step 2: Register Service Provider

Edit `bootstrap/providers.php`:

```php
<?php

return [
    // ...existing providers...
    Webkul\BagistoApi\Providers\BagistoApiServiceProvider::class,
    // ...rest of providers...
];
```

#### Step 3: Update Autoloading

Edit `composer.json` and update the `autoload` section:

```json
{
  "autoload": {
    "psr-4": {
      "Webkul\\BagistoApi\\": "packages/Webkul/BagistoApi/src"
    }
  }
}
```

#### Step 4: Install Dependencies

```bash

composer require \
  api-platform/laravel:~4.3.8 \
  api-platform/graphql:~4.3.8
```

#### Step 5: Run the installation
```bash
php artisan bagisto-api-platform:install
```

#### Step 6: Environment Setup (Update in the .env)
```bash
STOREFRONT_DEFAULT_RATE_LIMIT=100
STOREFRONT_CACHE_TTL=60
STOREFRONT_KEY_PREFIX=storefront_key_
STOREFRONT_PLAYGROUND_KEY=pk_storefront_xxxxxxxxxxxxxxxxxxxxxxxxxx 
API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY=true
```
### Access Points

Once verified, access the APIs at:

- **API Landing**: [https://your-domain.com/api](https://api-demo.bagisto.com/api)
- **REST API (Shop)**: [https://your-domain.com/api/shop/](https://api-demo.bagisto.com/api/shop)
- **REST API (Admin)**: [https://your-domain.com/api/admin/](https://api-demo.bagisto.com/api/admin)
- **REST API Docs (Shop)**: [https://your-domain.com/api/shop/docs](https://api-demo.bagisto.com/api/shop/docs)
- **REST API Docs (Admin)**: [https://your-domain.com/api/admin/docs](https://api-demo.bagisto.com/api/admin/docs)
- **GraphQL Playground (Shop)**: [https://your-domain.com/api/graphiql](https://api-demo.bagisto.com/api/graphiql)
- **GraphQL Playground (Admin)**: [https://your-domain.com/api/admin/graphiql](https://api-demo.bagisto.com/api/admin/graphiql)

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
 
## Support

For issues and questions, please visit:
- [GitHub Issues](https://github.com/bagisto/bagisto-api/issues)
- [Bagisto Documentation](https://bagisto.com/docs)
- [Community Forum](https://forum.bagisto.com)

## 📝 License

The Bagisto API Platform is open-source software licensed under the [MIT license](LICENSE).
