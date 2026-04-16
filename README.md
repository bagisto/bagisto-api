# Bagisto API Platform

Comprehensive REST and GraphQL APIs for seamless e-commerce integration and extensibility.

## Requirements

- PHP 8.3+
- [Bagisto](https://github.com/bagisto/bagisto) **v2.3.8** (the version this package is tested against in CI)
- Composer 2
- MySQL 8.0+ or PostgreSQL 14+
- `api-platform/laravel` `v4.1.25` and `api-platform/graphql` `v4.2.3` (installed automatically via `composer require`)

## Installation

### Method 1: Quick Start (Composer Installation – Recommended)

The fastest way to get started:

```bash
composer require bagisto/bagisto-api
php artisan bagisto-api-platform:install
```

Your APIs are now ready! Access them at:
- **REST API Docs**: `https://your-domain.com/api/docs`
- **GraphQL Playground**: `https://your-domain.com/graphiql`
 
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
# Install required packages
composer require api-platform/laravel:v4.1.25
composer require api-platform/graphql:v4.2.3
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

- **REST API (Shop)**: [https://your-domain.com/api/shop/](https://api-demo.bagisto.com/api/shop)
- **REST API (Admin)**: [https://your-domain.com/api/admin/](https://api-demo.bagisto.com/api/admin)
- **GraphQL Endpoint**: `https://your-domain.com/graphql`
- **GraphQL Playground**: [https://your-domain.com/graphiql](https://api-demo.bagisto.com/api/graphiql)

## Documentation
- Bagisto API: [Demo Page](https://api-demo.bagisto.com/api)
- API Documentation: [Bagisto API Docs](https://api-docs.bagisto.com/)
- GraphQL Playground: [Interactive Playground](https://api-demo.bagisto.com/graphiql)
- Release history: see [`CHANGELOG.md`](CHANGELOG.md)
 
## Support

For issues and questions, please visit:
- [GitHub Issues](https://github.com/bagisto/bagisto-api/issues)
- [Bagisto Documentation](https://bagisto.com/docs)
- [Community Forum](https://forum.bagisto.com)

## 📝 License

The Bagisto API Platform is open-source software licensed under the [MIT license](LICENSE).

 
