# Bagisto API Platform

Comprehensive REST and GraphQL APIs for seamless e-commerce integration and extensibility.

## Installation

### Method 1: Quick Start (Composer Installation – Recommended)

The fastest way to get started:

```bash
# 1. Install the Bagisto API package
composer require bagisto/bagisto-api

# 2. Run the installer
php artisan bagisto-api:install

# 3. Run database migrations
php artisan migrate

# 4. Clear and rebuild caches
php artisan optimize:clear
php artisan optimize

# 5. Create your first API key
php artisan bagisto-api:generate-key --name="Default Store"
```

Your APIs are now ready! Access them at:
- **REST API Docs**: `https://your-domain.com/api/docs`
- **GraphQL Playground**: `https://your-domain.com/graphql`
 
### Method 2: Manual Installation

Use this method if you need more control over the setup.

#### Step 1: Download and Extract

1. Download the BagistoApi package from [GitHub](https://github.com/bagisto/bagisto-api)
2. Extract it to: `packages/Webkul/BagistApi/`

#### Step 2: Register Service Provider

Edit `bootstrap/providers.php`:

```php
<?php

return [
    // ...existing providers...
    Webkul\BagistApi\Providers\BagistApiServiceProvider::class,
    // ...rest of providers...
];
```

#### Step 3: Update Autoloading

Edit `composer.json` and update the `autoload` section:

```json
{
  "autoload": {
    "psr-4": {
      "Webkul\\BagistApi\\": "packages/Webkul/BagistApi/src",
    }
  }
}
```

#### Step 4: Install Dependencies

```bash
# Install required packages
composer require api-platform/laravel:^4.1
composer require api-platform/graphql:^4.2
```

#### Step 5: Run the installation
```bash
php artisan bagisto-api:install
```
#### Step 6: Run database migrations
```bash
php artisan migrate --path=packages/Webkul/BagistoApi/src/Database/Migrations
```

#### Step 7: Clear caches
```bash
php artisan optimize:clear
php artisan optimize
```
#### Step 8: Create your first API key
```bash
php artisan bagisto-api:generate-key --name="Default Store"
```

#### Step 9: Environment Setup (Update in the .env)
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
- **GraphQL Endpoint**: https://your-domain.com/graphql`
- **GraphQL Playground**: [https://your-domain.com/graphqli](https://api-demo.bagisto.com/api/graphqli?)

## Documentation
- Bagisto API: [Demo Page](https://api-demo.bagisto.com/api) 
- API Documentation: [Bagisto API Docs](https://api-docs.bagisto.com/)
- GraphQL Playground: [Interactive Playground](https://api-demo.bagisto.com/graphiql)
 
## Support

For issues and questions, please visit:
- [GitHub Issues](https://github.com/bagisto/bagisto-api-platform/issues)
- [Bagisto Documentation](https://bagisto.com/docs)
- [Community Forum](https://forum.bagisto.com)

## 📝 License

The Bagisto API Platform is open-source software licensed under the [MIT license](LICENSE).

 
