# Upgrading Bagisto API on the 2.4 line

This guide covers updating `bagisto/bagisto-api` on a store that runs the Bagisto 2.4 line, and what changes when you upgrade Bagisto itself. Release notes for each version are in [`CHANGELOG.md`](CHANGELOG.md).

## 1. Update the package

```bash
composer update bagisto/bagisto-api
```

The package requires `api-platform/laravel` and `api-platform/graphql` at `~4.3.8`, and Composer installs the other `api-platform/*` components at a matching version. Symfony stays on 7: API Platform 4.3 does not support Symfony 8.

## 2. Run the installer again

```bash
php artisan bagisto-api-platform:install
```

You can run it on a store that already has the API. It re-publishes the configuration and assets, runs the new migrations and rebuilds the caches. It keeps an existing storefront key, and the package migrations skip a column or table that already exists, so a rerun after an interrupted install is safe.

If you changed the published `config/api-platform.php`, keep a copy before you run it.

## 3. Rebuild the caches

```bash
php artisan bagisto-api-platform:optimize
```

Run this after every update, and after any app-wide cache clear. On php-fpm or Octane, reload the workers too, so they pick up the new code and schema.

## Bagisto Compatibility

One package serves the whole 2.4 line. It detects what the store it is installed on can do, so most of the API is identical everywhere and only the theme surface follows the store.

| Store | Theme endpoints | Permissions |
|-------|-----------------|-------------|
| **v2.4.10 and newer** | `/api/admin/appearance/themes`, `/api/admin/appearance/sections`, `/api/shop/sections` and `/api/shop/theme`, including the draft, publish, discard, reorder, duplicate and preview flow | `appearance.*` |
| **v2.4.9 and older** | `/api/admin/settings/themes` with mass-delete and mass-update-status, and `/api/shop/theme-customizations` | `settings.themes.*` |

Only one set is registered, so endpoints the store cannot support are absent rather than failing when called. A few smaller behaviours follow the store the same way: product image `alt_text`, the derived columns behind the product listing, attribute `regex` validation, the attribute-family delete guard, and the in-use guards on email templates and marketing events.

Upgrading Bagisto is what moves the theme endpoints; the package itself needs no change. Rebuild the caches afterwards so the new surface is picked up:

```bash
php artisan bagisto-api-platform:optimize
```
