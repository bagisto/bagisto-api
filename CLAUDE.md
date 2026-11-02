# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Bagisto 2.4.x - open-source Laravel 12 e-commerce platform. PHP 8.3+, Vue.js 3, Tailwind CSS 3, Vite 5.

## Common Commands

### Development
```bash
composer install                # Install PHP dependencies
php artisan bagisto:install     # Full installation (migrations, seeders, assets)
php artisan serve               # Start PHP dev server
php artisan optimize:clear      # Clear all caches (run after config/code changes)
```

### Testing
```bash
vendor/bin/pest                                         # Run all tests
vendor/bin/pest --testsuite="Admin Feature Test"        # Run a specific test suite
vendor/bin/pest packages/Webkul/Admin/tests/Feature     # Run tests in a directory
vendor/bin/pest --filter="test name"                    # Run a single test by name
```

Test suites defined in `phpunit.xml`: Admin Feature, Core Unit, Customer Unit, DataGrid Unit, Installer Feature, PayU Unit/Feature, Razorpay Unit/Feature, Shop Feature, Stripe Unit/Feature.

Tests use **Pest 3** with package-specific TestCase classes bound in `tests/Pest.php`. Each package's tests live in `packages/Webkul/<Package>/tests/`.

### E2E Tests (Playwright)
E2E tests are run from within each package directory. Each package has its own Playwright config and tests:

**Admin**:
```bash
cd packages/Webkul/Admin
npm install
npx playwright install --with-deps chromium
npx playwright test --config=tests/e2e-pw/playwright.config.ts
```

**Shop**:
```bash
cd packages/Webkul/Shop
npm install
npx playwright install --with-deps chromium
npx playwright test --config=tests/e2e-pw/playwright.config.ts
```

Tests require a running Laravel server (`php artisan serve`) and seeded database. Set `BASE_URL` env var if not using default.

### Code Style
```bash
vendor/bin/pint             # Fix PHP code style (Laravel Pint)
vendor/bin/pint --test      # Check style without fixing
```

### Translations
When adding new translation keys, always provide translations for **all locales** in the package's `Resources/lang/` directory. Verify with:
```bash
php artisan bagisto:translations:check
```

## Architecture

### Modular Package System

All core functionality lives in **`packages/Webkul/`** (~42 packages). Each package is a self-contained Laravel package with its own models, controllers, routes, views, migrations, and service providers.

**Dual registration**: Each package registers in two places:
1. **`bootstrap/providers.php`** - Main ServiceProvider (routes, views, events, config)
2. **`config/concord.php`** - ModuleServiceProvider (Konekt Concord model/enum registration)

### Key Design Patterns

**Repository Pattern**: All database access goes through repositories (`Prettus L5 Repository`). Interfaces in `Contracts/`, implementations in `Repositories/`. Never use models directly for queries in controllers.

**Proxy Pattern**: Models have Proxy classes (e.g., `ProductProxy`, `CategoryProxy`) enabling model substitution without modifying core code. Always reference proxies when type-hinting across packages.

**Event-Driven Extensibility**: The framework fires events at key lifecycle points. Extend behavior via listeners rather than modifying core packages.

### Package Anatomy

```
packages/Webkul/<Package>/src/
├── Config/           # system.php (admin settings), admin-menu.php, acl.php
├── Database/         # Migrations/, Seeders/, Factories/
├── Http/Controllers/ # Separate Admin/ and Shop/ controller directories
├── Models/           # Eloquent models + Proxy classes
├── Repositories/     # Data access layer
├── Contracts/        # Interfaces for models and repositories
├── Resources/
│   ├── views/        # Blade templates (admin/, shop/)
│   ├── lang/         # Localization (translatable strings)
│   └── assets/       # CSS/JS source files
├── Routes/           # admin-routes.php, shop-routes.php, api.php
├── Providers/        # ServiceProvider + ModuleServiceProvider
└── Listeners/        # Event listeners
```

### Frontend Assets

Admin, Shop, and Installer each have independent Vite builds. Run `npm install` and `npm run dev`/`npm run build` from within the respective package directory:
- **Admin**: `packages/Webkul/Admin/` builds to `public/themes/admin/default/build/`
- **Shop**: `packages/Webkul/Shop/` builds to `public/themes/shop/default/build/`
- **Installer**: `packages/Webkul/Installer/`

Vue 3 components are used within Blade templates via `@pushOnce('scripts')` / Blade component slots.

### Naming Conventions

- **Namespace**: `Webkul\<PackageName>` (e.g., `Webkul\Product`)
- **Routes**: Separate `admin-routes.php` and `shop-routes.php` per package
- **Models**: Singular (`Product`, `Category`)
- **Repositories**: `<Model>Repository` (e.g., `ProductRepository`)
- **Controllers**: `<Model>Controller` in `Http/Controllers/Admin/` or `Shop/`

### Adding a New Package

1. Create `packages/Webkul/<Name>/src/` with the standard structure
2. Add PSR-4 namespace to root `composer.json` autoload
3. Register ServiceProvider in `bootstrap/providers.php`
4. Register ModuleServiceProvider in `config/concord.php`
5. Run `composer dump-autoload && php artisan optimize:clear`

Or use: `php artisan package:make Webkul/<Name>` (requires `bagisto/bagisto-package-generator`)

## CI Pipeline

- **pest_tests.yml**: Pest tests on PHP 8.3 + MySQL 8.0
- **pint_tests.yml**: Code style checks with Laravel Pint
- **admin_playwright_tests.yml / shop_playwright_tests.yml**: E2E tests (6 parallel shards)
- **translation_tests.yml**: Translation file validation

## BagistoApi — Pending 2.4.x / Laravel 12 Work

The `Webkul/BagistoApi` package was built for Bagisto **2.3.8 / Laravel 11**; the host is now
**2.4.7 / Laravel 12**. The package runs, but needs the changes tracked in
[`packages/Webkul/BagistoApi/BAGISTO-2.4-COMPATIBILITY.md`](packages/Webkul/BagistoApi/BAGISTO-2.4-COMPATIBILITY.md):

- **Compat fixes:** with `APP_DEBUG=true` only the OpenAPI doc pages `/api/shop` & `/api/admin` time out (whole-spec metadata build on the non-persistent `array` pool — real endpoints are fine); `bagisto-api-platform:optimize` route-cache check (L12 memoizes `routesAreCached()`); api-platform asset publish after project moves. None of this is a version incompatibility.
- **New 2.4 features to expose (admin + shop):** RMA (returns), EU Withdrawal — both net-new core modules with no API yet. Plus Sales-by-coupon report type and product url_key cross-locale fallback.

See that doc for the full prioritized admin/shop gap tables and root-cause notes.

## 2.4.7 upgrade — what broke and what was changed (review list, 2026-07-13)

Every failure below came from a **behaviour change in the upgraded libraries**, not from a logic change on our side. Nothing in the business logic, queries or response contracts was altered.

| # | What changed upstream | Symptom | Fix | Files |
|---|---|---|---|---|
| 1 | The 4.3 Laravel bridge honours `api-platform.path_segment_name_generator`; the old one hardcoded `Underscore` | 272 auto-generated routes silently flipped `_` → `-` (5 REST suites 404'd; the rest changed unnoticed) | Pin the generator to `UnderscorePathSegmentNameGenerator` so every legacy URL is preserved. Route table verified byte-identical to 2.3.x | the 3 `api-platform.php` configs |
| 2 | Property types moved from `symfony/property-info` (`builtinTypes`) to `symfony/type-info` (`nativeType`) | Our property-metadata decorator became a silent no-op | Read/write `nativeType` | `Admin/Metadata/NullableToOnePropertyMetadataFactory` |
| 3 | Eloquent property nullability is derived from the DB schema, so accessors / `$appends` / to-one relations are typed non-null | GraphQL "cannot return null for non-nullable field" — whole nodes null or 500 | Mark non-column properties nullable | same file |
| 4 | A mutation declaring `output:` = its own resource class no longer reuses the query type; it builds a `deleteXPayloadData` carrying **only `id`** | `deleteWishlist` / `deleteCompareItem` / `deleteGdprRequest` lost `_id` and every other field | Drop the redundant `output:` (it is the default) | `Models/{Wishlist,CompareItem,GdprRequest}` |
| 5 | A relation is only exposed if its related class is an API resource, and camel/snake method names collapse to one property — core's wins | `filterableAttributes` vanished from the `Category` GraphQL type | Override the core relation method | `Models/Category` |
| 6 | The GraphQL read path dispatches item queries through the IRI converter | The converter's catch-all masked a provider's not-found → returned a **blank record instead of an error** | Rethrow `ClientAware` exceptions | `Routing/CustomIriConverter` |
| 7 | The serializer no longer accepts an undeclared `stdClass` as an operation's output | Contact-us / newsletter / login / logout / forgot-password / verify-token / place-order returned empty payloads | Return the operation's declared output resource | the 7 shop processors |
| 8 | **Bagisto 2.4** renamed ACL keys (`settings.users.create`, `catalog.products`) | `permission_type=custom` roles always 403'd on admin-user CRUD + downloadable-file download (an `all` token still worked, so it hid in review) | Use the real keys from core's `acl.php` | `AdminSettingsUserProcessor`, `AdminCatalogProductDownloadableFileProvider` |
| 9 | Playwright's `webServer` had `reuseExistingServer: !CI` + a hardcoded local `BAGISTO_PATH` | CI: "port 8000 is already used" — it fought the workflow's own `php artisan serve` | Reuse the running server; resolve the path relative to the config | `tests/e2e-pw/playwright.config.ts` |
| 10 | — (pre-existing CI hole) | The pest workflow had `continue-on-error` at **job** level, so it could never go red — which is why 1–8 never surfaced in CI | Fail the job when any suite is red | `.github/workflows/pest_tests.yml` |

## api-platform 4.3 + Bagisto 2.4 — migration gotchas (2026-07-13)

The package now requires **`api-platform/laravel` + `api-platform/graphql` at `~4.3.8`** only (they pull the other `api-platform/*` components in at a matching version — the old per-component pin list is gone, and it also clears the `json-api` CVE). Everything below broke on 4.3 and is now fixed; heed them when adding resources.

- **Always declare an explicit `uriTemplate` on a new operation.** The 4.3 bridge finally honours `api-platform.path_segment_name_generator`, which the old one hardcoded to `Underscore`. The config is pinned to **`UnderscorePathSegmentNameGenerator`** so the 272 auto-generated paths (`/api/shop/compare_items`, …) stay byte-identical to 2.3.x — switching it to `Dash` silently rewrites every one of them. Never rely on the generated path: name the route yourself (kebab, as everywhere else).
- **Property types come from `symfony/type-info` (`getNativeType()`), not `builtinTypes`.** Any custom `PropertyMetadataFactory` must handle `nativeType` (see `Admin/Metadata/NullableToOnePropertyMetadataFactory`).
- **Eloquent property nullability is derived from the DB schema**, so anything that is NOT a real column (accessor, `$appends`, computed value, to-one relation) is typed **non-null** → GraphQL "cannot return null for non-nullable field" nukes the whole node. `NullableToOnePropertyMetadataFactory` re-marks non-column properties nullable; extend its allow-list for a nullable to-one relation.
- **A mutation declaring `output: <SameResourceClass>::class` now stops the query type being reused** and builds a `<op><Resource>PayloadData` wrapper — and for `name: 'delete'` that wrapper is hardcoded to **only `id`** (no `_id`, no other fields). **Never set `output:` on a `delete` mutation when it equals the resource class** (it is the default). Admin delete mutations were unaffected only because they never declared one.
  - **EXCEPTION — `create`/`update` mutations on a resource whose REST ops declare a DTO `output:` MUST set `output: <Resource>::class` explicitly (2026-08-11).** `AdminCatalogProduct`'s REST ops carry `output: AdminCatalogProductRestDto::class`; with no `output:` on the GraphQL `create`/`update` mutations, the mutation payload type was built from **that DTO** — so `id` resolved to `/api/admin_catalog_product_rest_dtos/<id>` (a routeless path) and every connection on the payload re-resolved against the wrong resource, returning **unrelated rows from the whole table** (a brand-new product with zero images came back with `images { edges }` holding `_id` 1…10, while `adminCatalogProduct(id:)` correctly returned empty edges). Adding `output: AdminCatalogProduct::class` to the two mutations fixes both: `id` is the real product IRI and connections resolve against the created/updated product. Verified live on create + update; REST unchanged (55-key DTO payload); Catalog Product suites green (GraphQL 23, REST 109, Detail 32, Image 17, Inventory 11, CGP 17). The "no `output:`" rule stands for `delete` and for resources with no DTO on their REST ops.
- **`ProductRelationResolverFactory` matches on FIELD NAME — always scope a resolver decorator to its resource (2026-08-11).** The package decorates API Platform's `ResolverFactory` to hand-build a few storefront `Product` connections (`upSells`, `crossSells`, `relatedProducts`, `superAttributes`, `reviews`, `bookingProducts`). It matched `$info->fieldName` **with no check on the root class**, so it also hijacked the identically-named connections on **`AdminCatalogProduct`**: it did `Webkul\BagistoApi\Models\Product::find($id)` (the shop model), walked the shop relation, and emitted hand-built nodes from `$item->toArray()` with camelised keys and storefront IRIs. Consequence — `adminCatalogProduct { superAttributes { edges { node { options { … } } } } }` returned **`options: null`**, because the hand-built node carries only `id`/`code`/`_id` and never the nested connection; the admin `relatedProducts`/`upSells`/`crossSells` nodes were storefront-shaped too. **Fix: gate the branch on `$rootClass === Product::class`** (the shop resource) so admin resources fall through to the inner resolver and resolve via their own Eloquent sub-resources. **Diagnosis technique worth reusing:** the field returns `null` (not empty edges) precisely because `ResolverFactory::__invoke` returns `null` when `$info->fieldName` is absent from the normalized `$source`. Log `array_keys($source)` there — a properly normalized node shows only the requested fields plus `#itemResourceClass`/`#itemIdentifiers`, while a hand-built or raw-`toArray()` node shows the whole attribute bag (here: `pivot`, `translation`, `name`). That difference identifies the culprit immediately; a `debug_backtrace()` in the same spot names the decorator.
- **A relation is only exposed if its related class is an API resource, and camel/snake method names collide.** Core `Category::filterableAttributes()` (related = core Attribute) and the API's `filterable_attributes()` (related = the Attribute resource) normalise to the same property; core's won and the field vanished. Override the core method when shadowing a core relation.
- **The GraphQL read path now dispatches item queries through the IRI converter.** `CustomIriConverter::getResourceFromIri` must rethrow `ClientAware` exceptions — its catch-all previously masked a provider's not-found/forbidden with an empty resource.
- **A processor must return the operation's declared output resource, never a `stdClass`** — an undeclared object now serialises to an empty payload.
- **Bagisto 2.4 renamed ACL keys:** `settings.users.create|edit|delete` (was `settings.users.users.*`) and `catalog.products` (there is no `.view`). A permission key that does not exist in `packages/Webkul/Admin/src/Config/acl.php` can never be granted, so every `permission_type=custom` role 403s. Check new permission keys against that file.
- **`@var array<X>` DTO connection fields break under php-fpm `opcache.save_comments=0` (2026-07-20).** A DTO property typed only by a docblock (`public ?array $items` + `@var array<CartItemData>`) loses its element type when php-fpm OPcache is built with `opcache.save_comments=0` — OPcache strips doc comments from bytecode, `Reflection::getDocComment()` returns `false`, `symfony/type-info` can't read the generic, and api-platform GraphQL types the field as the **`Iterable` scalar** → `Field "items" of type "Iterable" must not have a sub selection.` **Prod-only** symptom: CLI (`artisan serve`, `save_comments=1`) resolves the field as a `[CartItem]` connection; php-fpm with `save_comments=0` does not — same code/deps/config/warmed-cache. Systemic across every `@var X[]`/`array<X>` DTO property, not just cart `items`. **Fix (package-carried, no server config):** `src/Metadata/SourceDocblockPropertyMetadataFactory.php` decorates `PropertyMetadataFactoryInterface` — when a `Webkul\BagistoApi\` array-typed property's decorated `nativeType` lacks a class element, it re-reads the `@var` element class from the class **source file on disk** (comments intact — OPcache only strips bytecode) via `token_get_all`, resolves the FQCN from the file's `namespace`+`use` map, and re-applies `withNativeType(Type::nullable(Type::array(Type::object($fqcn))))`. No-op when the docblock is readable (CLI). Registered via a 2nd `$this->app->extend(PropertyMetadataFactoryInterface::class, …)` after `NullableToOnePropertyMetadataFactory` in `BagistoApiServiceProvider`. Only sets `nativeType` — the legacy `withBuiltinTypes` path throws (`"mixed" is not a valid array key`), do NOT use it. **Do NOT** "fix" this by editing/reverting docblocks (they're correct — just invisible to stripped-comment fpm) or by relying on php.ini `opcache.save_comments=1` (it's `PHP_INI_SYSTEM` — needs root, can't be set per-dir/`.user.ini`, doesn't survive deploys). Confirm the SAPI cause with a probe: `var_dump((new ReflectionProperty(CartData::class,'items'))->getDocComment())` returns `false` under fpm, the `@var` string under CLI.
  - **VERSION-GATED — only api-platform 4.3+ is affected; the pre-4.3 stack is immune (2026-07-21).** api-platform **4.3 switched property-type extraction** from `symfony/property-info` (legacy `builtinTypes`) to `symfony/type-info` (`nativeType`). The stripped-comment `Iterable` failure lives **only in the new `type-info` path**. A deployment on the **older 4.1/4.2 stack** (e.g. `api-platform/laravel:4.1.25` + `api-platform/graphql:4.2.3`, with `api-platform/metadata` resolving to 4.1/4.2 and `symfony/type-info` typically **not installed**) reads the `@var` the old way and **works with byte-identical code + the SAME server + SAME `opcache.save_comments=0`** — no `SourceDocblockPropertyMetadataFactory` needed. Proven: `.../BagistoApi-2.3.8/api-demo` (bare 4.1/4.2 package, no fix) resolves `items { edges }`, while `bagisto-2.4.7` (4.3.17) does not without the fix. **The real trigger is a 4.3-family `api-platform/metadata` / `symfony/type-info` in the RESOLVED set — not the top-level 4.1/4.2 pins.** That's why the "nextjs" box broke despite pinning 4.1.25/4.2.3: its lock **forced `api-platform/metadata` to 4.3.1** (a mixed pin set), pulling in the `type-info` path. **Diagnosis when a deployment shows the bug: check the resolved lock — `grep -A2 '"name": "symfony/type-info"' composer.lock` and `... "api-platform/metadata" ...`. type-info present / metadata 4.3.x → needs the fix; type-info absent / metadata 4.1-4.2 → immune, do NOT add the fix.**


## Extending a core model that BECOMES translatable — two traps (2026-08-27)

Core turned `Product\Models\ProductImage` into a `TranslatableModel` in 2.4.10 (`product_image_translations.alt_text`). Our `Models\ProductImage` extends it, and inheriting that change broke twice — both silently, both worth knowing before the next core bump.

- **Astrotomic derives the translation class from OUR class name.** `getTranslationModelName()` returns `$this->translationModel ?: <CurrentClass>Translation`, so the subclass went looking for `Webkul\BagistoApi\Models\ProductImageTranslation` and every read of the resource 500'd with `Class ... not found` — core's `$with = ['translations']` eager-loads it on the way in, so listing, detail and any relation that touches images all died at once. **Fix: `public $translationModel = <core>Translation::class;`** on the subclass (the `Models\CoreAttribute` precedent). Audit the rest with a boot-time reflection sweep over `src/Models` + `src/Admin/Models`: for every class extending `TranslatableModel`, assert `class_exists((new $c)->getTranslationModelName())`. Everything else in the package already ships its own `*Translation` subclass, so `ProductImage` was the only miss.
- **A translated attribute is NOT part of the API resource until an accessor exists.** `$visible` does not drive API Platform — its Eloquent property extractor sees columns, relations and accessor **methods**, nothing else. `alt_text` persisted, `toArray()` carried it, and the endpoint still returned nothing, because `product_images` has no such column and the model had no `getAltTextAttribute()`. (`file_name` resolves only because core defines an accessor for it.) **Fix: declare `getAltTextAttribute(?string $value = null): ?string { return $value; }`** — a shim, not a lookup: Translatable's `getAttribute()` sees `hasGetMutator()`, writes the resolved translation into `$this->attributes` and then calls `getAttributeValue()`, so the accessor is handed the value and must simply return it. Re-querying inside it would be redundant and risks recursion. **Diagnose by dumping the resource's real property list rather than guessing** — `app(PropertyNameCollectionFactoryInterface::class)->create(<Resource>::class)` next to `$model->toArray()`: the field present in the second and absent from the first is exactly this trap. Regression-locked by `RestApi/ProductImageTest::test_image_carries_its_alt_text`.

## Bagisto 2.4.8 → 2.4.10 core deltas the API had to re-mirror (2026-08-27)

Found by auditing core's own CHANGELOG against our code, then fixed. Each one is a **core
behaviour change with no change on our side** — the pattern the "2.4.7 upgrade" table above
describes. Worth reading before the next core bump, because the same shapes recur.

- **A sitemap is channel-specific now.** `sitemap_channels` pivot + `SitemapRepository::create|update`
  doing `$sitemap->channels()->sync($data['channels'])` **unconditionally** — a payload without
  `channels` fatals on the missing key, so our create/update was broken outright, and
  `ProcessSitemap::handle()` returns early when `channels` is empty, so a row that did get in
  generated nothing. `additional` also changed shape: per-channel `additional.channels[<id>] =
  {hostname, index, sitemaps}` replaced the flat `additional.{index,sitemaps}`. We now accept and
  return `channels`, expose `urls` (the per-channel index URL, mirroring the datagrid's
  "Link for Google" closure) and `generatedFiles`, keep the flat keys only as a legacy fallback,
  and refuse a generate on a channel-less sitemap with a real message. Shared mapping lives in
  `Admin/State/Concerns/MapsSitemapChannels`.
- **`product_flat` gained derived columns** (`quantity`, `images_count`, `base_image`,
  `manage_stock`, `category_name`, `attribute_family_name`) filled by
  `Product\Helpers\Indexers\Flat::refreshDerivedColumns()`, and `ProductDataGrid` reads them
  instead of joining. Our listing was computing the same values with correlated subqueries, which
  drifted twice: the base image was `ORDER BY id` where core orders `position, id` (#11414), and
  the category was one row where core `GROUP_CONCAT`s every category. The provider now reads the
  columns. **Test consequence:** a fixture that writes a `product_flat` row by hand never runs the
  indexer, so those columns stay null — call `refreshDerivedColumns([$id])` after inserting, which
  is exactly what core's own tests do.
- **Product images carry translated `alt_text`** (`product_image_translations`), written by
  `ProductMediaRepository::saveAltText` for every `core()->getRequestedLocaleCodes()`. Exposed on
  upload, on the new `PUT /catalog/products/{productId}/images/{id}`, on the admin detail
  connection and on the storefront image endpoints. `Product::images()` also orders by `position`
  now, so the storefront nested provider orders too.
- **A product import chooses where its images come from** — `image_source` in `url|upload|directory`
  plus `images_archive_name`. An `upload` import needs its ZIP unpacked into `imports/{id}/images`
  on the **private** disk, because `Importer::resolveUploadedImage()` reads exactly that path. That
  forces core's create order: insert the row first, then store its files under `imports/{id}`, and
  roll the row back if storing throws.
- **Guards core added:** an email template or marketing event that a campaign still uses cannot be
  deleted (#11420); an attribute's `regex` is validated with `Core\Rules\Regex` because the pattern
  is also written into the storefront form (#11417); a customer email is unique **per channel**
  (`unique:customers,email,{id},id,channel_id,{channel}`), not per table (#11418).
- **A guard core replaced:** attribute-family delete now refuses `AttributeFamily::DEFAULT_CODE`
  rather than the last remaining family (#11419). We swapped ours to match — parity wins over
  keeping our old guard, per the principle above.

  **Test consequence of the two new validators.** Both reject payloads that were valid in 2.4.8, so
  a suite written before the bump fails at the endpoint rather than in our code: an attribute
  `regex` must now be a **delimited** pattern the browser can compile too (`/^[A-Z]+$/`, not
  `^[A-Z]+$` — `Core\Rules\Regex::isUsable` requires `/…/` and only the `imsu` modifiers), and a
  products import must carry the field its `image_source` implies (`images_directory_path` for the
  default `directory`, an `upload_images` ZIP for `upload`, neither for `url`). Fixture payloads
  need both; each is regression-locked by a test that asserts the 422.

- **Carousel options must not reach the core repository (2026-08-27).** `SectionRepository::update()` unsets `options` for `image_carousel` / `services_content` and then calls `uploadImage(request()->all(), $section)`, which walks the locale's options as a **list of upload rows** and reads `$image['image']` on each. This endpoint takes the **stored** shape (`{"images": [{image, link, title}]}`, paths already uploaded through the media endpoint), so the one element it walked was the inner `images` array and the write 500'd with `Undefined array key "image"`. The processor merges its payload into the request for core's locale lookup, which is how the options got there. **Fix: for those two types the payload no longer carries `[$locale]['options']` at all** — core then returns early and the translation is written by the processor, which it already did. Core still normalises each row's key order, so assert carousel options with `toEqual`, not `toBe`. Regression-locked by `RestApi/AppearanceSectionTest::test_update_writes_carousel_options_in_the_shape_they_are_stored`.
- **`Cache::flush()` in a test can destroy the API metadata pool — this is what broke CI (2026-08-27).** The first cut of `forgetCoreConfigCache()` fell back to `Cache::flush()` when the store was not taggable. Locally that never fired (`phpunit.xml` sets `CACHE_STORE=array`, which is taggable), but **CI runs the upstream Bagisto host, whose `.env` keeps `CACHE_STORE=file`** — the workflow only set the legacy `CACHE_DRIVER`, which Laravel 12 ignores. `Cache::getStore()` was therefore a `FileStore`, the fallback ran, and it deleted `storage/framework/cache/data/**`, which is exactly where `API_PLATFORM_CACHE=file` keeps the metadata pool. Four parallel workers then rebuilt it on top of each other and the GraphQL path built its serializer from a half-written pool, surfacing as **`Call to undefined method ApiPlatform\GraphQl\Serializer\ItemNormalizer::setSerializer()`** — an error that is impossible from the installed versions and never reproduced locally. The failing suites were exactly the ones whose tests call the helper (shop GraphQL: Gdpr / SocialLogin / EuWithdrawal; admin GraphQL: Sitemap / OrderAction). Verified by `CACHE_STORE=file php artisan tinker --execute 'Cache::flush()'`: 256 metadata entries to 0. **Two rules: a test must never flush a whole cache store, and the app cache must not share a store with the metadata pool** (the workflow now sets `CACHE_STORE=array` as well).

- **Never clear the API cache while a suite is running (2026-08-27).** With the metadata pool on the `file` store (TEST TRAP 2), `bagisto-api-platform:clear-cache` / `optimize:clear` deletes `storage/framework/cache/data/**` out from under the parallel workers, and whichever worker is mid-write dies with `file_put_contents(...): Failed to open stream: No such file or directory` or serves a request off half a pool and returns `Internal server error`. Both look like resource defects and neither reproduces afterwards. Clear the cache **before** a run, never during one.

- **Two Appearance defects the e2e rewrite surfaced (2026-08-27).** (a) **`deleteAdminAppearanceSection` returned a null payload.** The processor chose its transport with `isGraphQL($context)`, but API Platform does **not** set `graphql_operation_name` on a `delete` mutation (the documented Phase-3 finding), so the snapshot was never taken and the mutation answered `adminAppearanceSection: null` — with no `errors`, which is why it looked like it worked. Fixed by branching on `$operation instanceof Mutation`, which is what the rest of the package already does. **Any processor that decides transport for a delete must use the operation, never the context.** The Pest test was named `..._returns_the_snapshot` but asserted only "no errors" + the row is gone, so it passed throughout; it now asserts `_id`, `name` and `message`. (b) **`adminAppearanceThemes` / `adminAppearanceSections` are plain GraphQL LISTs, not connections** — both declare `paginationEnabled: false`, so `{ edges { node } }` is a schema error ("Cannot query field \"edges\""). Introspect before writing a listing query: `paginationEnabled: false` ⇒ select the fields directly.

- **Appearance replaced Settings → Themes, so the e2e specs had to move with it.** The theme-customization
  surface is gone on both sides: shop `themeCustomization(s)` / `/api/shop/theme-customizations` →
  `section(s)` / `/api/shop/sections`, and admin `adminSettingsTheme(s)` / `/api/admin/settings/themes` →
  `adminAppearanceTheme(s)` + `adminAppearanceSection(s)` under `/api/admin/appearance`. The Pest suites
  were rewritten with the feature; the **Playwright suites were not**, which is the whole of the 19 CI
  failures (`themes.spec.ts` ×2, `getThemeCustomizations.spec.ts`) — every one is a query against a field
  that no longer exists, not a defect. Shop specs were a straight rename; the admin ones are new files
  under `tests/{graphQL,restAPI}/api/automation/admin/appearance/`. **A section write in e2e must stage
  and discard rather than publish** — a published section changes the storefront the other suites read.

## `export-schema` — never rebuild `TypesContainerInterface` (2026-08-26)

`ExportSchemaCommand::buildGraphQlSchema()` forgets a few container singletons between the shop and admin passes so neither surface carries the other's resources. **`TypesContainerInterface` must NOT be one of them.** The types container is shared by `TypeConverter` and `FieldsBuilder`; forgetting only the container gives the schema builder a fresh one (which `SchemaBuilder::getSchema()` seeds with the scalar types) while the already-resolved `TypeConverter` keeps reading the old one. `TypeConverter::convertType()` returns the **string** `'Iterable'` for any array/iterable property, `resolveType('Iterable')` then misses in the stale container and throws, and `FieldsBuilder` **catches that and skips the field** — so every array-valued field vanished from the printed SDL with no error and no failing test.

Symptom to recognise: `grep -c Iterable schema/generated/*.graphql` returns **0** while the live endpoint introspects the same field as `Iterable!`. It hit `AdminCart.items`/`billingAddress`, `AdminAppearanceTheme.tags`/`activeOn`, `AdminAppearanceSectionFields.schema`/`options`, `Theme.sectionTypes` — everything typed `?array`. Diagnose by comparing `spl_object_id()` of the container held by `TypeConverter` against `app(TypesContainerInterface::class)`. Regression-locked by `tests/Feature/SchemaExportTest::test_exported_graphql_keeps_array_typed_fields`.

## Upgrading the package to a newer Bagisto core (e.g. 2.4.10) — playbook (2026-08-21)

**Context for whoever ports this package into a 2.4.10 (or newer) host.** This package is built against **Bagisto 2.4.7 / Laravel 12 / PHP 8.3 / api-platform `~4.3.8` (resolves 4.3.17) / Symfony 7.x / laravel/socialite 5.x**. It is a **mirror of core Bagisto** — it re-implements core admin/shop controller + repository + config + event + ACL behaviour over REST + GraphQL. So a core bump can break it **with no change on our side**, exactly as the 2.3.8 → 2.4.7 bump did (see "2.4.7 upgrade — what broke" above). Treat the upgrade as a **re-verification of those mirrors**, not a no-op. The upgrade happens in the HOST project: drop the package in, install deps, run `bagisto-api-platform:install`, then work the checklist. Keep this CLAUDE.md (and `packages/Webkul/BagistoApi/BAGISTO-2.4-COMPATIBILITY.md`) with the package so the context travels.

### 1. Dependency resolution — do this FIRST (the trap that bit us hardest)
- **Pin api-platform to `~4.3.8`** (`api-platform/laravel` + `api-platform/graphql`). Never let it float to 5.x. Do NOT add `api-platform/laravel` to the host's `dont-discover` removal — the package registers it manually.
- **Symfony must stay `^7`.** api-platform 4.3 does **not** support Symfony 8 — Symfony 8 removed `Symfony\Component\PropertyInfo\Type`, which api-platform 4.3.17 still references → fatal at boot (`Class "Symfony\Component\PropertyInfo\Type" not found`). Easy to hit because **composer resolves against the machine's PHP**: on a **PHP 8.4** box it pulls Symfony 8 (needs PHP ≥ 8.4.1) for the loosely-constrained components (`property-info`, `property-access`, `serializer`, `validator`, `type-info`, `filesystem`, `web-link`) → a broken mixed 7.4-core + 8.1 set.
  - **Fix / prevention:** target PHP 8.3 for the install — `composer config platform.php 8.3.x && composer update -W` (Symfony 8 is then excluded, drops those components back to 7.4). If the host may run PHP 8.4, **cap Symfony to `^7` in the package `composer.json` `require`** (`symfony/property-info:^7.1`, `symfony/property-access:^7.0`, `symfony/serializer:^7.4.9`, `symfony/validator:^7.0`, `symfony/type-info:^7.3`) so 4.3 can never pair with 8.
  - Diagnose a suspect install: `grep -A2 '"name": "symfony/property-info"' composer.lock` — 8.x = broken, 7.4.x = good.
- **A newer Symfony minor can widen a vendor interface and fatal a package override at class-load.** When we override a Symfony/api-platform interface method, it must mirror the FULL current signature incl. trailing optional params. This already bit `src/Serializer/OutputOnlySnakeToCamelNameConverter::normalize|denormalize` — Symfony 7.2+ widened `NameConverterInterface::normalize` to `(string $propertyName, ?string $class = null, ?string $format = null, array $context = [])`. After the bump, re-check every `src/**` class implementing a vendor interface (normalizers, name converters, `*PropertyMetadataFactory`, `CustomIriConverter`, `Scoped*SchemaBuilder`, `FixedSerializerContextBuilder`).

### 2. Re-verify the core mirrors (grep core, confirm our copy still matches)
- **ACL keys** — 2.4 already renamed some (`settings.users.*`, `catalog.products`). A permission string absent from `packages/Webkul/Admin/src/Config/acl.php` can never be granted → every `permission_type=custom` token 403s. Check every key our processors/guards use.
- **Controller / repository behaviour** the processors mirror — validation rules, events fired, guards, force-written fields, state transitions (e.g. `ProductRepository::update` surgical-3rd-arg, `OrderRepository::cancel`, config-save flow). If core changed the shape, our processor drifts.
- **Config field definitions** — `Admin/Config/system.php` (`channel_based` / `locale_based` flags, defaults, added/removed fields). e.g. social login reads `customer.settings.social_login.enable_<provider>` (channel-based).
- **Events** — the `*.before` / `*.after` names our processors dispatch or depend on.
- **Migrations / columns** — a renamed/dropped column (like `addresses.address1 → address`, the dropped `customers.notes`) breaks a DTO or a raw `DB::table` query.
- **Route parity** — after `bagisto-api-platform:optimize`, confirm the ~272 auto-generated api-platform routes and underscore segments survive: `GET /api/shop/channel_translations` must 200 (the `UnderscorePathSegmentNameGenerator` pin in the three `api-platform.php` configs).

### 3. Validate
- `php artisan optimize:clear` first (never run tests with cached config — see the TEST TRAP note; `optimize` bakes `app.env=local` and mis-fires the testing guards).
- Run pest: `php artisan test packages/Webkul/BagistoApi/tests/Feature --parallel --processes=8`. A red suite after a core bump is almost always a **drifted mirror**, not our logic.
- Restore the fast path with `bagisto-api-platform:optimize`; reload php-fpm so workers pick up new code + a fresh schema.

**Rule:** every failure after a Bagisto bump comes from a **behaviour change in core or a bumped library**, not from our business logic — find the upstream delta and re-mirror it. Do not rewrite the response contract to make a test pass.

---

Claude reference from 2.3.8 Bagisto Api development
# BagistoApi — Claude Code Context

Auto-loaded by Claude Code on every session in this project. Keep this file up to date as patterns evolve.

---

## Release roadmap — post-2.0.0 bug-fix releases (2.1 / 2.2 / 2.3)

**Status: PLAN ONLY (no fixes applied yet).** Found by a deep multi-agent audit of the whole package (admin + shop, REST + GraphQL) on 2026-06-30, cross-checked against `CHANGELOG.md` so nothing already fixed in 2.0.0 is re-listed. **Structure (user decision 2026-06-30): two SOLID releases (2.1, 2.2) carrying the real substance, then 2.3 = a STABLE release** (shop REST docs/examples cleanup + minor/Octane polish + any small bugs surfacing during 2.1/2.2). The API is genuinely more complete than a "3 releases of gaps" framing implies — these are the real shippable items, not padding. Tick a row when fixed; move it into `CHANGELOG.md` under the matching `## [2.x]` block. Each fix follows the project rule: run the affected resource's GraphQL + REST tests green before marking done.

### 2.1 — Sales + critical correctness (SOLID)

One real missing feature + two confirmed small bugs (D1 dashboard `dateRange` over GraphQL — high; I1 rate-limiter mis-count — medium). S6 dropped as a non-issue (see below).

**✅ SHIPPED in 2.1.0 (2026-06-30).** All three landed + tested green: Transaction-create (`POST /api/admin/transactions` + `createAdminTransaction`, 9 REST / 3 GraphQL), D1 (snake `$date_range` + `AcceptsCamelCaseWrites` on `AdminDashboard`; GraphQL `statsAdminDashboard { dateRange }` now non-null — regression-locked in `Admin/GraphQL/DashboardTest::test_date_range_resolves_over_graphql`), I1 (distinct `:min:`/`:day:` throttle keys — verified by inspection against `ThrottleRequests.php` keying). Coverage table + CHANGELOG `[2.1.0]` updated.

- **Transaction create — MISSING endpoint (both transports). [headline]** Core `POST sales/transactions/create` → `Webkul\Admin\Http\Controllers\Sales\TransactionController::store` ([sales-routes.php:84]) records a manual payment against an invoice: validates `invoice_id`/`payment_method`/`amount` (numeric); 400 if invoice missing / already `paid` / cumulative > `base_grand_total` / amount ≤ 0; inserts an `order_transactions` row (`status=paid`, random `transaction_id`); when cumulative payments ≥ `base_grand_total` it marks the invoice **paid** + the order **completed** (if a shipment exists) or **processing**. The API `AdminTransaction` resource exposes only `GET /transactions`, `GET /transactions/{id}`, `GET /transactions/export` + `Query`/`QueryCollection` — **no `Post`, no `create` Mutation, no create processor.** Integrators can't record an out-of-band / partial / later payment (the invoice-create `canCreateTransaction` flag only captures the full amount at invoice time). **Build = `POST /api/admin/transactions` + `createAdminTransaction` mutation + a processor mirroring `store` (4 guards + invoice/order auto-complete + `sales.transaction.save.{before,after}` events).** Effort: M.

- **I1 — rate-limiter mis-counts when both caps set (verified 2026-06-30, medium).** `src/Providers/BagistoApiServiceProvider.php:1359-1366` — the per-minute + per-day `Limit` objects use the **same `->by('admin-api-token:{id}')` key**. Laravel keys each throttle bucket `md5($limiterName.$limit->key)` (`ThrottleRequests.php:132`), so both limits share ONE bucket, and `handleRequest` calls `hit()` once per limit → the shared counter is **incremented twice per request**. When a token has BOTH caps set (the default 60/min + 10000/day): the per-minute cap trips at HALF the configured value (60/min → 429 at ~30 req/min) AND the per-day cap shares the 60s-decay counter so it resets every minute → never enforced. Only manifests when both caps are non-null (single-cap tokens fine). Fix = distinct keys (`admin-api-token:min:{id}` / `:day:{id}`). Effort: S. *(Repro: default token, 31 requests in a minute → 429.)*

**DROPPED — S6 (order-action guard `'*'` wildcard), verified non-issue (2026-06-30).** `AdminOrderActionGuard::adminHasPermission` already returns true for `permission_type='all'` (line 193); the only delta vs the sibling `ChecksAdminPermission` guard is the sibling's extra `in_array('*', $perms)` check. That only fires for a `permission_type='custom'` role whose `permissions` literally contains `'*'` — a config the admin UI never produces and `applyAbilityScope` never creates (full-access tokens stay type `all`, handled by the `'all'` branch). Not reachable → not a bug.

- **D1 — dashboard `dateRange` null over GraphQL (HIGH).** `src/Admin/Models/AdminDashboard.php:96` (+ provider:64, resolver:38) — camelCase `?string` with no snake backing / `AcceptsCamelCaseWrites` → `statsAdminDashboard { dateRange }` resolves **null** (REST fine). Reporting was fixed in 2.0.0; Dashboard was the documented pending one-liner. Fix = snake `$date_range` + the trait (mirror the Reporting fix). Effort: S.

**REJECTED — currency-format hardening (was S1–S5).** Detail/builder/item-mapper call raw `core()->formatPrice()` (vs `safeFormatPrice` in list providers); a deleted/renamed currency still referenced by an order → `formatCurrency(null)` TypeError → 500. **Decision (user, 2026-06-30): do NOT guard.** Core admin order view uses the same raw `formatPrice` and 500s identically — the API must behave as core does (parity-with-core principle). If core later hardens it, we follow. Not a release item.

### 2.2 — Catalog + Marketing (SOLID)

**✅ PARTIALLY SHIPPED in 2.2.0 (2026-07-01) — the 6 bug fixes landed + tested green.** M1–M4 (CampaignSend `campaignId` / CouponGenerate `cartRuleId` / SitemapGenerate `sitemapId`+paths+timestamp / CouponMassDelete `cartRuleId` — snake props + `AcceptsCamelCaseWrites` on each action-result model; **gotcha: M2/M4 also hand-build a REST `JsonResponse` reading the prop back — those reads had to switch to the snake name `$dto->cart_rule_id` since the trait only provides `__set`, not `__get`**), CU1 (`AdminCustomerAddressProcessor::assertOwnedAddress` now rejects a missing `customerId` with 422 + wrong owner with 403 — REST unaffected), M5 (Campaign update merges existing values before validate → partial `PUT` works). Regression-locked in the per-resource suites (the CampaignSend / CouponGenerate / CouponMassDelete / SitemapGenerate GraphQL happy-path tests now assert the multi-word field resolves; `Admin/GraphQL/CustomerTest` has 3 address-ownership tests; `Admin/RestApi/MarketingCampaignTest::test_update_is_partial`). **STILL PENDING: CAT1–CAT3** (the multipart file-upload / video / download feature-adds — larger, next batch).

**✅ CAT1 + CAT3 SHIPPED in 2.2.0 (2026-07-01); CAT2 (video) moved to 2.3 (user decision).** Downloadable file endpoints (REST-only, binary): `POST /catalog/products/{id}/downloadable-links/upload` + `.../downloadable-samples/upload` (store the file-type link/sample binary, return `{type,path,name,url}` — the client sets the returned `path` on the link/sample via product update) and `GET /catalog/products/{id}/downloadable/{attributeId}/download` (stream the stored file). Resource `AdminCatalogProductDownloadableFile` (POPO, `graphQlOperations: []` — binary is REST-only) + `AdminCatalogProductDownloadableFileProcessor` (uploads, JsonResponse, `ChecksAdminPermission` `catalog.products.edit`) + `AdminCatalogProductDownloadableFileProvider` (download — returns `Storage::disk('private')->download()`; provider return type MUST be `object|array|null`, NOT `mixed`, or API Platform fatals the whole metadata build). Files on the `private` disk. Tested green (`Admin/RestApi/CatalogProductDownloadableFileTest`, 9). Lang under `admin.product.downloadable.*`.

**Catalog — 2 genuine niche gaps + 1 minor helper** (re-vetted 2026-06-30 against the "covered by existing list-filter / detail-inline?" bar — the same bar that dropped Sales order-search):

| # | feature | core route | transport | effort | note |
|---|---------|-----------|-----------|--------|------|
| CAT1 | Downloadable **file-type** link/sample upload | `POST catalog/products/upload-file/{id}` (`uploadLink`) + `upload-sample/{id}` (`uploadSample`) | REST multipart (GraphQL 422 — mirror image-upload) | M | Narrowed: link/sample **metadata** (url-type) is already settable via product update; only the **binary file** for file-type links/samples is missing. Niche. |
| CAT2 | Product video write/upload | product form `videos` (update processor **strips** `videos`; readable in detail via `AdminProductDetailVideo` but not writable) | REST multipart + update support | M | Niche product feature. |
| CAT3 | Downloadable file download (stream stored file) | `GET catalog/products/{id}/{attribute_id}` (`download`) | REST-only binary | S | Minor helper; bundle with CAT1. |

**DROPPED as NOT real gaps** (covered by existing endpoints — same padding the Sales scan rejected):
- ~~CAT4 category search~~ — `AdminCategoryCollectionProvider` already has a `name` LIKE filter (+ parent_id/status/position). Functionally covered.
- ~~CAT5 attribute options list~~ — `AdminAttributeItemProvider` already inlines `options` (with translations) in `GET /catalog/attributes/{id}`. Reachable via detail.
- ~~C1 GraphQL `adminCatalogProduct(id:)` null~~ — FALSE POSITIVE; provider branches GraphQL→Eloquent connections (migrated 2026-06-19), detail resolves correctly. The first audit agent mis-read a `markTestSkipped`.

**Marketing — action-result GraphQL nulls** (5 one-liner fixes). Root pattern: the snake-case + `AcceptsCamelCaseWrites` sweep that fixed list/detail resources **missed the action-only result resources** (CampaignSend, CouponGenerate, SitemapGenerate, CouponMassDelete) — same class of miss flagged for `AdminReorder`/`AdminCustomerImpersonate`. `FixedSerializerContextBuilder` denormalizes the GraphQL mutation field (`campaignId`→`campaign_id`) for admin resources then reads the snake name off a POPO that only has the camel prop → **null over GraphQL**. Fix each = snake props + the trait, plus a GraphQL test selecting the multi-word field. Plus the customer-address ownership fix (correctness):

| # | file:line | sev | bug | repro |
|---|-----------|-----|-----|-------|
| CU1 | `src/Admin/State/AdminCustomerAddressProcessor.php:73-86,139-194` (+ `AdminCustomerAddress.php:345-362`) | low-med | GraphQL address `update`/`delete`/`setDefault` declare no required `customerId` arg (queries do); processor resolves `customerId ?? 0`, so when omitted the `if ($customerId>0 && ...)` ownership check is **skipped** → admin can mutate/delete any address by id regardless of claimed `{customerId}`. REST is safe (path always carries it). Fix = make `customerId` a required mutation arg | `updateAdminCustomerAddress(input:{id:"/api/admin/customers/4/addresses/12",city:"X"})` (no `customerId`) edits address 12 even cross-customer |
| M1 | `src/Admin/Models/AdminMarketingCampaignSend.php:93` (+ processor:65) | med | `campaignId` camelCase POPO, no trait → null over GraphQL | `createAdminMarketingCampaignSend{ campaignId }` → null |
| M2 | `src/Admin/Models/AdminMarketingCartRuleCouponGenerate.php:115` (+ processor:145) | med | `cartRuleId` camelCase POPO, no trait → null over GraphQL | `createAdminMarketingCartRuleCouponGenerate{ cartRuleId }` → null |
| M3 | `src/Admin/Models/AdminMarketingSitemapGenerate.php:91-100` (+ processor:69-72) | med | `sitemapId`/`indexFile`/`generatedSitemaps`/`generatedAt` camelCase POPO, no trait → all null over GraphQL (client can't read generated paths/timestamp) | `createAdminMarketingSitemapGenerate{ indexFile generatedSitemaps generatedAt }` → all null |
| M4 | `src/Admin/Models/AdminMarketingCartRuleCouponMassDelete.php:89` | low | `cartRuleId` camelCase POPO, no trait → null over GraphQL | mass-delete mutation selecting `cartRuleId` → null |
| M5 | `src/Admin/State/AdminMarketingCampaignProcessor.php:106` | low | Campaign **update not partial** — validates raw input (no merge), all 5 core fields `required` → `PUT {"status":1}` 422. Inconsistent with CartRule/CatalogRule (merge-then-validate). Borderline (matches monolith full-form) — flag for product decision | `PUT /campaigns/{id}` body `{"subject":"x"}` → 422 "name field is required" |

### 2.3 — STABLE (docs cleanup + minor polish + CAT2 video)

Low-risk stabilization — safe to tag **stable**. Correctness polish + the CAT2 video feature + the long-pending shop REST docs cleanup.

**✅ SHIPPED in 2.3.0 (2026-07-02): CAT2 video + SH1/SH2 + TaxRate + DRY cleanup.** All landed + tested green: **CAT2** (product video upload/delete — `POST /catalog/products/{id}/videos` multipart + `DELETE .../videos/{id}` + `deleteAdminCatalogProductVideo`; resource `AdminCatalogProductVideo` + processor/provider mirroring the image resource, `product_videos` `type='videos'` on the `public` disk; GraphQL create rejects 422; `Admin/RestApi/CatalogProductVideoTest` 8 green + **live-verified** upload 201/delete 200; lang `admin.product.video.*`. **Gotcha reused from CAT1: the Delete-op provider return type MUST be `object|array|null`, not `mixed`, and REST delete tests must use `deleteJson` not `delete` — a plain DELETE without the JSON Accept header returns 200 without routing to the processor.**), **SH1/SH2** (`FilterableAttributesProvider` now resolves `maxPrice` against the authenticated customer's group — `Auth::guard('sanctum')->user()?->group` else guest — and computes a real `minPrice` via `->min('min_price')`; shop `CategoryTest` 13 green), **TaxRate** (`normalisePayload` nulls the inactive-mode zip fields when `is_zip` flips; `SettingsTaxRateTest` 37 green), **DRY** (`AdminCategoryProcessor::slugTaken` + `AdminCmsPageProcessor::urlKeyTaken` deleted, now delegate to `CategoryRepository::isSlugUnique` / `PageRepository::isUrlKeyUnique` — **pass `0` not `null` for the create-path exclude id**, since `WHERE id <> NULL` matches nothing in MySQL; Category 71 / CmsPage 45 green). **STILL PENDING: shop REST docs cleanup** (needs a valid storefront key — see below).

- **Shop REST examples/docs cleanup** (pending memory item): wrong plural shop-auth paths, the fictional reset-password page, fake request/response bodies. Verify each example against the live endpoint with a valid storefront key; correct the docs (bagisto-api-docs REST pages).
- **SH1/SH2 — FilterableAttributes price bounds.** `src/State/FilterableAttributesProvider.php:67-73,94` — `maxPrice` always computed against the **guest** customer group (provider never authenticates) so group-priced customers get the wrong max; `minPrice` hardcoded `0.0`. Low (correctness, not crash). Reads the existing `FilterableAttributesProvider:67` TODO ("use customer group from active customer when auth is implemented").
- **CAT2 — Product video write/upload (moved from 2.2, user decision 2026-07-01).** Videos are readable in product detail (`AdminProductDetailVideo`) but the update processor **strips** the `videos` field and there's no dedicated write endpoint. Build a REST multipart upload + delete, mirroring the image resource (`AdminCatalogProductImage`): `POST /catalog/products/{id}/videos` (multipart `video` → store on `public` disk under `product/{id}`, insert a `product_videos` row `type='videos'`) + `DELETE /catalog/products/{id}/videos/{id}`; GraphQL upload rejects 422 (binary). `product_videos` mirrors `product_images` (id/product_id/type/path/position). Also fix the misleading update docblock that lumps `videos` with images as "use the dedicated endpoint" — until CAT2 ships there is none. Effort: M.
- **DROPPED — I2 (AdminApiGuard stale-request IP allowlist) + I3 (audit context not reset), both Octane-only (user decision 2026-07-01).** Only manifest on a persistent worker (Octane/Swoole); fpm is unaffected. Not required for the stable release.
- **TaxRate zip-orphan cosmetic.** `AdminSettingsTaxRateProcessor::normalisePayload` leaves orphan `zip_from`/`zip_to` (or `zip_code`) when `is_zip` is flipped — no crash, active-mode fields still validated. Clean up opportunistically.
- **DRY-vs-core cleanup (audit 2026-07-01, only 2 real violations found — the API is otherwise DRY-clean, writes already delegate to core repos).** Both are hand-rolled uniqueness queries that shadow an already-injected public core repo method: (1) `AdminCategoryProcessor::slugTaken()` (line 215, called create:183 + update:210) → replace with `$this->categoryRepository->isSlugUnique($excludeId ?? 0, $slug)` (core `CategoryRepository::isSlugUnique`, line 194) and delete the helper; (2) `AdminCmsPageProcessor::urlKeyTaken()` (line 215, called create:183) → replace with `$this->pageRepository->isUrlKeyUnique($excludeId, $urlKey)` (core `PageRepository::isUrlKeyUnique`, line 69) and delete the helper — the SAME processor's update path (line 208) already delegates, so the create path is just inconsistent. Both S, behavior-identical, zero-risk. **Not violations (correctly excluded):** the `code`-uniqueness checks (Channel/Currency/TaxCategory/InventorySource) have no core `isCodeUnique()` method (plain `unique:`-rule equivalents), and the Sales action math / order-state transitions live only inside core controller actions (no extractable method) — the API already delegates the actual writes.
- **Any small bugs surfacing during 2.1/2.2 implementation** — collect here.

**Settings menu: CLEAN** — audited ×2, 0 confirmed bugs (action-result resources use single-word props; TaxCategory↔TaxRate pivot uses `HasMany` + `tax_rate_id as id` alias handling gotcha j; delete guards + partial-update merges correct).

**Final tally:** **1 missing feature** (Transaction create) + **2 high** bugs (I1, D1) + **3 Catalog niche/helper gaps** (CAT1–CAT3) + **1 customer + 5 marketing GraphQL-null fixes** (CU1, M1–M5) + **shop-docs cleanup** + **3 Octane/cosmetic polish** (I2, I3, TaxRate). Dropped as non-issues: S1–S5 (core-parity), C1/CAT4/CAT5 (covered/false-positive). Settings clean.

---

## Current Development Status

**Phase: Manual QA pass on the Admin API (REST + GraphQL).** Build is feature-complete; the user is now walking through every admin endpoint by hand against the api-docs site and reporting defects. Active work is **bug-fixing + doc-fixing**, not new features.

### Manual QA workflow — how every session in this phase runs

The user drives the session menu-by-menu (mirroring the Bagisto admin sidebar). For each menu (or sub-menu when the menu is large) the protocol is:

1. **User announces the menu** they're about to test (e.g. "testing Customers → Groups now"). If big, they'll split it into sub-parts.
2. **Before they hit any endpoint, explain the menu to them.** Every time, cover:
   - **Whole flow of the menu** — what the user-facing admin feature does, which endpoints back it, and the order a client would call them in.
   - **Directory structure & file map for every call** — Model (`src/Admin/Models/Admin*.php`) ↔ DTO (`src/Admin/Dto/*Input.php`) ↔ Provider (`src/Admin/State/*Provider.php`) ↔ Processor (`src/Admin/State/*Processor.php`) ↔ Resolver (if GraphQL custom) ↔ lang keys ↔ tests. Link each file with markdown paths.
   - **Why each directory exists** — Model = `#[ApiResource]` declaration (REST + GraphQL ops, OpenAPI block); DTO = typed input contract (validation surface + GraphQL input type); Provider = read path (auth + query + envelope); Processor = write path (validation + repo call + events + permission gate); lang = all user-facing strings (single-file convention under `Resources/lang/en/app.php`).
   - **Logic used in the API + parameters considered** — which Bagisto admin controller / request / repository is mirrored, which validation rules, which events fire, which permission key gates it.
   - **What we built new vs. what we reused from the Admin package** — call out shared scaffolding (`AbstractAdminCollectionProvider`, `AbstractAdminItemProvider`, `AdminAuthHelper`, `AdminCartGuard`, `AdminOrderActionGuard`, `AdminCollectionEnvelopeNormalizer`, etc.) vs. the per-resource pieces.
   - Goal: the user should understand the wiring well enough to modify it directly later without asking.
3. **User runs the endpoint** from the api-docs page and reports back: what's missing, what's broken in the API, what's wrong in the docs.
4. **For every defect, fix both sides:**
   - **API fix** lives in `packages/Webkul/BagistoApi/src/Admin/...` (or shared infra).
   - **Docs fix** lives in `/home/users/yashvir.singh/www/html/Bagisto/bagisto-api-docs/` (VitePress site — sidebar in `.vitepress/config.mts`, pages under `src/api/admin-api/{graphql-api,rest-api}/`). Both branches must be updated in lockstep.
   - Cover REST **and** GraphQL for every menu — don't leave one transport behind.
5. **Update the coverage table in this file** if a row's status changes (e.g. an endpoint was "covered" but a defect proves a gap — adjust the row's notes).
5a. **Record every shipped fix in the package `CHANGELOG.md`** under `## [Unreleased]` (Keep-a-Changelog: Added / Fixed / Changed, grouped by menu, consumer-facing — no internal class/method names), and tick its row in the bug-fix timeline `docs/superpowers/specs/2026-06-08-admin-api-bugfix-qa-timeline.md`. The timeline is the menu-by-menu QA tracker; the changelog is the user-facing record. Do both for every menu/fix going forward.
6. **Do not auto-commit** (project memory rule) — user commits themselves.
7. **Do not run regression sweeps unprompted** (project memory rule) — only run the targeted test for the endpoint just fixed if there's a real risk of regression.

### Rule that still applies during QA

**GraphQL endpoints must not be broken by REST fixes** (the original project rule). Since both transports go through the same Processor/Provider classes, every API change in this phase still needs the affected resource's GraphQL test run before being considered done.

This is the most important rule in this project:

> Every REST change touches the same Processor/Provider classes that serve GraphQL.
> After every REST change — no matter how small — run the GraphQL test suite for the affected resource and confirm it is green before considering the task done.

### Guiding principle — GraphQL response shape: Shopify-style nested objects/connections (2026-06-17)

**DECISION (supersedes the earlier "plain-array / JSON-scalar" convention for admin nested data).** Admin GraphQL responses must follow the idiomatic, Shopify-aligned shape the shop API already uses — nested data is **field-selectable typed objects**, never opaque JSON scalars:

- **to-one** nested data (customer, billing/shipping address, order, payment) → a **typed object** the client sub-selects: `customer { name email }`.
- **to-many** nested collections (items, invoices, shipments, refunds, comments, translations, options, variants, images, …) → a **Relay connection** the client sub-selects: `items { edges { node { sku qtyOrdered } } }`.

**It IS possible — the earlier failure was a POPO parent, not an API Platform limit.** Old notes (now revised) said the Eloquent rebuild was "impossible / do not retry / proven exhaustively and reverted." That conclusion was wrong about the cause: the 2026-06-04 attempt produced all-null connection nodes **only because the parent resource was a plain DTO (POPO)** — API Platform re-resolves a connection's nodes *as resources*, which a POPO cannot satisfy. The shop API proves the correct recipe works (`Models/CustomerOrder.php` → `Models/CustomerOrderItem.php`): an **Eloquent-backed parent** exposing real **`HasMany`/`HasOne`/`BelongsTo` relations** to **Eloquent `#[ApiResource]` sub-resources** resolves `items { edges { node } }` correctly.

**This also fixes the camelCase-null bug for free.** An Eloquent model's snake_case columns route through the central `Serializer/OutputOnlySnakeToCamelNameConverter` — the same central file that makes the shop API resolve multi-word fields. So an Eloquent-backed admin resource gets correct camelCase resolution with **no per-DTO snake migration**. (A central converter cannot fix camelCase *POPO* props — API Platform reads them by reflection on the snake name with no class context — so the only fix for a POPO is snake_case props; an Eloquent source avoids the problem entirely.)

**RULES for any new/migrated admin GraphQL resource with nested data:**
0. **The Eloquent resource MUST declare a REST `Get` (item) operation** (`new Get(uriTemplate: '…/{id}', provider: …)`). This is non-obvious and critical: with `operations: []` (a bare GraphQL `Query`), API Platform never wires up the model's Eloquent property metadata / state, so **EVERY field and relation resolves `null`** (multi-word scalars AND connections). Adding the `Get` op fixes both — verified end-to-end on the admin endpoint 2026-06-17 (`grandTotal`/`baseGrandTotal`/`qtyOrdered` + `items { edges { node { sku formattedPrice } } }` all resolved). The shop `CustomerOrder` works precisely because it has a full op set; bare-`Query` Eloquent resources resolve nothing on EITHER endpoint. The sub-resources (connection nodes, e.g. `AdminOrderItem`) keep `operations: []` — only the queried parent needs the `Get`.
1. Make the resource **Eloquent-backed** (extend the core model, set `$table`). Expose nested **collections as relations** to Eloquent `#[ApiResource]` sub-resources (→ `edges { node }`); expose **to-one** nested data as typed objects.
2. Keep the **REST shape stable via an `output:` DTO** — REST has no connections, so it stays a flat inline array (no breaking REST change).
3. Give each nested sub-resource a real identifier/route so **`node { id }` resolves** (or expose `_id`); never leave a node `id` un-resolvable (the IRI-on-routeless-resource 500).
4. **Money/derived fields, Shopify-style:** prefer raw `value` + `currencyCode` and let the client format. If you keep a server-side `formatted*` string, **eager-load the parent** so the per-row currency lookup is not an N+1 (order items format in the order's snapshot currency, not the current channel currency).
5. **Do NOT objectify** scalar lists (`int[]`/`string[]` — permissions, locale_ids, indices) or genuinely dynamic JSON (statistics, conditions, configuration values, reporting date_range) — those stay plain lists / JSON scalars.
6. Per the standard QA cycle: after converting a resource, **update the api-docs example (query + input variables + response)** and **update + re-run that resource's REST + GraphQL tests**; run the full shop + admin suites at the end of the migration.

**Migration scope (tracked):** 19 admin resources embed fixed-schema nested objects/collections. **OrderDetail was attempted as the pilot (2026-06-18) and reverted/kept Iterable — see PILOT LEARNINGS below.** Remaining order: validate the recipe on a **simple single-collection resource first** (e.g. CustomerReview-images / Settings-Theme-translations) before the rest; **OrderDetail and AdminCatalogProduct are deferred** (the two "monster" resources — many scalars + nested to-one objects + dual REST-DTO — are the worst case and stay Iterable until the pattern is proven clean on simpler resources).

**ORDERDETAIL MIGRATED to connections (2026-06-18 — the monster, done with the AdminReview recipe).** `adminOrderDetail` now serves the nested data as field-selectable connections / typed objects over GraphQL, flat arrays over REST — verified live + regression-locked (`Admin/GraphQL/OrderDetailTest` 5 tests / 27 assertions + `Admin/RestApi/OrderDetailTest` 5/39 + Cancel REST 6/11 + GraphQL 3/3, all green). Structure: `Admin/Models/OrderDetail.php` is now a **bare Eloquent model** (`extends Model`, `$table='orders'`, NOT extending core Order — its morphTo/proxy relations break serialization, same reason as shop `CustomerOrder`) with `HasMany` items/invoices/shipments/refunds/comments + `addresses`, `BelongsTo` customer, and appended **scalar** accessors (`status_label`/`payment_title`/`total_due`/`base_total_due`/`formatted_*` — payment via a `DB::table('order_payment')` lookup, total_due computed `grand_total - grand_total_invoiced`). Sub-resources (all Eloquent `#[ApiResource]` `operations: []` + `normalizationContext.attributes`): `AdminOrderDetailItem` (`order_items`; `children()` HasMany self-ref on `parent_id` + bare `child`/`downloadable_links` accessors), `AdminOrderDetailInvoice`/`Shipment`/`Refund`/`Comment` (slim), `AdminOrderDetailAddress` (`addresses`), `AdminOrderDetailCustomer` (`customers`; `group()` BelongsTo) + `AdminOrderDetailCustomerGroup`. `OrderDetailProvider` branches: GraphQL → `OrderDetail::with(GRAPHQL_RELATIONS)` raw model (connections); REST → maps core Order to `OrderDetailRestDto`. `AdminCancelOrder` output switched to `OrderDetailRestDto`. **THREE hard-won gotchas (cost the most time — heed them for Product + the rest):** (a) **to-one HasOne-with-`where` 500s over GraphQL** (constrained to-one can't resolve) — billing/shipping are exposed as ONE `addresses` HasMany **connection** (both, distinguished by `addressType`), not two HasOne; a plain `BelongsTo` to-one (customer) resolves fine, and `group` is a `BelongsTo` typed object. (b) **array-returning accessors on an Eloquent resource 500 over GraphQL** (string accessors are fine) — never expose nested data as an appended `?array` accessor; use a relation→sub-resource. (c) **THE REST `output:`-DTO TRAP — a DTO prop is only serialised if its name matches an attribute/relation on the PARENT resource.** With `output: OrderDetailRestDto`, API Platform filtered the DTO's props against `AdminOrderDetail`'s attributes: `customer`/`items`/`invoices`/etc. survived (they're relations on OrderDetail) but **`billingAddress`/`shippingAddress` were silently dropped** (no matching relation) — regardless of snake/camel name, `#[ApiProperty]`, `#[SerializedName]`, or the value being populated (confirmed via log: the provider set it, the serializer dropped it). Fix: name the DTO prop **`addresses`** to match OrderDetail's `addresses()` relation → renders. **So when using an `output:` DTO on an Eloquent resource, every DTO field name MUST correspond to a relation/attribute on that Eloquent resource.** Net REST shape change: the old `billingAddress`/`shippingAddress` objects are now a single `addresses` array (consistent with the GraphQL connection + shop `CustomerOrder`). Product is next, same recipe.

**ADMININVOICE MIGRATED to connections (2026-06-18 — same recipe, list+detail+create on one type).** `adminInvoice` now serves `items` as a connection and the billing/shipping addresses via `order { addresses { edges { node } } }`; REST returns `items` (array) + `order: { id, addresses: [...] }`. Verified live + green (`Admin/GraphQL/InvoiceTest` 8 + `Admin/RestApi/InvoiceTest` 16 + SalesInvoicesList REST 12 / GraphQL 2 = 38). Structure: `Admin/Models/AdminInvoice.php` is now a **bare Eloquent model** (`$table='invoices'`) with `HasMany items()` → `AdminInvoiceItem` (`invoice_items`; `productType` accessor from `order_item->type`, `baseImageUrl` from product, base-currency `formatted*` accessors), `BelongsTo order()` → **the existing `OrderDetail` resource** (so `order { addresses { edges } }` reuses its proven addresses connection), ~16 `formatted_*` scalar accessors + order-context accessors (`order_status`/`customer_name`/etc.) that read a memoised `orderRow()` lookup but **prefer pre-set attributes** (the listing `forceFill`s them so no N+1), and a `transaction_id` accessor (falls back to `order_transactions` by `invoice_id`). `AdminInvoiceRestDto` is the REST `output:` (snake props + `AcceptsCamelCaseWrites`; address block named **`order`** to match the relation — the name-match trap). `BuildsAdminInvoice` now has `loadInvoiceForGraphQL()` (Eloquent) + `buildInvoiceRestDto()`; provider + create-processor branch on `graphql_operation_name`; the listing (`AdminInvoiceCollectionProvider`) `forceFill`s an Eloquent `AdminInvoice` for GraphQL (order/items detail-only) and a DTO for REST. **TWO NEW gotchas on top of OrderDetail's three:** (d) **a custom-local-key `HasMany` does NOT resolve over GraphQL connections** — API Platform's connection resolver uses the parent's PRIMARY key as the local key, ignoring a custom one. The invoice's addresses live on the order (`addresses.order_id = invoice.order_id`), so a `hasMany(Address,'order_id','order_id')` returned EMPTY edges over GraphQL (queried `order_id = invoice.id`). Fix: reach them through a standard-key relation — `BelongsTo order` (invoice.order_id → orders.id) whose own `addresses()` HasMany uses standard keys. (e) **a `BelongsTo order` relation HIDES the `order_id` FK** over BOTH transports (GraphQL "Cannot query field orderId, did you mean order?"; REST `output:`-DTO drops the `order_id` prop because the FK attribute is hidden on the resource) — so **`orderId` is gone; the order id is now `order { _id }` (GraphQL) / `order.id` (REST)**. Also: **GraphQL create/update mutations do NOT resolve nested connections** in their payload — `createAdminInvoice` returns the invoice's scalars + `order { _id }` but NOT `items { edges }`; clients re-query via `adminInvoice` for the items connection. The detail QUERY resolves everything.

**ADMINCATALOGPRODUCT (Product detail) MIGRATED to connections (2026-06-19 — the monster, the biggest one). All 18+ nested blocks are field-selectable connections/objects over GraphQL, flat arrays over REST.** `adminCatalogProduct` now serves every nested block as a Relay connection over GraphQL (`images`/`videos`/`categories`/`inventories`/`customerGroupPrices`/`translations`/`superAttributes`(→`options`)/`variants`(→`attributeValues`)/`bundleOptions`(→`products`)/`linkedProducts`/`downloadableLinks`(→`translations`)/`downloadableSamples`(→`translations`)/`customizableOptions`(→`translations`+`prices`)/`attributeValues`/`channels`/`relatedProducts`/`upSells`/`crossSells`) and the historical **flat** shape over REST. Verified live + green (`Admin/RestApi/CatalogProductTest` 108 + `Admin/GraphQL/CatalogProductTest` 23 + `RestApi/CatalogProductDetailTest` 32 + `GraphQL/CatalogProductDetailTest` 5/1-skip + the 3 sub-resource CRUD suites untouched). Structure: `Admin/Models/AdminCatalogProduct.php` is now a **bare Eloquent model** (`$table='products'`) — scalars come from `product_flat`/EAV via accessors (a `scalar($key, $compute)` helper: returns the forceFilled listing value if `array_key_exists`, else computes live; memoised `flat()` row), ~24 relations to **24 new `AdminProductDetail*` Eloquent sub-resources** (`operations: []` + `normalizationContext.attributes` allow-list each, in `Admin/Models/`). `Admin/Dto/AdminCatalogProductRestDto.php` is the REST `output:` (snake props + `AcceptsCamelCaseWrites`, all the flat arrays). The 4 providers/processors branch on `graphql_operation_name` (detail/create/update: Eloquent w/ `loadEloquentForGraphQL()` for GraphQL, RestDto via the existing `mapToDto` for REST; the listing `forceFill`s an Eloquent row + `setRelation(..., collect())` for the detail-only connections so no N+1). **THREE NEW gotchas on top of the OrderDetail/Invoice five (heed these for any future connection migration):**
- **(f) MULTI-WORD relation method names MUST be snake_case.** API Platform reads a relation by its **snake_case** property name (the central converter), so a camelCase multi-word relation method (`customerGroupPrices()`) is invisible — Eloquent can't find `customer_group_prices()` → returns null → GraphQL 500 **"Unexpected non-iterable value for to-many relation."** Single-word relations (`images`/`categories`) work because snake==camel. **Fix: name every multi-word relation method snake_case** (`customer_group_prices()`, `super_attributes()`, `attribute_values()`, `related_products()`, …); the converter still emits the camelCase GraphQL field (`customerGroupPrices`). Update the `with([...])` eager-load paths + listing `setRelation()` keys to the snake names too. (OrderDetail/Invoice never hit this — all their relations are single-word.)
- **(k) THE NAME-MATCH TRAP RUNS ON THE INPUT SIDE TOO — an `input:` DTO property whose name is not an attribute/relation on the Eloquent resource is SILENTLY DROPPED (2026-08-26).** Found on `PUT /api/admin/appearance/sections/{id}` (Eloquent parent `AdminAppearanceSection`, `input: AdminAppearanceSectionUpdateInput`): `name` / `status` / `sortOrder` arrived on the DTO because they are columns, while **`options` and `locale` were always `null`** — they are not columns (options lives on the translation, locale is a scope) — even though `request()->all()` held them. Consequence: the update endpoint could never write section content and never target a locale, and because the repository rewrites the locale's options from the payload it also **erased** them. The failure is silent on both transports and looks like "the DTO isn't binding". **Rule: on a write op whose resource is Eloquent, every input DTO property that is not a real column must be read back off `request()->input()` / `$context['args']['input']` in the processor** (the same fallback chain the POPO-resource processors already use) — a POPO-resource input DTO is unaffected (the sibling `.../draft` op with the identical `?array $options` prop binds fine). Diagnose by logging the DTO props next to `request()->all()`: the ones that vanish are exactly the non-column names.
- **(g) THE `output:`-DTO NAME-MATCH TRAP needs a parent accessor for every COMPUTED REST-only block.** A RestDto prop renders only if its name matches an attribute/relation on the Eloquent parent (the documented trap). Relation-backed props (`channels`/`images`/`super_attributes`) match their relations and render; but **computed blocks with no backing relation are silently dropped** — here `attributes` (the EAV edit-form field set, REST-only — GraphQL uses the `attributeValues` connection instead), `bookingProduct`, and `warnings`. Fix: add a **null-returning `?string` accessor** on the parent for each (`getAttributesAttribute`/`getBookingProductAttribute`/`getWarningsAttribute`) purely to satisfy the name-match — the RestDto's real array value passes through for REST; over GraphQL the field is a harmless null string. NOTE `getAttributesAttribute` is safe (Eloquent reads its internal bag via `$this->attributes` direct-access, never `__get`, so the accessor only fires for external reads). The old update field **`_warnings` was renamed `warnings`** — a leading-underscore key can't map to an Eloquent accessor (`getWarningsAttribute` ⇒ key `warnings`). **After adding any parent accessor, `bagisto-api-platform:clear-cache`** — the API Platform property metadata is Redis-cached and `php artisan test` does NOT flush it (a stale cache silently drops the new field → confusing test-only failure).
- **(h) a nullable to-one (HasOne that's null) → GraphQL "Cannot return null for non-nullable field".** `booking` is a HasOne that's null for ~every non-booking product; API Platform types a to-one relation field non-null and `required: false` does NOT flip it. **`booking` is therefore REST-only** (the `bookingProduct` block via `populateBooking`); it's dropped from the GraphQL surface (its sub-resources deleted). Booking products are the rarest type — not worth a synthetic empty-object workaround. (A BelongsTo to-one with a nullable FK — e.g. OrderDetail `customer` — IS nullable and resolves fine; only HasOne-null is the problem.)
The `attributeValues` connection is the GraphQL counterpart of the REST `attributes` block (one node per stored EAV value with `code`/`adminName`/`type`/`groupCode`/`value`; the value is coerced to a String scalar; empty family fields appear only in REST `attributes`). Per the standard quirk, GraphQL **create/update mutations** return scalars but **not** the nested connections (re-query `adminCatalogProduct`); listing rows leave connections empty (detail-only). **This is now the reference for the remaining migrations — the multi-word-relation-snake-case fix (f) is the single most important new lesson.**

**ADMINORDER LISTING — `items` migrated to a connection (2026-06-19).** `adminOrders` now serves the per-row `items` preview as a field-selectable connection (`items { edges { node { _id sku name qtyOrdered productImage } } }`); REST `GET /api/admin/orders` keeps `items` as a flat array. Verified live + green (`Admin/RestApi/OrderTest` 16 + `Admin/GraphQL/OrderTest` 5 incl. a new `test_items_resolve_as_a_connection`). Structure: `Admin/Models/AdminOrder.php` converted POPO → **bare Eloquent** (`$table='orders'`) with a `HasMany items()` → new `AdminOrderItemPreview` (`order_items`, slim `id/sku/name/qty_ordered/product_image`); `Admin/Dto/AdminOrderListDto.php` is the REST `output:` (snake props, no trait — the provider writes snake). `OrderCollectionProvider` branches on `graphql_operation_name` (`toAdminOrderEloquent` forceFills the row + `setRelation('items', …)` for GraphQL; `toAdminOrderDto` for REST). **ONE NEW gotcha (i): a connection's nodes are RE-RESOLVED from the DB, so a forceFilled NON-COLUMN value on a node is LOST** — `product_image` (computed, not an `order_items` column) came back null over GraphQL even though forceFilled (the real columns `sku`/`name`/`qty_ordered` resolved because the re-resolve re-reads them from the table). Fix: the sub-resource's `getProductImageAttribute` **computes from a real column** (`product_id` → first `product_images.path`) with the forceFilled value as a no-N+1 fast-path (`array_key_exists('product_image', $this->attributes) ? … : compute`). **Rule: any computed field on a connection-node sub-resource must be a real accessor that can recompute from the node's own columns — never rely solely on forceFill.** Also confirmed: a listing-only Eloquent resource (AdminOrder has a `GetCollection` + export `Get` but **no single-`{id}` Get**) STILL resolves scalars + the connection — the rule-0 null-out is specific to `operations: []`, not to "missing `{id}` Get". (Known cosmetic: AdminOrder's GraphQL node `id` IRI points at the export route — use `_id`; the detail lives on the separate `AdminOrderDetail` resource at `/orders/{id}`.)

**ADMIN SETTINGS nested-data migrated to connections (2026-06-22) — Tax Categories, Themes, Channels.** Three settings resources converted POPO → bare Eloquent so their nested data is field-selectable over GraphQL while REST keeps flat arrays via an `output:` DTO (the AdminReview recipe). (a) **Tax Categories** — `taxRates` → connection (`AdminSettingsTaxRateRef`), 9 GQL/29 REST green. (b) **Themes** — `translations` → connection (`AdminSettingsThemeTranslationRef`; the per-locale `options` JSON column resolves on the node), 7 GQL/34 REST green. (c) **Channels** — fully objectified (user-approved breaking change): `translations`/`locales`/`currencies`/`inventorySources` all → connections (4 new `AdminSettingsChannel*Ref` sub-resources), **replacing** the old scalar `localeIds`/`currencyIds`/`inventorySourceIds` int-array fields on BOTH transports; 9 GQL/35 REST green. **THREE hard-won learnings (heed for any future settings/admin connection migration):**
- **(j) NEW — a `belongsToMany` whose pivot table has its OWN `id` column resolves the connection node `_id` to the PIVOT row id, not the related model's id** (even though `getId()` returns the right value — API Platform's edge resolver reads the raw `id` attribute directly). Tax Categories' `tax_categories_tax_rates` pivot has an `id`; the working Product pivots (`product_categories` etc.) do NOT. **Fix:** model it as a `HasMany` over the pivot table to a sub-resource and ALIAS the related id into `id` (`->select(..., '<pivot>.<x>_id as id')`), never selecting the pivot's own `id`. Overriding `getKeyName()`/`getKey()` did NOT work — only the column alias did. (Channels' pivots have NO `id` column → straight `belongsToMany` worked, no alias needed.)
- **gotcha (b) is STRONGER than documented — array ACCESSORS 500 over GraphQL on an Eloquent resource EVEN FOR A FLAT `int[]`** (not just nested-object arrays). Proven on Channels: a `getLocaleIdsAttribute(): ?array` returning `[1,2]` → "Internal server error" over GraphQL. **Declared public `?array` props are ALSO ignored** on an Eloquent resource (the Eloquent property extractor only sees columns / relations / accessor-METHODS, not reflected public props — unlike a POPO). So a **derived scalar int[] CANNOT be exposed on an Eloquent parent over GraphQL** — only a relation→connection or a real JSON-cast COLUMN works (a real `$casts`-array column like `channel_translations.home_seo` / `theme_customization_translations.options` resolves fine as a JSON value; only computed accessors 500). This is the architectural reason a resource that needs BOTH a connection AND derived scalar id-lists (Channels) **must** objectify the id-lists into connections — a POPO can expose the scalar lists but can't have connections; an Eloquent parent can have connections but can't expose the derived scalar lists. They're mutually exclusive on one resource.
- **A JSON-map column CANNOT become a nested typed object (`homeSeo { metaTitle … }`) on an Eloquent parent** — a GraphQL ObjectType needs a backing resource/relation, and a JSON column has none (and a constrained/null HasOne onto a synthetic SEO row hits gotchas (a)/(h)). Channels exposes the SEO as **flat top-level string accessors** (`seoMetaTitle`/`seoMetaDescription`/`seoMetaKeywords`, reading the channel's own `home_seo` column then falling back to the default-locale translation's `home_seo`) PLUS the per-locale `homeSeo` JSON value on each `translations` node — the field-selectable equivalent. The migrated parent's `output:` REST DTO must name each prop to match a relation/attribute (name-match trap), and the GraphQL delete returns the in-memory Eloquent snapshot (the Bug-1 delete-snapshot fix, now via the Eloquent model not a POPO).

**PILOT SUCCEEDED (AdminReview → AdminReviewImage, 2026-06-18 — the proven admin template). DO NOT re-litigate "connections don't work on admin."** A clean simple resource was built the **single-Eloquent-parent way** (NOT the OrderDetail dual-POPO way) and works end-to-end on both transports — verified live + regression-locked (`Admin/GraphQL/ReviewConnectionTest`, 2 tests / 15 assertions). Files: `Admin/Models/AdminReview.php` (Eloquent parent, `$table='product_reviews'`, REST `Get`+`GetCollection` with `output:` DTOs + GraphQL `Query`+`QueryCollection` with NO output + a `HasMany images()` to the sub-resource), `Admin/Models/AdminReviewImage.php` (Eloquent sub-resource, `$table='product_review_attachments'`, `operations: []`/`graphQlOperations: []` + `normalizationContext.attributes` allow-list), `Admin/State/AdminReviewProvider.php` (branching: returns the raw Eloquent model when `! empty($context['graphql_operation_name'])`, else maps to the flat DTO), `Admin/Dto/AdminReview{List,Detail}Dto.php` (snake_case props → camelCase via the central converter; detail's `images` is a flat `array`). Result proven: GraphQL `adminReview(id){ productId createdAt images { edges { node { _id path mimeType } } } }` resolves **multi-word scalars AND the connection nodes together** (the exact combo the OrderDetail split nulled), while REST `GET /api/admin/reviews/{id}` returns `images` as a **flat array**. **THE RECIPE, distilled:** (1) parent is ONE Eloquent `#[ApiResource]` (extends nothing special — a bare `Model` with `$table`+`$casts` is enough), serving BOTH transports — never a POPO, never a dual REST-DTO/GraphQL-Eloquent split (that split is what nulled scalars). (2) REST shape via per-op `output:` DTO (flat array); GraphQL ops carry NO `output:` so they return the Eloquent model → connections. (3) provider branches on `graphql_operation_name`: Eloquent model for GraphQL, DTO for REST. (4) sub-resource is an Eloquent `#[ApiResource]` with empty ops + an `attributes` allow-list; exposed via a real `HasMany`/`HasOne`/`BelongsTo` on the parent. (5) the parent MUST declare a REST `Get` (wires the metadata). No `AcceptsCamelCaseWrites` needed — the Eloquent source routes snake columns through the central output converter for free. **This is the template for the OrderDetail/Product/etc. sweep; the OrderDetail revert below was a wrong-STRUCTURE problem (dual POPO), not a connection limitation.**

**PILOT LEARNINGS (OrderDetail, 2026-06-18 — attempted, reverted):** the recipe's pieces all work in isolation (Get-op wiring ✓, to-many connections resolve with real node data ✓, BelongsTo customer resolves ✓), but **composing them into OrderDetail hit a config-dependent wall** that two configs split between them:
- **Single Eloquent resource** (branching provider + `output:` DTO): GraphQL fully works (multi-word scalars + all connections resolve), but **REST drops to ~8 fields** — `output:` on an Eloquent resource serialises the camelCase POPO via the *Eloquent's* snake-derived metadata, so camelCase props name-mismatch and vanish. Fixing this needs a **snake_case DTO** (not done).
- **Two resources** (POPO owns REST `/orders/{id}`, Eloquent owns GraphQL via a side `Get` route): **REST fully works (50 fields)**, but GraphQL **multi-word scalars resolve null** (only single-word `status` + relations resolve) — the camelCase converter isn't applied in this split path. Connections + `customer` work (use `_id` on nested objects; the IRI `id` is unreliable for to-one nested resources).
- Other gotchas proven: an `*_id`-suffixed column (e.g. `increment_id`) auto-exposes over GraphQL as a non-null **entity reference** and aborts — hide it (`$hidden`) and re-expose under a non-`Id` name (`orderNumber`); sub-resource `getId()` must be `?int` (empty-instance normalization).
- **Conclusion:** for a resource with ~45 scalars + 5 connections + nested customer/addresses + a REST shape that must stay byte-identical, the dual REST-DTO ↔ GraphQL-Eloquent split is too finicky to land cheaply. **Keep OrderDetail (and Product) as Iterable JSON;** validate the connection recipe on simpler resources where one Eloquent resource can serve both transports without a separate DTO.

**CamelCase-null fix APPLIED to OrderDetail (2026-06-18 — separate from connections, kept Iterable).** `OrderDetail` was a camelCase POPO with no `AcceptsCamelCaseWrites`, so every multi-word top-level scalar (`statusLabel`/`grandTotal`/`formattedGrandTotal`/`totalDue`/… — the order-level financials + status) resolved **null** over GraphQL (the documented camelCase value-read quirk — this was the originally-reported `adminOrderDetail` nulls bug). Fixed by converting the **top-level** props to snake_case + `use AcceptsCamelCaseWrites` (provider untouched — its camelCase writes are mapped by the trait's `__set`; REST output stays camelCase via the output converter). Now resolves over both transports; regression-locked by `Admin/GraphQL/OrderDetailTest::test_order_detail_query_returns_the_order` (asserts `incrementId`/`statusLabel`/`channelName`/`grandTotal`/`formattedGrandTotal`/`totalDue` non-null). **Nested objects ALSO fixed (2026-06-18) — `customer`/`billingAddress`/`shippingAddress` converted from typed `#[ApiResource]` DTOs to plain `?array`** (provider `toCustomer()`/`toAddress()` now return associative arrays; the 3 DTO files `OrderDetailCustomer`/`OrderDetailCustomerGroup`/`OrderDetailAddress` deleted + their entries removed from `CustomIriConverter`). Why: as typed routeless nested resources, `customer { id }` **500'd over GraphQL** (IRI generation on a no-route resource) and `billingAddress { city }` returned an empty-instance object with all sub-fields null (API Platform re-resolves a nested resource as a fresh empty instance). As plain arrays they are JSON scalars — **query bare** (`customer`, `billingAddress`), and you get every field including the multi-word ones (`firstName`/`lastName`/`addressType`/`group`) which previously could not resolve. REST output is byte-identical (same camelCase keys, now from an array instead of a DTO). Regression-locked by `OrderDetailTest::test_order_detail_resolves_nested_customer_and_addresses` (bootstraps an order with addresses, asserts `customer.email`/`customer.group`/`billingAddress.city`/`shippingAddress.city` non-null + `errors` null). Arrays (`items`/`invoices`/`shipments`/`refunds`/`comments`) were already Iterable — query bare. **So the entire `adminOrderDetail` payload is now JSON-bare for every nested block + field-selectable for every top-level scalar.** **Sweep done (2026-06-18):** `AdminOrder` (listing) was already correct (snake_case props + snake_case provider writes — no trait needed, no bug); `AdminCart` had the bug (camelCase props + `AdminCartPresenter` camelCase writes) and got the same snake_case + `AcceptsCamelCaseWrites` fix — `customerId`/`isActive`/`subTotal`/`grandTotal`/`formattedGrandTotal`/`shippingMethod`/`paymentMethodTitle`/… now resolve over GraphQL (regression-locked in `Admin/GraphQL/CartTest::test_query_returns_cart`; its `items`/`billingAddress`/`shippingAddress` are `?array` → Iterable, query bare). api-docs GraphQL examples updated for both `adminOrderDetail` (`sales/orders/order-detail.md`) and `adminCart` (`sales/carts/get-cart.md`) — corrected the old invalid `items { … }` sub-selection to bare `items`, removed the stale "fields null / use REST" notes, and refreshed the responses.

### Guiding principle — no code duplication (DRY)

Every cross-cutting concern in this package has exactly one home. Before writing a new helper, search the package for an existing one that already does the job — reuse it. Examples already in place:

- **Auth** → `AdminAuthHelper::resolveAdmin()` (Sanctum, fresh resolution, no session cache). Never reinvent.
- **Cart resolution + draft-only ownership** → `AdminCartGuard::resolve()` (Wave 2). Used by every cart-keyed processor/provider.
- **Cart-state sequence checks** → `AdminCartSequenceGuard` (Wave 3). Used by shipping/payment/place-order.
- **Cart response shape** → `AdminCartPresenter` (Wave 2). All cart-mutating endpoints return identical payloads.
- **Collection envelope** → `AdminCollectionEnvelopeNormalizer` (Orders Wave). Every paginated `/api/admin/*` collection wraps as `{ data, meta }` automatically.

Rule for new work: if two processors/providers/resources have the same logic, extract a shared class. If a new feature needs the same eligibility-split pattern as Reorder, build it on top of a shared guard (e.g. `AdminOrderActionGuard` for Wave 4 actions) — do NOT inline the same `canCancel()`/`canInvoice()`/permission-check code into each processor. Lang keys: if `*.closed` and `*.fraud` repeat across actions, share them under `app.admin.order.status-closed` / `status-fraud` instead of per-action duplicates. Same for monolith-payload translation helpers and DTO-to-Eloquent mappers.

When the principle conflicts with parity (e.g. monolith inlines logic but we'd want to share), parity wins for behaviour, DRY wins for our own structure.

### Guiding principle — API parity with admin, not API as superset

The BagistoApi admin surface **mirrors what the Bagisto admin panel can do, no more.** When core admin lacks a feature (e.g. booking products in Create-Order — confirmed missing in both 2.3.8 and 2.4.1), our API matches that gap with an explicit error rather than building a half-extension. Reasons: (1) integrators expect API ↔ admin-UI consistency, (2) extending beyond core means owning surface area that diverges when core catches up, forcing a breaking change later, (3) a clear "not supported" error is more honest than a partial implementation. When core adds the feature, we remove the block and follow their shape.

### Guiding principle — 2.4.x feature build order: shop first, GraphQL first

Every net-new **2.4.x** feature (the compatibility/feature-parity work tracked in `packages/Webkul/BagistoApi/BAGISTO-2.4-COMPATIBILITY.md`) is built in this fixed transport order:

```
1. Shop  GraphQL   ← build + test first
2. Shop  REST
3. Admin REST
4. Admin GraphQL
```

Rules:
- **Shop before admin, GraphQL before REST** within each side. Build and green each stage before starting the next.
- **Shared provider/processor:** when the two transports on one side share a provider/processor (the normal case), the second transport is mostly wiring + its own tests — but still verify + test it explicitly before moving on; never assume it works because the first did (see "Why GraphQL Can Break from REST Changes").
- **Not every feature has all four surfaces.** Admin-only features (e.g. Sales reporting — no storefront analog) skip stages 1–2; storefront-only features skip 3–4. Skip the stages that have no core counterpart (parity principle above) and note the skip. Example: **Sales-by-coupon report** is admin-only, so only stages 3–4 applied, and they landed together because `adminReportingSales` shares one provider (`buildPayload`) across REST + GraphQL.
- Per-stage completion still follows the per-transport workflows below (run GraphQL tests first as the regression check, then REST) and the admin per-feature MANDATORY workflow ("CHECK BAGISTO CORE FIRST").

### Workflow for Every REST Endpoint Task

```
1. Make the REST change (Model operations, Processor logic, Provider, etc.)
2. Identify the affected resource (e.g., Wishlist → WishlistTest.php)
3. Run the GraphQL tests for that resource first
4. Run the REST tests for that resource
5. If any GraphQL test fails → fix before marking done
6. Only ship when both suites are green
```

### Test Commands to Run After a REST Change

```bash
# Step 1 — Run the GraphQL test for the changed resource (regression check)
php artisan test packages/Webkul/BagistoApi/tests/Feature/GraphQL/<ResourceName>Test.php

# Step 2 — Run the REST test for the changed resource
# All REST tests live in tests/Feature/RestApi
# Naming: <ResourceName>Test.php (no `Rest` suffix), namespace Webkul\BagistoApi\Tests\Feature\RestApi
php artisan test packages/Webkul/BagistoApi/tests/Feature/RestApi/<ResourceName>Test.php

# Step 3 — If the change touched shared logic (Processor/Provider), run the full GraphQL suite
php artisan test --testsuite="BagistoApi GraphQL Test" --parallel
```

### Why GraphQL Can Break from REST Changes

REST and GraphQL share the **same Processor and Provider**. The `process()` method routes by:
- `$data` type (DTO class vs Model class)
- `$operation` instance type (`Post`, `Delete`, `GraphQl\Mutation`, etc.)

Adding a REST branch (e.g., handling `$data instanceof YourModel && $operation instanceof Post`)
can shadow or interfere with the GraphQL branch if the conditions are not precise.
Always check both paths in the processor after a change.

### REST Endpoint Test Coverage

Update status in-place as tests are written. Never append — only update "missing" → "covered".

| Resource | REST Operations | GraphQL Test | REST Test | Status |
|----------|----------------|-------------|-----------|--------|
| Wishlist | GetCollection, Post, Delete, Toggle | `GraphQL/WishlistTest.php` | `RestApi/WishlistTest.php` | covered |
| AddProductInCart | Post | — | `RestApi/AddProductInCartTest.php` | covered |
| CancelOrder | Post | — | `RestApi/OrderActionTest.php` | covered |
| ReorderOrder | Post | — | `RestApi/OrderActionTest.php` | covered |
| MoveWishlistToCart | Post | `GraphQL/WishlistTest.php` | `RestApi/WishlistTest.php` | covered |
| DeleteAllWishlists | Post | `GraphQL/WishlistTest.php` | `RestApi/WishlistTest.php` | covered |
| DeleteAllCompareItems | Post | — | `RestApi/CompareItemTest.php` | covered |
| CompareItem | GetCollection, Get, Post, Delete | — | `RestApi/CompareItemTest.php` | covered |
| CustomerLogin | Post | — | `RestApi/CustomerTest.php` | covered |
| SocialLogin | Post | `GraphQL/SocialLoginTest.php` | `RestApi/SocialLoginTest.php` | covered (2026-08-21 — `POST /api/shop/customers/social-login` + `createSocialLogin`; storefront-key, no bearer. Token-in-JSON flow: the client (native SDK / Google Identity Services / provider JS) holds the token and posts `{ provider, idToken\|accessToken, deviceToken? }`; server verifies + resolves/creates the customer + returns a Sanctum bearer — the same `token` as password login. Providers: **google** (idToken via Google `tokeninfo` + audience = web `google_client_id`; or accessToken via Socialite), **facebook** / **linkedin** (accessToken via `Socialite::driver()->userFromToken()`). Reuses core `CustomerSocialAccountRepository`; sets `channel_id` (2.4.2 fix); blocks only `!status` (suspended allowed, like the login fix). Returns `{ id, token, apiToken, firstName, lastName, email, phone, isNewCustomer, success, message, code }`; errors return `success:false` + a stable `code`. Files: `Models/SocialLogin.php`, `Dto/SocialLoginInput.php`, `Services/SocialLoginTokenVerifier.php` + `SocialLoginService.php`, `State/SocialLoginProcessor.php`, `Exception/SocialLoginException.php`. **Twitter + GitHub deliberately NOT supported** — see the note below.) |
| CustomerLogout | Post | — | `RestApi/CustomerTest.php` | covered |
| CustomerProfile | GetCollection | — | `RestApi/CustomerTest.php` | covered |
| CustomerProfileUpdate | Put | — | `RestApi/CustomerTest.php` | covered |
| CustomerProfileDelete | Post | — | `RestApi/CustomerTest.php` | covered (REST has no custom processor — delete not functional via REST) |
| Customer (registration) | Post | — | `RestApi/CustomerTest.php` | covered |
| VerifyToken | Post | `GraphQL/CustomerAuthTest.php` | `RestApi/CustomerTest.php` | covered |
| ForgotPassword | Post | `GraphQL/CustomerAuthTest.php` | `RestApi/CustomerTest.php` | covered |
| CartToken | Post | `GraphQL/CustomerCartTest.php`, `GraphQL/GuestCartTest.php` | `RestApi/CartTest.php`, `RestApi/AddProductInCartTest.php` | covered (guest-cart bootstrap; GET ops removed — redundant with POST /api/shop/cart) |
| ReadCart (Get cart details) | Post | `GraphQL/CustomerCartTest.php`, `GraphQL/GuestCartTest.php` | `RestApi/CartTest.php` | covered |
| ApplyCoupon | Post | — | — | missing |
| RemoveCoupon | Post | — | — | missing |
| RemoveCartItem | Post | — | — | missing |
| RemoveCartItems | Post | — | — | missing |
| UpdateCartItem | Post | — | — | missing |
| MergeCart | Post | — | — | missing |
| CustomerAddress | GetCollection, Get, Post, Put, Delete | — | — | missing |
| CheckoutAddress | Post | `GraphQL/CustomerCheckoutTest.php`, `GraphQL/GuestCheckoutTest.php` | `RestApi/CheckoutAddressTest.php` | covered |
| CheckoutShippingMethod | GetCollection, Post | `GraphQL/CustomerCheckoutTest.php`, `GraphQL/GuestCheckoutTest.php` | `RestApi/CheckoutShippingPaymentTest.php` | covered (GetCollection returns rates via ShippingRatesProvider; Post saves selected method) |
| ShippingRates | — (GraphQL only) | `GraphQL/CustomerCheckoutTest.php`, `GraphQL/GuestCheckoutTest.php` | — | REST consolidated under `CheckoutShippingMethod` GetCollection |
| CheckoutPaymentMethod | Post | `GraphQL/CustomerCheckoutTest.php`, `GraphQL/GuestCheckoutTest.php` | `RestApi/CheckoutShippingPaymentTest.php` | covered |
| PaymentMethods | GetCollection | `GraphQL/CustomerCheckoutTest.php`, `GraphQL/GuestCheckoutTest.php` | `RestApi/CheckoutShippingPaymentTest.php` | covered |
| EstimateShipping | Get, GetCollection | — | — | missing |
| Category | Get, GetCollection | `GraphQL/CategoryTest.php` | `RestApi/CategoryTest.php` | covered (REST `GetCollection` uses `CategoryRestProvider`; **always filters `status=1`** — admin-disabled categories never returned. Optional `?parent_id=N` / `?parentId=N` for direct children. Flat list; for hierarchical tree use `/category-trees`) |
| CategoryTree | GetCollection | `GraphQL/CategoryTest.php` | `RestApi/CategoryTreeTest.php` | covered |
| Attribute | Get, GetCollection | — | — | missing |
| Channel | Get, GetCollection | `GraphQL/ChannelTest.php` | `RestApi/ChannelTest.php` | covered |
| ContactUs | Post | — | `RestApi/ContactUsTest.php` | covered |
| Country | Get, GetCollection | `GraphQL/CountryTest.php` | `RestApi/CountryTest.php` | covered |
| CountryState | Get, GetCollection (nested + root) | `GraphQL/CountryTest.php` | `RestApi/CountryTest.php` | covered |
| Newsletter | Post | — | `RestApi/NewsletterTest.php` | covered |
| Product | Get, GetCollection | `GraphQL/ProductQueryTest.php`, `GraphQL/ProductSearchFilterTest.php`, `GraphQL/ProductTest.php` | `RestApi/ProductTest.php` | covered (REST list delegates to GraphQL provider via `ProductRestProvider extends ProductGraphQLProvider`; REST `Get` returns embedded `ProductDetailDto` via `ProductDetailProvider` — categories/channels/bookingProducts/bundleOptions/variants/superAttributes/etc. all inlined, no follow-up calls needed. GraphQL `product(urlKey:)` lookup is **cross-locale** — `SingleProductBagistoApiResolver::resolveByUrlKey` matches `text_value` for `attribute_id=3` with no locale filter, so a slug in any locale resolves under any request locale; regression-locked by `ProductQueryTest::test_product_resolves_by_url_key_stored_only_in_another_locale`. url_key lookup is GraphQL-only; REST `Get` is by numeric id. Booking products expose `startingPrice`/`formattedStartingPrice`/`startingRegularPrice`/`formattedStartingRegularPrice` (base price + cheapest event ticket or min rental unit rate, mirroring core `Booking::getPriceHtml`; null for default/appointment/table) on both transports — GraphQL `bookingProducts` node + REST `bookingProducts[]`/`/booking-products/{id}`; shared `Service/BookingStartingPriceCalculator`) |
| BookingSlot | GetCollection | `GraphQL/BookingSlotQueryTest.php` | `RestApi/BookingSlotTest.php` | covered (REST and GraphQL share `BookingSlotProvider`; provider falls back to `request()->query()` when `$context['args']` is empty) |
| ProductReview | GetCollection, Get, Post, Patch, Delete | — | `RestApi/ProductReviewTest.php` | covered |
| CustomerReview | GetCollection, Get | — | `RestApi/ProductReviewTest.php` | covered |
| ProductBundleOptionProduct | Get, GetCollection | — | — | missing |
| ProductDownloadableLink | Get, GetCollection | — | — | missing |
| ProductDownloadableSample | Get, GetCollection | — | — | missing |
| CustomerOrder | Get, GetCollection | `GraphQL/CustomerOrderTest.php` | `RestApi/CustomerOrderTest.php` | covered (REST `GetCollection` returns 23-field `CustomerOrderListDto` matching GraphQL `customerOrders` shape — no dangling IRIs; REST `Get` returns `CustomerOrderDetailDto` via `CustomerOrderProvider::toDetailDto()` with `items` / `addresses` / `payment` / `shipments` (and shipment `items`) all embedded as objects so no follow-up calls are needed; sub-resources `CustomerOrderItem` / `CustomerOrderAddress` / `CustomerOrderPayment` keep `operations: []` — IRIs are not emitted from either endpoint) |
| GdprRequest — list own | GetCollection | `GraphQL/GdprRequestTest.php` | `RestApi/GdprRequestTest.php` | covered (2026-06-25 — customer-scoped; `gdprRequests` cursor / `GET /api/shop/gdpr-requests`; sort id\|created_at, order; `{data,meta}`; gated by admin config `general.gdpr.settings.enabled`) |
| GdprRequest — view own | Get | `GraphQL/GdprRequestTest.php` | `RestApi/GdprRequestTest.php` | covered (2026-06-25 — `gdprRequest(id:)` / `GET /api/shop/gdpr-requests/{id}`; 404 if not yours; config-gated) |
| GdprRequest — raise | Post | `GraphQL/GdprRequestTest.php` | `RestApi/GdprRequestTest.php` | covered (2026-06-25 — `createGdprRequest` / `POST /api/shop/gdpr-requests` `{ type: delete\|update, message }` → 201 pending; config-gated; fires core create events) |
| GdprRequest — revoke | Post | `GraphQL/GdprRequestTest.php` | `RestApi/GdprRequestTest.php` | covered (2026-06-25 — `revokeGdprRequest` / `POST /api/shop/gdpr-requests/{id}/revoke` → 200; only while pending/processing else 422; config-gated) |
| GdprRequest — delete | Delete | `GraphQL/GdprRequestTest.php` | `RestApi/GdprRequestTest.php` | covered (2026-06-25 — `deleteGdprRequest` snapshot / `DELETE /api/shop/gdpr-requests/{id}` → 204; config-gated) |

### Social login — why only Google / Facebook / LinkedIn (Twitter + GitHub excluded) — 2026-08-21

The social-login endpoint is a **token-in-JSON** flow: the client obtains a token from the provider itself (native SDK, Google Identity Services, or provider JS — **no browser redirect**) and posts it; the server verifies it and returns a bearer. This is client-agnostic (works for app AND web-via-GIS with one endpoint) but it only works for providers that have a **client-side token flow**:

- **Google** — native SDK / GIS returns an **idToken**; verified via Google's `tokeninfo` + an audience check against the web `google_client_id` (mobile SDKs request the token with the web id as `serverClientId`, so one client id covers app + web — "option A", no android/ios config fields needed). An access token is also accepted (Socialite `userFromToken`).
- **Facebook / LinkedIn** — the SDK returns an **accessToken**; verified via `Socialite::driver($driver)->userFromToken()` (the same drivers the storefront social login configures, so credentials come from the admin `customer.settings.social_login.*` config). LinkedIn's API provider name is `linkedin-openid`; the API accepts `linkedin` and maps it.

**Twitter and GitHub are NOT supported by this endpoint** because they have **no client-side token the app can hand us**:
- **GitHub** OAuth is redirect/code-exchange only — the app never holds a token; getting one needs the `client_secret` server-side. `userFromToken` would work *if* a token existed, but the client can't obtain one.
- **Twitter** is OAuth 1.0a (Socialite's default driver) — `userFromToken` needs a token **and** a token secret, and there is no simple client-side token. OAuth2/PKCE is possible but not what the storefront driver ships.

So the split is about **token availability on the client**, not about the server verify (which is uniform). Provider verify is `Services/SocialLoginTokenVerifier.php`; the `PROVIDERS` map there is the allow-list.

**If Twitter/GitHub are ever needed**, the clean path is to extend the SAME `POST /api/shop/customers/social-login` with an additional optional input — a `code` (authorization code the app got from a web-view OAuth) — and a code-exchange branch in the verifier that swaps `code` → access token using the admin-configured `client_secret`, then continues through `userFromToken` + the existing `SocialLoginService::resolve`. That keeps one endpoint with per-method optional inputs (`idToken` / `accessToken` / `code`) filled according to the login method, rather than separate endpoints. Not built (no current need); documented so the extension point is known. **Consumer docs must mention only the supported providers — never list Twitter/GitHub as unsupported.**

---

## Project Overview

**BagistoApi** is a Laravel package (`packages/Webkul/BagistoApi/`) that exposes Bagisto's e-commerce data via REST and GraphQL APIs built on [API Platform for Laravel](https://api-platform.com/docs/laravel/).

- **Framework**: Laravel 11 + API Platform
- **GraphQL**: `webonyx/graphql-php` via `api-platform/graphql`
- **Auth**: Laravel Sanctum — always use `Auth::guard('sanctum')->user()`
- **Package root**: `packages/Webkul/BagistoApi/src/`
- **Tests**: `packages/Webkul/BagistoApi/tests/`
- **Lang strings**: `packages/Webkul/BagistoApi/src/Resources/lang/en/app.php` — **single-file convention.** All lang strings live in `Resources/lang/en/app.php`; namespace by top-level key (e.g. `app.admin.*`, `app.integration.*`). Do NOT create additional lang files (e.g. `admin.php`, `integration.php`) — mirror the Bagisto core `Admin` package convention.
- **Service provider**: `packages/Webkul/BagistoApi/src/Providers/BagistoApiServiceProvider.php`

---

## Coding rules

### Code style — MANDATORY (user preference)

- **Minimal comments.** Do NOT write verbose multi-line docblocks explaining what the code does. Keep only what earns its place: a one-line class/method purpose when non-obvious, and machine-readable `@param`/`@return` type hints where they add real type info. Drop narrative prose, step-by-step "mirrors core X" essays, and per-property comments. Keep functional OpenAPI `summary`/`description` strings (those are the API contract, not code comments). Prefer self-explanatory names over comments.
- **No inline fully-qualified class names.** Never reference a class inline as `\Vendor\Package\Foo::bar()` in the method body. Add a `use Vendor\Package\Foo;` import at the top of the file and reference the short name `Foo::bar()`. Applies to every class reference (static calls, `new`, type hints, `instanceof`) — the only exception is a one-off reference where importing would collide with an existing short name.

### DRY — no code duplication

**Don't repeat scaffolding across providers, processors, or DTOs.** If two files share more than a few lines of mechanical logic (auth, paging, sort, error handling, lang lookups, etc.), extract a base class, trait, or helper.

- New **admin collection providers** must extend `src/Admin/State/Concerns/AbstractAdminCollectionProvider`. Implement only `getSortable()`, `buildQuery($args)`, `applyFilters($query, $args)`, `applySort($query, $args)`, `mapRow($row)`.
- New **admin item providers** must extend `src/Admin/State/Concerns/AbstractAdminItemProvider`. Implement only `getNotFoundLangKey()`, `findEntity($id)`, `mapToDto($entity)`.
- Shop API equivalent: `ApiPlatform\State\Pagination\Pagination` injected via constructor (see `src/State/CategoryRestProvider.php`).
- Tree, mass-action, file-upload, and other bespoke shapes that genuinely don't fit a base class implement `ProviderInterface`/`ProcessorInterface` directly — but flag the reason in a class docblock.

**Before writing a new provider/processor**, grep for similar logic. If it exists, reuse it. If it almost exists, extract a base class first then add yours on top. The cursor-pagination bug (had to be fixed in two duplicated providers separately) is the canonical "this is why we DRY" lesson on this project.

### Model-relations-driven file structure

When adding API support for a Bagisto core model, **read the model's `with`, relation methods, `appends`, `casts`, and `translatedAttributes` first** to know what relations / computed fields exist. Then design the API files to surface them:

- One **resource (`AdminX.php`)** per logical user-facing entity. Listing + detail can share the resource (listing rows leave detail-only fields null).
- One **collection provider** + one **item provider**, both extending the abstract bases above.
- One **DTO per nested object shape** the response inlines — but populated as plain associative arrays in the provider (not as DTO instances) to avoid API Platform's IRI-serialization trap for nested resources. DTOs serve as typed schema reference for GraphQL.
- If a relation is heavy or has its own admin sub-page, surface it as a separate endpoint, NOT inlined.

Don't add API files speculatively for relations the admin UI doesn't expose. Parity with admin, not superset.

---

## Extending the API from ANOTHER package (external modules)

The rule of thumb: **a Bagisto module never builds its own API stack.** BagistoApi is the host — it owns auth, rate limiting, pagination, the error shapes, the Swagger/GraphiQL playgrounds and the schema. A module (product Q&A, loyalty, marketplace, …) registers a handful of classes and its endpoints appear inside the same surface, with the same credentials and conventions.

This section is the in-repo copy of the consumer-facing **For Developers** docs (`bagisto-api-docs/src/api/for-developers/`), which use a generic `Vendor\ProductQuestion` module as the worked example — keep both in sync, and **never name a paid Webkul module in the public docs**. A real implementation of this pattern exists locally at `/home/users/yashvir.singh/www/html/Bagisto/deliveryboyApiDemo/packages/Webkul/DeliveryBoy/src/Api/`; read it for reference, do not cite it.

**Use this section when the endpoint lives in a different package. Use "Adding a New REST + GraphQL Endpoint" (below) when it lives in BagistoApi itself.**

### Install + file layout in the extending package

**Composer install works; there is no manual step.** The module is an ordinary Bagisto/Laravel package — `composer require webkul/product-question` + auto-discovery (`extra.laravel.providers`) registers its service provider, which does the API wiring below. Then `php artisan bagisto-api-platform:optimize`. Listing it in `bootstrap/providers.php` is optional (Bagisto does that for first-party packages only). In-repo dev works too: the root `composer.json` has a `packages/*/*` path repository, so `composer require webkul/product-question:@dev` symlinks it.

- Depend on the host (`"require": {"bagisto/bagisto-api": "^2.4"}`) only if the module is useless without the API; otherwise `suggest` it and rely on the `interface_exists` guard so the module still installs on a store without it.
- **Never add `api-platform/*` to the module's `require`** — the host pins them; a second constraint conflicts or yields a mixed version set that breaks the metadata build.
- **Never remove `api-platform/laravel` from the root `extra.laravel.dont-discover`** — BagistoApi registers API Platform itself; discovery on top registers it twice.

Bagisto packages live at `packages/<Vendor>/<Package>/src` — the project's own package dir, **not** Composer's `vendor/`. The public docs use `Webkul\ProductQuestion` as the worked example so it matches the layout a dev already has in front of them.

```
packages/Webkul/ProductQuestion/
├── composer.json
└── src/
    ├── Api/
    │   ├── Shop/          # Resources/ State/ Resolver/ Dto/  → routePrefix '/api/shop'
    │   ├── Admin/         # Resources/ State/ Resolver/ Dto/  → routePrefix '/api/admin'
    │   └── Support/       # shared guards/lookups (e.g. QuestionContext)
    ├── Config/acl.php     # permission keys the admin endpoints check
    └── Providers/ProductQuestionServiceProvider.php
```

Mirrors the host's own split (`src/Models/` shop, `src/Admin/Models/` admin). **Register EVERY resource dir** — `Api/Shop/Resources` AND `Api/Admin/Resources`; only listed dirs are scanned.

- **The `\Admin\` namespace segment is load-bearing, not cosmetic.** `GraphQl/ScopedSchemaBuilder` splits the shop and admin schemas with `str_contains($resourceClass, '\\Admin\\')` — nothing else (not `routePrefix`, not the class name). An admin resource in a namespace without that segment still routes correctly over REST, but its GraphQL field is compiled into the **shop** schema: missing from `/api/admin/graphql` and reachable by anyone with a storefront key.
- **Admin permission keys must exist in an ACL config** or no role can be granted them and every `permission_type=custom` token 403s (the 2.4 ACL-rename bug, same failure mode). Ship `src/Config/acl.php` and merge it in `boot()`: `config(['acl' => array_merge((array) config('acl', []), require __DIR__.'/../Config/acl.php')]);`
- Admin listing/detail: extend `AbstractAdminCollectionProvider` / `AbstractAdminItemProvider` + `use ChecksAdminPermission` — never hand-roll auth/paging/sort/envelope.

### Registration — the only wiring required

In the module's `ServiceProvider::register()`:

```php
protected function registerApiState(): void
{
    // Module must still install on a store WITHOUT BagistoApi.
    if (! interface_exists(ProviderInterface::class)) {
        return;
    }

    config([
        'api-platform.resources' => array_merge(
            config('api-platform.resources', []),
            [
                dirname(__DIR__).'/Api/Shop/Resources',
                dirname(__DIR__).'/Api/Admin/Resources',
            ],
        ),
    ]);

    foreach ([YourThingProvider::class] as $p) {
        $this->app->tag($p, ProviderInterface::class);
    }

    foreach ([YourThingProcessor::class] as $p) {
        $this->app->tag($p, ProcessorInterface::class);
    }

    foreach ([YourThingResolver::class] as $r) {
        $this->app->tag($r, QueryItemResolverInterface::class);
    }
}
```

Imports: `ApiPlatform\State\ProviderInterface`, `ApiPlatform\State\ProcessorInterface`, `ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface`.

- **Merge into `api-platform.resources` at runtime** — never publish/patch the host's 3 `api-platform.php` configs. Survives a host upgrade; integrator never edits a vendor file.
- **`interface_exists` guard is mandatory** — without it the module fatals at boot on a store without BagistoApi and takes the whole store down.
- **An untagged provider/processor/resolver is silently ignored** — API Platform falls back to its default Eloquent state handling, so the endpoint 404s or (worse) returns plausible data your class never produced. First thing to check when "my provider isn't running".
- After adding/changing a resource: `php artisan bagisto-api-platform:optimize` (while iterating use `optimize:clear`, then `optimize` once at the end).
- Verify: `php artisan tinker --execute="print_r(config('api-platform.resources'));"` and `php artisan route:list --path=api/shop | grep <your-prefix>`.

### Resource + provider (one class serves both transports)

```php
#[ApiResource(
    routePrefix: '/api/shop',                     // or '/api/admin'
    shortName: 'ProductQuestion',                 // GraphQL type + field base — ALWAYS prefix with the module
    operations: [
        new Get(
            uriTemplate: '/product-questions/{id}',        // ALWAYS declare; never rely on the generated path
            provider: ProductQuestionProvider::class,
            openapi: new Operation(tags: ['Product Questions'], summary: '…', responses: [...]),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: ProductQuestionResolver::class,
            args: ['orderId' => ['type' => 'Int!']],
        ),
    ],
)]
class ProductQuestion
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?int $order_id = null;        // snake_case prop → client sees orderId
}
```

```php
public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ProductQuestion
{
    // REST puts it in $uriVariables, GraphQL in $context['args'] — read both, one code path.
    return $this->resolve($uriVariables['order_id'] ?? $context['args']['orderId'] ?? null);
}
```

- `#[Groups(['query'])]` on every readable prop, `['mutation']` on writable input props — an ungrouped field does not render.
- A GraphQL **item** query with custom args needs a `QueryItemResolverInterface` (2-line adapter onto the provider). A **collection** query does not — point `QueryCollection` at the provider and declare `args:`.
- Custom GraphQL args MUST be declared in `args:` / `extraArgs:` or they're "Unknown argument" at query time (looks like the filter is being ignored).
- `paginationEnabled: false` when the provider pages itself; otherwise return `ApiPlatform\Laravel\Eloquent\Paginator`.

### Two resource shapes

1. **Plain class + provider** — the provider builds and returns it. Use for computed/assembled payloads, action results, anything not 1:1 with a table.
2. **Subclass an existing model + decorate it** — what the package does for core entities (`Webkul\BagistoApi\Models\CompareItem extends \Webkul\Customer\Models\CompareItem`): inherits table/casts/relations, adds `#[ApiResource]`, an `#[ApiProperty(identifier: true)] getId()`, and the relations to expose. A module can do the same with a core model or its own.

Rules for shape 2: **never edit the model you extend** (upgrade-lost, and it changes storefront/admin behaviour too); **relations must point at API resource classes** (`belongsTo(Product::class)` = the API's Product resource — a relation whose related class isn't a resource is silently not exposed); and **redeclaring a core relation requires overriding the core method name too** (multi-word camel/snake names collapse to one property and core's wins — see `Models/Category::filterableAttributes()` delegating to `filterable_attributes()`).

### Writes

`Post` + `Mutation(name: 'create')` share one input DTO and one processor. `name: 'create'` on `ProductQuestion` ⇒ field `createProductQuestion` (host composes `{name}{ShortName}` — never write the field name yourself). Processor: type-check `$data` → validate → **authorise → then write** → return the declared output DTO (never a bare `stdClass`; an undeclared object serialises to `{}`).

### Auth + ownership (the security-critical part)

`routePrefix` decides what the host has already enforced before your code runs:

| Prefix | Host enforces | Read the caller |
|---|---|---|
| `/api/shop` | `X-STOREFRONT-KEY` | `Auth::guard('sanctum')->user()` (null for guest — a storefront key is NOT a person) |
| `/api/admin` | admin Integration Bearer | `AdminAuthHelper::resolveAdmin()` |

**The host cannot know that this record belongs to this customer — only the module does.** Every endpoint keyed by a client-supplied id must prove ownership before returning or writing, or it's an IDOR. Extract it into one `Api/Support/*Context` class and reuse. Return **404, not 403**, for a record that exists but isn't the caller's.

Throw the host's exceptions from `Webkul\BagistoApi\Exception` (`AuthenticationException` 401 / `AuthorizationException` 403 / `InvalidInputException` 400 **or any status passed as its 2nd ctor arg — this is how you return 422** / `ResourceNotFoundException` 404 / `OperationFailedException` 500) — they map to the right REST status AND into GraphQL `errors[]` automatically. **`ValidationException` is mapped to 400, NOT 422** (`exception_to_status` in the api-platform configs; it implements only `ClientAware`, no `HttpExceptionInterface`) — prefer `new InvalidInputException($msg, 422)`, which is what the 3 real 422 sites in the package use. Messages from the module's own lang file; never a hard-coded string or a raw lang key.

### Naming rules (collision + null traps)

- **Props snake_case** (`$product_id`, `$created_at`). Mechanism: `OutputOnlySnakeToCamelNameConverter` normalises snake→camel on output and **denormalises camel→snake when reading a requested field**, so GraphQL resolves `productId` by reading the property `product_id`. A camelCase prop is therefore never found → **null over GraphQL**, while REST (which walks real props outward) still works. Single-word props are unaffected, so a half-converted class looks fine. If the provider/processor assigns camelCase, add `use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites` (its `__set` maps the write; without it PHP creates a dynamic prop and the declared one stays null on BOTH transports).
- **Multi-word relation methods snake_case too** (`order_items()`) — same converter: Eloquent is asked for `order_items`, so `orderItems()` is never found → to-many throws "Unexpected non-iterable value for to-many relation", to-one silently nulls.
- **Prefix `shortName` with the module.** GraphQL types share ONE namespace across every installed module; a collision breaks the entire schema, not just your field.
- **Namespace REST paths** (`/product-questions/...`) so they don't collide with core as the API grows.
- **Never set a custom `name:` on `QueryCollection`** — the auto-derived plural is correct; a custom one throws "Operation collection_query not found" and takes down **every** GraphQL operation on that endpoint.
- Provider return type must be `object|array|null`, **not `mixed`** (mixed fatals the whole metadata build). A `Delete` op still needs a `provider:`.
- **One broken resource breaks the shared schema** — GraphQL/OpenAPI compile as a unit, so a bad resource in your module 500s unrelated core endpoints. When something that worked breaks right after you add a resource, suspect the new resource.

### Tests

One test per transport per operation — the shared provider means a green REST test says nothing about GraphQL (untagged resolver, undeclared arg, camelCase null all live in the transport layer). **Assert `errors` is null on GraphQL**: a failure still returns HTTP 200, so `assertOk()` alone passes on a query that resolved nothing. Cover happy path + missing credential + **another customer's id** (the one that catches a missing ownership check). Select multi-word fields explicitly in at least one test.

### Shipping

`php artisan bagisto-api-platform:export-schema` writes REST OpenAPI JSON + GraphQL SDL for both surfaces including the module's endpoints — commit them so consumers get a Postman collection / codegen input without a running store. The endpoints also auto-appear in Swagger UI (`/api/shop`, `/api/admin`) and GraphiQL (`/api/graphiql`, `/api/admin/graphiql`); if they're missing there, the resource isn't registered.

---

## Adding a New REST + GraphQL Endpoint — Step by Step

Every resource follows the same 5-file pattern. Use Wishlist as the reference implementation.

### Step 1 — Model (`src/Models/YourModel.php`)

Extend the Bagisto core model and decorate with `#[ApiResource]`.

```php
#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get,
        new GetCollection(provider: YourModelProvider::class),
        new Post(
            processor: YourModelProcessor::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                summary: '...',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['fieldName'],
                                'properties' => [
                                    'fieldName' => ['type' => 'integer', 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Delete(processor: YourModelProcessor::class),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(provider: YourModelProvider::class, paginationType: 'cursor'),
        new Mutation(name: 'create', input: CreateYourModelInput::class, output: YourModel::class, processor: YourModelProcessor::class),
        new Mutation(name: 'delete', input: DeleteYourModelInput::class, output: YourModel::class, processor: YourModelProcessor::class),
    ],
)]
class YourModel extends \Webkul\SomeCore\Models\YourModel
{
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int { return $this->id; }

    // Expose relationships with #[ApiProperty]
    #[ApiProperty(writable: false)]
    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'product_id'); }
}
```

**Rules:**
- Always filter by `customer_id` AND `channel_id` for customer-scoped resources
- Use `core()->getCurrentChannel()->id` for channel, never hardcode
- Use `#[ApiProperty(identifier: true, writable: false)]` on `getId()`
- Mark relationship methods with `#[ApiProperty(writable: false)]`

### Step 2 — DTOs (`src/Dto/`)

One DTO per mutation input. Keep them thin — just typed public properties with `#[ApiProperty]` and `#[Groups]`.

```php
class CreateYourModelInput
{
    #[ApiProperty(description: 'The product ID')]
    #[Groups(['mutation'])]
    public ?int $product_id = null;

    public function getProduct_id(): ?int { return $this->product_id; }
    public function setProduct_id(?int $v): void { $this->product_id = $v; }
}
```

For delete DTOs, use an `$id` field (IRI format: `/api/shop/your-models/123`):
```php
class DeleteYourModelInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $id = null;
    // getter/setter
}
```

### Step 3 — Provider (`src/State/YourModelProvider.php`)

Used for `GetCollection`. Always: auth check → filter by customer + channel → eager load → paginate.

```php
class YourModelProvider implements ProviderInterface
{
    public function __construct(private readonly Pagination $pagination) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();
        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        // Handle cursor pagination from GraphQL args
        $args   = $context['args'] ?? [];
        $first  = isset($args['first']) ? (int) $args['first'] : null;
        $perPage = $first ?? 30;
        $offset  = 0;
        if ($after = $args['after'] ?? null) {
            $decoded = base64_decode($after, true);
            $offset  = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        $query = YourModel::where('customer_id', $customer->id)
            ->where('channel_id', core()->getCurrentChannel()->id)
            ->with(['product', 'customer'])
            ->orderBy('id', 'asc');

        $total   = (clone $query)->count();
        $items   = $query->offset($offset)->limit($perPage)->get();
        $page    = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => request()->url()])
        );
    }
}
```

### Step 4 — Processor (`src/State/YourModelProcessor.php`)

Route by DTO type and operation name. REST `Post` receives `Wishlist` (model) not a DTO — handle both.

```php
class YourModelProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $persistProcessor) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // GraphQL create
        if ($data instanceof CreateYourModelInput) {
            return $this->handleCreate($data);
        }

        // REST POST — model received, build DTO from request body
        if ($data instanceof YourModel && $operation instanceof \ApiPlatform\Metadata\Post) {
            $input = new CreateYourModelInput;
            $input->product_id = request()->input('product_id') ?? request()->input('productId');
            return $this->handleCreate($input);
        }

        // GraphQL / REST delete
        if ($data instanceof DeleteYourModelInput) {
            $this->hydrateDeleteInput($data, $context);
            return $this->handleDelete($data);
        }

        if ($operation instanceof \ApiPlatform\Metadata\Delete) {
            return $this->handleRestDelete($uriVariables);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function handleCreate(CreateYourModelInput $input): YourModel
    {
        if (empty($input->product_id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.your-model.product-id-required'));
        }
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }
        // ... create and return model
    }

    private function hydrateDeleteInput(DeleteYourModelInput $input, array $context): void
    {
        if (! empty($input->id)) return;
        $id = $context['args']['input']['id'] ?? $context['args']['id'] ?? null;
        if ($id !== null) $input->id = (string) $id;
    }
}
```

**GraphQL toggle/remove pattern:** When a GraphQL mutation removes an item and should return `null` with a message in `errors[]`, throw `OperationFailedException` instead of returning the deleted model:
```php
if ($operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation) {
    throw new OperationFailedException(__('bagistoapi::app.graphql.your-model.removed'));
}
// REST: return $deletedItem with message set
$deletedItem->setMessage(...);
return $deletedItem;
```

### Step 5 — Register in ServiceProvider

Two registrations required in `BagistoApiServiceProvider::register()`:

```php
// 1. Tag as ProcessorInterface (required for DI)
$this->app->tag(YourModelProcessor::class, ProcessorInterface::class);

// 2. Singleton only needed if constructor requires non-auto-resolvable deps
// If constructor only has ProcessorInterface and standard services, no singleton needed
```

Providers (`ProviderInterface`) **must be tagged** in `BagistoApiServiceProvider::register()`. Without the tag, API Platform falls back to the default Eloquent provider and your custom logic is silently bypassed (the endpoint returns 404 if the resource isn't auto-resolvable, or wrong data if it is). Add a line next to the other provider tags:

```php
$this->app->tag(YourCustomProvider::class, ProviderInterface::class);
```

This applies to every class used as `provider:` on an `ApiResource` operation — including REST-only providers, sub-resource providers, and detail-DTO providers.

### Step 6 — Language Strings

Add to `src/Resources/lang/en/app.php` under `graphql.your-model`:

```php
'your-model' => [
    'id-required'       => 'ID is required',
    'not-found'         => 'Item not found',
    'product-id-required' => 'Product ID is required',
    'product-not-found' => 'Product not found',
    'added'             => 'Item Successfully Added',
    'removed'           => 'Item Successfully Removed',
    'already-exists'    => 'Item already exists',
],
```

---

## Exception Types — When to Use Which

| Exception | GraphQL | REST | Use when |
|-----------|---------|------|----------|
| `AuthenticationException` | `errors[]` | **401** | Request is unauthenticated (missing/invalid token) |
| `AuthorizationException` | `errors[]` | **403** | Authenticated but accessing another user's resource (also used for missing-auth cases historically) |
| `InvalidInputException` | `errors[]` | **400** | Missing/invalid input, business rule violations |
| `ResourceNotFoundException` | `errors[]` | **404** | Entity not found in DB |
| `OperationFailedException` | `errors[]` + `null` data | **500** | GraphQL remove that must return `null` with message in `errors[]` |
| `ValidationException` | `errors[]` | **400** (mapped in `exception_to_status`, despite the name — use `InvalidInputException($msg, 422)` for a real 422) | Form-level validation failures |

All implement `\GraphQL\Error\ClientAware` and (except `OperationFailedException`) `HttpExceptionInterface` + `ProblemExceptionInterface`, so they return their declared HTTP status in REST.

```php
// REST "requires auth" tests
expect($response->getStatusCode())->toBeIn([401, 403]);

// REST "not found" / "cross-user" tests
expect($response->getStatusCode())->toBeIn([403, 404]);
```

---

## REST Response Conventions

- **GET collection**: HTTP 200, JSON array
- **GET single**: HTTP 200, JSON object
- **POST create**: HTTP 201
- **DELETE**: HTTP 204 (no content) for standard delete; HTTP 200 if returning data
- **Toggle remove (REST)**: HTTP 200 with item + message field
- **Toggle remove (GraphQL)**: `errors[]` with message, `data.toggleX: null` — achieved by throwing `OperationFailedException`
- **Null fields are always included**: REST responses include null fields (e.g., `"logo": null`). Every `#[ApiResource]` on an Eloquent model **must** set `normalizationContext: ['skip_null_values' => false]` — without it, API Platform silently drops all null-valued fields from the response.

### Pagination — `per_page` is the standard

Every paginated `GetCollection` endpoint **must** accept client-controlled page size via `?per_page=N` and `?page=N`. Defaults are set globally in `config/api-platform.php` and `packages/Webkul/BagistoApi/config/api-platform.php`:

```php
'defaults' => [
    'pagination_client_items_per_page'  => true,   // clients can override per_page
    'pagination_items_per_page'         => 10,     // default page size
    'pagination_maximum_items_per_page' => 50,     // hard cap
],
'pagination' => [
    'page_parameter_name'           => 'page',
    'items_per_page_parameter_name' => 'per_page', // NOT 'itemsPerPage' — match storefront convention
],
```

Rules for any new paginated endpoint:
- Use `?per_page=N` (snake_case), not `?itemsPerPage=N`. The global config maps the API Platform `itemsPerPage` parameter to `per_page`.
- Default page size is **10** unless the resource has a justified reason to differ (`/products` uses 30 via its custom `ProductRestProvider`, set explicitly).
- Cap is **50** per page globally.
- Resources that use API Platform's default pagination (no custom provider) get all of this for free — no changes to the resource needed.
- Resources with a custom REST provider (e.g. `/products`, `/attributes` if/when added) must read `request()->query('per_page')` and apply the same default and cap.
- Document the parameter in the operation's `openapi.parameters` so it shows up in Swagger UI alongside `page`.

### Pagination response headers

Every paginated REST collection response carries these headers:

```
X-Total-Count: 137      // total items across all pages
X-Page: 2               // current page (1-based)
X-Per-Page: 10          // items on this page
X-Total-Pages: 14       // total page count
```

CORS-exposed automatically — JS clients can read them with `response.headers.get('X-Total-Count')`. Wired up package-side via `Webkul\BagistoApi\Serializer\PaginationHeaderNormalizer` (captures the `Paginator` during normalization) and `Webkul\BagistoApi\Http\Middleware\PaginationHeaders` (sets the headers on the response). No-ops for non-paginated responses.

The normalizer is inserted into `api_platform_normalizer_list` (NOT the Laravel `serializer.normalizer` tag — API Platform Laravel ignores that tag and builds its own SplPriorityQueue). The middleware is registered as a kernel-level global via `$kernel->pushMiddleware()` — required because API Platform reads `routes.middleware` config before our package's service provider can patch it.

### Cache store — `file` by default, no Redis requirement (2026-07-21)

The API's metadata cache, GraphQL schema cache, and rate-limit counters were hardcoded to `redis`, which forced every client installing the package to run Redis. They are now **env-driven with a `file` default** in all three `api-platform.php` configs:

```php
'cache' => env('CACHE_STORE', 'file'),
'schema_cache' => ['enabled' => true, 'store' => env('CACHE_STORE', 'file')],
'rate_limit'   => ['cache_driver' => env('RATE_LIMIT_CACHE', env('CACHE_STORE', 'file'))],
```

They reuse the core **`CACHE_STORE`** env (always present in a Bagisto install) rather than introducing a package-specific variable — one knob, nothing extra for a client to set, and `file` only as the fallback when it is absent.

- **Read `env('CACHE_STORE')`, NOT `config('cache.default')`, inside these config files.** Laravel loads config files **alphabetically**, so `api-platform.php` is loaded *before* `cache.php` — `config('cache.default')` would resolve to `null` there.
- Because the API now shares the app cache store, a plain `php artisan cache:clear` also drops the API metadata → the next request pays the cold rebuild. Run `bagisto-api-platform:optimize` after any app-wide cache clear.

- **Never use `array`** — the metadata store MUST be persistent, or the multi-second cold metadata build runs on *every* request (and under php-fpm's 30s limit it truncates → see the `Iterable` gotcha above).
- **`file` ≈ `redis` for this workload** (read-heavy, small, OS page-cached). The real latency levers are `route:cache` + `config:cache` + OPcache, not the cache driver. **`database` is the slowest option** (a DB round-trip per metadata read, ×hundreds per request) — offer it, don't default to it.
- **Single server → `file` is correct.** Rate-limit races on `file` are harmless (throttling is approximate; a race lets one extra request through).
- **Multi-server → set `CACHE_STORE=redis` (or `database`).** The reason is *shared state*, not atomicity: `file` cache is node-local, so a 60/min limit becomes 60/min **per node**, and each node keeps its own metadata copy.
- `bagisto-api-platform:clear-cache` / `warm-cache` read `config('api-platform.cache')` dynamically, so they follow whatever store is configured — no code change needed when switching. **After changing the store, run `php artisan bagisto-api-platform:optimize`** (the old store's entries are simply orphaned).

### Cache flush after adding normalizationContext

`bagisto-api-platform:clear-cache` only flushes Redis. When you add or change `normalizationContext` on a model, also clear the compiled config cache:

```bash
php artisan config:clear && php artisan bagisto-api-platform:clear-cache
```

### Response-time fix — route+config cache are now REQUIRED (2026-06-08)

**Root cause of the 350ms → 2.4s regression:** API Platform's Laravel route file (`vendor/api-platform/laravel/routes/api.php`) re-runs on **every request** when Laravel's route cache is off (the dev default). It walks the 3 resource directories on the filesystem, reflects every `#[ApiResource]` class, does a Redis GET of the metadata per resource, and registers ~600 routes — **per request**. With ~30 resources that was cheap; after the ~150 admin resources (Models + DTOs) it grew ~5× → multi-second on slower FS/Redis. It's global (every shop/admin REST/GraphQL request goes through route registration). `APP_DEBUG` was already `false` — not the cause.

**Fix (live on this copy):** Laravel `route:cache` + `config:cache` (so the api-platform route file isn't re-executed per request), plus a new `php artisan bagisto-api-platform:warm-cache` command (registered in `BagistoApiServiceProvider`) that pre-builds the Redis metadata cache so the first request after a deploy/clear doesn't pay the 4–15s cold rebuild. Steady-state is back to ~0.28s.

**DEV WORKFLOW — this changes how we add endpoints.** With the route cache ON, a **newly-added `#[ApiResource]` endpoint/operation will NOT appear until the route cache is rebuilt.** So the cache cycle when adding/changing admin API resources during the QA pass is:

```bash
# 1. After editing a resource/op — clear so the new route is picked up (route cache now OFF, responses slow):
php artisan optimize:clear && php artisan bagisto-api-platform:clear-cache
#    …live-test the new endpoint here (it now resolves)…

# 2. When done with the batch / before the user tests — rebuild all three caches for speed (route:cache build ~16s, one-time):
php artisan config:cache && php artisan route:cache && php artisan bagisto-api-platform:warm-cache
```

`optimize:clear` wipes route+config cache (so new endpoints appear but responses slow again); step 2 restores the fast path. **`php artisan bagisto-api-platform:optimize` (added 2026-06-08) runs the whole cycle in one command** — `optimize:clear` → `bagisto-api-platform:clear-cache` → `config:cache` → `route:cache` → `bagisto-api-platform:warm-cache`. **This is the canonical post-deploy / post-endpoint-change / post-test command** (running `php artisan test` wipes the route+config cache, so run this after a test session). **Rule: never leave the working copy with route cache cleared — run `bagisto-api-platform:optimize` so responses stay fast.** On php-fpm/Octane also reload the workers after deploy so they pick up new code + a fresh schema.

### API response-time playbook — what makes it fast (2026-06-17)

The whole speed story, distilled. Steady-state target ≤500ms; achieved ~0.26–0.30s on fpm+OPcache (server + Docker at parity).

**Deploy recipe — the two things that matter operationally:**
1. `APP_DEBUG=false` in `.env` (once per environment) — debug mode adds Symfony/API-Platform error-collector overhead to *every* request.
2. `php artisan bagisto-api-platform:optimize` after every deploy/endpoint change — caches config + events + routes, warms the API metadata + GraphQL query-scope map, verifies the route cache landed (aborts loudly if it didn't), and warns if `APP_DEBUG` is still on.

**The levers, by measured impact (Docker fpm went 1.28s → 0.10s):**
- **`route:cache` (biggest, ~0.8s):** without it API Platform re-registers ~600 routes on EVERY request. This is the #1 killer.
- **`APP_DEBUG=false` (~0.85s on top):** debug collectors on every request.
- **Scoped GraphQL schema + per-item IRI fast-path** (already in the package — see the two sections below): each request builds only the queried slice of the schema; node IRIs are string ops. These trim the *variable* cost once boot is cheap.
- **OPcache 256MB / `max_accelerated_files=20000` / `validate_timestamps=0`** (php.ini, needs sudo): keeps bytecode cached. Secondary but real on fpm.

**Never benchmark on `artisan serve`.** It's the CLI SAPI with `opcache.enable_cli=0`, so it recompiles all of Bagisto on every request (~1.4s floor, no matter what's cached). Use Docker (fpm + OPcache) or the real server for representative numbers — that's why the package ships the Docker setup.

**Remote-vs-localhost gap is the TLS handshake, not the app.** A remote HTTPS call over a fresh connection (Postman/curl per-request) pays ~100–120ms TLS handshake; localhost HTTP doesn't. Real clients (browsers/SDKs/Next.js) reuse the connection (keep-alive) so they pay it once, then ~0. Proven: server-local curl ≈ Docker ≈ ~0.26s; the gap was purely TLS. Don't chase that 100ms.

**TEST TRAP — never run tests with cached config.** `optimize` runs `config:cache`, which bakes `app.env=local`; under tests `app()->environment('testing')` then reads that cached value (not phpunit's `APP_ENV=testing`), the testing guards mis-fire, and the scoped GraphQL schema wrongly activates → ~all admin GraphQL **query** tests fail ("Cannot query field …"). **Run `php artisan optimize:clear` before `php artisan test`.** `optimize` is a deploy command, not a pre-test one. (`php artisan test` itself also wipes the route/config cache, so re-run `bagisto-api-platform:optimize` afterward to restore the fast path.)

**TEST TRAP 2 — the API metadata cache must be persistent in tests too (2026-08-27).** `phpunit.xml` sets `CACHE_STORE=array`, and the three `api-platform.php` configs resolve their store as `env('API_PLATFORM_CACHE', env('CACHE_STORE', 'file'))` — so the metadata pool was `array`, which dies with the container. Every test process therefore paid the full ~30s rebuild over all ~261 `#[ApiResource]` classes before its first request. Measured on `Admin/RestApi/MarketingSitemapTest` (36 tests, `--parallel --processes=8`): **125s → 27s**. Fixed by `<env name="API_PLATFORM_CACHE" value="file"/>` in `phpunit.xml`; `CACHE_STORE` stays `array` so the application cache is still isolated per test. (CI was already fast — `pest_tests.yml` writes `API_PLATFORM_CACHE=file` into `.env`.) **Consequence: the metadata now survives between runs, so `php artisan bagisto-api-platform:clear-cache` after adding or changing a resource is required before testing it** — the same rule that already applies to the running app; a stale pool silently tests the previous shape.

**TEST TRAP 3 — a `core_config` row written directly is invisible to the endpoint (2026-08-27).** `CoreConfigRepository` is one of the six repositories `config/repository.php` caches (`repository.cache.repositories`), and Prettus only cleans that cache when the write goes **through the repository**. A test that seeds config with `DB::table('core_config')->insert()` or `CoreConfig::create()` therefore leaves whatever was read earlier in the process cached, and the endpoint under test reads the stale value — the row is in the table, `getConfigData()` returns the old value, and **no query is issued at all**, which is what makes it look like a memoisation bug in `SystemConfig`. It is order-dependent, so the same file passes alone and fails in a suite, and flips between serial and `--parallel` (the GDPR "disabled" tests failed in parallel while the happy paths failed serially). **Fix: the repository cache is turned off for tests** — `BagistoApiTestCase::setUp()` sets `repository.cache.enabled=false` and clears `repository.cache.repositories` (the per-class entry wins over the global flag, so both are needed). `forgetCoreConfigCache()` remains for intent and flushes the repository's tag when the store is taggable, but **never flushes the store** — it flushes the `CoreConfigRepository` tag on a taggable store, else the whole store. Applied to the GDPR / EU-withdrawal / social-login / sitemap-generate / reorder / place-order helpers. Production is unaffected: the admin config screen saves through `CoreConfigRepository::create`, which fires the clean-cache listener. Diagnose by logging the row next to `core()->getConfigData()` inside the guard — the row present with a null value and an empty query log is exactly this.

### Per-item IRI generation — the listing-serialization killer (fixed 2026-06-08)

**Every admin collection endpoint was paying ~58ms PER ITEM** of serialization — a fixed cost regardless of field count (a 10-field currency row and a 45-field product row both ~58ms). At `per_page=50` that's ~3s+ (Products listing was 3.4s; the query itself was only 10ms). Root cause: API Platform calls `getIriFromResource()` once per item to build the node `id`/`@id` IRI, and the **decorated** converter's URL generation over the ~600-route collection is ~58ms each. Proven by short-circuiting the converter (Products 3.4s → 0.63s).

**Fix:** a fast path in `src/Routing/CustomIriConverter.php::getIriFromResource()`. For any `Webkul\BagistoApi\Admin\Models\*` resource it (1) resolves the single-`{id}` `Get` op's full template once per class (cached in `$adminIriTemplateCache` = `routePrefix . uriTemplate`, e.g. `/api/admin/catalog/products/{id}`), then (2) `str_replace('{id}', $resource->id, $template)` — a string op, microseconds. Resources with no single-`{id}` Get (listing/action-only, e.g. `AdminOrder`) get a `null` IRI immediately (correct — they have no item route). This made EVERY admin listing fast: Products default(10) 3.4s→**0.37s**, orders default(10)→**0.29s**, both <500ms; at per_page=50 Products 1.05s / orders 0.6s / categories 0.28s (remaining cost is per-field serialization, far lower on a production runtime — see the Octane/php-fpm note). The IRI format is byte-identical to the canonical one, so REST `id` (numeric value, unaffected), GraphQL `id` (IRI), and `getResourceFromIri` round-trips all still work — verified by the full Catalog/Order Pest suites.

**Also (Products listing query):** `AdminCatalogProductCollectionProvider::buildQuery()` was rewritten from 1:many LEFT JOINs (inventories × images × categories) + `distinct`/`groupBy`/aggregates — a cartesian explosion across all products before limiting — to **correlated subqueries** (quantity / images_count / base_image / category) evaluated only for the ~50 returned rows, plus a lean `countTotal` override (COUNT(DISTINCT product_id) with only the family join). Query: ~10ms. **Pattern for any heavy admin listing: avoid 1:many joins + groupBy; use correlated subqueries for the per-row aggregates, override `countTotal` to count without them.**

---

## Authentication Pattern

```php
$user = Auth::guard('sanctum')->user();
if (! $user) {
    throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
}
```

Always check auth inside the Processor/Provider, not in middleware, for resource-level operations.

---

## Admin Integration Tokens (custom guard, separate table)

Admin-side API tokens are **NOT** Laravel Sanctum and **NOT** Laravel Passport. They live in their own table with their own custom guard, fully contained in the BagistoApi package.

### Why a separate stack (not Sanctum / not Passport)

| Concern | Resolution |
|---|---|
| Customer storefront API already uses Sanctum's shared `personal_access_tokens` table | Admin tokens get their own physical table — `admin_personal_access_tokens` — so customer/admin tokens never collide, can have different retention/backup policies, and the customer table won't balloon into the admin token list |
| Passport is full OAuth (5+ tables, RSA keys, /oauth/token endpoint) | Overkill for first-party admin API integrations. Avoided. |
| Token format | Opaque `<id>|<random40>` — same shape Sanctum uses, but the validation lives in our own `AdminApiGuard` |

### Architecture

```
packages/Webkul/BagistoApi/src/
├── Admin/
│   ├── Auth/AdminApiGuard.php          ← custom guard implementing Illuminate\Contracts\Auth\Guard
│   ├── Models/AdminPersonalAccessToken.php
│   ├── Services/AdminTokenService.php   ← create draft / generate / regenerate / revoke
│   ├── DataGrids/IntegrationDataGrid.php
│   ├── Http/
│   │   ├── Controllers/IntegrationController.php
│   │   └── Requests/{Store,Update}Request.php
│   ├── Config/
│   │   ├── acl.php                       ← `settings.integration` ACL nodes
│   │   ├── menu.php                      ← Integration sidebar entry
│   │   └── auth/guards.php               ← `admin-api` guard config
│   ├── Routes/admin.php                  ← /admin/settings/integration/* routes
│   └── Resources/views/integration/      ← index / create / edit / _form blades
└── Database/Migrations/2026_05_13_120000_create_admin_personal_access_tokens_table.php
```

### Guard registration

The `admin-api` guard is registered from `BagistoApiServiceProvider`:
- `register()` calls `registerAdminApiGuardConfig()` which uses `mergeConfigFrom('Admin/Config/auth/guards.php', 'auth.guards')` — same convention as DeliveryBoy package
- `boot()` calls `bootAdminIntegration()` which `Auth::extend('admin-api', fn() => new AdminApiGuard(...))`
- The `admins` user provider already exists in `config/auth.php` (used by the session `admin` guard) — `admin-api` reuses it

**Do not** add the guard to `config/auth.php` directly. Everything is package-contained so the package can be published cleanly.

### Token lifecycle states

`admin_personal_access_tokens.status` enum:

| Status | Meaning | `token` column |
|---|---|---|
| `draft` | Row exists (admin filled in name/permissions on Create page) but Generate has not been clicked | NULL |
| `active` | Generate clicked. Token is usable. | SHA-256 hash of plaintext |
| `revoked` | Revoke clicked. Cryptographically dead. Kept for audit. | NULL |
| `regenerated` | Superseded by a newer row (linked via `regenerated_to_id`). Cryptographically dead. Kept for audit. | NULL |

**Setting `token = NULL` is sufficient to invalidate** — the guard hashes the incoming plaintext and compares to the column. NULL can't match any hash, so the row stops working immediately. No extra middleware needed.

### One active token per admin

Enforced in `IntegrationStoreRequest::rules()` and `AdminTokenService::adminsWithoutActiveToken()`. The Create page's Assign User dropdown filters out admins who already have a `draft` or `active` row.

To replace an admin's token: Revoke (or Regenerate) the existing one first, then create a new draft for the same admin.

### Permission resolution (`permission_type` column)

| Value | Runtime abilities |
|---|---|
| `all` | `['*']` — capped by admin's current role at runtime |
| `custom` | Frozen `abilities` JSON column — `array_intersect` with admin's current role |
| `same_as_web` | Live read of `admin.role.permissions` — follows role changes automatically |

The `same_as_web` mode does **not** snapshot. It reads the role through the existing `admins.role_id → roles.id` FK chain on every request. If the admin's role changes (role reassignment OR role's permissions edited), the token follows.

Effective abilities are always capped by `admin.role.permissions` — a token can never do more than its owner admin can currently do.

Implementation: `AdminPersonalAccessToken::resolvedAbilities()`.

#### Enforcement — `applyAbilityScope()` (2026-06-04 fix)

The ~70 endpoint permission checks (`ChecksAdminPermission`, `AdminOrderActionGuard`, and every processor's inline `assertPermission`) read `$admin->role->permission_type` / `->permissions` directly. They did **not** consult the token, so `permission_type` was silently ignored — a `custom` token always got the owner's **full role** (a `custom`-restricted token could hit endpoints its abilities forbade), and an `all` token bypassed the role cap entirely. Confirmed by test: custom token with `abilities=[sales.invoices.view]` on a role granting invoices+shipments returned **200** on `/api/admin/shipments` (should be 403).

Fix is **central, zero per-endpoint change**: every check resolves the admin through one chokepoint (`AdminApiGuard::user()` and `AdminAuthHelper::resolveFromExplicitToken()`), which already attaches the token. Both now call **`AdminPersonalAccessToken::applyAbilityScope($admin)`**, which replaces the resolved admin's in-memory `role` relation with a **clone** whose `permission_type='custom'` + `permissions = $token->resolvedAbilities()` (the role-capped effective set). The persisted `roles` row is never mutated; a `__token_ability_scoped` marker makes it idempotent; when effective abilities are `['*']` (role is `all`) the role is left untouched. So the existing role-based checks transparently enforce the token. `resolvedAbilities()` was also corrected so `all` is capped by the role (like `same_as_web`) — an `all` token = full access *within the role*, never beyond it. Regression suite: `tests/Feature/Admin/RestApi/TokenAbilityScopeTest.php` (custom-restricts, custom-cannot-exceed-role, same_as_web-follows-role, all-on-all-role-stays-full).

### IP allowlist (`allowed_ips` column)

- Schema: nullable JSON array on `admin_personal_access_tokens` (one entry per line in the admin form, persisted as `["10.0.0.0/24", "203.0.113.1", "2001:db8::/32"]`).
- **NULL or empty array = any IP allowed** (current behaviour preserved). Restriction is fully opt-in per token.
- Supports IPv4, IPv6, and CIDR notation (both /32 IPv4 and /128 IPv6 ranges). Validation uses Symfony's `IpUtils::checkIp()`; form input is validated by `Webkul\BagistoApi\Admin\Rules\IpOrCidr`.
- Enforced in `AdminApiGuard::user()` immediately after token resolution and BEFORE returning the admin — denied requests return null (effective 401) and log `Admin API token denied by IP allowlist` at `warning` level with `{token_id, admin_id, client_ip}`.
- `127.0.0.1` is always allowed regardless of the list (mirrors `ApiKeyService::ipAllowed()` dev-convenience short-circuit). Production deployments behind a proxy must configure trusted proxies so `$request->ip()` reflects the real client IP.
- The lifecycle email notification **does not** mention `allowed_ips` (kept identical to pre-2026-05-27 behaviour — noise vs. value trade-off).

### `AdminApiGuard::user()` re-resolution (2026-05-28 fix)

File: `src/Admin/Auth/AdminApiGuard.php`. Previously the guard memoised the resolved `$this->user` for the lifetime of the PHP process — fine in production (one request per worker), broken for tests that fire several requests with different Bearer tokens (e.g. permission-matrix tests). All subsequent requests silently returned the FIRST admin. Fix: `user()` now compares the current request's Bearer token id against the cached token id and resets + re-resolves on mismatch. Caller-facing behaviour is unchanged; the fix unblocked ~5 admin Pest tests that were quietly using the wrong admin and either passing for the wrong reason or failing intermittently. Rule for any future custom guard in this package: never memoise across requests without an identity-stable cache key.

### Defaults on Generate

- `expires_at` = now + 1 year
- `rate_limit_per_minute` = 60
- `rate_limit_per_day` = 10000

NULL on any of these three columns means **no limit / no expiry** (e.g. user explicitly selected "Unlimited" / "Never expires" radio on the edit form). The edit page maps:

| Form value | DB value |
|---|---|
| `rate_min_mode=unlimited` | `rate_limit_per_minute = NULL` (no per-minute cap) |
| `rate_min_mode=limited`, `rate_limit_per_minute=120` | `rate_limit_per_minute = 120` |
| `expires_mode=never` | `expires_at = NULL` (never expires) |
| `expires_mode=expires`, `expires_at=2027-01-01` | `expires_at = 2027-01-01` |

### Regenerate semantics

Hard-deleting on regenerate would lose history. Instead:
1. New row created with status `draft`, copying name/description/permissions/rate-limits from old row
2. New row generated → status `active`, fresh hash & preview
3. Old row's `token` set to NULL, status flipped to `regenerated`, `regenerated_to_id` points to the new row, `regenerated_at` + `regenerated_by_admin_id` recorded
4. Redirect lands on the **new** row's edit page (different URL/id) with the plaintext flashed once

Old row is preserved for audit but cryptographically inert.

### Revoke semantics

Same idea — preserve the row for audit, kill the token:
- `token = NULL`
- `status = revoked`
- `revoked_at`, `revoked_by_admin_id` recorded

### DataGrid filtering

`IntegrationDataGrid::prepareQueryBuilder()` filters `WHERE status IN ('draft', 'active')`. Revoked and regenerated rows do NOT show in the default list (they exist in the DB for audit). If a "history" view is needed later, it's a separate datagrid / query.

### Authentication + throttling on `/api/admin/*`

**There is no admin route group.** API Platform registers all ~493 admin routes itself from the `#[ApiResource]` metadata and applies ONE global middleware list (`routes.middleware` in the three `api-platform.php` configs), so `Route::middleware(['auth:admin-api', 'throttle:admin-api'])->prefix('api/admin')` — the pattern this file used to show — was never possible. Both concerns are therefore **path-scoped middleware** in that global list:

| Middleware | Job |
|---|---|
| `Http/Middleware/EnforceAdminApiAuth` | 401s an `/api/admin/*` request with no valid admin Bearer token; passes everything else through |
| `Http/Middleware/ThrottleAdminApi` | Delegates to the `admin-api` named limiter for `/api/admin/*` only; extends Laravel's `ThrottleRequests` |

Both are registered in **all three** `api-platform.php` configs AND on the `/api/admin/graphql` route in the service provider (that route bypasses the api-platform list). Adding a new global admin concern means touching all four places.

The `admin-api` limiter (in `bootAdminIntegration()`) reads `rate_limit_per_minute` / `rate_limit_per_day` off the resolved token; NULL on a column = that bucket uncapped, and NULL on both = `Limit::none()`.

**Two traps this cost us (both shipped broken for months, fixed 2026-08-12):**
1. **The limiter was never attached to a route.** It was registered, keyed correctly, and had a documented "fix" in 2.1.0 — but nothing invoked it, so no admin request was ever throttled. The 2.1.0 note even claimed a `31 requests → 429` repro that was never executed. **Never record a repro you did not run.**
2. **The limiter read the token from the wrong place.** It used `$request->user('admin-api')?->getAttribute('current_access_token')`, but `Admin` has `HasApiTokens`, so `AdminApiGuard` attaches via `withAccessToken()` and that attribute is never set. It now reads `currentAccessToken() ?? getAttribute('current_access_token')`. Even once wired, the old lookup would have silently disabled throttling.

**The 429 is rendered by the middleware, not thrown.** A bare `ThrottleRequestsException` on these routes is converted to an HTML **500** by the app's exception handler, so `ThrottleAdminApi` catches it and returns `{"message": "Too Many Attempts.", "error": "rate_limit_exceeded"}` with the `Retry-After` / `X-RateLimit-*` headers — mirroring how `EnforceAdminApiAuth` emits its 401.

**Unauthenticated `/api/admin` traffic** falls back to `Limit::perMinute(60)->by(ip)`, **skipped under `testing`** — the admin suite fires 262 deliberate 401s from one IP and would otherwise start seeing 429s.

Regression-locked by `tests/Feature/Admin/RestApi/RateLimitTest.php` (5 tests: per-minute enforced, per-day enforced, uncapped token unthrottled, per-token bucket isolation, shop routes unaffected). **Do not call `Cache::flush()` in those tests** — they run in parallel with the rest of the admin suite and it wipes shared cache state, which fails an unrelated test.

### Token format

Bearer header carries `<id>|<plaintext>`:

```
Authorization: Bearer 5|1dYWpciAn2Ro8dfsabA89ohhduVWWXqicyPyQeIH
```

- `<id>` = `admin_personal_access_tokens.id` (lets the guard look up by primary key in O(log n) without scanning)
- `<plaintext>` = 40-char `Str::random()` output (never stored — only its SHA-256 in `token` column, plus first 8 chars in `token_preview` for masked UI display)

After creation, plaintext is shown to the admin **exactly once** (in the green flash on the edit page). They must save it. If lost, only option is Regenerate.

### Token lifecycle email notifications

Every generate / regenerate / revoke fires an email to the **token owner** (`admin_personal_access_tokens.admin_id` → `admins.email`).

- Mailable: `Admin/Mail/AdminTokenNotification.php` (queued; extends `Webkul\Admin\Mail\Mailable`). Constructor: `(AdminPersonalAccessToken $token, string $event, ?string $ipAddress)`. Events: `generated`, `regenerated`, `revoked`.
- View: `Admin/Resources/views/emails/token-notification.blade.php` (uses `admin::emails.layout`).
- **The plaintext token is NEVER in the email.** Plaintext is shown only once in the browser. Email is a notification, not a delivery channel.
- Generate/regenerate emails include a **signed, 7-day, login-free revoke link** (`URL::temporarySignedRoute`). Revoked emails have no button (confirmation only).
- `IntegrationController::notifyOwner()` queues the mail; failures are logged via `Log::error`, never block token management.
- Wired in `IntegrationController` on `generate()` / `regenerate()` / `destroy()`. `revokeViaEmail()` (the email link target) does **not** re-send a "revoked" email — the owner already sees the on-screen confirmation.

### Signed revoke-via-email route

`admin.integration.revoke-via-email` (`GET /{admin_url}/integration/revoke-via-email/{id}`) — registered in `Admin/Routes/admin.php` under the **`signed` middleware** (NOT `admin`), so the owner can revoke from their inbox without logging in. Tampered/expired links → 403. Revokes attributed to the token's own `admin_id`. Confirmation view: `Admin/Resources/views/integration/revoke-confirmation.blade.php`.

### Module enable/disable toggle

The plugin can be turned off from **Admin → Configuration → API → Integration → Module Settings**.

- System config: `Admin/Config/system.php` registers `api.integration.settings.enabled` (boolean, default `true`), merged into `core` config in `mergeAdminConfigs()`.
- Menu visibility: `BagistoApiServiceProvider::registerIntegrationMenu()` merges the sidebar menu **only when enabled**. It is called from `bootAdminIntegration()` (boot phase) — **not** `register()`, because `core()->getConfigData()` is not reliable during register.
- Route guard: `IntegrationController` constructor middleware returns **404** for all UI actions when disabled — but `revokeViaEmail` is `except()`-ed, so the email revoke link still works after the module is switched off.
- `isIntegrationModuleEnabled()` defaults to `true` when the config table is unavailable (config:cache, migrations, pre-install).
- `getConfigData()` caches its result; Bagisto's config UI flushes the cache on save, so the menu updates on the next page load. Direct DB edits need `php artisan cache:clear`.


### Admin feature — progress & tests

| Piece | Status |
|---|---|
| `admin_personal_access_tokens` table + `AdminPersonalAccessToken` model | done |
| `AdminApiGuard` + `admin-api` guard, `throttle:admin-api` limiter | done |
| Web UI: `IntegrationController`, DataGrid, create/edit/index/_form blades, menu, ACL | done |
| `AdminTokenService` (draft / generate / regenerate / revoke) | done |
| Token lifecycle emails + signed revoke link | done |
| Module enable/disable system config | done |
| Admin REST API endpoints (`/api/admin/*`) | **not started — next phase** |
| Admin GraphQL endpoints | **not started — next phase** |

**Test layout** — admin tests live under `tests/Feature/Admin/`, split by transport:

```
tests/Feature/Admin/
├── Web/        ← admin-panel (blade/web routes) tests
│   └── AdminIntegrationTest.php   ← 24 tests: token CRUD, generate/regenerate/revoke,
│                                     lifecycle emails, signed revoke link, module toggle
├── RestApi/    ← admin REST API tests (empty — for the next phase)
└── GraphQL/    ← admin GraphQL tests (empty — for the next phase)
```

`AdminIntegrationTest.php` extends `Tests\TestCase` (web auth via `actingAs($admin, 'admin')`, `Mail::fake()`). It is **not** a REST/GraphQL test and not a "central" file — when the admin API is built, add one file per resource per transport under `RestApi/` and `GraphQL/`, mirroring the storefront convention. A shared `AdminApiTestCase` base class (admin-token auth) should be created once at that point.

Run it:
```bash
php artisan test packages/Webkul/BagistoApi/tests/Feature/Admin/Web/AdminIntegrationTest.php --parallel --processes=8
```

---

## Admin API — Development Workflow

The admin API exposes Bagisto's admin-side data over REST + GraphQL, authenticated by the admin integration tokens (`AdminApiGuard`, `auth:admin-api`). This is the **active development phase**.

### Route + naming conventions

- REST prefix: **`/api/admin/...`** (e.g. `/api/admin/login`) — mirrors the storefront `/api/shop/...` convention, no version segment. Never mix with `/api/shop/`.
- Auth: `auth:admin-api` guard + `throttle:admin-api` limiter. Public endpoints (login, forgot-password) are exempt from `auth:admin-api`.
- GraphQL: admin operations live under the same GraphQL endpoint; name them `admin*` (e.g. `adminLogin`, `adminProfile`) so they never collide with storefront operations.
- Models/DTOs/State for admin resources live under `src/Admin/` (Models, Dto, State) — keep them separate from the storefront `src/{Models,Dto,State}` trees so the two never tangle.

### Admin provider base classes — central scaffolding

To avoid duplicating auth + pagination + sort + Paginator-wrap across every admin endpoint, two abstract bases live at `src/Admin/State/Concerns/`:

- **`AbstractAdminCollectionProvider`** — implements `ProviderInterface`. Handles: auth check, args extraction (REST `request()->query()` + GraphQL `$context['args']`), `resolvePaging` (with cursor `after` decoding), `resolveSort` (dual-form compound `?sort=name-asc` and split `?sort=name&order=desc`), subquery-safe `countTotal`, and `Paginator` wrap. Concrete providers override only:
  - `getSortable(): array` — column allow-list; first entry is the default sort column.
  - `buildQuery(array $args)` — base query builder (joins, select, groupBy, locale/channel wheres).
  - `applyFilters($query, array $args): void` — additional WHERE/HAVING clauses.
  - `applySort($query, array $args): void` — ORDER BY (call `$this->resolveSort($args)` internally).
  - `mapRow(object $row): object` — map a raw DB `stdClass` to the resource DTO.

- **`AbstractAdminItemProvider`** — handles auth, id resolution from `$uriVariables['id']` / `$context['args']['id']`, and 404 throw. Concrete providers override only:
  - `getNotFoundLangKey(): string` — e.g. `'bagistoapi::app.admin.category.not-found'`.
  - `findEntity(int $id): ?object` — Eloquent `::find($id)` with eager loads; return null if missing.
  - `mapToDto(object $entity): object` — map the model to the resource DTO.

**Always use these bases for new admin collection/item providers.** Tree providers and other bespoke shapes can implement `ProviderInterface` directly when the contract doesn't fit.

**No constructor injection required.** The base classes have no constructor. If a concrete provider needs `ApiPlatform\State\Pagination\Pagination` (e.g. to delegate limit/offset to API Platform), inject it in the concrete constructor only — mirrors the shop-API `CategoryRestProvider` pattern.

**`DEFAULT_PER_PAGE` (10) and `MAX_PER_PAGE` (50)** are `protected const` on the base; override them on the subclass only when justified (e.g. a product picker might use 30).

**Sortable allow-list:** return from `getSortable()` (not a class constant). First entry is the default sort column.

**`countTotal()`** uses a subquery-wrapping strategy (`DB::table(DB::raw('(...) as sub'))->count()`) to avoid MySQL errors with `GROUP BY` or aggregate columns. Override in the subclass when a simpler `->count()` suffices.

### Per-feature workflow — MANDATORY for every admin API feature

Follow these steps for **every** endpoint/feature. Do not skip the Bagisto-core check.

```
1. CHECK BAGISTO CORE FIRST
   - Find how the feature works in Bagisto core (controller, repository, events,
     validation, mail, etc.). e.g. admin login → Webkul\Admin login controller.
   - List every behaviour core covers (validation rules, events fired, password
     rules, notifications, edge cases). Confirm our API covers the same points —
     or consciously document why one is intentionally dropped.

2. BUILD REST + GraphQL
   - Build BOTH transports for the feature, sharing the same Processor/Provider
     where possible (same pattern as the storefront API).
   - REST under /api/admin/...  |  GraphQL admin* operations.

3. WRITE TESTS FOR BOTH — with edge cases
   - REST tests  → tests/Feature/Admin/RestApi/<Feature>Test.php
   - GraphQL tests → tests/Feature/Admin/GraphQL/<Feature>Test.php
   - Cover happy path AND edge cases: missing/invalid input, wrong credentials,
     unauthenticated, expired/revoked token, permission denied, disabled module.

4. RUN TESTS — all green
   php artisan test packages/Webkul/BagistoApi/tests/Feature/Admin --parallel --processes=8
   Fix until every test passes. Do not mark a feature done with red tests.

5. FILL THE OPENAPI EXAMPLES — request body + example response
   - Every `#[ApiResource]` operation's `openapi` block must ship realistic
     sample data so the Swagger UI at /api/admin is usable out of the box:
       * REST write ops → `requestBody` content with an `example` (or `examples`)
         showing real field values, not just a schema.
       * Every op → a `responses` entry with an `example` of the actual payload.
   - Derive the sample values by **hitting the endpoint once** (a passing test,
     tinker, or curl) and copying the real shape — never invent fields.
   - GraphQL operations: put the same query/variables/response sample in the
     api-docs `examples:` block (step 7) — GraphQL has no OpenAPI block.
   - Do not skip this: an endpoint with an empty request body / blank example
     in Swagger is treated as incomplete.

6. UPDATE CLAUDE.md
   - Update the coverage table below (missing → covered).
   - Note any Bagisto-core behaviour intentionally not covered, with the reason.

7. UPDATE THE API DOCS  (only after the feature is tested and green)
   - Docs repo: /home/users/yashvir.singh/www/html/Bagisto/bagisto-api-docs/
     (VitePress site — sidebar in .vitepress/config.mts, pages under src/api/).
   - Under the "Admin API" sidebar group there are two branches — "GraphQL API"
     and "REST API". Add the feature under BOTH.
   - The Admin API sidebar must MIRROR the admin panel's menu / sub-menu, so a
     reader can tell which APIs belong to which admin screen. For each feature
     the requester provides the menu + sub-menu it sits under; if not provided,
     ASK before placing it.
   - For each feature add: a page with the API example (GraphQL query/mutation
     with variables + response; REST endpoint with request/response) plus the
     theory/explanation content. Mirror the existing page format — GraphQL docs
     use the `examples:` frontmatter block (see
     src/api/graphql-api/shop/mutations/customer-login.md).
   - Replace the placeholder "Coming Soon" entries as real features land.
```

### Documentation writing style — MANDATORY (write for the API consumer)

The API docs are for an integrator who only cares about **what to send, what they get back, and what the endpoint represents**. Never leak our internals.

- **Never mention code internals.** No class names, file paths, method names, controllers, helpers, providers/processors, DataGrids, events, or our internal workflow/architecture. Describe *behaviour and payload shape*, not implementation. ❌ "Mirrors `Webkul\Admin\Helpers\Dashboard`", "`DashboardController::$typeFunctions`", "backed by `getOverAllStats`". ✅ "Returns the data behind the admin Dashboard screen."
- **Transport-neutral pages link REST-first (rule 2026-07-28).** On a page that isn't under `rest-api/` or `graphql-api/` (Authentication, the Core Concepts pages, Workflows overviews, Build-with-AI, etc.), a reference link to an endpoint points at the **REST** page by default (self-describing path+verb; REST pages already cross-link to their GraphQL twin; keeps prose to one link). Add a GraphQL link inline only when the behaviour genuinely differs by transport. Pages that already live under `rest-api/` or `graphql-api/` always link within their own transport.
- **NO callouts and NO "Note:" anywhere — hard rule (user pref 2026-07-27, tightened 2026-08-06).** Do not write `::: note` / `::: tip` / `::: warning` / `::: info` blocks, and do not write `> **Note:** …` blockquotes, on ANY page. Every fact goes in ordinary prose, a normal list item, or a table — presented as a first-class part of the page, never as something bolted on after the fact. A "Note:" reads as "this was added later and needs special attention"; an ordinary fact does not deserve that framing, and none of the reference docs we mirror use it. If a fact matters, give it a real sentence or its own section heading (e.g. `## Locale Handling`) instead of a callout. **Also do not restate in prose what a table on the same page already says** — duplicated content reads as a redundant note; keep it in the table only, or expand the table's Description column.
- **"Use Cases" and "Best Practices" must be endpoint-specific and verified against core Bagisto behaviour (rule 2026-08-06).** Both sections are worthless as generic advice and are a common source of *wrong* advice, so treat every line as a claim to verify against the package code and core (the controller/repository/config the endpoint mirrors) exactly like a field type. **Banned:** items that would read identically on any endpoint of any API ("Validate Input", "Handle Errors", "Cache Results", "Use Pagination", "Prevent Spam", "Show Ratings"), and Use Cases that are only a pointer to the examples dropdown ("Use the *Basic* example to submit a review"). **Required:** each item states something a client would otherwise get wrong on THIS endpoint — the real limit, the real default, the real ordering, the real config gate, the field that is silently ignored, the argument that must not be sent — and Use Cases carry a concrete query/snippet with the actual arguments rather than a description of one. Re-derive limits and defaults from the code (`5 MB`, `default 30`, `orderBy('id','asc')`, `?? 'approved'`), never from what the page already claims. A best practice that recommends an operation the API does not support (sorting where there is no sort argument, `status: 1` to auto-approve) is a defect, not a style issue.
- **Verify SHAPE locally, never rewrite example VALUES to local data (rule 2026-08-06).** The local dev instance and the instance the docs are captured against hold **different data** — different product/category/page IDs, SKUs, slugs, prices, and content. Local verification is authoritative ONLY for things that come from the package code and are identical everywhere: GraphQL/REST **field names, argument names, types, nullability, which filter and sort keys exist, response structure, error conditions, and whether a query is even valid**. It is NOT authoritative for any **value** — never "fix" an example's IDs, SKUs, slugs, prices, counts, or response payload to match what the local box returns, and never justify an example by "I checked, it returns N rows here". A local-only ID silently breaks every reader on production. When an example is wrong because it uses a **nonexistent key or field** (a schema fault), fix the key and keep the values illustrative and instance-neutral; when an example is wrong because its **data no longer exists** (a value fault), flag it for re-capture against the real docs instance instead of substituting local values.
- **Never start a heading, paragraph, list item, or table cell with a lowercase word or a lowercase code span (rule 2026-08-06).** `### translations Connection Fields` and a paragraph opening `` `translation` then carries… `` read as mid-sentence fragments and look casual. Capitalize the heading's first word even when it names a lowercase API field (`### Translations Connection Fields`, then use the exact field name inside the prose/table), and open body text with a capitalized word or a **Bold label —** prefix rather than a bare code span.
- **Prefer a bulleted or numbered list over stacked loose paragraphs (rule 2026-08-06).** Whenever a section describes 2+ related behaviours, rules, or steps, list them — an ordered list when the order matters (resolution/precedence/steps), a bulleted list with a **Bold label —** lead-in otherwise. A run of one-sentence paragraphs with no marker reads as an afterthought pile; the list makes each fact a first-class item and forces a capitalized lead word.
- **Document authentication once, in the Authentication page only.** Admin auth lives solely on the per-transport page (`src/api/{graphql-api,rest-api}/admin/authentication.md`). Endpoint pages MUST NOT restate the auth model — at most a one-line pointer: "All admin endpoints require an admin Bearer token — see [Authentication](…)." Never repeat token-format / header details on each page.
- **No `X-Admin-Key` anywhere on admin pages.** `/api/admin/*` authenticates with a pre-issued Integration token only (`Authorization: Bearer <id>|<token>`). Do not put `X-Admin-Key` in admin examples or notes. (Admin login/logout/forgot-password endpoints no longer exist — don't document them.)
- **One endpoint = one page. Put each `type`/filter/option as a separate entry in the `examples:` dropdown** — each with its own `query` / `variables` / `response`, pretty-printed **multi-line** (never crammed onto one line). Do NOT create a separate sidebar sub-menu page per filter/type/option. Every example's `variables` (or curl URL) should carry the same full input set (e.g. include `start`/`end`) so the dropdown stays consistent.
- **GraphQL query/mutation examples: EXACTLY ONE FIELD PER LINE in every selection set** (the user has flagged this twice — do NOT cram `id _id urlKey pageTitle` onto one line; each selected field goes on its own indented line, `pageInfo`/nested selections included). Operation args may stay on the operation line; only the `variables`/`response` JSON is already-multi-line. After writing GraphQL examples, grep for `^\s+[a-zA-Z_]+ [a-zA-Z_]+ [a-zA-Z_]+` inside the query bodies to catch crammed lines.
- **LIST EVERY FIELD THE OPERATION CAN RETURN — never trim the example selection (user-flagged 2026-06-18).** An example query/response MUST include **every** field the operation exposes (every top-level scalar AND every sub-field of each nested object / connection `node`), so the consumer sees the complete menu and picks what they need — our job is to hand them all of it, not curate a subset. When migrating/editing an example, re-derive the full field list from the resource's props / the sub-resource's `normalizationContext.attributes` (e.g. an invoice item node has _id, orderItemId, sku, name, qty, price, formattedPrice, basePrice, basePriceInclTax, total, formattedTotal, baseTotal, baseTotalInclTax, taxAmount, formattedTaxAmount, discountAmount, formattedDiscountAmount, productId, productType, baseImageUrl, additional — list ALL of them). **The ONLY exception is a field that genuinely does not resolve on that operation** (e.g. a connection like `items`/`order.addresses` on a *create/update mutation* payload returns empty — omit it there and add a one-line note pointing to the detail query; never show a field that returns null/empty, since the user tests examples and flags nulls). The response JSON must echo the same complete field set with real values.
- **Admin GraphQL docs split into `queries/` and `mutations/` subfolders** (mirrors the shop GraphQL convention `src/api/graphql-api/shop/{queries,mutations}/`). Per menu: `…/<menu>/index.md` (overview), `…/<menu>/queries/<name>.md` (list/detail/get), `…/<menu>/mutations/<name>.md` (create/update/delete/mass-*). Sidebar groups them under nested **Queries** / **Mutations** sub-items. **Rollout: CMS Pages done (2026-06-08); apply this layout to every admin GraphQL menu going forward (Sales/Catalog/etc. are still flat — retrofit per menu as they're QA'd).** REST docs do NOT split (REST has no queries/mutations distinction).
- **VitePress sidebar HARD-CAPS nesting at depth 5** (`VPSidebarItem.vue`: `<template v-if="depth < 5">` — a depth-5 group's children are NOT rendered). The sidebar tree is `Bagisto APIs(0) › GraphQL API(1) › Admin API(2) › <category>(3) › <menu>(4) › Queries/Mutations(5) › items(6)` — so the leaf items land at **depth 6 and silently don't render** (the Queries/Mutations groups show but appear empty). **For the queries/mutations sidebar grouping to render, its items must be at depth ≤5, i.e. the `Queries`/`Mutations` group at depth ≤4.** Fix for a **single-menu category** (CMS = only Pages): collapse the category+menu into ONE node — the `CMS` group (depth 3) holds `Queries`/`Mutations` (depth 4) directly, with `link:` pointing at the menu overview, and the `Pages` level dropped. For a **multi-menu category** (Catalog has Products/Attributes/Families/Categories; Sales has many): you can't collapse, so `Queries` sits at depth 5 and its items won't render — either keep that menu's sidebar items FLAT (files stay in `queries/`·`mutations/` folders for the URL, but list them directly under the menu, no Queries/Mutations sub-group), OR raise the cap via a `VPSidebarItem` theme override (Vite `resolve.alias` to a local copy with `depth < 7` + level-6/7 CSS). Decide per category when retrofitting; CMS used the collapse.
- **Use real data.** Derive example request/response values by hitting the live endpoint once and copying the actual shape — never invent fields.
- **Keep `outline: false`** in frontmatter (site-wide convention, ~650 pages) so the right column shows the examples panel, not an "On this page" TOC.
- **Surface output quirks the consumer needs** (e.g. "this field is `null` over GraphQL", "this field is pre-rendered HTML — use the X endpoint instead") in plain consumer terms, without referencing the code reason.
- **Every sidebar menu group has a landing/overview page.** Each collapsible group in `.vitepress/config.mts` (the top-level admin menus AND every sub-group/datagrid) carries a `link:` pointing at an `index.md` in that group's folder (URL = the folder root, e.g. `link: '/api/rest-api/admin/sales/invoices/'` → `src/api/rest-api/admin/sales/invoices/index.md`). VitePress's default theme renders the group **title as a clickable link** (the chevron still toggles). The overview page is the **home for the menu's feature-logic** — what the menu is, when a row appears, status/derived-field semantics, what each action does, mass-actions — plus a short endpoint index table linking to the leaf pages. Endpoint pages (`list.md`, `get-*.md`) keep only a one-line `::: tip` pointer to the overview, not the full feature-logic. Applies to **every** admin menu group, both transports. **Rollout status: Sales menus done (2026-06-05); Catalog done (2026-06-05 — Products, Attributes, Attribute Families, Categories all have an overview `index.md` + group `link:` for REST+GraphQL); CMS / Customers / Marketing / Settings still to do** — same pattern, one `index.md` + group `link:` per group.

### Admin API QA — working protocol (Catalog + CMS pass onward, 2026-06-08)

Conventions for the menu-by-menu QA bug-fix pass. They refine the per-menu template + the doc-style rules above; follow them for **every** menu from here on.

- **Verify admin core BEFORE writing any theory.** For each menu/sub-menu, first trace what Bagisto **core** actually does (the admin controller → repository → blade flow) and exactly which features the screen exposes. The doc theory must match that reality — never vague, never invented. Confirm the feature exists and how it behaves, THEN describe it. A wrong/hand-wavy explanation is a defect.
- **Overview-page hierarchy — FUNCTIONAL relations, not DB relations.** Every menu group has an `index.md` overview (the group `link:`). The **main menu** overview (e.g. Catalog) describes each sub-menu's functionality and why it matters. Each **sub-menu** overview (e.g. Attributes) explains its significance, impact, and **functional relations** to other menus — e.g. "Attributes define the dynamic attribute set assigned to products; Attribute Families group attributes and are chosen when creating a product." Describe how features relate in the admin/storefront **workflow**, NOT database FKs/joins/table names.
- **Always update BOTH REST and GraphQL.** They are the same feature behind two transports, so the functional behaviour, rules, validation, and example data stay in **lockstep**. The ONLY theory difference is the feature-surface split: **GraphQL** can combine filtering + relation-loading + single-item fetch in ONE query (its docs note that), while **REST** splits the same capabilities across separate endpoints (list / get / filter params) so its docs reflect the per-endpoint separation. Same data, same behaviour — just framed per transport. Never update one transport's docs/tests and leave the other behind.
- **Datagrid filters are AND-combined; expose ONLY the datagrid's own fields.** Document that supplying multiple filters **narrows** the result (logical AND — more filters = more restrictive). Only declare/expose the filters the admin **datagrid** actually shows (Products: **Channel, Name, SKU, Attribute Family, Price, ID, Status, Type**). Do NOT add a filter arg for every DB column — that's unnecessary surface area; mirror the datagrid's filter set (check the core `*DataGrid` class to confirm the list). GraphQL custom filter args still need `extraArgs`.
- **Mass actions belong to the listing menu.** Any listing with a mass-action button must have those endpoints covered + documented. Products: **mass-delete** and **mass-update-status (active/disable)**. Document them on the listing + overview.
- **Tests: update AND extend.** The existing per-resource suite may be stale or miss newly-found endpoints/bugs. For every menu, fix the outdated tests and add new ones for the missing endpoints + each bug fixed — REST and GraphQL both. Don't assume the existing suite is complete.
- **A GraphQL report implies the REST endpoint too.** The user usually sends a GraphQL query + response; the matching REST endpoint shares the same provider/processor, so verify and fix it in the same pass (and vice-versa).
- **Two menus in parallel.** To move faster, two different menus are worked concurrently (currently **Catalog + CMS**). Keep their changes in independent resource/doc files so they don't collide.

### Test infrastructure

- Create a shared `AdminApiTestCase` base class (admin-token auth helpers: `authenticatedAdminGet/Post/...`, `createAdminToken()`) once, before the first endpoint — the admin equivalent of `RestApiTestCase` / `GraphQLTestCase`.
- Admin REST tests → `tests/Feature/Admin/RestApi/`, GraphQL → `tests/Feature/Admin/GraphQL/`. One file per feature/resource.

### Admin API — endpoint coverage

Update status in-place (missing → covered). Never append duplicates.

| Feature | REST endpoint | GraphQL operation | REST Test | GraphQL Test | Status |
|---------|--------------|-------------------|-----------|--------------|--------|
| Admin Profile (get) | `GET /api/admin/get` | `readAdminProfile` query | `Admin/RestApi/AuthenticationTest.php` | `Admin/GraphQL/AuthenticationTest.php` | covered — only auth-related endpoint after the 2026-05-27 refactor. Login / Logout / Forgot Password / Profile Update were removed; admin clients authenticate with pre-issued `AdminPersonalAccessToken` Bearer tokens via the `admin-api` guard (`Webkul\BagistoApi\Admin\Auth\AdminApiGuard`). |
| Admin Navigation Menu | `GET /api/admin/menu` | `getAdminMenu` query | `Admin/RestApi/MenuTest.php` | `Admin/GraphQL/MenuTest.php` | covered (2026-06-15) — returns the admin sidebar (`config('menu.admin')`) as a nested tree, **permission-filtered** to the resolved token's role (a node shows if `adminHasPermission(key)` OR any child is visible; `permission_type=all` → full tree). Each node: `{ key, label (translated `name`), icon, sort, permission (=key), apiResource: { rest, graphql } | null, children }`. The core `route` (a Blade panel route) is **dropped**; `apiResource` is **derived dynamically** — `AdminMenuProvider::discoverResources()` reads API Platform's resource metadata (via `ResourceNameCollectionFactory` + `ResourceMetadataCollectionFactory`) to index every admin resource's REST listing path (`routePrefix`+`uriTemplate` of the base `GetCollection`) + GraphQL field (custom-named query like `stats`/`menu`, else `lcfirst(Str::plural(shortName))` — so `catalog/families` correctly yields `adminAttributeFamilies`). Menu key → path via `candidatePaths()` convention (kebab segments; full / first+last / drop-first / last-only), with a 5-entry `IRREGULAR` alias for keys whose route diverges (`cms`→`/cms/pages`, `dashboard`/`reporting`→`/…/stats`, `configuration`→`/configuration/menu`, `email_templates`→`/marketing/templates`); aliases are still validated against the discovered index. New resources auto-appear — no map to maintain. Resource `AdminMenu` (POPO: `id`, `tree`) + `AdminMenuProvider` (reuses `ChecksAdminPermission`; recursive `nest()`, no references; request-static index cache) + `AdminMenuQueryResolver`. REST `[{ id, tree }]`; GraphQL `getAdminMenu` (`Query(name: 'get')`) returns the resource with `tree` as a plain JSON array. No write surface. |
| Admin Permissions | `GET /api/admin/permissions` | `getAdminPermissions` query | `Admin/RestApi/PermissionsTest.php` | `Admin/GraphQL/PermissionsTest.php` | covered (2026-06-15) — returns the authenticated token's effective permission set: `{ permissionType: all\|custom\|same_as_web, permissions: [keys] }` (`all` → `["*"]`). Reads the token-scoped `admin->role` (already capped by `applyAbilityScope`), so it reflects the real granted abilities for UI gating without trial-and-error 403s. Resource `AdminPermissions` (POPO; `permission_type` is **snake_case** so it resolves over GraphQL, surfaced as `permissionType` via the output converter — `buildPayload()` emits the camelCase key for the plain-array REST path, `toDto()` maps it to the snake prop for the GraphQL DTO) + `AdminPermissionsProvider` + `AdminPermissionsQueryResolver`. REST `[{ id, permissionType, permissions }]`; GraphQL `getAdminPermissions`. No write surface. |
| Orders — listing/index | `GET /api/admin/orders` | `adminOrders` query (cursor) | `Admin/RestApi/OrderTest.php` | `Admin/GraphQL/OrderTest.php` | covered (slim list + 7 filters) |
| Orders — detail/view | `GET /api/admin/orders/{id}` | `adminOrderDetail` query | `Admin/RestApi/OrderDetailTest.php` | `Admin/GraphQL/OrderDetailTest.php` | covered (REST + GraphQL) — full eager-loaded payload (customer / addresses / type-aware items / invoices / shipments embedded). `create` not built. |
| Orders — Reorder action | `POST /api/admin/orders/{id}/reorder` | `createAdminReorder` mutation | `Admin/RestApi/OrderActionTest.php` | `Admin/GraphQL/OrderActionTest.php` | covered — builds an admin draft cart (`is_active = false`) for the order's customer and re-adds each item via `Cart::addProduct($item->product, $item->additional)`; returns `{ success, message, cartId }`. **Enforces the same 3-check guard as the Bagisto admin UI** (guest, saleable, `sales.orders.create` permission, `sales.order_settings.reorder.admin` config) — each failure surfaces a distinct HTTP 422 / GraphQL `errors[]` message. See "Admin Reorder — 3-check guard" below. **GraphQL input field is `orderId`** (not `id`) — `id` is reserved as the resource IRI in API Platform GraphQL. **2026-06-19 — `cartId` was null over GraphQL fixed:** `AdminReorder` was a camelCase POPO, so the multi-word `cartId` resolved null over GraphQL (the documented camelCase-read quirk — `success`/`message` are single-word so they worked); converted the prop to snake_case `cart_id` + `use AcceptsCamelCaseWrites` (processor's `$r->cartId =` write is mapped by the trait). Now `cartId` + `_id` (the source order id) both resolve. **Systemic limitation (shared with `AdminDraftCart`): the GraphQL `id` IRI field 500s** ("Cannot return null for non-nullable field id") — a synthetic POPO mutation-result has no resource route, and the GraphQL mutation-payload IRI path does NOT go through `CustomIriConverter` (verified: the converter returns a valid IRI but GraphQL still nulls it), so the auto-injected `id: ID!` resolves null. **Clients must query the explicit value field (`cartId`) + `_id`, never `id`, on these action results.** Docs updated with a `::: tip` to that effect. |
| Customer addresses (Create-Order sidebar) | `GET /api/admin/customers/{customerId}/addresses` | `adminCustomerAddresses(customerId:)` query | `Admin/RestApi/CustomerAddressTest.php` | `Admin/GraphQL/CustomerAddressTest.php` | covered — read-only sub-resource for the Create-Order address picker. Extended in Block C C1 (full CRUD on `/customers/{customerId}/addresses/{id}` — see Admin Customer Addresses rows below). |
| Admin Customers — listing | `GET /api/admin/customers` | `adminCustomers` (cursor) | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — DataGrid parity (listing returns slim rows with `status`/etc.; detail-only fields like `totalAddresses`/`totalOrders`/`totalAmountSpent` are null on listing). **2026-06-24 — migrated POPO→Eloquent: the customer group is now a field-selectable `group { id code name }` typed object over GraphQL (flat array over REST via the `output:` DTO), populated on the listing AND detail. BREAKING: the flat `customerGroupId`/`customerGroupName` scalars were dropped on BOTH transports — read `group { _id code name }` (GraphQL) / `group.id`/`group.code`/`group.name` (REST).** The group is a NULLABLE BelongsTo (820/942 customers have a null FK) — made nullable over GraphQL via the scoped `NullableToOnePropertyMetadataFactory` decorator so a group-less row doesn't 500 the listing. Filters: name (first/last LIKE), email, phone, customer_group_id, status, channel_id, date_of_birth_from/to, created_at_from/to. Sort: id (default desc), email, first_name. |
| Admin Customers — detail | `GET /api/admin/customers/{id}` | `adminCustomer(id:)` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — surfaces `totalAddresses`, `totalOrders`, `totalAmountSpent` (sum of `orders.base_grand_total_invoiced`). **2026-06-24 — the customer group is now a `group { id code name }` typed object (same migration as the listing row); flat `customerGroupId`/`customerGroupName` dropped on both transports.** |
| Admin Customers — create | `POST /api/admin/customers` | `createAdminCustomer` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — `send_password` (default true) generates a random password and queues `Webkul\Admin\Mail\Customer\NewCustomerNotification`. When false, `password` is required. Fires `customer.registration.{before,after}` + `customer.create.{before,after}`. Permission `customers.customers.create`. |
| Admin Customers — update | `PUT /api/admin/customers/{id}` | `updateAdminCustomer` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — email unique excluding self; optional `password` re-hash; fires `customer.update.{before,after}`. Permission `customers.customers.edit`. |
| Admin Customers — delete | `DELETE /api/admin/customers/{id}` | `deleteAdminCustomer` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — guard refuses when the customer has pending/processing orders (`CustomerRepository::haveActiveOrders`) → HTTP 400. Permission `customers.customers.delete`. |
| Admin Customers — mass delete | `POST /api/admin/customers/mass-delete` | `createAdminCustomerMassDelete` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — per-id active-orders guard skips with reason instead of aborting. |
| Admin Customers — mass update status | `POST /api/admin/customers/mass-update-status` | `createAdminCustomerMassUpdateStatus` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — `value` must be `0` or `1`; fires `customer.update.{before,after}` per row. |
| Admin Customer Addresses — detail | `GET /api/admin/customers/{customerId}/addresses/{id}` | `adminCustomerAddress(customerId:, id:)` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered |
| Admin Customer Addresses — create | `POST /api/admin/customers/{customerId}/addresses` | `createAdminCustomerAddress` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — when `default_address=true` the previous default for that customer is unset first. Permission `customers.addresses.create`. |
| Admin Customer Addresses — update | `PUT /api/admin/customers/{customerId}/addresses/{id}` | `updateAdminCustomerAddress` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — ownership guard: if the path `customerId` doesn't match the address's `customer_id`, 403. Permission `customers.addresses.edit`. |
| Admin Customer Addresses — delete | `DELETE /api/admin/customers/{customerId}/addresses/{id}` | `deleteAdminCustomerAddress` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — same ownership guard. Permission `customers.addresses.delete`. |
| Admin Customer Notes — create | `POST /api/admin/customers/{customerId}/notes` | `createAdminCustomerNote` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — writes to the `customer_notes` table (append-only; the legacy `customers.notes` text column was dropped in 2023). Fires `customer.note.create.{before,after}`. Empty note → 422. Permission `customers.customers.edit`. |
| Admin Customer Notes — list | `GET /api/admin/customers/{customerId}/notes` | `adminCustomerNotes(customerId:)` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered (2026-06-09) — lists the customer's notes newest-first (`{ id, note, customerId, customerNotified, createdAt }`) in the `{ data, meta }` envelope. The admin customer **view** screen renders this notes list; previously we could only POST a note, not read them back. 404 on unknown customer. |
| Admin Customer Compare Items (Create-Order sidebar) | `GET /api/admin/customers/{customerId}/compare-items` | `adminCustomerCompareItems(customerId:)` | — | — | covered (2026-06-09) — the third Create-Order sidebar panel alongside cart-items + wishlist-items (was missing). Reads `compare_items` for the customer; rows `{ id, productId, sku, name, price, formattedPrice, productImage }`. Mirrors the wishlist-items shape. |
| Admin Customer Address — set default | `POST /api/admin/customers/{customerId}/addresses/{id}/set-default` | `setDefaultAdminCustomerAddress` | `Admin/RestApi/CustomerAddressTest.php` | — | covered (2026-06-09) — mirrors core `AddressController::makeDefault`: sets the chosen address `default_address=1` and zeroes all the customer's other addresses. Ownership-guarded (address must belong to `{customerId}` → 403). Returns the updated address detail. Empty body. Permission `customers.addresses.edit`. (Previously default-address could only be toggled inline on create/update.) |
| Admin Customer Impersonate / **Login as Customer** | `POST /api/admin/customers/{customerId}/impersonate` | `createAdminCustomerImpersonate` | `Admin/RestApi/CustomerTest.php` | `Admin/GraphQL/CustomerTest.php` | covered — **this is the API equivalent of the admin datagrid's "Login as Customer" action.** Core `CustomerController::loginAsCustomer` does a SESSION login (`auth()->guard('customer')->login($customer)` + redirect to the storefront) — browser/session-based, can't translate to a stateless API. **Design decision (2026-06-09):** the API issues a short-lived **customer Sanctum token** instead — the client uses it as the customer's Bearer token to act as them against the storefront API (`/api/shop/*`). Token name `admin-impersonate:{adminId}` + ability `impersonated-by-admin:{adminId}` for audit; expires in 1 hour. Returns `{ token, customerId, customerEmail, customerName, impersonatedByAdminId, expiresAt }`. Permission `customers.customers.edit`. So "login as customer" = "get a customer token" — the correct headless translation. **2026-06-24 — GraphQL null fix: this resource was a camelCase POPO without `AcceptsCamelCaseWrites`, so `customerId`/`customerEmail`/`customerName`/`impersonatedByAdminId`/`expiresAt` resolved null over GraphQL; converted to snake props + the trait (processor untouched, REST unaffected). The 2026-06-09 Customer sweep missed this action-only resource.** |
| Admin Customer Groups — listing | `GET /api/admin/customers/groups` | `adminCustomerGroups` (cursor) | `Admin/RestApi/CustomerGroupTest.php` | `Admin/GraphQL/CustomerGroupTest.php` | covered — DataGrid-shape rows (id/code/name/isUserDefined/timestamps). Filters: code (LIKE), name (LIKE), is_user_defined. Sort: id (default desc), code, name. `{ data, meta }` envelope. |
| Admin Customer Groups — detail | `GET /api/admin/customers/groups/{id}` | `adminCustomerGroup(id:)` | `Admin/RestApi/CustomerGroupTest.php` | `Admin/GraphQL/CustomerGroupTest.php` | covered — slim row plus detail-only `customersCount`. `requirements: ['id' => '\d+']`. |
| Admin Customer Groups — create | `POST /api/admin/customers/groups` | `createAdminCustomerGroup` | `Admin/RestApi/CustomerGroupTest.php` | `Admin/GraphQL/CustomerGroupTest.php` | covered — body `{ code, name }`. Validates code required + unique + Code rule, name required. Always sets `is_user_defined = 1` (system groups cannot be created via API). Fires `customer.customer_group.create.{before,after}`. Permission: `customers.groups.create`. |
| Admin Customer Groups — update | `PUT /api/admin/customers/groups/{id}` | `updateAdminCustomerGroup` | `Admin/RestApi/CustomerGroupTest.php` | `Admin/GraphQL/CustomerGroupTest.php` | covered — partial; code uniqueness excludes current id. **System groups (`is_user_defined=0`) refuse code/is_user_defined changes → 422**; only `name` is editable. Fires `customer.customer_group.update.{before,after}`. Permission: `customers.groups.edit`. |
| Admin Customer Groups — delete | `DELETE /api/admin/customers/groups/{id}` | `deleteAdminCustomerGroup` | `Admin/RestApi/CustomerGroupTest.php` | `Admin/GraphQL/CustomerGroupTest.php` | covered — refuses if `is_user_defined=0` (system group, 400) or `customers()->count() > 0` (in use, 400). Fires `customer.customer_group.delete.{before,after}`. Permission: `customers.groups.delete`. |
| Admin Customer Groups — mass delete | `POST /api/admin/customers/groups/mass-delete` | `createAdminCustomerGroupMassDelete` | `Admin/RestApi/CustomerGroupTest.php` | `Admin/GraphQL/CustomerGroupTest.php` | covered — per-id system-group + in-use guards skip with reason instead of aborting; returns `{ deleted, skipped, message }`. Empty indices → 422. |
| Admin Customer Reviews — listing | `GET /api/admin/customers/reviews` | `adminCustomerReviews` (cursor) | `Admin/RestApi/CustomerReviewTest.php` | `Admin/GraphQL/CustomerReviewTest.php` | covered (Block C C3) — moderation listing built on top of storefront `product_reviews`. Filters: status, rating, product_id, customer_id, created_at range. Sort: id (default desc), rating, created_at. `{ data, meta }` envelope. **2026-06-24 — migrated POPO→Eloquent (see detail row): `product`/`customer` are now typed objects, `images` a connection over GraphQL. BREAKING: flat `productName`/`productSku`/`customerName`/`customerEmail` dropped on both transports.** **2026-06-25 GraphQL listing-500 fix: a guest review (null `customer_id`) or a review whose customer/product was deleted made the non-nullable `customer`/`product` to-one return null → "Internal server error" that nulled the whole node. Fixed by adding `AdminCustomerReview::customer` + `::product` to the scoped nullable-to-one allow-list (same `NullableToOnePropertyMetadataFactory` mechanism as `AdminCustomer::group`) — a missing owner/product now resolves as null instead of 500ing the row. Regression test added (guest review with null customer lists cleanly).** |
| Admin Customer Reviews — detail | `GET /api/admin/customers/reviews/{id}` | `adminCustomerReview(id:)` | `Admin/RestApi/CustomerReviewTest.php` | `Admin/GraphQL/CustomerReviewTest.php` | covered — eager-loads `product`, `customer`, `images`. **2026-06-24 — migrated POPO→Eloquent: `images` is now a field-selectable Relay connection (`images { edges { node { … } } }`), and `product { id name sku }` + `customer { id name email }` are typed objects over GraphQL (flat arrays over REST via the `output:` DTO). BREAKING: the flat `productName`/`productSku`/`customerName`/`customerEmail` scalars were dropped on BOTH transports — read them from the nested `product { name sku }` / `customer { name email }` objects instead.** `requirements: ['id' => '\d+']`. |
| Admin Customer Reviews — update status | `PUT /api/admin/customers/reviews/{id}` | `updateAdminCustomerReview` | `Admin/RestApi/CustomerReviewTest.php` | `Admin/GraphQL/CustomerReviewTest.php` | covered — body `{ status: 'pending'\|'approved'\|'disapproved' }` (only status is editable; reviews originate on storefront). Invalid value → 422. Fires `customer.review.update.{before,after}`. Permission: `customers.reviews.edit`. |
| Admin Customer Reviews — delete | `DELETE /api/admin/customers/reviews/{id}` | `deleteAdminCustomerReview` | `Admin/RestApi/CustomerReviewTest.php` | `Admin/GraphQL/CustomerReviewTest.php` | covered — fires `customer.review.delete.{before,after}`. Permission: `customers.reviews.delete`. |
| Admin Customer Reviews — mass delete | `POST /api/admin/customers/reviews/mass-delete` | `createAdminCustomerReviewMassDelete` | `Admin/RestApi/CustomerReviewTest.php` | `Admin/GraphQL/CustomerReviewTest.php` | covered — empty indices → 422. Returns `{ deleted, skipped, message }`. |
| Admin Customer Reviews — mass update status | `POST /api/admin/customers/reviews/mass-update-status` | `createAdminCustomerReviewMassUpdateStatus` | `Admin/RestApi/CustomerReviewTest.php` | `Admin/GraphQL/CustomerReviewTest.php` | covered — body `{ indices: int[], value: 'pending'\|'approved'\|'disapproved' }`. Invalid `value` or empty indices → 422. Permission: `customers.reviews.edit`. |
| Admin Customer GDPR — listing | `GET /api/admin/customers/gdpr-requests` | `adminCustomerGdprRequests` (cursor) | `Admin/RestApi/CustomerGdprTest.php` | `Admin/GraphQL/CustomerGdprTest.php` | covered (Block C C4) — datagrid parity. Filters: status, type, customer_id, email, customer_name, created_at range. Sort: id (default desc), status, type, created_at. `{ data, meta }` envelope. |
| Admin Customer GDPR — detail | `GET /api/admin/customers/gdpr-requests/{id}` | `adminCustomerGdprRequest(id:)` | `Admin/RestApi/CustomerGdprTest.php` | `Admin/GraphQL/CustomerGdprTest.php` | covered — single request with customer name resolved. `requirements: ['id' => '\d+']`. |
| Admin Customer GDPR — update | `PUT /api/admin/customers/gdpr-requests/{id}` | `updateAdminCustomerGdprRequest` | `Admin/RestApi/CustomerGdprTest.php` | `Admin/GraphQL/CustomerGdprTest.php` | covered — partial body `{ status?, message? }`. Invalid status → 422 (allowed: pending, processing, declined, approved, revoked). Fires `customer.gdpr-request.update.{before,after}` / `customer.account.gdpr-request.update.after` so the StatusUpdateNotification email listener still fires. Permission: `customers.gdpr_requests.edit`. |
| Admin Customer GDPR — delete | `DELETE /api/admin/customers/gdpr-requests/{id}` | `deleteAdminCustomerGdprRequest` | `Admin/RestApi/CustomerGdprTest.php` | `Admin/GraphQL/CustomerGdprTest.php` | covered — hard delete. Permission: `customers.gdpr_requests.delete`. |
| Admin Customer GDPR — process | `POST /api/admin/customers/gdpr-requests/{id}/process` | `createAdminCustomerGdprProcess` | `Admin/RestApi/CustomerGdprTest.php` | `Admin/GraphQL/CustomerGdprTest.php` | covered — sets status `approved` and, for `type=delete`, cascades `CustomerRepository::delete` (fires `customer.delete.{before,after}`). For `type=update`, marks approved without touching the customer (admin applies the edit through the regular Customer update endpoint). Refuses already-approved/revoked requests (422). GraphQL input uses `requestId` (the `id` field is shadowed by API Platform's `_id` rewrite on mutation inputs). Permission: `customers.gdpr_requests.edit`. **2026-06-24 — GraphQL null fix: camelCase POPO without `AcceptsCamelCaseWrites` → multi-word result fields (`requestId`/`customerId`/`customerDeleted`/`processedAt`/etc.) resolved null over GraphQL; converted to snake props + the trait (processor untouched, REST unaffected). Missed by the 2026-06-09 sweep.** |
| Admin Customer GDPR — download data | `POST /api/admin/customers/{customerId}/gdpr-download-data` | `createAdminCustomerGdprDownloadData` | `Admin/RestApi/CustomerGdprTest.php` | `Admin/GraphQL/CustomerGdprTest.php` | covered — ad-hoc (not bound to a GDPR request). Returns `{ customerId, customerEmail, generatedAt, data: { customer, addresses, orders, reviews, wishlist, notes } }`. `password` / `remember_token` stripped. Permission: `customers.gdpr_requests.view`. **2026-06-24 — GraphQL null fix: camelCase POPO without `AcceptsCamelCaseWrites` → `customerId`/`customerEmail`/`generatedAt` resolved null over GraphQL; converted to snake props + the trait (processor untouched, REST unaffected). Missed by the 2026-06-09 sweep.** |
| Dashboard — stats | `GET /api/admin/dashboard/stats` | `statsAdminDashboard` query | `Admin/RestApi/DashboardTest.php` | `Admin/GraphQL/DashboardTest.php` | covered (Block D) — `?type=` picks one of `Webkul\Admin\Helpers\Dashboard`'s 7 stat groups (default `over-all`). `?start=` / `?end=` (ISO) + `?channel=` (code) bound the window. Returns `[{ type, dateRange, statistics }]`. **GraphQL field auto-derived** from `Query(name: 'stats')` + resource shortname → `statsAdminDashboard` (the spec's hypothetical `adminDashboardStats` would have required a manual GraphQL builder override; we followed the project's auto-naming convention instead). |
| Reporting — overview | `GET /api/admin/reporting/stats` | `statsAdminReportingOverview` query | `Admin/RestApi/ReportingTest.php` | `Admin/GraphQL/ReportingTest.php` | covered (Block E) — `?type=` picks headline stats (`total-sales` default / `total-orders` / `total-customers` / `top-selling-products-by-revenue`). |
| Reporting — sales | `GET /api/admin/reporting/sales` | `statsAdminReportingSales` query | `Admin/RestApi/ReportingTest.php` | `Admin/GraphQL/ReportingTest.php` | covered (Block E) — 10 stat groups mirroring `Reporting/SaleController` (`total-sales`, `average-sales`, `total-orders`, `purchase-funnel`, `abandoned-carts`, `refunds`, `tax-collected`, `shipping-collected`, `top-payment-methods`, `sales-by-coupon`). `sales-by-coupon` added 2026-07-06 (2.4.x parity — was missing from the 2.3.8 port). |
| Reporting — customers | `GET /api/admin/reporting/customers` | `statsAdminReportingCustomers` query | `Admin/RestApi/ReportingTest.php` | `Admin/GraphQL/ReportingTest.php` | covered (Block E) — 5 stat groups (`total-customers`, `customers-with-most-sales`, `customers-with-most-orders`, `customers-with-most-reviews`, `top-customer-groups`). |
| Reporting — products | `GET /api/admin/reporting/products` | `statsAdminReportingProducts` query | `Admin/RestApi/ReportingTest.php` | `Admin/GraphQL/ReportingTest.php` | covered (Block E) — 7 stat groups (`total-sold-quantities`, `total-products-added-to-wishlist`, `top-selling-products-by-revenue`, `top-selling-products-by-quantity`, `products-with-most-reviews`, `last-search-terms`, `top-search-terms`). |
| Reporting — View Details (table form) | `GET /api/admin/reporting/{sales,customers,products}/view?type=` | `viewStatsAdminReporting{Sales,Customers,Products}` query | `Admin/RestApi/ReportingTest.php` | `Admin/GraphQL/ReportingTest.php` | covered (2026-06-09) — the admin "View Details" link behind every reporting panel (`Reporting/Controller::viewStats`). Same `?type=` map as the stats endpoint but the helper is called in **table mode** (`'table'` arg), so `statistics` is `{ columns: [{key,label}], records: [...] }` (the full list, not the top-N summary). Mirrors `stats` for auth (no permission gate). REST view providers `AdminReporting{Sales,Customers,Products}ViewProvider` (subclass the stats providers, set `$mode='table'`); GraphQL `AdminReporting*ViewResolver`. Base `AdminReportingProvider::buildPayload($entity, $type, $mode)` gained the `$mode` param. |
| Reporting — export (CSV) | `GET /api/admin/reporting/{sales,customers,products}/export?type=&format=csv` | — (REST only) | `Admin/RestApi/ReportingTest.php` | — | covered (2026-06-09) — the Export button on each reporting sub-page (`Reporting/Controller::export`). Streams the **table-form** stat (`{columns, records}`) as a `text/csv` attachment `{entity}-{type}.csv`: column `label`s become the header row, each record's column-`key` values the data rows. `?format=` accepts only `csv` (else 422). REST only (binary). `AdminReportingExportProvider` base + 3 entity subclasses. No ACL gate (reporting is open to any admin) — auth only. Send `Accept: text/csv`. **Note:** the admin's Maatwebsite-Excel export supports xls/xlsx too; the API ships csv only (parity with the project's CSV-export convention). |
| Reporting — GraphQL `dateRange` fix | (all reporting queries) | `statsAdminReporting*` / `viewStatsAdminReporting*` | — | `Admin/GraphQL/ReportingTest.php` | fixed (2026-06-09) — `dateRange` returned `null` over GraphQL on every reporting query (the camelCase-resolution quirk — the resolver-built resource used a camelCase `$dateRange` prop). All 4 reporting resources (`AdminReportingOverview/Sales/Customers/Products`) converted to a snake_case `$date_range` prop + `AcceptsCamelCaseWrites`; the resolver's `$resource->dateRange = …` write is mapped by the trait. REST output is unchanged (the REST provider returns a plain array keyed `dateRange`). Regression test: `ReportingTest::test_date_range_resolves_over_graphql`. **The same `dateRange` bug still exists on `statsAdminDashboard`** (Dashboard menu, `$dateRange` is `?string`) — a one-line follow-up when the Dashboard menu is QA'd. |
| Customer active cart items (Create-Order sidebar) | `GET /api/admin/customers/{customerId}/cart-items` | `adminCustomerCartItems(customerId:)` query | `Admin/RestApi/CustomerSidebarTest.php` | `Admin/GraphQL/CustomerSidebarTest.php` | covered — items in the customer's **own** active storefront cart (`carts.is_active = 1`), top-level only. Distinct from the admin draft cart. |
| Customer wishlist items (Create-Order sidebar) | `GET /api/admin/customers/{customerId}/wishlist-items` | `adminCustomerWishlistItems(customerId:)` query | `Admin/RestApi/CustomerSidebarTest.php` | `Admin/GraphQL/CustomerSidebarTest.php` | covered |
| Customer recent order items (Create-Order sidebar) | `GET /api/admin/customers/{customerId}/recent-order-items` | `adminCustomerRecentOrderItems(customerId:)` query | `Admin/RestApi/CustomerSidebarTest.php` | `Admin/GraphQL/CustomerSidebarTest.php` | covered — up to 5 most-recent distinct ordered products. |
| Admin draft cart — read | `GET /api/admin/carts/{id}` | `adminCart(id: ID!)` query | `Admin/RestApi/CartTest.php` | `Admin/GraphQL/CartTest.php` | covered — full payload (items, totals, addresses, selected shipping/payment). Only `is_active = 0` carts are accessible; storefront carts (`is_active = 1`) → 403 via `AdminCartGuard`. |
| Admin draft cart — add item | `POST /api/admin/carts/{id}/items` | `addItemAdminCart` mutation | `Admin/RestApi/CartTest.php` | `Admin/GraphQL/CartTest.php` | covered — forwards full request body to `Cart::addProduct` so every product type (simple/configurable/bundle/grouped/downloadable) works; returns updated cart. **Booking products are explicitly blocked** with `InvalidInputException` (HTTP 400 / GraphQL `errors[]`) — matches Bagisto monolith Create-Order (which ships no booking partial). **Non-saleable guard (2026-06-08 fix — the Create-Order flow killer):** `AdminCartAddItemProcessor` now checks `$product->getTypeInstance()->isSaleable()` **up front** (right after the booking check, BEFORE `Cart::setCart`/`addProduct`) and throws `InvalidInputException` (HTTP 400, `app.admin.cart.product-not-saleable`) for an out-of-stock/disabled product. **Why this was critical:** previously the processor added the non-saleable item, then `Cart::collectTotals()` → core `validateItems()` removed the invalid item and **garbage-collected the now-empty draft cart** (`removeCart`) — so a misleading `201` came back AND the cart was deleted, 404-ing every subsequent Create-Order step (GET cart / addresses / shipping / payment / place-order). The up-front guard rejects with a clear error and **preserves the draft cart** (it's never touched), so the admin can add a different product. Regression: `CartTest::test_add_non_saleable_product_returns_400_and_preserves_cart` (disables via the `status` EAV `boolean_value=0`, asserts 400 + the cart still GETs 200). `isSaleable()` reads the EAV `status` (attribute_id 8), NOT `product_flat.status` — flipping the flat doesn't affect it. The configurable-variant edge (parent saleable but chosen variant out of stock) still falls through to core validation; the guard covers the reported simple-product case. |
| Products — search/listing (admin) | `GET /api/admin/products` | `adminProducts` query (cursor) | `Admin/RestApi/ProductTest.php` | `Admin/GraphQL/ProductTest.php` | covered — slim picker row (id/sku/type/name/status/price/formattedPrice/baseImageUrl/isSaleable). Returns ALL statuses by default (no auto enabled-only filter — admin must see disabled products); optional `?status=0\|1`. Filters: `query` (sku OR name partial), `sku` exact, `type`, `categoryId`, `channel`, `locale`. `?per_page` default 30, cap 50. Booking products ARE listed here (so admin can find them) but blocked at admin cart add-to-cart time — see add-item row. Detail endpoint deferred. |
| Catalog Products — datagrid listing | `GET /api/admin/catalog/products` | `adminCatalogProducts` (cursor) | `Admin/RestApi/CatalogProductTest.php` | `Admin/GraphQL/CatalogProductTest.php` | covered — DataGrid parity (1:1 with `Webkul\Admin\DataGrids\Catalog\ProductDataGrid`). **2026-06-05 Sales-template pass:** the listing now surfaces the cheap `product_flat` scalar columns too (`shortDescription`/`description`/`metaTitle`/`metaDescription`/`metaKeywords`/`weight`/`featured`/`new`/`createdAt`/`updatedAt`) on top of the original 18 datagrid fields — added to `AdminCatalogProductCollectionProvider::buildQuery()` select + `mapRow()` (no new joins, no N+1, same `groupBy(product_id)`). Genuinely heavy fields stay detail-only and null on the listing: the relations (`translations`/`images`/`categories`/`inventories`/`customerGroupPrices`/`variants`/`bundleOptions`/`linkedProducts`/`downloadable*`/`superAttributes`/`videos`/etc.) AND `manageStock`/`taxCategoryId` (EAV attribute-value lookups) and `inStock` (per-row `isSaleable()` — would be N+1). Tagged `Admin Catalog: Products`. DB path only; **Elasticsearch branch deferred** to Phase 1.1.b. 11 filters, 9 sort columns. **2026-06-08 — special-price columns added to the listing:** `specialPrice` / `formattedSpecialPrice` / `specialPriceFrom` / `specialPriceTo` (cheap `product_flat` scalars; `*From`/`*To` are null unless a dated window is set) — added to the `buildQuery()` select + `mapRow()` + 4 snake_case props on `AdminCatalogProduct`. **This datagrid query is the canonical admin "List Products"** (docs renamed the sidebar entry `Catalog Products (Datagrid)` → **List Products**); the slim `adminProducts` / `GET /api/admin/products` picker is the Create-Order "Add Product" search, NOT the product listing. |
| Catalog Products — create (step 1, all 7 types) | `POST /api/admin/catalog/products` | `createAdminCatalogProduct` mutation | `Admin/RestApi/CatalogProductTest.php` | `Admin/GraphQL/CatalogProductTest.php` | covered (Phases 5.3-5.8 + 5.8-booking) — **all 7 types**: simple / virtual / downloadable / grouped / bundle / configurable / booking. Mirrors Bagisto core `ProductController::store` minimal-input path: body `{ sku, attribute_family_id, type?, super_attributes? }` (type defaults to `simple`; `super_attributes` required for `configurable`). Validates sku required + unique + Slug regex, attribute_family_id required + exists. For `configurable`, `super_attributes` must be a non-empty map of attribute code (or id) → option_ids; the core repo generates the full Cartesian-product of variants. For `booking`, the 5 sub-types (default/appointment/event/rental/table) plus slots/tickets are set during the step-2 update (Phase 5.9). Fires `catalog.product.create.before/after`. Returns the full `AdminCatalogProduct` detail payload. Permission: `catalog.products.create`. |
| Catalog Products — copy | `POST /api/admin/catalog/products/{sourceId}/copy` | `createAdminCatalogProductCopy` mutation | `Admin/RestApi/CatalogProductTest.php` | `Admin/GraphQL/CatalogProductTest.php` | covered (Phase 5.2) — mirrors monolith `ProductController::copy`. Fires `catalog.product.create.before/after`, calls `ProductRepository::copy($id)`. Refuses variants (`parent_id != null`) with HTTP 422 (pre-checked before the repository call so the message is package-translated, not the core `trans()` string). Permission gate: `catalog.products.create`. Returns `{ id, sourceId, sku, type, name, success, message }` — the new product id + auto-suffixed sku. |
| Catalog Products — detail | `GET /api/admin/catalog/products/{id}` | `adminCatalogProduct(id:)` | `Admin/RestApi/CatalogProductDetailTest.php` | `Admin/GraphQL/CatalogProductDetailTest.php` | covered — type-aware payload across all 6 product types. **Already conforms to the Sales template** (verified 2026-06-05): the detail returns the `AdminCatalogProduct` resource **itself** (no `output:` DTO), so the GraphQL `id` IRI resolves to `/api/admin/catalog/products/{id}` (NOT the default `admin_catalog_products` — the docs were corrected to match), and the plain-array nested blocks (`translations`/`images`/`categories`/`inventories`/`customerGroupPrices` + type-specific `superAttributes`+`variants`/`bundleOptions`/`linkedProducts`/`downloadable*`) **do resolve over GraphQL** when queried bare (the old docs `::: warning` claiming they return null was wrong, now fixed). Base ~25 fields; type-specific blocks null on non-matching types. Tagged `Admin Catalog: Products`. **2026-06-08 — edit-page parity pass:** (1) `channels` now lists **every available channel** as `{id, code, name, assigned}` (was only the assigned ones) — mirrors the edit-screen Channels checkbox box; singular `channel` unchanged (the saved channel code). (2) New **`attributes`** block surfaces the product's **attribute-family field set field-for-field** (the dynamic General/Description/Meta/Settings/Price fields the edit screen renders — `product_number`/`color`/`size`/`brand`/`GST`/`meta_*`/`visible_individually`/`guest_checkout`/etc.), each entry `{id, code, adminName, type, isRequired, valuePerChannel, valuePerLocale, groupCode, groupName, value, options}`. Built by reusing the **exact** core edit-form path — iterate `attribute_family->attribute_groups` → `$product->getEditableAttributes($group)` (super-attrs excluded, surfaced under `superAttributes`) → value via `$product->getCustomAttributeValue($attribute)` (channel/locale-aware) — so empty fields **appear** with `value: null` (the previous "missing family fields" gap: those attrs were never emitted, not null-stripped — REST already kept declared nulls). select/multiselect/checkbox carry their `options` inline. The standard top-level flat fields (`sku`/`status`/`urlKey`/…) are kept as conveniences and also appear in `attributes` (intentional duplication = parity). Plain `?array` → bare JSON in GraphQL (query `attributes`/`channels` without sub-selection), inline in REST. |
| Catalog Products — update | `PUT /api/admin/catalog/products/{id}` | `updateAdminCatalogProduct` mutation | `Admin/RestApi/CatalogProductTest.php` | `Admin/GraphQL/CatalogProductTest.php` | covered (Phase 5.9; **reworked 2026-06-08 — three real bugs fixed, see below**). True partial PATCH: send only the fields you change; every attribute on the product's family is editable **by its code** (`name`/`url_key`/`short_description`/`description`/`meta_*`/`color`/`size`/`brand`/`product_number`/`price`/`weight`/`status`/…). Translatable values write to the requested locale — pass `?locale=fr&channel=default` (defaults to store default; one locale per request, mirroring the admin form). Type-structure keys accepted for the matching type and **replace** that structure: `variants` (configurable), `bundle_options` (bundle), `links` (grouped), `downloadable_links`/`downloadable_samples` (downloadable), `booking` (booking), `customizable_options`. `categories`/`channels`/`up_sells`/`cross_sells`/`related_products` replace the current set when sent, preserved when omitted. `images`/`videos`/`inventories`/`customer_group_prices` have dedicated endpoints, ignored here (in `_warnings`). Fires `catalog.product.update.before/after`. Permission: `catalog.products.edit`. Returns the full detail payload. **2026-06-08 — the update was fundamentally broken; root-caused against core `ProductAttributeValueRepository::saveValues` + `AbstractType::update` and fixed:** (1) **deserializer blocked unknown top-level keys** → any family attribute code (even `name`) gave HTTP 500 "input data is misformatted" → fixed with **`deserialize: false`** on the REST `Put` so the processor reads the raw body (GraphQL keeps the typed input; custom attrs go via the `extras` JSON catch-all, which the processor unpacks to top-level). (2) **Core reads values by top-level code + `$data['locale']`/`['channel']`, NOT nested `translations`** → the old `translations:{en:{…}}`-nesting silently no-op'd (name/url_key/meta never persisted) → fixed by writing top-level codes to the resolved request locale/channel (`translations:{<locale>:{…}}` still accepted as a convenience: the request-locale block is flattened, other locales get a `_warnings` "use ?locale=" hint). (3) **Core force-writes booleans (unset→0) + multiselects (unset→'') + syncs channels/categories/up·cross·related-sells (unset→wiped) + deletes customer_group_prices (unset→all gone)** → a partial update wiped half the product → fixed with a **two-path processor**: when NO type-structure key is present it uses the core's **surgical 3rd-arg** `ProductRepository::update($data, $id, $attributeCodes)` (writes only the sent attribute values, early-returns before any sync, so structures/relations/inventory/CGP are untouched) and **explicitly `sync()`s only the relation lists the caller sent**; when a structure key IS present it runs the full-form update with `mergeCurrentState()` first reconstructing booleans/multiselects/relations/CGP from the product's current values (so the structure replaces and nothing else is lost). Verified live + tested across all 7 types: family attrs + custom attrs persist, partial updates preserve booleans + variants/bundle-options + categories, `?locale=` targets a locale, GraphQL `extras` path persists. Regression: `test_update_persists_family_attributes_via_refetch` / `test_partial_update_does_not_wipe_booleans` / `test_attribute_only_update_preserves_variants` / `test_update_with_custom_attribute_codes_does_not_500`. **Lesson: the core product update assumes a FULL admin-form submission — any API partial update must use the surgical 3rd-arg for attribute-only edits and reconstruct current state for the full-form (structure) path.** |
| Catalog Products — delete | `DELETE /api/admin/catalog/products/{id}` | `deleteAdminCatalogProduct` mutation | `Admin/RestApi/CatalogProductTest.php` | `Admin/GraphQL/CatalogProductTest.php` | covered (Phase 5.10) — no "refuse if in order" guard (mirrors monolith `ProductController::destroy`). 204 on success / 404 on miss. Fires `catalog.product.delete.before/after`. Permission: `catalog.products.delete`. |
| Catalog Products — mass-delete | `POST /api/admin/catalog/products/mass-delete` | `createAdminCatalogProductMassDelete` mutation | `Admin/RestApi/CatalogProductTest.php` | `Admin/GraphQL/CatalogProductTest.php` | covered — best-effort batch delete. Silently skips missing IDs; surfaces HTTP 500 with the underlying message if any single delete throws. **No pre-validation guard** (unlike Categories, which rejects channel-root IDs up front — Products has no equivalent "undeletable" rule). Permission: `catalog.products.delete`. Fires `catalog.product.delete.{before,after}` per ID. |
| Catalog Products — inventories (list) | `GET /api/admin/catalog/products/{productId}/inventories` | `adminCatalogProductInventories(productId:)` (cursor) | `Admin/RestApi/CatalogProductInventoryTest.php` | `Admin/GraphQL/CatalogProductInventoryTest.php` | covered (Phase 5.12) — per-source rows `{ id, sourceId, sourceCode, sourceName, qty }` wrapped in the `{ data, meta }` envelope. `meta.totalQty` is the sum across all sources (contributed by the inventory paginator via the new `getExtraMeta()` hook on `AdminCollectionEnvelopeNormalizer`). Empty product → `data: []`, `totalQty: 0`. 404 if the product is unknown. |
| Catalog Products — inventories (bulk update) | `PUT /api/admin/catalog/products/{productId}/inventories` | `updateAdminCatalogProductInventory` mutation | `Admin/RestApi/CatalogProductInventoryTest.php` | `Admin/GraphQL/CatalogProductInventoryTest.php` | covered (Phase 5.12) — mirrors monolith `ProductController::updateInventories`. Body `{ inventories: { <sourceId>: <qty>, ... } }`. Calls `ProductInventoryRepository::saveInventories`; fires `catalog.product.update.{before,after}`. Validation: `inventories` required (422), each source id must exist in `inventory_sources` (422), each qty must be non-negative integer (422). Pass `qty=0` to zero-out a source; sources omitted from the map are left untouched. Permission: `catalog.products.edit`. Returns the refreshed listing payload — same shape as GET. **2026-06-25 GraphQL fix:** the mutation 500'd over GraphQL ("Cannot return null for non-nullable field id"); it now returns a resolvable single node — query `adminCatalogProductInventory { _id sourceId sourceCode sourceName qty }` (select `_id`, not the routeless `id` IRI). The full per-source list + total stays the `adminCatalogProductInventories(productId:)` connection. REST unaffected. |
| Catalog Products — customer-group prices (list) | `GET /api/admin/catalog/products/{productId}/customer-group-prices` | `adminCatalogProductCustomerGroupPrices(productId:)` | `Admin/RestApi/CatalogProductCustomerGroupPriceTest.php` | `Admin/GraphQL/CatalogProductCustomerGroupPriceTest.php` | covered (Phase 5.13) — rows `{ id, productId, qty, valueType, value, customerGroupId, customerGroupName }` ordered by qty asc, wrapped in `{ data, meta }`. 404 on unknown product. **2026-06-25 GraphQL fix:** this resource was a camelCase POPO (missed by the 2026-06-05 catalog sweep), so `productId`/`valueType`/`customerGroupId`/`customerGroupName` resolved **null** over GraphQL; fixed with snake props + `AcceptsCamelCaseWrites`. The GraphQL list is a **plain list** (not a Relay connection). REST unaffected. |
| Catalog Products — customer-group prices (create) | `POST /api/admin/catalog/products/{productId}/customer-group-prices` | `createAdminCatalogProductCustomerGroupPrice` mutation | `Admin/RestApi/CatalogProductCustomerGroupPriceTest.php` | `Admin/GraphQL/CatalogProductCustomerGroupPriceTest.php` | covered (Phase 5.13) — body `{ qty (>=1), value_type ('fixed'\|'discount'), value (>=0), customer_group_id (int\|null) }`. `customer_group_id = null` means "applies to all groups". `(qty, customer_group_id)` must be unique per product (422). Computes `unique_id = "{qty}\|{product_id}[\|{customer_group_id}]"`. Fires `catalog.product.update.{before,after}`. Permission: `catalog.products.edit`. **2026-06-25 GraphQL fix:** the multi-word result fields now resolve over GraphQL (snake props + `AcceptsCamelCaseWrites`). REST unaffected. |
| Catalog Products — customer-group prices (update) | `PUT /api/admin/catalog/products/{productId}/customer-group-prices/{id}` | `updateAdminCatalogProductCustomerGroupPrice` mutation | `Admin/RestApi/CatalogProductCustomerGroupPriceTest.php` | `Admin/GraphQL/CatalogProductCustomerGroupPriceTest.php` | covered (Phase 5.13) — partial update; re-validates the new `(qty, customer_group_id)` combination. Row must belong to `{productId}` (404 otherwise). Recomputes `unique_id`. **2026-06-25 GraphQL fix:** the multi-word result fields now resolve over GraphQL (snake props + `AcceptsCamelCaseWrites`). REST unaffected. |
| Catalog Products — customer-group prices (delete) | `DELETE /api/admin/catalog/products/{productId}/customer-group-prices/{id}` | `deleteAdminCatalogProductCustomerGroupPrice` mutation | `Admin/RestApi/CatalogProductCustomerGroupPriceTest.php` | `Admin/GraphQL/CatalogProductCustomerGroupPriceTest.php` | covered (Phase 5.13) — returns 200 + `{ message }`. Cross-product id → 404. **2026-06-25 GraphQL fix:** the GraphQL delete returned an unusable payload; now returns the deleted row via the delete-snapshot pattern — select `_id` (the routeless resource has no resolvable `id` IRI). REST unaffected. |
| Catalog Products — mass-update-status | `POST /api/admin/catalog/products/mass-update-status` | `createAdminCatalogProductMassUpdateStatus` mutation | `Admin/RestApi/CatalogProductTest.php` | `Admin/GraphQL/CatalogProductTest.php` | covered — best-effort bulk status flip. Body `{ indices: int[], value: 0\|1 }`. Validates `indices` (400 if empty) and `value` (400 if not 0/1). Permission: `catalog.products.edit`. Fires `catalog.product.update.{before,after}` per ID and delegates to `ProductRepository::update(['status' => $value], $id, ['status'])` — matches monolith line-for-line. Test fixtures write/read `status` from `product_flat` (the column lives there, not on `products`). |
| Admin draft cart — bulk-update qty | `PUT /api/admin/carts/{id}/items` | `updateItemsAdminCart` mutation | `Admin/RestApi/CartTest.php` | `Admin/GraphQL/CartTest.php` | covered — body shape `{ qty: { itemId: newQty, ... } }` mirrors monolith. |
| Admin draft cart — remove item | `DELETE /api/admin/carts/{id}/items` | `removeItemAdminCart` mutation | `Admin/RestApi/CartTest.php` | `Admin/GraphQL/CartTest.php` | covered — body: `{ cartItemId }`. Returns 200 + updated cart (not 204) by setting `status: 200` on the Delete operation. |
| Admin draft cart — save addresses | `POST /api/admin/carts/{id}/addresses` | `saveAddressAdminCart` mutation | `Admin/RestApi/CartTest.php` | `Admin/GraphQL/CartTest.php` | covered — body shape `{ billing: { firstName,...,useForShipping }, shipping? }`. CamelCase keys normalised to snake_case for `Cart::saveAddresses`. |
| Admin draft cart — apply coupon | `POST /api/admin/carts/{id}/coupon` | `applyCouponAdminCart` mutation | `Admin/RestApi/CartTest.php` | `Admin/GraphQL/CartTest.php` | covered — mirrors monolith status codes: 404 unknown/inactive (`ResourceNotFoundException`), 422 already-applied (`ValidationException`), 200 success. |
| Admin draft cart — remove coupon | `DELETE /api/admin/carts/{id}/coupon` | `removeCouponAdminCart` mutation | `Admin/RestApi/CartTest.php` | `Admin/GraphQL/CartTest.php` | covered — idempotent. Returns 200 + updated cart. |
| Create-Order — bootstrap fresh draft cart | `POST /api/admin/customers/{customerId}/draft-carts` | `createAdminDraftCart` mutation | `Admin/RestApi/DraftCartTest.php` | `Admin/GraphQL/DraftCartTest.php` | covered — customer-nested URL spawns an empty `is_active=0` draft cart for the customer. Distinct from Reorder. Unknown / zero customer → 404; underlying `Cart::createCart` failure → 422 (no 2xx success=false). |
| Admin draft cart — list shipping methods | `GET /api/admin/carts/{cartId}/shipping-methods` | `adminCartShippingRates(cartId:)` query | `Admin/RestApi/CheckoutTest.php` | `Admin/GraphQL/CheckoutTest.php` | covered — flattens `Shipping::collectRates()` into per-rate rows in the `{ data, meta }` envelope. Addresses must be saved (409 otherwise). |
| Admin draft cart — set shipping method | `POST /api/admin/carts/{cartId}/shipping-methods` | `setShippingMethodAdminCart` mutation | `Admin/RestApi/CheckoutTest.php` | `Admin/GraphQL/CheckoutTest.php` | covered — body: `{ shippingMethod }`. Sequence 409 (items + both addresses required). |
| Admin draft cart — list payment methods | `GET /api/admin/carts/{cartId}/payment-methods` | `adminCartPaymentMethods(cartId:)` query | `Admin/RestApi/CheckoutTest.php` | `Admin/GraphQL/CheckoutTest.php` | covered — `Payment::getSupportedPaymentMethods()` in envelope. Shipping must be selected (409 otherwise). |
| Admin draft cart — set payment method | `POST /api/admin/carts/{cartId}/payment-methods` | `setPaymentMethodAdminCart` mutation | `Admin/RestApi/CheckoutTest.php` | `Admin/GraphQL/CheckoutTest.php` | covered — body: `{ method }`. Sequence 409 (items + addresses + shipping required). |
| Admin Place Order | `POST /api/admin/orders/place/{cartId}` | `createAdminPlaceOrder` mutation | `Admin/RestApi/PlaceOrderTest.php` | `Admin/GraphQL/PlaceOrderTest.php` | covered — finalises a fully prepared draft cart. Sequence 409 chain (empty / addresses / shipping / payment) + 422 unsupported-payment (only `cashondelivery` / `moneytransfer`). |
| Orders — Cancel action | `POST /api/admin/orders/{id}/cancel` | `createAdminCancelOrder` mutation | `Admin/RestApi/OrderCancelTest.php` | `Admin/GraphQL/OrderCancelTest.php` | covered — runs the 4-check `AdminOrderActionGuard::assertCanCancel` (closed/fraud/nothing-to-cancel/no-permission), then `OrderRepository::cancel`. Returns the updated `OrderDetail` payload (same shape as `GET /api/admin/orders/{id}`) so the client can refresh the order-view screen without a follow-up GET. |
| Orders — Add Comment | `POST /api/admin/orders/{id}/comments` | `createAdminOrderComment` mutation | `Admin/RestApi/OrderCommentTest.php` | `Admin/GraphQL/OrderCommentTest.php` | covered — no permission gate (matches monolith). Fires `sales.order.comment.create.{before,after}` events so core listeners send the customer email when `customerNotified=true`. |
| Orders — List Comments | `GET /api/admin/orders/{id}/comments` | `adminOrderComments(orderId:)` query (cursor) | `Admin/RestApi/OrderCommentTest.php` | `Admin/GraphQL/OrderCommentTest.php` | covered — newest-first. REST wrapped in the `{ data, meta }` admin envelope (`per_page` default 10, cap 50); GraphQL uses native cursor pagination. **2026-06-18 two GraphQL fixes:** (1) the `QueryCollection` had **no `extraArgs`**, so the parent `orderId` could not be passed over GraphQL → every `adminOrderComments` call resolved "order not found"; added `extraArgs: ['orderId' => ['type' => 'Int!']]` (the provider reads `$context['args']['orderId']` via `AdminOrderActionGuard::resolveOrder`). (2) `AdminOrderComment` was a camelCase POPO with no `AcceptsCamelCaseWrites`, so `orderId`/`customerNotified`/`createdAt`/`updatedAt` resolved **null** over GraphQL → converted props to snake_case + the trait (provider/create-processor untouched — their camelCase writes are mapped). Node `id` (IRI) is **`null`** (no single-`{id}` Get route → `fastIri` yields null); use `_id` for the integer comment id. Regression-locked by `Admin/GraphQL/OrderCommentTest::test_list_query_resolves_all_fields` (passes `orderId`, asserts `orderId`/`customerNotified`/`createdAt` non-null + `errors` null). |
| Orders — Create Invoice | `POST /api/admin/orders/{id}/invoices` | `createAdminInvoice` mutation | `Admin/RestApi/InvoiceTest.php` | `Admin/GraphQL/InvoiceTest.php` | covered — guard splits closed / fraud / **paypal_standard-blocked** / nothing-to-invoice / no-permission (`sales.invoices.create`). Per-SKU qty validation against `qty_to_invoice` returns a message carrying both requested and available quantities. **2026-06-18 — added the admin "Create Transaction" checkbox** as input `canCreateTransaction` (GraphQL) / `can_create_transaction` (REST body), default false. The core `InvoiceRepository::create` reads the flag from the global `request()` (not the data array) and the `Admin\Listeners\Invoice` listener records an `order_transactions` row on `sales.invoice.save.after` when set — so `AdminInvoiceCreateProcessor::wantsTransaction()` resolves the flag from the DTO / GraphQL input / REST body and `request()->merge(['can_create_transaction' => '1'])` before calling the repo. No payment-method restriction (the listener captures against the order's payment method for any method). Regression: `InvoiceTest::test_create_with_transaction_flag_records_order_transaction` (REST + GraphQL — assert an `order_transactions` row is created) + REST `_without_transaction_flag_records_no_order_transaction`. |
| Orders — Get Invoice | `GET /api/admin/invoices/{id}` | `adminInvoice(id: ID!)` query | `Admin/RestApi/InvoiceTest.php` | `Admin/GraphQL/InvoiceTest.php` | covered — **2026-06-04 rebuild**: detail Get/Query/Post return the `AdminInvoice` resource itself (no `output:` DTO — `AdminInvoiceDetailDto` deleted), so the GraphQL `id` IRI resolves (`/api/admin/invoices/{id}`) instead of 500-ing. Full payload: every `invoices` column (all `base_*` / `*_incl_tax` / `shipping_tax_*` / `reminders` + `formatted*`), order/customer context (`customerName`/`customerEmail`/`orderStatus`/`orderDate`/`channelName`), embedded `billingAddress`/`shippingAddress` (plain objects), and `items` as a **plain-array** list (inline objects in REST, queryable Iterable leaf in GraphQL — was an IRI/all-null connection). Mapping shared by provider + create-processor via `State/Concerns/BuildsAdminInvoice` (which `use`s `MapsOrderActionItems` + `MapsOrderAddress`). GraphQL create wrapper renamed `adminInvoiceDetailDto`→`adminInvoice`. **This is the template** — Shipment/Refund/Transaction/Booking detail still use `output:` DTOs and must be migrated the same way when their menus are QA'd. **2026-06-05 — invoice `state` values corrected:** the real states are `paid` / `pending` / `pending_payment` / `overdue` — there is **NO `refunded` invoice state** (removed from the OpenAPI `state` filter enum + the list/get-invoice docs + the overview `state` table). The grid normally shows only **Paid** + **Pending**; the red "Overdue by N days" / "N days left" line under a Pending invoice is a **derived countdown** (`createdAt + sales.invoice_settings.payment_terms.due_duration`, compared to today) — the stored `state` stays `pending`. `overdue` as a literal state is only settable via Mass Update Status (rare). Overview (`invoices/index.md` REST+GraphQL) now also explains what each action does (Print / Send Duplicate / Mass Update Status / Export) so the list page's "How this menu works" tip is fully backed. |
| Orders — Print Invoice (PDF) | `GET /api/admin/invoices/{id}/print` | — (REST only) | `Admin/RestApi/InvoiceTest.php` | — | covered — binary `application/pdf` attachment via dompdf + `admin::sales.invoices.pdf` blade (mirrors monolith `InvoiceController::printInvoice`). One test is `markTestSkipped` because dompdf fails to render in the test env. GraphQL has no counterpart — binary streams aren't supported over GraphQL. |
| Invoices — Mass Update Status | `POST /api/admin/invoices/mass-update-status` | `createAdminInvoiceMassUpdateStatus` mutation | `Admin/RestApi/InvoiceTest.php` | `Admin/GraphQL/InvoiceTest.php` | covered — the Invoices datagrid's bulk "Update Status" action. Body `{ indices: int[], value: 'pending'\|'paid'\|'overdue' }`. Mirrors core `InvoiceController::massUpdateState` exactly (`$invoice->state = value; save()` per id — **no events, no payment capture**; it's a manual status override). Standalone one-op resource `AdminInvoiceMassUpdateStatus` (mirrors the catalog mass-action pattern). Empty indices or out-of-range value → 422. Permission: `sales.invoices.view` (the admin mass-action carries no separate ACL, so gated on the only invoice list-permission). Returns `{ updated: int[], message }`. **GraphQL quirk:** the `updated` array renders as the project-wide wrapped `{ data, meta }` structure — read `message` / verify via the row; REST returns the clean flat array. Tagged `Admin Sales: Invoices`. |
| Orders — Create Shipment | `POST /api/admin/orders/{id}/shipments` | `createAdminShipment` mutation | `Admin/RestApi/ShipmentTest.php` | `Admin/GraphQL/ShipmentTest.php` | covered — guard splits closed/fraud/nothing-to-ship/no-permission (`sales.shipments.create`). Validates per-SKU qty vs `qty_to_ship`, AND inventory availability at the chosen source (`product.inventories(inventory_source_id, sum(qty))`). |
| Orders — Get Shipment | `GET /api/admin/shipments/{id}` | `adminShipment(id: ID!)` query | `Admin/RestApi/ShipmentTest.php` | `Admin/GraphQL/ShipmentTest.php` | covered — **2026-06-05 rebuild to the Invoice template**: detail Get/Query + the per-order create Post/Mutation all return the `AdminShipment` resource itself (no `output:` DTO — `AdminShipmentListDto` + `AdminShipmentDetailDto` deleted), so the GraphQL `id` IRI resolves (`/api/admin/shipments/{id}`) instead of 500-ing. Full payload: every `shipments` column + order/customer context (`orderIncrementId`/`shippedTo`/`orderStatus`/`orderStatusLabel`/`channelName`/`customerName`/`customerEmail`/`orderDate`) + inventory source + embedded `billingAddress`/`shippingAddress` (plain `?array`) + `items` (plain array). Mapping shared by detail provider + create-processor via `State/Concerns/BuildsAdminShipment` (`use`s `MapsOrderActionItems` + `MapsOrderAddress`). **GraphQL create wrapper renamed `adminShipmentDetailDto`→`adminShipment`.** Create tagged `Admin Sales: Orders` (per-order action — see the Swagger-grouping note); detail tagged `Admin Sales: Shipments`. **2026-06-05: detail + listing now also carry the order's Payment & Shipping panel (matches the admin Shipment view) — `paymentMethod`/`paymentTitle`/`orderCurrencyCode`/`shippingMethod`/`shippingTitle`/`baseShippingAmount`/`formattedBaseShippingAmount` (shipping fields `null` when the order had no shipping method, e.g. virtual/free). Eager-load adds `order.payment`; `BuildsAdminShipment` maps the block + a `safeFormatBasePrice()` wrapper.** |
| Orders — Create Refund | `POST /api/admin/orders/{id}/refunds` | `createAdminRefund` mutation | `Admin/RestApi/RefundTest.php` | `Admin/GraphQL/RefundTest.php` | covered — guard splits closed/fraud/nothing-to-refund/no-permission (`sales.refunds.create`). Per-SKU qty vs `qty_to_refund`; computes totals via `RefundRepository::getOrderItemsRefundSummary`; rejects `amount-zero` and `amount-exceeds-max` before `RefundRepository::create`. |
| Orders — Refund Preview | `POST /api/admin/orders/{id}/refunds/preview` | `previewAdminRefund` mutation | `Admin/RestApi/RefundTest.php` | `Admin/GraphQL/RefundTest.php` | covered — same body shape as Create Refund, returns `RefundTotalsSummary` (subtotal/discount/tax/shipping/grandTotal + formatted variants). Never writes. Same eligibility gates fire. |
| Orders — Get Refund | `GET /api/admin/refunds/{id}` | `adminRefund(id: ID!)` query | `Admin/RestApi/RefundTest.php` | `Admin/GraphQL/RefundTest.php` | covered — **2026-06-04 rebuild to the Invoice template**: detail Get/Query/Post return the `AdminRefund` resource itself (no `output:` DTO — `AdminRefundListDto` + `AdminRefundDetailDto` deleted), so the GraphQL `id` IRI resolves (`/api/admin/refunds/{id}`) instead of 500-ing. Full payload: every `refunds` column (all `base_*`/`*_incl_tax`/`formatted*` + `adjustment_refund`/`adjustment_fee` + base variants + `email_sent`), order/customer context (`customerName`/`customerEmail`/`orderStatus`/`orderStatusLabel`/`orderDate`/`channelName`/`orderIncrementId`/`billedTo`), payment info (`paymentMethod`/`paymentTitle`/`shippingMethod`/`shippingTitle`), embedded `billingAddress`/`shippingAddress` (plain `?array`), and `items` as a **plain-array** list (inline in REST, bare queryable JSON in GraphQL — NOT a cursor connection). Mapping shared by detail provider + create-processor via `State/Concerns/BuildsAdminRefund` (`use`s `MapsOrderActionItems` + `MapsOrderAddress`). Tagged `Admin Sales: Refunds`. No print/email (admin Refunds screen has neither — parity). |
| Catalog Categories — listing | `GET /api/admin/catalog/categories` | `adminCatalogCategories` (cursor) | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — DataGrid parity (1:1 with `Webkul\Admin\DataGrids\Catalog\CategoryDataGrid`: 13-field row, 6 filters, 4 sort columns). Read-only; CRUD is Phase 2 of the Catalog API roadmap. |
| Catalog Categories — tree | `GET /api/admin/catalog/categories/tree` | `adminCatalogCategoryTrees` (cursor over roots) | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — full nested category tree via Kalnoy\Nestedset `toTree()`. Filters: `locale`, `status`, `rootId`. |
| Catalog Categories — detail | `GET /api/admin/catalog/categories/{id}` | `adminCatalogCategory(id:)` | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — single category with ALL locale translations inlined + `filterableAttributeIds`. 404 on unknown id; `requirements: ['id' => '\\d+']` disambiguates the route from `/tree`. |
| Catalog Categories — create | `POST /api/admin/catalog/categories` | `createAdminCategory` mutation | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — top-level validation (slug/name/position/attributes/description-by-mode); slug uniqueness via `category_translations.slug`. Fires `catalog.category.create.{before,after}`. Permission: `catalog.categories.create`. |
| Catalog Categories — update | `PUT /api/admin/catalog/categories/{id}` | `updateAdminCategory` mutation | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — **locale-nested** validation (`<locale>.slug`/`name`/`description-by-mode`); slug uniqueness excludes current id. Fires `catalog.category.update.{before,after}`. Permission: `catalog.categories.edit`. **Move = this endpoint with `parent_id`+`position`** — no separate `/move` (parity with monolith, see Phase 2 notes). **2026-06-25 GraphQL fix:** `updateAdminCategory` 500'd over GraphQL — the core URL-rewrite listener reads the locale-nested body from the global `request()`, which is null over GraphQL → `url_rewrites.target_path` NOT-NULL violation. Fixed by `request()->merge()` of the normalized locale payload before the update event (the documented CMS-page pattern). REST unaffected. |
| Catalog Categories — delete | `DELETE /api/admin/catalog/categories/{id}` | `deleteAdminCategory` mutation | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — refuses if `id===1` or `channels.root_category_id` contains id → HTTP 400. Fires `catalog.category.delete.{before,after}`. Permission: `catalog.categories.delete`. **2026-06-25 GraphQL fix:** the GraphQL delete returned an unusable payload; now returns the deleted row via the delete-snapshot pattern (the `id` IRI resolves). REST unaffected. |
| Catalog Categories — mass-delete | `POST /api/admin/catalog/categories/mass-delete` | `createAdminCategoryMassDelete` mutation | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — pre-validates whole batch; if any id is undeletable (root / channel root), rejects with HTTP 400 before any work. |
| Catalog Categories — mass-update-status | `POST /api/admin/catalog/categories/mass-update-status` | `createAdminCategoryMassUpdateStatus` mutation | `Admin/RestApi/CategoryTest.php` | `Admin/GraphQL/CategoryTest.php` | covered — body: `{ indices, value }` (value: 0 or 1). Fires `catalog.categories.mass-update.{before,after}` per id. Permission: `catalog.categories.edit`. |
| Catalog Attributes — listing | `GET /api/admin/catalog/attributes` | `adminAttributes` (cursor) | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — DataGrid parity (~15-field row, ~10 filters, ~5 sort columns). |
| Catalog Attributes — detail | `GET /api/admin/catalog/attributes/{id}` | `adminAttribute(id:)` | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — full translations + options (each with their own translations) inlined as plain arrays. 404 on unknown id; `requirements: ['id' => '\\d+']` disambiguates the route. **2026-06-08 read-back fix:** `is_comparable` / `enable_wysiwyg` / `regex` were **writable but not readable** — accepted by the create/update input DTOs + persisted by the processor (`AdminAttributeProcessor` lines 250-252) but absent from the `AdminAttribute` resource props AND `AdminAttributeItemProvider::mapToDto`, so a client could set them and never read them back (broken round-trip). Added the 3 `?int`/`?string` props to the resource (detail-only, null on listing — the datagrid doesn't show them) + mapped them in the item provider (`isComparable`/`enableWysiwyg` cast to int, `regex` raw). Resolves on both transports (verified live + `AttributeTest::test_create_attribute_reads_back_comparable_wysiwyg_regex`). Note: `validation` / `default_value` / `swatch_type` were already mapped — only these 3 were missing. |
| Catalog Attributes — create | `POST /api/admin/catalog/attributes` | `createAdminAttribute` mutation | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — validates code (unique, Code rule), admin_name, type; fires `catalog.attribute.{create,update}.{before,after}` events; re-fetches Eloquent model after repository create (repository returns contract). |
| Catalog Attributes — update | `PUT /api/admin/catalog/attributes/{id}` | `updateAdminAttribute` mutation | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — partial update; if options provided, replaces the full option set (upsert existing by id, delete omitted, insert new). System-attribute immutable fields (code/type) are silently not passed to the update payload by convention. |
| Catalog Attributes — delete | `DELETE /api/admin/catalog/attributes/{id}` | `deleteAdminAttribute` mutation | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — guards: system attribute (403), in-use by attribute family (409). `requirements: ['id' => '\\d+']`. **2026-06-25 GraphQL fix:** the GraphQL delete returns the deleted row via the delete-snapshot pattern (select `_id`). REST unaffected. |
| Catalog Attribute Options — create | `POST /api/admin/catalog/attributes/{attributeId}/options` | `createAdminAttributeOption` mutation | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — only allowed on select/multiselect/checkbox types; validates admin_name required. **2026-06-25 GraphQL fix:** the mutation was broken over GraphQL (returned the wrong payload type / `adminName` camelCase mismatch → 0 rows); now returns `adminAttributeOption` (select `_id`), input `attributeId: Int!`. REST unaffected. |
| Catalog Attribute Options — update | `PUT /api/admin/catalog/attributes/{attributeId}/options/{optionId}` | `updateAdminAttributeOption` mutation | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — partial update of option fields + translations. **2026-06-25 GraphQL fix:** the mutation was uncallable over GraphQL (DTO `?string attributeId`/`optionId` collided with the `Int!` extraArgs); now returns `adminAttributeOption` (select `_id`), input `id` (IRI) + `attributeId: Int!` + `optionId: Int!`. REST unaffected. |
| Catalog Attribute Options — delete | `DELETE /api/admin/catalog/attributes/{attributeId}/options/{optionId}` | `deleteAdminAttributeOption` mutation | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — 409 if option is referenced in product_attribute_values. **2026-06-25 GraphQL fix:** the GraphQL delete returns `adminAttributeOption` via the delete-snapshot pattern (select `_id`), input `id` (IRI) + `attributeId: Int!` + `optionId: Int!`. REST unaffected. |
| Catalog Attributes — mass-delete | `POST /api/admin/catalog/attributes/mass-delete` | `createAdminAttributeMassDelete` mutation | `Admin/RestApi/AttributeTest.php` | `Admin/GraphQL/AttributeTest.php` | covered — pre-validates entire batch before deleting; rejects if any ID is a system attribute. |
| Catalog Attribute Families — listing | `GET /api/admin/catalog/families` | `adminAttributeFamilies` (cursor) | `Admin/RestApi/AttributeFamilyTest.php` | `Admin/GraphQL/AttributeFamilyTest.php` | covered — DataGrid parity (3-field slim row: id/code/name; no timestamps). Filters: id (int or comma list), code (partial), name (partial). Sort: id (default desc), code, name. |
| Catalog Attribute Families — detail | `GET /api/admin/catalog/families/{id}` | `adminAttributeFamily(id:)` | `Admin/RestApi/AttributeFamilyTest.php` | `Admin/GraphQL/AttributeFamilyTest.php` | covered — slim fields + `attributeGroups: [{id, code, name, column, position, attributes: [{id, code, type, isRequired, column, position}]}]` as plain arrays. Groups sorted by position. 404 on unknown id; `requirements: ['id' => '\\d+']`. Note: `attribute_group_mappings` pivot only has `position` (no `column` — column is on `attribute_groups`); both `column` and `position` in the attribute ref are populated from the pivot `position` field. |
| Catalog Attribute Families — create | `POST /api/admin/catalog/families` | `createAdminAttributeFamily` mutation | `Admin/RestApi/AttributeFamilyTest.php` | `Admin/GraphQL/AttributeFamilyTest.php` | covered — validates code (unique, `Code` rule), name, and per-group `code`/`name`/`column in 1,2`. Accepts nested `attribute_groups[*].custom_attributes[*]` and delegates the whole payload to `AttributeFamilyRepository::create()`. Fires `catalog.attribute_family.create.{before,after}`. Permission: `catalog.families.create`. |
| Catalog Attribute Families — update | `PUT /api/admin/catalog/families/{id}` | `updateAdminAttributeFamily` mutation | `Admin/RestApi/AttributeFamilyTest.php` | `Admin/GraphQL/AttributeFamilyTest.php` | covered — same validation, code unique except self. Repository keys existing groups by numeric id, new groups by `group_*`; omitted ids are deleted (and so are omitted custom_attributes within an existing group). Fires `catalog.attribute_family.update.{before,after}`. Permission: `catalog.families.edit`. |
| Catalog Attribute Families — delete | `DELETE /api/admin/catalog/families/{id}` | `deleteAdminAttributeFamily` mutation | `Admin/RestApi/AttributeFamilyTest.php` | `Admin/GraphQL/AttributeFamilyTest.php` | covered — refuses with HTTP 400 if this is the last family (`families.last-delete-error`) or any product is attached (`families.attribute-product-error`). Fires `catalog.attribute_family.delete.{before,after}`. `requirements: ['id' => '\\d+']`. Permission: `catalog.families.delete`. **2026-06-25 GraphQL fix:** the GraphQL delete returned an unusable payload; now returns the deleted row via the delete-snapshot pattern (the `id` IRI resolves). REST unaffected. |
| CMS Pages — listing | `GET /api/admin/cms/pages` | `adminCmsPages` (cursor) | `Admin/RestApi/CmsPageTest.php` | `Admin/GraphQL/CmsPageTest.php` | covered — DataGrid parity (1:1 with `Webkul\Admin\DataGrids\CMS\CMSPageDataGrid`: 5-field row id/urlKey/pageTitle/channel/locale + createdAt; 4 filters id/page_title/url_key/channel/locale; 4 sort columns id/page_title/url_key/created_at). `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| CMS Pages — detail | `GET /api/admin/cms/pages/{id}` | `adminCmsPage(id:)` | `Admin/RestApi/CmsPageTest.php` | `Admin/GraphQL/CmsPageTest.php` | covered — full payload with `translations: [{ locale, url_key, page_title, html_content, meta_title, meta_keywords, meta_description }]` and `channels: [{ id, code, name }]` inlined as plain arrays. 404 on unknown id; `requirements: ['id' => '\\d+']` keeps `mass-delete` literal route from being captured by `{id}`. |
| CMS Pages — create | `POST /api/admin/cms/pages` | `createAdminCmsPage` mutation | `Admin/RestApi/CmsPageTest.php` | `Admin/GraphQL/CmsPageTest.php` | covered — top-level fields (`url_key`, `page_title`, `html_content`, `meta_*`) broadcast to every locale by `PageRepository::create`. Validates url_key (slug regex + unique on `cms_page_translations`), page_title, html_content, channels (non-empty + existing ids). Fires `cms.page.create.{before,after}`. Permission: `cms.create`. |
| CMS Pages — update | `PUT /api/admin/cms/pages/{id}` | `updateAdminCmsPage` mutation | `Admin/RestApi/CmsPageTest.php` | `Admin/GraphQL/CmsPageTest.php` | covered — LOCALE-NESTED body: `{ "<locale>": { url_key, page_title, html_content, ... }, channels, locale }`. url_key uniqueness excludes self via `PageRepository::isUrlKeyUnique`. Fires `cms.page.update.{before,after}`. Permission: `cms.edit`. **2026-06-23 GraphQL 500 fixed:** core `Marketing\Listeners\Page::beforeUpdate` (on `cms.page.update.before`) reads `request()->input('locale')` + `request()->input("$locale.url_key")` to build the old→new 301 url_rewrite. Over GraphQL the HTTP body is the `{query,variables}` envelope, so those keys are null → `url_rewrites.target_path` NOT-NULL insert violation → 500 (REST was fine, body has the keys). Fix: `AdminCmsPageProcessor::handleUpdate` now `request()->merge($payload)` (the normalized `{ <locale> => {...}, channels, locale }`) BEFORE dispatching `cms.page.update.before`, so the core listener resolves on both transports. Regression: GraphQL `test_mutation_update_happy_path` strengthened (was lenient/`hasErrors`-tolerant — masked this bug; now asserts no `errors` + `pageTitle` resolves + DB updated). **Lesson: any core listener that reads the locale-nested body from the global `request()` needs `request()->merge()` of the normalized payload in the GraphQL path.** |
| CMS Pages — delete | `DELETE /api/admin/cms/pages/{id}` | `deleteAdminCmsPage` mutation | `Admin/RestApi/CmsPageTest.php` | `Admin/GraphQL/CmsPageTest.php` | covered — fires `cms.page.delete.{before,after}`. Permission: `cms.delete`. REST returns 204. **2026-06-23 GraphQL snapshot fix (the Settings 2026-06-19 pattern, applied to CMS):** `handleDelete` returned `null` → GraphQL delete result came back null/unselectable. Now `handleDelete(int $id, bool $asResource=false)`; the GraphQL delete branch passes `true` and returns an `AdminCmsPage` snapshot loaded `with(['translations','channels'])` BEFORE `pageRepository->delete()` — so `id`/`_id`/`urlKey`/`pageTitle`/scalars resolve on the delete result. REST path unchanged (returns null → 204). Connection nodes (`translations`/`channels` edges) come back EMPTY on a delete result (re-resolved from DB, row already gone) — scalars are from the in-memory snapshot. **2026-06-23 — added a `message` field** for the success signal: `AdminCmsPage` gained a transient `public ?string $actionMessage` + `getMessageAttribute()` accessor + `'message'` in `$appends` (the `channel`-accessor pattern — a bare `#[ApiProperty] getMessage()` method is NOT exposed on an Eloquent GraphQL type; only columns/relations/`$appends`-accessors are, so `$appends` is required + flush `bagisto-api-platform:clear-cache`). `handleDelete` sets `$snapshot->actionMessage = __('…cms.page.deleted')`. So GraphQL delete returns `message: "CMS page deleted successfully."`; `message` is `null` on read/list/create/update (string accessor → safe over GraphQL; array accessors 500 per gotcha (b)). Failure path unchanged (top-level `errors[]`). REST output DTOs are untouched (model `$appends` only affects GraphQL model serialization). Regression: GraphQL `test_mutation_delete_happy_path` asserts `_id`/`pageTitle` resolve + `message` == the `deleted` lang string + no `errors` + row gone. |
| CMS Pages — mass-delete | `POST /api/admin/cms/pages/mass-delete` | `createAdminCmsPageMassDelete` mutation | `Admin/RestApi/CmsPageTest.php` | `Admin/GraphQL/CmsPageTest.php` | covered — body: `{ indices: int[] }`. Non-existent IDs silently skipped (matches monolith). Empty/missing indices → 422. Permission: `cms.delete`. |
| Settings Currencies — listing | `GET /api/admin/settings/currencies` | `adminSettingsCurrencies` (cursor) | `Admin/RestApi/SettingsCurrencyTest.php` | `Admin/GraphQL/SettingsCurrencyTest.php` | covered (Block B Wave 1) — DataGrid parity with `Webkul\Admin\DataGrids\Settings\CurrencyDataGrid`. 10-field row. Filters: `code`/`name`/`symbol` (partial LIKE). Sort: `id`/`code`/`name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Settings Currencies — detail | `GET /api/admin/settings/currencies/{id}` | `adminSettingsCurrency(id:)` | `Admin/RestApi/SettingsCurrencyTest.php` | `Admin/GraphQL/SettingsCurrencyTest.php` | covered — full currency payload. 404 on unknown id; `requirements: ['id' => '\\d+']` keeps `mass-delete` literal route from being captured by `{id}`. |
| Settings Currencies — create | `POST /api/admin/settings/currencies` | `createAdminSettingsCurrency` mutation | `Admin/RestApi/SettingsCurrencyTest.php` | `Admin/GraphQL/SettingsCurrencyTest.php` | covered — validates `code` (required + alpha + size:3 + unique, uppercased by the model mutator), `name` (required). Fires `core.currency.create.{before,after}`. Permission: `settings.currencies.create`. |
| Settings Currencies — update | `PUT /api/admin/settings/currencies/{id}` | `updateAdminSettingsCurrency` mutation | `Admin/RestApi/SettingsCurrencyTest.php` | `Admin/GraphQL/SettingsCurrencyTest.php` | covered — validates `name` (required). `code` is **immutable** on update (parity with monolith — silently dropped from the repository payload). Fires `core.currency.update.{before,after}`. Permission: `settings.currencies.edit`. |
| Settings Currencies — delete | `DELETE /api/admin/settings/currencies/{id}` | `deleteAdminSettingsCurrency` mutation | `Admin/RestApi/SettingsCurrencyTest.php` | `Admin/GraphQL/SettingsCurrencyTest.php` | covered — refuses with HTTP 400 if (a) this is the only currency left in the table (`cannot-delete-last`), or (b) any channel references it as `channels.base_currency_id` (`cannot-delete-channel-base`). Fires `core.currency.delete.{before,after}`. Permission: `settings.currencies.delete`. |
| Settings Currencies — mass-delete | `POST /api/admin/settings/currencies/mass-delete` | `createAdminSettingsCurrencyMassDelete` mutation | `Admin/RestApi/SettingsCurrencyTest.php` | `Admin/GraphQL/SettingsCurrencyTest.php` | covered — pre-validates whole batch. Rejects with HTTP 400 if the batch would empty the currencies table, OR if any id is a channel `base_currency_id`. Non-existent ids silently skipped. Empty `indices` → 422. Permission: `settings.currencies.delete`. |
| Settings Channels — listing | `GET /api/admin/settings/channels` | `adminSettingsChannels` (cursor) | `Admin/RestApi/SettingsChannelTest.php` | `Admin/GraphQL/SettingsChannelTest.php` | covered (Block B Wave 2) — DataGrid parity. Joins `channels × channel_translations` for the current locale so each row carries `name`/`description` inline. Filters: `code`/`hostname` (LIKE on `channels`), `name` (LIKE on `channel_translations.name`). Sort: `id` (default desc), `code`, `name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Settings Channels — detail | `GET /api/admin/settings/channels/{id}` | `adminSettingsChannel(id:)` | `Admin/RestApi/SettingsChannelTest.php` | `Admin/GraphQL/SettingsChannelTest.php` | covered — eager-loads `locales`/`currencies`/`inventory_sources`/`translations`. Surfaces `localeIds`/`currencyIds`/`inventorySourceIds` arrays and `translations: [{ locale, name, description, homePageContent, footerContent, maintenanceModeText, homeSeo }]` inline as plain associative arrays. `requirements: ['id' => '\\d+']`. |
| Settings Channels — create | `POST /api/admin/settings/channels` | `createAdminSettingsChannel` mutation | `Admin/RestApi/SettingsChannelTest.php` | `Admin/GraphQL/SettingsChannelTest.php` | covered — validates `code` (required + unique + alpha-dash), `name` (required), `hostname` (unique when set), `locales`/`currencies`/`inventory_sources` (non-empty integer arrays whose ids all exist), `default_locale_id` ∈ `locales`, `base_currency_id` ∈ `currencies`, `root_category_id` exists. Top-level translatable scalars (name/description/seo_*/maintenance_mode_text) are broadcast to every configured locale by the repository; optional `translations: { <locale>: { ... } }` map writes per-locale overrides. SEO triplet (seo_title/seo_description/seo_keywords) rolled into `home_seo`. Fires `core.channel.create.{before,after}`. Permission: `settings.channels.create`. |
| Settings Channels — update | `PUT /api/admin/settings/channels/{id}` | `updateAdminSettingsChannel` mutation | `Admin/RestApi/SettingsChannelTest.php` | `Admin/GraphQL/SettingsChannelTest.php` | covered — partial update; `code`/`hostname` uniqueness excludes the current id. Cross-array membership (default_locale ∈ locales, base_currency ∈ currencies) re-validated when the relevant arrays are present. Fires `core.channel.update.{before,after}`. Permission: `settings.channels.edit`. |
| Settings Channels — delete | `DELETE /api/admin/settings/channels/{id}` | `deleteAdminSettingsChannel` mutation | `Admin/RestApi/SettingsChannelTest.php` | `Admin/GraphQL/SettingsChannelTest.php` | covered — refuses with HTTP 400 if (a) this is the only remaining channel (`cannot-delete-last`), or (b) the channel's code matches `config('app.channel')` (`cannot-delete-default`, mirrors monolith). FK cascades from `channel_locales`/`channel_currencies`/`channel_inventory_sources`/`channel_translations` are handled by the DB (all `onDelete('cascade')`); the `categories.root_category_id` FK uses `set null`, so deleting a channel does NOT orphan-delete its root category. Fires `core.channel.delete.{before,after}`. Permission: `settings.channels.delete`. |
| Catalog Product Images — upload | `POST /api/admin/catalog/products/{productId}/images` | `createAdminCatalogProductImage` mutation (placeholder — REST-only) | `Admin/RestApi/CatalogProductImageTest.php` | `Admin/GraphQL/CatalogProductImageTest.php` | covered — multipart `image` (bmp/jpeg/jpg/png/webp, ≤4MB); optional `position`. Writes file under `product/{productId}/{random}.<ext>` on the `public` disk and inserts a `product_images` row. Fires `catalog.product.update.{before,after}`. Permission: `catalog.products.edit`. GraphQL upload is REST-only (binary file not transportable over JSON GraphQL); the GraphQL `create` mutation rejects with HTTP 422 and points to the REST route. |
| Catalog Product Images — reorder | `PUT /api/admin/catalog/products/{productId}/images/reorder` | `reorderAdminCatalogProductImage` mutation | `Admin/RestApi/CatalogProductImageTest.php` | `Admin/GraphQL/CatalogProductImageTest.php` | covered — body `{ order: [{id, position}, ...] }`. Validates that every supplied image id belongs to the product (otherwise 422 `id-not-on-product`). REST response wraps in a `JsonResponse` to bypass API Platform's IRI generation (resource is a POPO, not an Eloquent model — see implementation notes). |
| Catalog Product Images — delete | `DELETE /api/admin/catalog/products/{productId}/images/{id}` | `deleteAdminCatalogProductImage` mutation | `Admin/RestApi/CatalogProductImageTest.php` | `Admin/GraphQL/CatalogProductImageTest.php` | covered — removes both the DB row and the file from the `public` disk. 404 if the image id doesn't belong to the product. GraphQL standard delete-mutation schema only exposes `id` (the IRI); the processor extracts the image id from the basename and looks up the parent product id from the row. |
| Sales — Invoices listing | `GET /api/admin/invoices` | `adminInvoices` (cursor) | `Admin/RestApi/SalesInvoicesListTest.php` | `Admin/GraphQL/SalesInvoicesListTest.php` | covered — **2026-06-04: now returns the full `AdminInvoice` per row, not a slim DTO** (`AdminInvoiceListDto` deleted). The collection provider's `buildQuery` selects `invoices.*` + the cheap order-context columns (customer name/email, status, channel_name, order created_at) from the existing `orders` join; `mapRow` returns `AdminInvoice` with **every** invoice column populated (all `base_*`/`*_incl_tax`/`formatted*`/`reminders` + `orderStatus`/`orderStatusLabel`/`orderDate`/`channelName`/`customerName`/`customerEmail`). `billingAddress`/`shippingAddress` ARE populated on the listing too — batch-loaded for the whole page in ONE `addresses` query (no N+1) via a new `mapRows()` hook on `AbstractAdminCollectionProvider` (default = one `mapRow()` per row; the invoice provider overrides it to pre-load addresses keyed by `order_id`, then delegates). Only `items` stays `[]` on the listing (genuinely heavy per-row — use the detail endpoint). Node `id` IRI is now the clean `/api/admin/invoices/{id}` (was `/api/admin_invoice_list_dtos/{id}`). **Why:** the listing GraphQL type is `AdminInvoice` (the QueryCollection's resource), so once the detail merge added all columns to `AdminInvoice`, the slim list provider left them null — clients querying e.g. `subTotal`/`emailSent` on the listing got null despite DB values. Filters/sort unchanged. Permission: `sales.invoices.view`. Envelope `{ data, meta }`. **Apply the same full-column listing to Shipment/Refund/Transaction/Booking when migrated.** |
| Sales — Shipments listing | `GET /api/admin/shipments` | `adminShipments` (cursor) | `Admin/RestApi/SalesShipmentsListTest.php` | `Admin/GraphQL/SalesShipmentsListTest.php` | covered — **2026-06-05: now returns the full `AdminShipment` per row, not a slim DTO** (`AdminShipmentListDto` deleted). The collection provider's `buildQuery` selects the explicit shipment columns + order context (status/channel_name/customer name+email/created_at) from the `orders` join (+ the `shipped_to` CONCAT from the shipping `addresses` join + resolved `inventory_source_name`); `mapRow` returns `AdminShipment` with every shipment column + order/customer context. `billingAddress`/`shippingAddress` ARE populated on the listing too — batch-loaded for the whole page in ONE `addresses` query (no N+1) via the `mapRows()` hook on `AbstractAdminCollectionProvider`. Only `items` stay `[]` on the listing (detail-only). Inventory source resolves to `inventory_sources.name` when the FK is set, falls back to the row's snapshot `inventory_source_name`. Filters/sort unchanged (id/order_id/total_qty/inventory_source_name/shipped_to/order_date/created_at). Permission: `sales.shipments.view`. Tagged `Admin Sales: Shipments`. |
| Sales — Refunds listing | `GET /api/admin/refunds` | `adminRefunds` (cursor) | `Admin/RestApi/SalesRefundsListTest.php` | `Admin/GraphQL/SalesRefundsListTest.php` | covered — **2026-06-04: now returns the full `AdminRefund` per row, not a slim DTO** (`AdminRefundListDto` deleted). The collection provider's `buildQuery` selects `refunds.*` + the order-context columns (customer name/email, status, channel_name, order created_at) from the `orders` join (+ the `billed_to` CONCAT from `addresses` ADDRESS_TYPE_BILLING); `mapRow` returns `AdminRefund` with **every** refund column populated (all `base_*`/`*_incl_tax`/`formatted*` + adjustments + order/customer context). `billingAddress`/`shippingAddress` ARE populated on the listing too — batch-loaded for the whole page in ONE `addresses` query (no N+1) via the `mapRows()` hook on `AbstractAdminCollectionProvider`. Only `items` + payment info stay null on the listing (detail-only). Filters: id, order_id, state, base_grand_total (+range), billed_to, created_at. Permission: `sales.refunds.view`. Tagged `Admin Sales: Refunds`. |
| Sales — Refunds export (CSV) | `GET /api/admin/refunds/export` | — (REST only) | `Admin/RestApi/SalesRefundsListTest.php` | — | covered (2026-06-05) — the admin Refunds datagrid's **Export** button, as an API. REST-only binary download (`text/csv` attachment, `refunds.csv`), mirrors the Print-Invoice binary pattern (`outputFormats: ['csv' => ['text/csv']]`, `AdminRefundExportProvider` returns an `Illuminate\Http\Response`). CSV columns = the 5 datagrid columns (ID, Order ID, **Refunded Amount** = `base_grand_total` via `formatBasePrice` — NOT sub_total, e.g. `$4,233.00`, Billed To, Refund Date). Honours the **same filters as the listing** (id/order_id/state/base_grand_total_from/_to/billed_to/created_at_from/_to); no pagination (exports all matching rows, capped at 50000). `?format=` accepts only `csv` (xls/xlsx → 422 `export-format-unsupported`). Permission: `sales.refunds.view`. The `/refunds/export` route doesn't collide with `/refunds/{id}` (the latter has `requirements ['id' => '\d+']`). GraphQL has no counterpart (binary). **This is the template for exporting any admin datagrid — roll out per menu as needed.** |
| Sales — Transactions listing | `GET /api/admin/transactions` | `adminTransactions` (cursor) | `Admin/RestApi/SalesTransactionsTest.php` | `Admin/GraphQL/SalesTransactionsTest.php` | covered — **2026-06-05 rebuild to the Invoice template**: the listing now returns the full `AdminTransaction` per row (every `order_transactions` column + resolved `paymentTitle` + the raw `data` blob + the inlined `order` summary), not a slim DTO (`AdminTransactionListDto` deleted). Both list + detail providers now share `State/Concerns/BuildsAdminTransaction` + `adminTransactionSelect()` (single raw `order_transactions ⟕ orders` join — the detail provider switched off Eloquent `OrderTransaction::find` to the same raw join). `paymentTitle` resolved from `core()->getConfigData('sales.payment_methods.{method}.title')` (no Eloquent accessor dependency). Filters: id, transaction_id (partial), invoice_id, order_id (partial), status (paid/pending/COMPLETED), created_at. Permission: `sales.transactions.view`. Tagged `Admin Sales: Transactions`. Read-only (no actions/mass-actions). |
| Sales — Transactions detail | `GET /api/admin/transactions/{id}` | `adminTransaction(id:)` | `Admin/RestApi/SalesTransactionsTest.php` | `Admin/GraphQL/SalesTransactionsTest.php` | covered — **2026-06-05 rebuild**: detail Get/Query return the `AdminTransaction` resource itself (no `output:` DTO — `AdminTransactionDetailDto` deleted), so the GraphQL `id` IRI resolves (`/api/admin/transactions/{id}`) instead of 500-ing. Full payload: every `order_transactions` column + `formattedAmount` + `paymentTitle` + the raw gateway `data` blob (decoded `?array`) + `order` summary (id/incrementId/status/customerName/customerEmail/grandTotal/orderCurrencyCode). `data` + `order` are plain JSON (inline in REST, bare queryable JSON in GraphQL — NOT field-selectable). `OrderTransaction` has no `order()` relation in 2.3.x — the raw join resolves order context directly (no `Order::find` follow-up). 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Sales — Transactions create (Record Payment) | `POST /api/admin/transactions` | `createAdminTransaction` mutation | `Admin/RestApi/TransactionCreateTest.php` | `Admin/GraphQL/TransactionCreateTest.php` | covered (2026-06-30, **2.1.0**) — the admin Sales → Transactions "Record Payment" action. Body `{ invoiceId, paymentMethod, amount }`. Mirrors core `TransactionController::store` 1:1: 422 if any field missing / `amount` non-numeric; 400 if the invoice is unknown / already `paid` / `amount` ≤ 0 / cumulative payments would exceed `base_grand_total`. Inserts a paid `order_transactions` row (random `transaction_id`, `status=paid`, `type`/`payment_method` = the chosen method, `data` = `{paidAmount}`); when cumulative payments ≥ `base_grand_total` it marks the invoice **paid** (`InvoiceRepository::updateState`) and advances the order to **completed** (if a shipment exists) or **processing** (`OrderRepository::updateOrderStatus`). Returns the created `AdminTransaction` (re-read through the same join the listing/detail use, so the response carries every transaction field + the `order` summary). Permission: `sales.transactions.view` (core has no `.create` ACL node — create is gated at view-level, parity). Processor `AdminTransactionCreateProcessor` (reuses `BuildsAdminTransaction` + `ChecksAdminPermission`); input DTO `AdminTransactionCreateInput`. **This is the standalone Record-Payment flow — distinct from the invoice-create `canCreateTransaction` checkbox** (`POST /api/admin/orders/{id}/invoices`), which auto-captures the FULL invoice amount via the core listener. Both write `order_transactions` → both surface in the listing; the guards compose (a full-amount checkbox transaction makes this endpoint reject a duplicate as "already paid"). |
| Sales — Bookings listing | `GET /api/admin/bookings` | `adminBookings` (cursor) | `Admin/RestApi/SalesBookingsTest.php` | `Admin/GraphQL/SalesBookingsTest.php` | covered — **2026-06-05 rebuild to the Invoice template**: the listing now returns the full `AdminBooking` per row (every booking column + `bookingType` + product name + the inlined `order` & `orderItem` summaries), not a slim DTO (`AdminBookingListDto` deleted). Built via the shared `State/Concerns/BuildsAdminBooking` trait + `adminBookingSelect()` (joins orders/order_items/products/booking_products — no N+1). Filters: id, order_id (partial), qty, product_id, from/to (range — ISO dates → unix ts), created_at. `from`/`to` as both raw unix ts and `d M, Y H:iA`-formatted strings (both `null` for non-time-based sub-types). Permission: `sales.bookings.view`. Tagged `Admin Sales: Bookings`. Read-only menu — no mass-actions, no create/edit, no print/email (admin Bookings screen has none; "view" jumps to the order). |
| Sales — Bookings detail | `GET /api/admin/bookings/{id}` | `adminBooking(id:)` | `Admin/RestApi/SalesBookingsTest.php` | `Admin/GraphQL/SalesBookingsTest.php` | covered — **2026-06-05 rebuild**: detail Get/Query return the `AdminBooking` resource itself (no `output:` DTO — `AdminBookingDetailDto` deleted), so the GraphQL `id` IRI resolves (`/api/admin/bookings/{id}`) instead of 500-ing. Full payload: every `bookings` column + `bookingType` (`default`/`appointment`/`event`/`rental`/`table` from `booking_products.type`) + `productName` + `bookingProductEventTicketId`, plus `order` (id/incrementId/status/customerName/customerEmail/grandTotal/orderCurrencyCode) and `orderItem` (id/sku/name/qtyOrdered) as plain `?array` objects (inline in REST, bare queryable JSON in GraphQL — NOT field-selectable). Same `BuildsAdminBooking` mapping shared by both providers. `requirements: ['id' => '\\d+']`. **2026-06-05: detail now also embeds the order's documents (the admin Booking "view" jumps to the order view, which shows them) — `billingAddress`/`shippingAddress` (plain `?array`, `null` when absent), `paymentMethod`/`paymentTitle`/`shippingMethod`/`shippingTitle`, and `invoices` (id/incrementId/state/baseGrandTotal/formattedBaseGrandTotal/createdAt) / `shipments` (id/totalQty/carrierTitle/trackNumber/createdAt) / `refunds` (id/state/baseGrandTotal/formattedBaseGrandTotal/createdAt) slim summary arrays. Loaded once via `Order::with(['addresses','payment','invoices','shipments','refunds'])` in the item provider only — detail-only, no listing N+1. Query the arrays/objects bare over GraphQL (not field-selectable / not cursor connections).** |
| Settings — Exchange Rates listing | `GET /api/admin/settings/exchange-rates` | `adminSettingsExchangeRates` (cursor) | `Admin/RestApi/SettingsExchangeRateTest.php` | `Admin/GraphQL/SettingsExchangeRateTest.php` | covered — joins `currency_exchange_rates` × `currencies` so each row carries `targetCurrencyCode` + `targetCurrencyName` inline. Filters: `target_currency` (exact), `rate_from` / `rate_to` (range). Sort: `id` (default desc), `target_currency`, `rate`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Settings — Exchange Rate detail | `GET /api/admin/settings/exchange-rates/{id}` | `adminSettingsExchangeRate(id:)` | `Admin/RestApi/SettingsExchangeRateTest.php` | `Admin/GraphQL/SettingsExchangeRateTest.php` | covered — single row with embedded currency code + name. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Settings — Exchange Rate create | `POST /api/admin/settings/exchange-rates` | `createAdminSettingsExchangeRate` mutation | `Admin/RestApi/SettingsExchangeRateTest.php` | `Admin/GraphQL/SettingsExchangeRateTest.php` | covered — validates `target_currency` (required, exists in currencies, composite-unique on `currency_exchange_rates.target_currency`) and `rate` (required, numeric, `> 0`). Fires `core.exchange_rate.create.{before,after}`. Permission: `settings.exchange_rates.create`. |
| Settings — Exchange Rate update | `PUT /api/admin/settings/exchange-rates/{id}` | `updateAdminSettingsExchangeRate` mutation | `Admin/RestApi/SettingsExchangeRateTest.php` | `Admin/GraphQL/SettingsExchangeRateTest.php` | covered — partial-update friendly (omitted fields keep their existing value). Composite-uniqueness excludes self. Fires `core.exchange_rate.update.{before,after}`. Permission: `settings.exchange_rates.edit`. |
| Settings — Exchange Rate delete | `DELETE /api/admin/settings/exchange-rates/{id}` | `deleteAdminSettingsExchangeRate` mutation | `Admin/RestApi/SettingsExchangeRateTest.php` | `Admin/GraphQL/SettingsExchangeRateTest.php` | covered — fires `core.exchange_rate.delete.{before,after}`. Returns 200 + message. Permission: `settings.exchange_rates.delete`. |
| Settings — Exchange Rates mass-delete | `POST /api/admin/settings/exchange-rates/mass-delete` | `createAdminSettingsExchangeRateMassDelete` mutation | `Admin/RestApi/SettingsExchangeRateTest.php` | `Admin/GraphQL/SettingsExchangeRateTest.php` | covered — body `{ indices: int[] }`. Non-existent IDs silently skipped (matches monolith). Empty/missing indices → 422. Permission: `settings.exchange_rates.delete`. |
| Settings — Exchange Rates auto-sync | `POST /api/admin/settings/exchange-rates/update-rates` | `createAdminSettingsExchangeRateUpdateRates` mutation | `Admin/RestApi/SettingsExchangeRateTest.php` | `Admin/GraphQL/SettingsExchangeRateTest.php` | covered (2026-06-08) — the admin "Update Rates" button. Resolves the configured provider via `services.exchange_api.<default>.class` and runs its `updateRates()`. Provider/network errors surface as HTTP 422 with the provider's own message (not the monolith's session-flash redirect). Returns `{ success, updated, message }` where `updated` = the count of exchange-rate rows after sync. Single-action resource `AdminSettingsExchangeRateUpdateRates`. Permission: `settings.exchange_rates.edit`. Tests bind a fake helper (no real HTTP). |
| Settings — Locales listing | `GET /api/admin/settings/locales` | `adminSettingsLocales` (cursor) | `Admin/RestApi/SettingsLocaleTest.php` | `Admin/GraphQL/SettingsLocaleTest.php` | covered (Block B Wave 1) — DataGrid parity. Filters: `code`/`name` (partial), `direction` (exact `ltr`/`rtl`). Sort: `id`/`code`/`name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Settings — Locale detail | `GET /api/admin/settings/locales/{id}` | `adminSettingsLocale(id:)` | `Admin/RestApi/SettingsLocaleTest.php` | `Admin/GraphQL/SettingsLocaleTest.php` | covered — single row + `logoUrl` accessor. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Settings — Locale create | `POST /api/admin/settings/locales` | `createAdminSettingsLocale` mutation | `Admin/RestApi/SettingsLocaleTest.php` | `Admin/GraphQL/SettingsLocaleTest.php` | covered — validates `code` (required, unique, lowercase letters/digits/underscore/hyphen), `name` (required), `direction` (required, `in:ltr,rtl`). Fires `core.locale.create.{before,after}`. Permission: `settings.locales.create`. `logo_path` accepted as a string path only — image upload deferred. |
| Settings — Locale update | `PUT /api/admin/settings/locales/{id}` | `updateAdminSettingsLocale` mutation | `Admin/RestApi/SettingsLocaleTest.php` | `Admin/GraphQL/SettingsLocaleTest.php` | covered — partial update; code uniqueness excludes self. Fires `core.locale.update.{before,after}`. Permission: `settings.locales.edit`. |
| Settings — Locale delete | `DELETE /api/admin/settings/locales/{id}` | `deleteAdminSettingsLocale` mutation | `Admin/RestApi/SettingsLocaleTest.php` | `Admin/GraphQL/SettingsLocaleTest.php` | covered — refuses with HTTP 400 if it's the only remaining locale OR if any channel references it as `default_locale_id`. Fires `core.locale.delete.{before,after}`. Permission: `settings.locales.delete`. |
| Settings — Locales mass-delete | `POST /api/admin/settings/locales/mass-delete` | `createAdminSettingsLocaleMassDelete` mutation | `Admin/RestApi/SettingsLocaleTest.php` | `Admin/GraphQL/SettingsLocaleTest.php` | covered — body `{ indices: int[] }`. Non-existent IDs silently skipped. Per-id guards (last-locale, channel-default) skip the id with a reason in the `skipped` response key. Empty/missing indices → 422. Permission: `settings.locales.delete`. |
| Settings — Inventory Sources listing | `GET /api/admin/settings/inventory-sources` | `adminSettingsInventorySources` (cursor) | `Admin/RestApi/SettingsInventorySourceTest.php` | `Admin/GraphQL/SettingsInventorySourceTest.php` | covered (Block B Wave 1) — DataGrid parity. Filters: `code`/`name` (partial), `status` (0/1), `country` (exact). Sort: `id`/`code`/`name`/`priority`/`status`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Settings — Inventory Source detail | `GET /api/admin/settings/inventory-sources/{id}` | `adminSettingsInventorySource(id:)` | `Admin/RestApi/SettingsInventorySourceTest.php` | `Admin/GraphQL/SettingsInventorySourceTest.php` | covered — full row (all address + contact fields + lat/lng + priority + status). 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Settings — Inventory Source create | `POST /api/admin/settings/inventory-sources` | `createAdminSettingsInventorySource` mutation | `Admin/RestApi/SettingsInventorySourceTest.php` | `Admin/GraphQL/SettingsInventorySourceTest.php` | covered — validates `code` (required, unique, alpha-dash), `name`/`contact_name`/`contact_number`/`country`/`state`/`city`/`street`/`postcode` required, `contact_email` valid email, `priority` numeric, `status` in `0,1`. Fires `inventory.inventory_source.create.{before,after}`. Permission: `settings.inventory_sources.create`. |
| Settings — Inventory Source update | `PUT /api/admin/settings/inventory-sources/{id}` | `updateAdminSettingsInventorySource` mutation | `Admin/RestApi/SettingsInventorySourceTest.php` | `Admin/GraphQL/SettingsInventorySourceTest.php` | covered — partial update merged onto the existing row; code uniqueness excludes self. Fires `inventory.inventory_source.update.{before,after}`. Permission: `settings.inventory_sources.edit`. |
| Settings — Inventory Source delete | `DELETE /api/admin/settings/inventory-sources/{id}` | `deleteAdminSettingsInventorySource` mutation | `Admin/RestApi/SettingsInventorySourceTest.php` | `Admin/GraphQL/SettingsInventorySourceTest.php` | covered — refuses with HTTP 400 if it's the only remaining source (parity with monolith) OR if any `product_inventories` row references it. Fires `inventory.inventory_source.delete.{before,after}`. Permission: `settings.inventory_sources.delete`. |
| Settings — Inventory Sources mass-delete | `POST /api/admin/settings/inventory-sources/mass-delete` | `createAdminSettingsInventorySourceMassDelete` mutation | `Admin/RestApi/SettingsInventorySourceTest.php` | `Admin/GraphQL/SettingsInventorySourceTest.php` | covered — body `{ indices: int[] }`. Non-existent IDs silently skipped. Pre-validates the whole batch: 400 if delete would leave zero sources OR if any id is referenced by `product_inventories`. Empty/missing indices → 422. Permission: `settings.inventory_sources.delete`. |
| Settings — Tax Rates listing | `GET /api/admin/settings/tax-rates` | `adminSettingsTaxRates` (cursor) | `Admin/RestApi/SettingsTaxRateTest.php` | `Admin/GraphQL/SettingsTaxRateTest.php` | covered (Block B Wave 3) — full row payload (id/identifier/isZip/zipCode/zipFrom/zipTo/state/country/taxRate/timestamps). Filters: `identifier` (LIKE), `country` (exact), `state` (exact), `tax_rate_from`/`tax_rate_to` (range). Sort: `id` (default desc), `identifier`, `tax_rate`. Envelope `{ data, meta }`. |
| Settings — Tax Rate detail | `GET /api/admin/settings/tax-rates/{id}` | `adminSettingsTaxRate(id:)` | `Admin/RestApi/SettingsTaxRateTest.php` | `Admin/GraphQL/SettingsTaxRateTest.php` | covered — single row. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Settings — Tax Rate create | `POST /api/admin/settings/tax-rates` | `createAdminSettingsTaxRate` mutation | `Admin/RestApi/SettingsTaxRateTest.php` | `Admin/GraphQL/SettingsTaxRateTest.php` | covered — validates `identifier` (required + unique), `country` (required, 2-letter), `tax_rate` (required + numeric, 0..100), `is_zip` (required boolean). **Conditional zip rules:** `is_zip=false` → `zip_code` required; `is_zip=true` → `zip_from`+`zip_to` required. Fires `tax.rate.create.{before,after}`. Permission: `settings.taxes.tax_rates.create`. |
| Settings — Tax Rate update | `PUT /api/admin/settings/tax-rates/{id}` | `updateAdminSettingsTaxRate` mutation | `Admin/RestApi/SettingsTaxRateTest.php` | `Admin/GraphQL/SettingsTaxRateTest.php` | covered — partial update; identifier uniqueness excludes self. Conditional zip rules re-evaluated after merging the patch onto the existing row (flipping `is_zip` without supplying the matching zip field → 422). Fires `tax.rate.update.{before,after}`. Permission: `settings.taxes.tax_rates.edit`. |
| Settings — Tax Rate delete | `DELETE /api/admin/settings/tax-rates/{id}` | `deleteAdminSettingsTaxRate` mutation | `Admin/RestApi/SettingsTaxRateTest.php` | `Admin/GraphQL/SettingsTaxRateTest.php` | covered — explicitly detaches `tax_categories_tax_rates` pivot rows (migration declares no `onDelete('cascade')`), then deletes. Fires `tax.rate.delete.{before,after}`. **No mass-delete** (the monolith admin UI doesn't expose one). Permission: `settings.taxes.tax_rates.delete`. |
| Settings — Tax Rates export (CSV) | `GET /api/admin/settings/tax-rates/export` | — (REST only) | `Admin/RestApi/SettingsTaxRateTest.php` | — | covered (2026-06-08) — the Tax Rates datagrid's Export button. Binary `text/csv` attachment (`tax-rates.csv`) via the shared `StreamsAdminCsvExport` trait + `AdminSettingsTaxRateExportProvider`. Columns = the 8 datagrid columns (ID, Identifier, State, Country, Zip Code, Zip From, Zip To, Tax Rate); honours the same filters as the listing (identifier/country/state/tax_rate_from/_to). `?format=` accepts only `csv` (else 422). Export `Get` declared LAST (after the detail Get) so the GraphQL node-id IRI stays `/settings/tax-rates/{id}`. Send `Accept: text/csv`. Permission: `settings.taxes.tax_rates`. No GraphQL counterpart (binary). Only Settings datagrid with an Export button. |
| Settings — Data Transfer Imports listing | `GET /api/admin/settings/data-transfer/imports` | `adminSettingsDataTransferImports` (cursor) | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered (Block B Wave 3) — filters: `code` (entity type, alias `type`), `action`, `state`, `created_at_from`/`to`. Sort: id (default desc), state, created_at. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. Listing rows leave heavy `errors`/`summary` JSON blobs null (detail-only). |
| Settings — Data Transfer Import detail | `GET /api/admin/settings/data-transfer/imports/{id}` | `adminSettingsDataTransferImport(id:)` | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — full row with `errors[]`, `summary{}`, counts, file paths, started/completed timestamps. `requirements: ['id' => '\\d+']`. |
| Settings — Data Transfer Import cancel | `POST /api/admin/settings/data-transfer/imports/{id}/cancel` | `cancelAdminSettingsDataTransferImportCancel` | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — sets `state = 'cancelled'` when currently `pending`/`processing`. Anything else → 422 with the current state inlined in the message. Lives on the separate `AdminSettingsDataTransferImportCancel` resource (mirrors the Phase 5.2 Copy single-action pattern). Permission: `settings.data_transfer.imports.edit`. |
| Settings — Data Transfer Import delete | `DELETE /api/admin/settings/data-transfer/imports/{id}` | `deleteAdminSettingsDataTransferImport` | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — removes the DB row + best-effort deletes the upload file (`file_path` + `error_file_path`). Returns 200 + message. Permission: `settings.data_transfer.imports.delete`. |
| Settings — Data Transfer Import create | `POST /api/admin/settings/data-transfer/imports` | `createAdminSettingsDataTransferImport` (REST-only — GraphQL rejects 422) | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered (2026-06-08) — **multipart** upload mirroring `ImportController::store`. Body: `type` (in `array_keys(config('importers'))` — `products`/`customers`/`tax_rates`/etc.), `action` (`append`/`delete`), `validation_strategy` (`stop-on-errors`/`skip-errors`), `allowed_errors` (int≥0), `field_separator`, optional `process_in_queue`/`images_directory_path`, and the `file` (csv/xls/xlsx/xml). Stores the file on the `private` disk under `imports/` (uniqid+sha256 name) and creates the row (state `pending`). Fires `data_transfer.imports.create.{before,after}`. Multipart binary isn't transportable over JSON GraphQL — the `create` mutation returns 422 pointing to REST (Phase 5.11 convention). Permission: `settings.data_transfer.imports.create`. |
| Settings — Data Transfer Import update | `PUT /api/admin/settings/data-transfer/imports/{id}` | — (REST-only multipart) | `Admin/RestApi/SettingsDataTransferImportTest.php` | — | covered — multipart re-upload mirroring `ImportController::update`. Resets `state`→`pending` and clears the counts / `error_file_path`; when a new file is supplied, deletes the old file and stores the new one. Fires `data_transfer.imports.update.{before,after}`. Permission: `settings.data_transfer.imports.edit`. |
| Settings — Data Transfer Import validate | `POST /api/admin/settings/data-transfer/imports/{id}/validate` | `validateAdminSettingsDataTransferImportValidate` (input `importId`) | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — runs the import-helper validation pass; returns `{ is_valid, import }`. Permission: `settings.data_transfer.imports.edit`. |
| Settings — Data Transfer Import start | `POST /api/admin/settings/data-transfer/imports/{id}/start` | `startAdminSettingsDataTransferImportStart` (input `importId`) | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — runs the next pending batch (mirrors `ImportController::start`). Guards: nothing-to-import (400 when `processed_rows_count=0`), not-valid (400), setup-queue-error (400 when `process_in_queue` && `queue.default=sync`). Returns `{ stats, import }`. Permission: `settings.data_transfer.imports.edit`. The client drives create → validate → start (loop) → link → index, same as the admin UI. |
| Settings — Data Transfer Import link | `POST /api/admin/settings/data-transfer/imports/{id}/link` | `linkAdminSettingsDataTransferImportLink` (input `importId`) | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — the post-process linking stage (mirrors `ImportController::link`). Same guards. Returns `{ stats, import }`. Permission: `settings.data_transfer.imports.edit`. |
| Settings — Data Transfer Import index | `POST /api/admin/settings/data-transfer/imports/{id}/index` | `indexAdminSettingsDataTransferImportIndex` (input `importId`) | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — the indexing stage (mirrors `ImportController::indexData`). Same guards. Returns `{ stats, import }`. Permission: `settings.data_transfer.imports.edit`. |
| Settings — Data Transfer Import stats | `GET /api/admin/settings/data-transfer/imports/{id}/stats?state=` | `adminSettingsDataTransferImportStats(id: <IRI>)` | `Admin/RestApi/SettingsDataTransferImportTest.php` | `Admin/GraphQL/SettingsDataTransferImportTest.php` | covered — per-state progress stats (mirrors `ImportController::stats`). Returns `{ stats, import }`. Separate `AdminSettingsDataTransferImportStats` resource. |
| Settings — Data Transfer Import download (file) | `GET /api/admin/settings/data-transfer/imports/{id}/download` | — (REST-only binary) | `Admin/RestApi/SettingsDataTransferImportTest.php` | — | covered — streams the uploaded source file from the `private` disk; 404 if missing. `Accept: application/octet-stream`. Permission: `settings.data_transfer.imports.view`. |
| Settings — Data Transfer Import download error-report | `GET /api/admin/settings/data-transfer/imports/{id}/download-error-report` | — (REST-only binary) | `Admin/RestApi/SettingsDataTransferImportTest.php` | — | covered — streams `error_file_path`; 404 if none. Permission: `settings.data_transfer.imports.view`. |
| Settings — Data Transfer Import download sample | `GET /api/admin/settings/data-transfer/imports/sample/{type}/{format}` | — (REST-only binary) | `Admin/RestApi/SettingsDataTransferImportTest.php` | — | covered — streams `config("importers.{type}.sample_paths.{format}")`; 422/404 for unknown type/format. All 3 binary downloads share one `AdminSettingsDataTransferImportDownload` resource whose provider branches on the request path; `graphQlOperations: []` (binary has no GraphQL). The `sample/{type}/{format}` literal route is safe alongside `/{id}/*` because the `{id}` ops carry `requirements ['id' => '\\d+']`. |
| Settings — Tax Categories listing | `GET /api/admin/settings/tax-categories` | `adminSettingsTaxCategories` (cursor) | `Admin/RestApi/SettingsTaxCategoryTest.php` | `Admin/GraphQL/SettingsTaxCategoryTest.php` | covered (Block B Wave 3) — datagrid-parity listing. Filters: `code`/`name` (partial LIKE). Sort: `id` (default desc), `code`, `name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. Listing rows omit `taxRates` (detail-only). |
| Settings — Tax Category detail | `GET /api/admin/settings/tax-categories/{id}` | `adminSettingsTaxCategory(id:)` | `Admin/RestApi/SettingsTaxCategoryTest.php` | `Admin/GraphQL/SettingsTaxCategoryTest.php` | covered — single row with `taxRates: [{id, identifier, taxRate}]` eager-loaded via `tax_categories_tax_rates` pivot. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Settings — Tax Category create | `POST /api/admin/settings/tax-categories` | `createAdminSettingsTaxCategory` mutation | `Admin/RestApi/SettingsTaxCategoryTest.php` | `Admin/GraphQL/SettingsTaxCategoryTest.php` | covered — validates `code` (required, unique), `name` (required), `description` (required), `taxrates` (required, array). Attaches the supplied tax_rate ids via the pivot. Fires `tax.category.create.{before,after}`. Permission: `settings.taxes.tax_categories.create`. |
| Settings — Tax Category update | `PUT /api/admin/settings/tax-categories/{id}` | `updateAdminSettingsTaxCategory` mutation | `Admin/RestApi/SettingsTaxCategoryTest.php` | `Admin/GraphQL/SettingsTaxCategoryTest.php` | covered — code uniqueness excludes self. Re-syncs the `tax_rates` pivot (detach + attach) to the supplied taxrates list. Fires `tax.category.update.{before,after}`. Permission: `settings.taxes.tax_categories.edit`. |
| Settings — Tax Category delete | `DELETE /api/admin/settings/tax-categories/{id}` | `deleteAdminSettingsTaxCategory` mutation | `Admin/RestApi/SettingsTaxCategoryTest.php` | `Admin/GraphQL/SettingsTaxCategoryTest.php` | covered — mirrors monolith `TaxCategoryController::destroy`: refuses with HTTP 400 if any `tax_rates` are still attached. Fires `tax.category.delete.{before,after}`. Permission: `settings.taxes.tax_categories.delete`. No mass-delete (monolith UI ships none). |
| Settings — Roles listing | `GET /api/admin/settings/roles` | `adminSettingsRoles` (cursor) | `Admin/RestApi/SettingsRoleTest.php` | `Admin/GraphQL/SettingsRoleTest.php` | covered (Block B Wave 2) — datagrid-parity listing. Filters: `name` (partial), `permission_type` (`all`/`custom`). Sort: `id` (default desc), `name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Settings — Role detail | `GET /api/admin/settings/roles/{id}` | `adminSettingsRole(id:)` | `Admin/RestApi/SettingsRoleTest.php` | `Admin/GraphQL/SettingsRoleTest.php` | covered — single row; `permissions` returned as decoded array. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Settings — Role create | `POST /api/admin/settings/roles` | `createAdminSettingsRole` mutation | `Admin/RestApi/SettingsRoleTest.php` | `Admin/GraphQL/SettingsRoleTest.php` | covered — validates `name`, `description`, `permission_type` (`in:all,custom`). `permissions` required when `permission_type=custom`. Fires `user.role.create.{before,after}`. Permission: `settings.roles.create`. |
| Settings — Role update | `PUT /api/admin/settings/roles/{id}` | `updateAdminSettingsRole` mutation | `Admin/RestApi/SettingsRoleTest.php` | `Admin/GraphQL/SettingsRoleTest.php` | covered — same validation as create. When `permission_type=all`, the `permissions` array is silently cleared to `[]` (matches monolith). Fires `user.role.update.{before,after}`. Permission: `settings.roles.edit`. |
| Settings — Role delete | `DELETE /api/admin/settings/roles/{id}` | `deleteAdminSettingsRole` mutation | `Admin/RestApi/SettingsRoleTest.php` | `Admin/GraphQL/SettingsRoleTest.php` | covered — refuses with HTTP 400 if any admin (`admins.role_id`) is using this role OR if it is the only remaining role. Fires `user.role.delete.{before,after}`. Permission: `settings.roles.delete`. No mass-delete (not in monolith UI). |
| Settings — Users listing | `GET /api/admin/settings/users` | `adminSettingsUsers` (cursor) | `Admin/RestApi/SettingsUserTest.php` | `Admin/GraphQL/SettingsUserTest.php` | covered (Block B Wave 2) — DataGrid parity on `admins` table joined to `roles`. Row: `id/name/email/roleId/roleName/status/image/imageUrl/createdAt/updatedAt`. Filters: `name`/`email` (partial), `role_id`, `status`. Sort: `id` (default desc), `name`, `email`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Settings — User detail | `GET /api/admin/settings/users/{id}` | `adminSettingsUser(id:)` | `Admin/RestApi/SettingsUserTest.php` | `Admin/GraphQL/SettingsUserTest.php` | covered — eager-loads `role`. 404 on unknown id; `requirements: ['id' => '\\d+']`. Password/api_token never returned (hidden on the model). |
| Settings — User create | `POST /api/admin/settings/users` | `createAdminSettingsUser` mutation | `Admin/RestApi/SettingsUserTest.php` | `Admin/GraphQL/SettingsUserTest.php` | covered — validates `name` required, `email` required+email+unique, `password` required+min:6, `role_id` required+exists, `status` in `0,1` (defaults to 1). Password hashed via `Hash::make()`. Fires `user.admin.create.{before,after}`. Permission: `settings.users.users.create`. |
| Settings — User update | `PUT /api/admin/settings/users/{id}` | `updateAdminSettingsUser` mutation | `Admin/RestApi/SettingsUserTest.php` | `Admin/GraphQL/SettingsUserTest.php` | covered — partial update merged onto existing row. Email uniqueness excludes self. Password is **optional** — re-hashed via `Hash::make()` when present, otherwise the existing hash is preserved. Fires `user.admin.update.{before,after}`. Permission: `settings.users.users.edit`. |
| Settings — User delete | `DELETE /api/admin/settings/users/{id}` | `deleteAdminSettingsUser` mutation | `Admin/RestApi/SettingsUserTest.php` | `Admin/GraphQL/SettingsUserTest.php` | covered — **GUARD 1**: refuses with HTTP 400 if the caller is deleting themselves (`id === $caller->id`). **GUARD 2**: refuses with HTTP 400 if this is the last admin (`Admin::count() <= 1`). Fires `user.admin.delete.{before,after}`. Permission: `settings.users.users.delete`. No mass-delete (not in monolith UI). |
| Settings — User self-delete | `POST /api/admin/settings/users/delete-self` | `createAdminSettingsUserDeleteSelf` mutation | `Admin/RestApi/SettingsUserTest.php` | `Admin/GraphQL/SettingsUserTest.php` | covered (2026-06-08) — the admin "delete my own account" flow (`UserController::destroySelf`). Deletes the authenticated admin (the token owner) after re-confirming their password. Refuses the last remaining admin (400) and a wrong/missing password (422). NO ACL permission gate — the password IS the gate (parity with core). Distinct from the `{id}` delete above, which keeps refusing self-deletion. Single-action resource `AdminSettingsUserDeleteSelf`. Fires `user.admin.delete.{before,after}`. Deleting the owner admin invalidates the calling token. |
| Theme (storefront) | `GET /api/shop/theme` | `theme` | `RestApi/ThemeTest.php` | `GraphQL/ThemeTest.php` | covered (2026-08-26) — the theme the current channel runs: `{ code, name, sectionTypes }`. `code` = `channels.theme` falling back to `config('themes.shop-default')`; `name` from `config('themes.shop.<code>.name')`; `sectionTypes` = `Section::TYPES`. Singleton served as a one-element array over REST (the `/api/admin/menu` convention) and as an object over GraphQL via `ThemeQueryResolver` — the `Query` MUST declare `args: []` or API Platform keeps the required `id: ID!`. Public (storefront key). Admin-only gallery metadata (author/version/screenshot/install state) is deliberately not exposed here. |
| Appearance — Themes listing | `GET /api/admin/appearance/themes` | `adminAppearanceThemes` | `Admin/RestApi/AppearanceThemeTest.php` | `Admin/GraphQL/AppearanceThemeTest.php` | covered (2026-08-26 — 2.4.10 Appearance rebuild) — the theme gallery from `Webkul\Theme\ThemeCatalog::all()`: installed + catalog-available themes, active first. Node `{ code, name, author, version, url, demoUrl, screenshot, rating, tags, description, isInstalled, status (active\|installed\|available), activeOn: [{id,name}] }`. Keyed by `code` (string identifier), not an int id. Not paginated, no filters. Permission `appearance.themes`. |
| Appearance — Theme detail | `GET /api/admin/appearance/themes/{code}` | `adminAppearanceTheme(code:)` | `Admin/RestApi/AppearanceThemeTest.php` | `Admin/GraphQL/AppearanceThemeTest.php` | covered — same node as the listing; 404 on an unknown code. GraphQL arg is the bare code, not an IRI. |
| Appearance — Theme impact | `GET /api/admin/appearance/themes/{code}/impact?channel_ids[]=` | `adminAppearanceThemeImpact(code:, channelIds:)` | `Admin/RestApi/AppearanceThemeTest.php` | `Admin/GraphQL/AppearanceThemeTest.php` | covered — mirrors `ThemeController::impact`, but exposed as a **read** (core POSTs it; it writes nothing). Returns `{ code, impact: [{channelId, channel, currentTheme, customizations}] }`, empty-filtered exactly as core does. 422 on missing/unknown channel ids. Permission `appearance.themes`. |
| Appearance — Theme activate | `POST /api/admin/appearance/themes/{code}/activate` | `createAdminAppearanceThemeActivate` | `Admin/RestApi/AppearanceThemeTest.php` | `Admin/GraphQL/AppearanceThemeTest.php` | covered — body `{ channelIds: int[] }`; writes `channels.theme` per channel and fires the same four core events (`appearance.theme.activate.{before,after}` + `core.channel.update.{before,after}`). 404 for an unknown or not-installed theme, 422 for missing/unknown channels. Returns `{ code, activatedOn, message }` instead of core's `redirect_url`. Permission `appearance.themes.activate`. |
| Appearance — Sections listing | `GET /api/admin/appearance/themes/{code}/sections` | `adminAppearanceSections(code:, channel:, locale:)` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `theme_sections` for one theme + channel in render order with `footer_links` pinned last (mirrors `SectionController::editableSections`). Not paginated. GraphQL returns the Eloquent resource so `translations { edges { node { locale options draftOptions } } }` resolves; REST returns the flat `AdminAppearanceSectionRestDto`. Channel/locale resolution mirrors core (`?channel=` else current; `?locale=` must be one the channel runs, else app locale, else the channel's first). 404 on an unknown theme code. Permission `appearance.sections`. |
| Appearance — Section detail | `GET /api/admin/appearance/sections/{id}` | `adminAppearanceSection(id:)` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — one section with every locale's content. `hasDraft` / `isPinned` are **int 0/1 accessors** (they render as `"1"`/`"0"` strings over GraphQL, booleans in the REST DTO — an int avoids the empty-string-for-false quirk). |
| Appearance — Section create | `POST /api/admin/appearance/themes/{code}/sections` | `createAdminAppearanceSection` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — mirrors `SectionController::store`: validates `name` + `type in Section::TYPES`, applies the **single-footer guard** (422), then forces `channel_id`/`theme_code`/`sort_order=count+1`/`status=0`/`draft_status=true` and re-runs `reorder`. So a new section is born unpublished. Fires `section.create.{before,after}`. Permission `appearance.sections.create`. |
| Appearance — Section update | `PUT /api/admin/appearance/sections/{id}` | `updateAdminAppearanceSection` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — writes the **published** values. Full-form validation (`name`, `type`, `sort_order`, `channel_id`, `theme_code`) + the footer guard with the row excluded; optional `status` and per-locale `options`. Fires `section.update.{before,after}`. For `image_carousel`/`services_content` the core repo drops `options` (it expects a multipart upload), so the processor writes the translation itself — over the API the paths already exist via the media endpoint. Permission `appearance.sections.edit`. |
| Appearance — Section delete | `DELETE /api/admin/appearance/sections/{id}` | `deleteAdminAppearanceSection` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — 204 REST; GraphQL returns the **delete snapshot** (scalars + `message`; connections come back empty). Fires `section.delete.{before,after}`. Permission `appearance.sections.delete`. |
| Appearance — Stage edits (draft) | `POST /api/admin/appearance/sections/{id}/draft` | `createAdminAppearanceSectionDraft` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `{ options }` (+`?locale=`) → `SectionRepository::saveDraft`; returns `{ sectionId, locale, hasDraft, message }`. The draft **replaces** the previous one for that locale. Fires `section.draft.save.{before,after}`. Permission `appearance.sections.edit`. |
| Appearance — Stage status | `POST /api/admin/appearance/sections/{id}/status` | `createAdminAppearanceSectionStatus` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `{ status: bool }` → `saveStatusDraft`; the published `status` does not move, the value lands in `draft_status`. Returns `{ sectionId, draftStatus, hasDraft, message }`. Permission `appearance.sections.edit`. |
| Appearance — Stage order | `POST /api/admin/appearance/sections/reorder` | `createAdminAppearanceSectionReorder` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `{ sectionIds: int[] }` → `withPinnedLast()` then `saveOrderDraft`, so a client cannot lift `footer_links` off the bottom; the response returns the order actually stored plus `hasDraft` per id. 422 on empty/unknown ids. Permission `appearance.sections.edit`. |
| Appearance — Section copy | `POST /api/admin/appearance/sections/{id}/duplicate` | `createAdminAppearanceSectionDuplicate` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `SectionRepository::duplicate`; returns `{ sectionId, sourceId, name, type, message }` (identity only — re-query the detail for content), mirroring the `AdminCatalogProductCopy` shape. Permission `appearance.sections.create`. |
| Appearance — Publish | `POST /api/admin/appearance/themes/{code}/sections/publish` | `createAdminAppearanceSectionPublish` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `draftedSections` → `publishDrafts` for the theme + channel, firing `section.update.{before,after}` per section as core does. Returns `{ themeCode, channelId, sectionIds, message }`; an empty `sectionIds` is a success. Permission `appearance.sections.edit`. |
| Appearance — Discard | `POST /api/admin/appearance/themes/{code}/sections/discard` | `createAdminAppearanceSectionDiscard` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `discardDrafts` for the theme + channel, firing `section.draft.discard.{before,after}`. Published content untouched. Permission `appearance.sections.edit`. |
| Appearance — Section fields | `GET /api/admin/appearance/sections/{id}/fields` | `adminAppearanceSectionFields(id:, locale:)` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered — `{ sectionId, type, locale, schema, options }` from `Webkul\Theme\SectionSchema::for($type)`, options resolving **draft over published**. `schema`/`options` stay plain JSON (dynamic per type — rule 5). GraphQL arg is a plain `Int!`, not an IRI. Permission `appearance.sections`. |
| Appearance — Section media upload | `POST /api/admin/appearance/sections/{id}/media` | — (REST only) | `Admin/RestApi/AppearanceSectionTest.php` | — | covered — multipart `file` → `SectionRepository::storeMedia`; returns `{ sectionId, path, type }` where `type` is `image`\|`video` (core branches on mime). Returns a `JsonResponse` directly (POPO resource → bypasses IRI generation, the Phase 5.11 image pattern). `graphQlOperations: []`. Fires `section.media.upload.{before,after}`. Permission `appearance.sections.edit`. |
| Appearance — Preview | `GET /api/admin/appearance/themes/{code}/sections/preview` | `adminAppearanceSectionPreview(code:, channel:, locale:)` | `Admin/RestApi/AppearanceSectionTest.php` | `Admin/GraphQL/AppearanceSectionTest.php` | covered (API-specific — core renders a Blade preview route the headless storefront cannot use) — `SectionRepository::getDraftedForPreview` so draft options/status/order are applied and off sections are dropped; footer pinned last. Returns `{ themeCode, channelId, locale, sections: [{id,name,type,sortOrder,status,hasDraft,options}] }` (plain JSON). Permission `appearance.sections`. |
| Marketing — Cart Rules listing | `GET /api/admin/marketing/cart-rules` | `adminMarketingCartRules` (cursor) | `Admin/RestApi/MarketingCartRuleTest.php` | `Admin/GraphQL/MarketingCartRuleTest.php` | covered (Block F1b) — DataGrid-shape rows (id/name/description/status/couponType/actionType/discountAmount/sortOrder/**couponCode**/timestamps + every other top-level scalar). **2026-06-09 — datagrid-parity filters added:** `id` (single/comma list), `name` (LIKE), `coupon_code` (partial, correlated subquery on the primary coupon), `status` (0/1), `coupon_type` (1/2, API superset — not on the datagrid), `sort_order` (priority, exact), `starts_from_from`/`starts_from_to` + `ends_till_from`/`ends_till_to` (date ranges). Listing now surfaces `couponCode` (primary coupon) via a `is_primary=1` subquery (was detail-only). Sort: `id` (default desc), `name`, `sort_order`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Marketing — Cart Rule detail | `GET /api/admin/marketing/cart-rules/{id}` | `adminMarketingCartRule(id:)` | `Admin/RestApi/MarketingCartRuleTest.php` | `Admin/GraphQL/MarketingCartRuleTest.php` | covered — full row + `conditions` JSON + attached `channels[]` / `customerGroups[]` ID arrays + `couponCode` (primary coupon). `requirements: ['id' => '\\d+']` so `/mass-delete` literal route isn't captured by `{id}`. **2026-06-09 fix:** `couponCode` was ALWAYS null in the detail — the item provider read it via the core `CartRule::coupon_code` HasOne relation (`->where('is_primary',1)`), which silently returns null even when the row exists. Now read with a direct `DB::table('cart_rule_coupons')->where('cart_rule_id',$id)->where('is_primary',1)->value('code')` query (the listing already used a subquery). |
| Marketing — Cart Rule create | `POST /api/admin/marketing/cart-rules` | `createAdminMarketingCartRule` | `Admin/RestApi/MarketingCartRuleTest.php` | `Admin/GraphQL/MarketingCartRuleTest.php` | covered — validates `name`, `channels` (non-empty), `customer_groups` (non-empty), `coupon_type` (`0` = no coupon, `1` = specific coupon — matches the monolith blade dropdown), `action_type` (`by_percent`\|`by_fixed`\|`cart_fixed`\|`buy_x_get_y`), `discount_amount` (numeric; capped at 100 when `action_type=by_percent`), `starts_from`/`ends_till` coherence (`after_or_equal`). When `coupon_type=1` AND `use_auto_generation=0`, `coupon_code` required + unique on `cart_rule_coupons.code` — repository auto-inserts the primary coupon row. Fires `promotions.cart_rule.create.{before,after}`. Permission: `marketing.promotions.cart_rules.create`. |
| Marketing — Cart Rule update | `PUT /api/admin/marketing/cart-rules/{id}` | `updateAdminMarketingCartRule` | `Admin/RestApi/MarketingCartRuleTest.php` | `Admin/GraphQL/MarketingCartRuleTest.php` | covered — partial; processor merges the patch onto the existing row before validating so callers can `PUT { "name": "..." }`. `channels` / `customer_groups`, when supplied, fully replace the pivot via repository `sync()`. Primary coupon-code uniqueness excludes the row's own primary coupon. Fires `promotions.cart_rule.update.{before,after}`. Permission: `marketing.promotions.cart_rules.edit`. |
| Marketing — Cart Rule delete | `DELETE /api/admin/marketing/cart-rules/{id}` | `deleteAdminMarketingCartRule` | `Admin/RestApi/MarketingCartRuleTest.php` | `Admin/GraphQL/MarketingCartRuleTest.php` | covered — 200 + `{ message }` on success / 404 on miss. Fires `promotions.cart_rule.delete.{before,after}`. Permission: `marketing.promotions.cart_rules.delete`. |
| Marketing — Cart Rules mass-delete | `POST /api/admin/marketing/cart-rules/mass-delete` | `createAdminMarketingCartRuleMassDelete` | `Admin/RestApi/MarketingCartRuleTest.php` | `Admin/GraphQL/MarketingCartRuleTest.php` | covered — body `{ indices: int[] }`. Non-existent IDs silently skipped (mirrors monolith pattern). Empty/missing indices → 422. Returns `{ deleted, skipped, message }`. Permission: `marketing.promotions.cart_rules.delete`. |
| Marketing — Cart Rule copy | `POST /api/admin/marketing/cart-rules/{id}/copy` | `copyAdminMarketingCartRule` | `Admin/RestApi/MarketingCartRuleTest.php` | — | covered (2026-06-09) — mirrors the admin datagrid **Copy** action (`CartRuleController::copy`): replicates the rule with `status=0` and name prefixed `Copy of …`, copies its `channels` + `customer_groups` associations, and returns the **new rule's full detail** (prefilled for editing, same shape as GET `/cart-rules/{id}`). Coupons are NOT copied (a specific code is unique). REST takes `{id}` from the URI (send `Content-Type: application/json`, empty body); GraphQL passes the rule IRI as `input.id` (API Platform auto-injects `id: ID!` on the non-create mutation). 404 on unknown source. Permission: `marketing.promotions.cart_rules.create`. |
| Marketing — Cart Rule Coupons listing | `GET /api/admin/marketing/cart-rules/{cartRuleId}/coupons` | `adminMarketingCartRuleCoupons(cartRuleId:)` (cursor) | `Admin/RestApi/MarketingCartRuleCouponTest.php` | `Admin/GraphQL/MarketingCartRuleCouponTest.php` | covered (Block F1c) — sub-resource of cart rules. Returns all coupons for the rule wrapped in `{ data, meta }`. Cross-rule isolation: unknown parent rule → 404; coupons from other rules never leak. |
| Marketing — Cart Rule Coupon create (single) | `POST /api/admin/marketing/cart-rules/{cartRuleId}/coupons` | `createAdminMarketingCartRuleCoupon` | `Admin/RestApi/MarketingCartRuleCouponTest.php` | `Admin/GraphQL/MarketingCartRuleCouponTest.php` | covered — body `{ code, usage_limit?, usage_per_customer?, expired_at? }`. `code` required + unique on `cart_rule_coupons.code`. Omitted limits inherit from the parent rule. Sets `is_primary=0`, `type=1`. Fires `cart_rules.coupons.create.{before,after}`. Permission: `marketing.promotions.cart_rules.create`. |
| Marketing — Cart Rule Coupon generate (bulk) | `POST /api/admin/marketing/cart-rules/{cartRuleId}/coupons/generate` | `createAdminMarketingCartRuleCouponGenerate` | `Admin/RestApi/MarketingCartRuleCouponTest.php` | `Admin/GraphQL/MarketingCartRuleCouponTest.php` | covered — body `{ length (4-30), format (alphabetic\|alphanumeric\|numeric), prefix?, suffix?, coupon_qty (1-100) }`. Accepts both spec keys and core keys (`code_length`/`code_format`/...). `alphabetic` maps to core's `alphabetical` charset. Delegates to `CartRuleCouponRepository::generateCoupons`. Returns `{ generated, coupons[] }`. Permission: `marketing.promotions.cart_rules.create`. |
| Marketing — Cart Rule Coupon delete | `DELETE /api/admin/marketing/cart-rules/{cartRuleId}/coupons/{id}` | `deleteAdminMarketingCartRuleCoupon` | `Admin/RestApi/MarketingCartRuleCouponTest.php` | `Admin/GraphQL/MarketingCartRuleCouponTest.php` | covered — ownership-checked (cross-rule → 404). Fires `cart_rules.coupons.delete.{before,after}`. 200 + message. Permission: `marketing.promotions.cart_rules.delete`. |
| Marketing — Cart Rule Coupons mass-delete | `POST /api/admin/marketing/cart-rules/{cartRuleId}/coupons/mass-delete` | `createAdminMarketingCartRuleCouponMassDelete` | `Admin/RestApi/MarketingCartRuleCouponTest.php` | `Admin/GraphQL/MarketingCartRuleCouponTest.php` | covered — body `{ indices: int[] }`. IDs not belonging to the named cart rule are silently skipped (surface in `skipped[]`). Empty indices → 422. Permission: `marketing.promotions.cart_rules.delete`. |
| Marketing — Newsletter Subscribers listing | `GET /api/admin/marketing/subscribers` | `adminMarketingSubscribers` (cursor) | `Admin/RestApi/MarketingSubscriberTest.php` | `Admin/GraphQL/MarketingSubscriberTest.php` | covered (Block F2d) — Filters: `email` (LIKE), `channel_id`, `is_subscribed` (0/1). Sort: `id` (default desc), `email`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. Row carries `email`/`channelId`/`channelName`/`customerId`/`customerName`/`isSubscribed`. |
| Marketing — Newsletter Subscriber detail | `GET /api/admin/marketing/subscribers/{id}` | `adminMarketingSubscriber(id:)` | `Admin/RestApi/MarketingSubscriberTest.php` | `Admin/GraphQL/MarketingSubscriberTest.php` | covered — single row with linked customer name resolved. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Marketing — Newsletter Subscriber toggle | `PUT /api/admin/marketing/subscribers/{id}` | `updateAdminMarketingSubscriber` | `Admin/RestApi/MarketingSubscriberTest.php` | `Admin/GraphQL/MarketingSubscriberTest.php` | covered — body `{ is_subscribed: bool }`. Mirrors the flag onto the linked `customers.subscribed_to_news_letter` column (parity with monolith). Missing flag → 422. Permission: `marketing.communications.subscribers.edit`. |
| Marketing — Newsletter Subscriber delete | `DELETE /api/admin/marketing/subscribers/{id}` | `deleteAdminMarketingSubscriber` | `Admin/RestApi/MarketingSubscriberTest.php` | `Admin/GraphQL/MarketingSubscriberTest.php` | covered — unsubscribes the linked customer (if any) before deleting the row, matching monolith `SubscriptionController::destroy`. 200 + `{ message }` on success / 404 on miss. No mass-delete (monolith does not expose one). Permission: `marketing.communications.subscribers.delete`. |
| Marketing — Search Terms listing | `GET /api/admin/marketing/search-terms` | `adminMarketingSearchTerms` (cursor) | `Admin/RestApi/MarketingSearchTermTest.php` | `Admin/GraphQL/MarketingSearchTermTest.php` | covered (Block F3b) — Filters: `term` (LIKE), `channel_id`, `locale`. Sort: `id` (default desc), `term`, `uses` (desc for popular), `results`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. Row carries `term`/`results`/`uses`/`redirectUrl`/`channelId`/`channelName`/`locale`. |
| Marketing — Search Term detail | `GET /api/admin/marketing/search-terms/{id}` | `adminMarketingSearchTerm(id:)` | `Admin/RestApi/MarketingSearchTermTest.php` | `Admin/GraphQL/MarketingSearchTermTest.php` | covered — single row. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Marketing — Search Term update | `PUT /api/admin/marketing/search-terms/{id}` | `updateAdminMarketingSearchTerm` | `Admin/RestApi/MarketingSearchTermTest.php` | `Admin/GraphQL/MarketingSearchTermTest.php` | covered — body `{ term, redirect_url? }`. `term` required (422). `redirect_url` validated via PHP `FILTER_VALIDATE_URL` when provided. `uses`/`results` are not editable (auto-recorded by storefront). Fires `marketing.search_seo.search_terms.update.{before,after}`. Permission: `marketing.search_seo.search_terms.edit`. |
| Marketing — Search Term delete | `DELETE /api/admin/marketing/search-terms/{id}` | `deleteAdminMarketingSearchTerm` | `Admin/RestApi/MarketingSearchTermTest.php` | `Admin/GraphQL/MarketingSearchTermTest.php` | covered — 200 + `{ message }` on success / 404 on miss. Fires `marketing.search_seo.search_terms.delete.{before,after}`. Permission: `marketing.search_seo.search_terms.delete`. |
| Marketing — Search Terms mass-delete | `POST /api/admin/marketing/search-terms/mass-delete` | `createAdminMarketingSearchTermMassDelete` | `Admin/RestApi/MarketingSearchTermTest.php` | `Admin/GraphQL/MarketingSearchTermTest.php` | covered — body `{ indices: int[] }`. Non-existent IDs silently skipped (matches monolith). Empty/missing indices → 422. Returns `{ deleted, message }`. Permission: `marketing.search_seo.search_terms.delete`. |
| Marketing — Catalog Rules listing | `GET /api/admin/marketing/catalog-rules` | `adminMarketingCatalogRules` (cursor) | `Admin/RestApi/MarketingCatalogRuleTest.php` | `Admin/GraphQL/MarketingCatalogRuleTest.php` | covered (Block F1a) — **2026-06-09 datagrid-parity filters:** `id` (single/comma list), `name` (LIKE), `status` (0/1), `sort_order` (priority, exact), `starts_from_from`/`starts_from_to` + `ends_till_from`/`ends_till_to` (date ranges). Sort: `id` (default desc), `name`, `sort_order`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. Listing rows omit `conditions`/`channels`/`customerGroups` (detail-only). **2026-06-09 — catalog-rule UPDATE made partial-merge** (parity with cart-rule): `handleUpdate` now loads the existing rule + its `channels`/`customer_groups` pivots, builds a `$current` base, `array_merge`s the non-null input over it, then validates the merged payload — so `PUT { "name": "x" }` no longer 422s "name required". (Note: a real catalog-rule create/update is slow + can hit a 30s timeout — the core `catalog_rule_product_prices` recalc N+1, documented separately; unrelated to the API.) **2026-06-09 — lang-key leak fixed:** the `admin.marketing` lang block had a DUPLICATE nested by mistake under `admin.settings.marketing` (12-space indent) holding `catalog-rule`/`campaign`/`sitemap`/`import`, so those messages were unreachable at `admin.marketing.*` and leaked raw keys (e.g. `bagistoapi::app.admin.marketing.catalog-rule.not-found`); the blocks were moved into the real `admin.marketing` block (8-space, direct child of `admin`). Lesson: when adding lang keys under a nested section, confirm the parent — a mis-indented duplicate key is silently kept (last-wins) at the wrong path. |
| Marketing — Catalog Rule detail | `GET /api/admin/marketing/catalog-rules/{id}` | `adminMarketingCatalogRule(id:)` | `Admin/RestApi/MarketingCatalogRuleTest.php` | `Admin/GraphQL/MarketingCatalogRuleTest.php` | covered — full payload with embedded `channels: int[]`, `customerGroups: int[]`, and the full `conditions` JSON array. 404 on unknown id; `requirements: ['id' => '\\d+']` keeps `mass-delete` literal route from being captured. |
| Marketing — Catalog Rule create | `POST /api/admin/marketing/catalog-rules` | `createAdminMarketingCatalogRule` mutation | `Admin/RestApi/MarketingCatalogRuleTest.php` | `Admin/GraphQL/MarketingCatalogRuleTest.php` | covered — validates `name`/`channels`/`customer_groups`/`action_type`/`discount_amount` (mirrors `CatalogRuleRequest`); date coherence `ends_till >= starts_from`; `by_percent` clamps `discount_amount` ≤ 100; `action_type` ∈ `by_percent`/`by_fixed`/`to_percent`/`to_fixed`. Repository handles pivot sync. Fires `promotions.catalog_rule.create.{before,after}`. Permission: `marketing.promotions.catalog_rules.create`. |
| Marketing — Catalog Rule update | `PUT /api/admin/marketing/catalog-rules/{id}` | `updateAdminMarketingCatalogRule` mutation | `Admin/RestApi/MarketingCatalogRuleTest.php` | `Admin/GraphQL/MarketingCatalogRuleTest.php` | covered — same validation as create. Re-syncs both pivots to the supplied lists (replace-semantics). Fires `promotions.catalog_rule.update.{before,after}`. Permission: `marketing.promotions.catalog_rules.edit`. |
| Marketing — Catalog Rule delete | `DELETE /api/admin/marketing/catalog-rules/{id}` | `deleteAdminMarketingCatalogRule` mutation | `Admin/RestApi/MarketingCatalogRuleTest.php` | `Admin/GraphQL/MarketingCatalogRuleTest.php` | covered — 200 + message on success / 404 on miss. Fires `promotions.catalog_rule.delete.{before,after}`. Permission: `marketing.promotions.catalog_rules.delete`. |
| Marketing — Catalog Rule mass-delete | `POST /api/admin/marketing/catalog-rules/mass-delete` | `createAdminMarketingCatalogRuleMassDelete` mutation | `Admin/RestApi/MarketingCatalogRuleTest.php` | `Admin/GraphQL/MarketingCatalogRuleTest.php` | covered (Block F1a) — body `{ indices: int[] }`. Non-existent IDs silently skipped. Empty/missing indices → 422. Permission: `marketing.promotions.catalog_rules.delete`. |
| Marketing — Email Templates listing | `GET /api/admin/marketing/templates` | `adminMarketingTemplates` (cursor) | `Admin/RestApi/MarketingTemplateTest.php` | `Admin/GraphQL/MarketingTemplateTest.php` | covered (Block F2a) — Filters: `name` (LIKE), `status` (`active`\|`inactive`\|`draft`). Sort: `id` (default desc), `name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. Listing rows include `content` (no pivots/relations to keep light). |
| Marketing — Email Template detail | `GET /api/admin/marketing/templates/{id}` | `adminMarketingTemplate(id:)` | `Admin/RestApi/MarketingTemplateTest.php` | `Admin/GraphQL/MarketingTemplateTest.php` | covered — full row (id/name/status/content/timestamps). 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Marketing — Email Template create | `POST /api/admin/marketing/templates` | `createAdminMarketingTemplate` mutation | `Admin/RestApi/MarketingTemplateTest.php` | `Admin/GraphQL/MarketingTemplateTest.php` | covered — validates `name` (required), `status` (required, in `active`/`inactive`/`draft`), `content` (required). Fires `marketing.templates.create.{before,after}`. Permission: `marketing.communications.email_templates.create`. File/blade-template upload deferred (accepts body string per spec). |
| Marketing — Email Template update | `PUT /api/admin/marketing/templates/{id}` | `updateAdminMarketingTemplate` mutation | `Admin/RestApi/MarketingTemplateTest.php` | `Admin/GraphQL/MarketingTemplateTest.php` | covered — same validation as create. Fires `marketing.templates.update.{before,after}`. Permission: `marketing.communications.email_templates.edit`. |
| Marketing — Email Template delete | `DELETE /api/admin/marketing/templates/{id}` | `deleteAdminMarketingTemplate` mutation | `Admin/RestApi/MarketingTemplateTest.php` | `Admin/GraphQL/MarketingTemplateTest.php` | covered — 200 + `{ message }` on success / 404 on miss. Fires `marketing.templates.delete.{before,after}`. Permission: `marketing.communications.email_templates.delete`. No mass-delete in monolith UI — not exposed. |
| Marketing — Events listing | `GET /api/admin/marketing/events` | `adminMarketingEvents` (cursor) | `Admin/RestApi/MarketingEventTest.php` | `Admin/GraphQL/MarketingEventTest.php` | covered (Block F2b) — slim row (id/name/description/date/timestamps). Filters: `name` (LIKE), `date_from`/`date_to` (range on `marketing_events.date`). Sort: `id` (default desc), `name`, `date`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Marketing — Event detail | `GET /api/admin/marketing/events/{id}` | `adminMarketingEvent(id:)` | `Admin/RestApi/MarketingEventTest.php` | `Admin/GraphQL/MarketingEventTest.php` | covered — full row. `requirements: ['id' => '\\d+']`. |
| Marketing — Event create | `POST /api/admin/marketing/events` | `createAdminMarketingEvent` mutation | `Admin/RestApi/MarketingEventTest.php` | `Admin/GraphQL/MarketingEventTest.php` | covered — validates `name` required, `description` required, `date` required + `date` rule (mirrors `EventController::store` exactly). Fires `marketing.events.create.{before,after}`. Permission: `marketing.communications.events.create`. |
| Marketing — Event update | `PUT /api/admin/marketing/events/{id}` | `updateAdminMarketingEvent` mutation | `Admin/RestApi/MarketingEventTest.php` | `Admin/GraphQL/MarketingEventTest.php` | covered — same validation as create (all three fields required, matches monolith). Fires `marketing.events.update.{before,after}`. Permission: `marketing.communications.events.edit`. |
| Marketing — Event delete | `DELETE /api/admin/marketing/events/{id}` | `deleteAdminMarketingEvent` mutation | `Admin/RestApi/MarketingEventTest.php` | `Admin/GraphQL/MarketingEventTest.php` | covered — 200 + message on success / 404 on miss. Fires `marketing.events.delete.{before,after}`. Permission: `marketing.communications.events.delete`. No mass-delete (monolith UI doesn't expose one). |
| Marketing — Search Synonyms listing | `GET /api/admin/marketing/search-synonyms` | `adminMarketingSearchSynonyms` (cursor) | `Admin/RestApi/MarketingSearchSynonymTest.php` | `Admin/GraphQL/MarketingSearchSynonymTest.php` | covered (Block F3c) — mirrors `SearchSynonymController` datagrid. Row: id/name/terms/timestamps. Filters: `name` (LIKE), `terms` (LIKE). Sort: `id` (default desc), `name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Marketing — Search Synonym detail | `GET /api/admin/marketing/search-synonyms/{id}` | `adminMarketingSearchSynonym(id:)` | `Admin/RestApi/MarketingSearchSynonymTest.php` | `Admin/GraphQL/MarketingSearchSynonymTest.php` | covered — single row payload. 404 on unknown id; `requirements: ['id' => '\\d+']` keeps `mass-delete` literal route from being captured by `{id}`. |
| Marketing — Search Synonym create | `POST /api/admin/marketing/search-synonyms` | `createAdminMarketingSearchSynonym` mutation | `Admin/RestApi/MarketingSearchSynonymTest.php` | `Admin/GraphQL/MarketingSearchSynonymTest.php` | covered — validates `name` required, `terms` required (mirrors `SearchSynonymController::store` exactly). Fires `marketing.search_seo.search_synonyms.create.{before,after}`. Permission: `marketing.search_seo.search_synonyms.create`. |
| Marketing — Search Synonym update | `PUT /api/admin/marketing/search-synonyms/{id}` | `updateAdminMarketingSearchSynonym` mutation | `Admin/RestApi/MarketingSearchSynonymTest.php` | `Admin/GraphQL/MarketingSearchSynonymTest.php` | covered — same validation as create (both fields required, matches monolith). Fires `marketing.search_seo.search_synonyms.update.{before,after}`. Permission: `marketing.search_seo.search_synonyms.edit`. |
| Marketing — Search Synonym delete | `DELETE /api/admin/marketing/search-synonyms/{id}` | `deleteAdminMarketingSearchSynonym` mutation | `Admin/RestApi/MarketingSearchSynonymTest.php` | `Admin/GraphQL/MarketingSearchSynonymTest.php` | covered — 200 + message on success / 404 on miss. Fires `marketing.search_seo.search_synonyms.delete.{before,after}`. Permission: `marketing.search_seo.search_synonyms.delete`. |
| Marketing — Search Synonym mass-delete | `POST /api/admin/marketing/search-synonyms/mass-delete` | `createAdminMarketingSearchSynonymMassDelete` mutation | `Admin/RestApi/MarketingSearchSynonymTest.php` | `Admin/GraphQL/MarketingSearchSynonymTest.php` | covered — body `{ indices: int[] }`. Non-existent IDs silently skipped (matches monolith). Empty/missing indices → 422. Permission: `marketing.search_seo.search_synonyms.delete`. |
| Marketing — URL Rewrites listing | `GET /api/admin/marketing/url-rewrites` | `adminMarketingUrlRewrites` (cursor) | `Admin/RestApi/MarketingUrlRewriteTest.php` | `Admin/GraphQL/MarketingUrlRewriteTest.php` | covered (Block F3a) — mirrors `URLRewriteController::index` datagrid. Filters: `entity_type`, `request_path` (LIKE), `redirect_type`, `locale`. Sort: `id` (default desc), `entity_type`, `locale`, `redirect_type`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Marketing — URL Rewrite detail | `GET /api/admin/marketing/url-rewrites/{id}` | `adminMarketingUrlRewrite(id:)` | `Admin/RestApi/MarketingUrlRewriteTest.php` | `Admin/GraphQL/MarketingUrlRewriteTest.php` | covered — single row payload. 404 on unknown id; `requirements: ['id' => '\\d+']` keeps `mass-delete` literal route from being captured by `{id}`. |
| Marketing — URL Rewrite create | `POST /api/admin/marketing/url-rewrites` | `createAdminMarketingUrlRewrite` mutation | `Admin/RestApi/MarketingUrlRewriteTest.php` | `Admin/GraphQL/MarketingUrlRewriteTest.php` | covered — validates `entity_type` (in:product,category,cms_page), `request_path`, `target_path`, `redirect_type` (in:301,302), `locale` (exists:locales,code). Fires `marketing.search_seo.url_rewrites.create.{before,after}`. Permission: `marketing.search_seo.url_rewrites.create`. |
| Marketing — URL Rewrite update | `PUT /api/admin/marketing/url-rewrites/{id}` | `updateAdminMarketingUrlRewrite` mutation | `Admin/RestApi/MarketingUrlRewriteTest.php` | `Admin/GraphQL/MarketingUrlRewriteTest.php` | covered — same validation as create. Fires `marketing.search_seo.url_rewrites.update.{before,after}`. Permission: `marketing.search_seo.url_rewrites.edit`. |
| Marketing — URL Rewrite delete | `DELETE /api/admin/marketing/url-rewrites/{id}` | `deleteAdminMarketingUrlRewrite` mutation | `Admin/RestApi/MarketingUrlRewriteTest.php` | `Admin/GraphQL/MarketingUrlRewriteTest.php` | covered — fires `marketing.search_seo.url_rewrites.delete.{before,after}`. Returns 200 + message. Permission: `marketing.search_seo.url_rewrites.delete`. |
| Marketing — URL Rewrites mass-delete | `POST /api/admin/marketing/url-rewrites/mass-delete` | `createAdminMarketingUrlRewriteMassDelete` mutation | `Admin/RestApi/MarketingUrlRewriteTest.php` | `Admin/GraphQL/MarketingUrlRewriteTest.php` | covered — body `{ indices: int[] }`. Non-existent IDs silently skipped (mirrors monolith). Empty/missing indices → 422. Permission: `marketing.search_seo.url_rewrites.delete`. |
| Marketing — Campaigns listing | `GET /api/admin/marketing/campaigns` | `adminMarketingCampaigns` (cursor) | `Admin/RestApi/MarketingCampaignTest.php` | `Admin/GraphQL/MarketingCampaignTest.php` | covered (Block F2c) — slim row (id/name/subject/status + FK ids). Filters: `name` (LIKE), `status` (0/1), `marketing_template_id`, `marketing_event_id`, `channel_id`, `customer_group_id`. Sort: `id` (default desc), `name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. Listing rows leave the resolved relation labels (`marketingTemplateName`/`marketingEventName`/`channelName`/`customerGroupCode`) null — detail-only. |
| Marketing — Campaign detail | `GET /api/admin/marketing/campaigns/{id}` | `adminMarketingCampaign(id:)` | `Admin/RestApi/MarketingCampaignTest.php` | `Admin/GraphQL/MarketingCampaignTest.php` | covered — eager-loads `email_template`/`event`/`channel`/`customer_group` so the detail payload carries human-readable names alongside the FK ids. 404 on unknown id; `requirements: ['id' => '\\d+']`. |
| Marketing — Campaign create | `POST /api/admin/marketing/campaigns` | `createAdminMarketingCampaign` mutation | `Admin/RestApi/MarketingCampaignTest.php` | `Admin/GraphQL/MarketingCampaignTest.php` | covered — validates `name`/`subject`/`marketing_template_id`/`channel_id`/`customer_group_id` required (mirrors `CampaignController::store`); `marketing_event_id` accepted nullable (monolith requires it but the column allows null and the manual-send flow doesn't depend on the date gate). Defaults the NOT-NULL `type='email'` and `mail_to=''` so MySQL strict mode is happy. Fires `marketing.campaigns.create.{before,after}`. Permission: `marketing.communications.campaigns.create`. |
| Marketing — Campaign update | `PUT /api/admin/marketing/campaigns/{id}` | `updateAdminMarketingCampaign` mutation | `Admin/RestApi/MarketingCampaignTest.php` | `Admin/GraphQL/MarketingCampaignTest.php` | covered — same validation as create; `status` coerced to 0/1 (monolith parity). Fires `marketing.campaigns.update.{before,after}`. Permission: `marketing.communications.campaigns.edit`. |
| Marketing — Campaign delete | `DELETE /api/admin/marketing/campaigns/{id}` | `deleteAdminMarketingCampaign` mutation | `Admin/RestApi/MarketingCampaignTest.php` | `Admin/GraphQL/MarketingCampaignTest.php` | covered — 200 + `{ message }` on success / 404 on miss. Fires `marketing.campaigns.delete.{before,after}`. Permission: `marketing.communications.campaigns.delete`. No mass-delete (monolith UI doesn't expose one). |
| Marketing — Campaign send | `POST /api/admin/marketing/campaigns/{id}/send` | `createAdminMarketingCampaignSend` mutation | `Admin/RestApi/MarketingCampaignTest.php` | `Admin/GraphQL/MarketingCampaignTest.php` | covered (Block F2c) — manually triggers the send. Mirrors `Helpers\Campaign::process` for a single campaign: resolves recipients from `customer_group.customers` where `subscribed_to_news_letter=1` (or the guest subscribers list when group code is `guest`), de-dupes, then `Mail::queue(new NewsletterMail($email, $campaign))` per address. Skips the date-based event gate so manual sends work regardless of `marketing_events.date`. Refuses inactive campaigns (`status=0`) → 422. Returns `{ campaignId, queued, message }`. Permission: `marketing.communications.campaigns.edit`. |
| Marketing — Sitemaps listing | `GET /api/admin/marketing/sitemaps` | `adminMarketingSitemaps` (cursor) | `Admin/RestApi/MarketingSitemapTest.php` | `Admin/GraphQL/MarketingSitemapTest.php` | covered (Block F3d) — slim row (id/fileName/path/generatedAt). Filter: `file_name` (LIKE). Sort: `id` (default desc), `file_name`. `?per_page` default 10, cap 50. Envelope `{ data, meta }`. |
| Marketing — Sitemap detail | `GET /api/admin/marketing/sitemaps/{id}` | `adminMarketingSitemap(id:)` | `Admin/RestApi/MarketingSitemapTest.php` | `Admin/GraphQL/MarketingSitemapTest.php` | covered — detail surfaces the JSON `additional` blob (written by the generate job) as `indexFile` + `generatedSitemaps`. 404 on unknown id; `requirements: ['id' => '\\d+']` keeps `/{id}/generate` from being captured. |
| Marketing — Sitemap create | `POST /api/admin/marketing/sitemaps` | `createAdminMarketingSitemap` mutation | `Admin/RestApi/MarketingSitemapTest.php` | `Admin/GraphQL/MarketingSitemapTest.php` | covered — validates `file_name` (`regex:/^[\w\-\.]+$/` + `ends_with:.xml`) and `path` (`starts_with:/` + `ends_with:/`, no double-slash) per `SitemapController::store`. Does NOT auto-dispatch `ProcessSitemap` (monolith does — it enqueues; we keep the API synchronous and require an explicit POST `/generate`). Fires `marketing.search_seo.sitemap.create.{before,after}`. Permission: `marketing.search_seo.sitemaps.create`. |
| Marketing — Sitemap update | `PUT /api/admin/marketing/sitemaps/{id}` | `updateAdminMarketingSitemap` mutation | `Admin/RestApi/MarketingSitemapTest.php` | `Admin/GraphQL/MarketingSitemapTest.php` | covered — same validation as create. Fires `marketing.search_seo.sitemap.update.{before,after}`. Permission: `marketing.search_seo.sitemaps.edit`. |
| Marketing — Sitemap delete | `DELETE /api/admin/marketing/sitemaps/{id}` | `deleteAdminMarketingSitemap` mutation | `Admin/RestApi/MarketingSitemapTest.php` | `Admin/GraphQL/MarketingSitemapTest.php` | covered — calls `Sitemap::deleteFromStorage()` first (removes any generated index + per-batch XML files from the storage disk), then deletes the row. 200 + `{ message }` on success / 404 on miss. Fires `marketing.search_seo.sitemap.delete.{before,after}`. Permission: `marketing.search_seo.sitemaps.delete`. |
| Marketing — Sitemap generate | `POST /api/admin/marketing/sitemaps/{id}/generate` | `createAdminMarketingSitemapGenerate` mutation | `Admin/RestApi/MarketingSitemapTest.php` | `Admin/GraphQL/MarketingSitemapTest.php` | covered (Block F3d) — runs `Webkul\Sitemap\Jobs\ProcessSitemap::dispatchSync($sitemap)` so the API response carries the generated file paths and `generatedAt` timestamp immediately (monolith queues the job). Walks every public Category / Product / Page, batches under `core()->getConfigData('general.sitemap.file_limits.max_url_per_file')`, writes XML files under the `public` disk at the sitemap's configured `path`, and updates `sitemaps.additional.{index,sitemaps}`. Returns `{ sitemapId, indexFile, generatedSitemaps, generatedAt, message }`. If `general.sitemap.settings.enabled` is false the job returns early; the response still carries `sitemapId` + a successful message but `indexFile` / `generatedSitemaps` stay empty. Permission: `marketing.search_seo.sitemaps.edit`. |
| Configuration — menu (schema) | `GET /api/admin/configuration/menu` | `menuAdminConfigurationMenu` query | `Admin/RestApi/ConfigurationTest.php` | `Admin/GraphQL/ConfigurationTest.php` | covered (Block G1) — generic schema tree from `system_config()`. Optional `?slug=<section>.<group>` scopes to one node; `?include_values=true` (+ `?channel=` / `?locale=`) embeds the effective per-field value. Blade-rendered `path` fields surface as `type: "custom"` + `customView`. No write surface. **2026-06-23 — GraphQL field is `menuAdminConfigurationMenu`** (`Query(name: 'menu')` + shortName), NOT `adminConfigurationMenu` (the old docs/coverage label was wrong; the old GraphQL test was passing **vacuously** via the wrong-field error branch). **`tree` is a bare JSON scalar** — query it without a sub-selection; objectifying it into typed connection nodes was tried + reverted (a synthetic POPO parent nulls the nodes — the documented POPO-null trap; the config tree is dynamic JSON per rule 5). **No-slug load fixed:** the resource used the nullable `slug` as its identifier, so a no-slug GraphQL query 500'd "No identifier value found"; switched to a constant `id = 'configuration-menu'` identifier (the `AdminMenu`/`AdminPermissions` pattern), `slug` demoted to a plain field. Do NOT select the IRI `id` field over GraphQL (routeless resource → IRI 500); query the real fields. |
| Configuration — slugs (discovery) | `GET /api/admin/configuration/slugs` | `listAdminConfigurationSlug` query | `Admin/RestApi/ConfigurationTest.php` | `Admin/GraphQL/ConfigurationTest.php` | covered (2026-06-23) — lists every registered slug so a client can discover what to pass to Values/Update without trial-and-error. Returns `slugs: [{ slug, name, sort, hasFields, hasChildren }]` as a **plain JSON scalar** (query `slugs` bare). Resource `AdminConfigurationSlug` (POPO, constant `id` identifier) + `AdminConfigurationSlugProvider` + `AdminConfigurationSlugQueryResolver`; resolver reuses `AdminConfigurationSchemaResolver::getSlugs()`. No `?slug=` arg (lists ALL); not paginated. **The GraphQL item `Query` MUST declare `args: []`** — without it API Platform keeps the default required `id: ID!` arg and the query rejects "argument id of type ID! is required". No write surface. |
| Configuration — values | `GET /api/admin/configuration?slug=<…>` | `adminConfigurationValues(slug:, channel:, locale:)` query | `Admin/RestApi/ConfigurationTest.php` | `Admin/GraphQL/ConfigurationTest.php` | covered (Block G2) — required `?slug=`; returns `{ slug, channel, locale, values: { dottedCode: "stringValue" } }`. Falls back to per-field `default` when no `core_config` row exists. Anti-foot-gun: slug is required (refuses to dump the whole table). The `/configuration/slugs` query lists valid slugs to pass here. |
| Configuration — update | `POST /api/admin/configuration` | `createAdminConfigurationUpdate` mutation | `Admin/RestApi/ConfigurationTest.php` | `Admin/GraphQL/ConfigurationTest.php` | covered (Block G3) — body `{ slug, channel?, locale?, values: { dottedCode: value } }`. Anti-scope-escape: every key in `values` must start with `slug.`. Server-side validation built from each field's `system_config` `validation` string (NOT trusted from the client). REST multipart supports file/image fields (`values[<code>]` part); GraphQL rejects file-type fields. Returns the freshly-resolved values (same shape as GET). Permission: `configuration` (Sanctum role-permission read). Fires `core.configuration.save.before/after` via `CoreConfigRepository::create()`. Encryption deferred (not in core). |

### Admin Configuration — implementation notes (Block G — final menu group)

After this lands, the admin API surface is at **full parity with the Bagisto admin sidebar**. Configuration is structurally unlike the rest of the admin API: it's a flat key/value store (`core_config`) whose **schema is registered at runtime** by every package's `Config/system.php` file. Building per-section endpoints would tightly couple us to every plugin tree. Three generic endpoints scale across any current/future registration.

**Files (10):**
- `Admin/Models/AdminConfigurationMenu.php` — `GET /api/admin/configuration/menu` + `adminConfigurationMenu`.
- `Admin/Models/AdminConfigurationValues.php` — `GET /api/admin/configuration` + `adminConfigurationValues`.
- `Admin/Models/AdminConfigurationUpdate.php` — `POST /api/admin/configuration` + `createAdminConfigurationUpdate`.
- `Admin/Dto/AdminConfigurationUpdateInput.php`.
- `Admin/State/AdminConfigurationSchemaResolver.php` — shared DRY helper.
- `Admin/State/AdminConfigurationMenuProvider.php` + `AdminConfigurationValuesProvider.php` + `AdminConfigurationUpdateProcessor.php`.
- `Admin/Resolver/AdminConfigurationMenuQueryResolver.php` + `AdminConfigurationValuesQueryResolver.php`.

**`AdminConfigurationSchemaResolver` is the central DRY piece.** Single class, registered as a `singleton()` in `BagistoApiServiceProvider` so the `system_config()->getItems()` tree walk + flattened code→field map is built once per request rather than once per endpoint hit. Exposes `getRoots()`, `getItem(slug)`, `getField(dottedCode)`, `getFieldsUnder(slug)`, `toArray(?slugFilter)`, `findSlug(slug)`.

**Anti-scope-escape (the foot-gun this prevents).** Without the `slug.` prefix check in the update processor, an integrator who POSTs `{ slug: "sales", values: { "catalog.inventory.stock_threshold": "0" } }` would silently overwrite an unrelated section's field because the repository keys upserts by `code` alone. The processor refuses with 422 + the offending key in the message before any write.

**Validation is server-side, NOT trusted from client.** The monolith's `ConfigurationController::store` trusts client-sent `keys[]` JSON metadata for the validation string. We always resolve each field's `validation` from `system_config()` at write time and run a Laravel Validator. Dotted codes (e.g. `general.content.header_offer.title`) would normally be interpreted by Laravel as nested array paths — the processor aliases them to flat keys (`f_0`, `f_1`, ...) before validating, then maps errors back to the original codes in the surfaced message.

**File upload handling — REST multipart only.**
- Mixed payloads (some scalars + some files) are supported in a single request.
- Files arrive at `values[<dotted.code>]` and are extracted in `extractPayload` via `request()->file('values')[$code]`.
- The processor **uploads the file itself** to the `configuration` disk via `$file->store('configuration')` and writes the resulting storage path as the field value. Originally tried passing through to `CoreConfigRepository::create()`'s own `request()->hasFile($fieldName)` branch, but Laravel's `hasFile()` interprets dots as nested array access and multipart `values[<dotted.code>]` arrives nested under `values` rather than top-level — `hasFile('a.b.c')` walks the files array as `files[a][b][c]`, which never matches. Pre-uploading sidesteps this entirely.
- GraphQL rejects file-type fields with HTTP 422 `file-upload-rest-only`. Mirrors the Phase 5.11 product-image convention — binary uploads aren't transportable over JSON GraphQL project-wide.

**Custom blade-rendered fields (`'path' => '<view>'`) are read-only.** The menu endpoint surfaces them with `type: "custom"` + `customView: "<view>"` so clients know they exist. The update processor refuses writes to them with 422 `custom-view-readonly`. The blade owns its own form-posting in the admin UI.

**Repository quirk — `channel` and `locale` keys must be truthy or absent.** `CoreConfigRepository::create($data)` inspects `$data['channel']` and `$data['locale']` directly. If either is null, the `if ($data['locale'] || $data['channel'])` branch doesn't fire (so unset doesn't run), and the subsequent `foreach ($data as $method => $fieldData)` then tries to call `recursiveArray($fieldData, $method)` on a null/empty-string $fieldData and crashes. The processor always passes non-empty strings (defaulting to `core()->getDefaultChannelCode()` and `app()->getLocale()` when the caller omits them) so the repository's unset-branch always runs cleanly.

**Permission resolution.** The Bagisto admin ACL exposes the Configuration menu under a single `configuration` key (no fine-grained `.edit`/`.delete` split — admin permissions for this menu are coarse-grained in core). The processor accepts EITHER `configuration.edit` (a fine-grained key we hope core eventually adds), the parent `configuration` key, or `permission_type='all'`. Sanctum-pattern read (`$admin->role->permission_type` / `permissions`); never `bouncer()`.

**Response after POST.** Returns the freshly-resolved values for the slug (same shape as G2) so the client can refresh its form state without a follow-up GET. Wrapped in `Illuminate\Http\JsonResponse` directly to bypass API Platform's IRI generation (the resource is a POPO, not an Eloquent model — same pattern as the Phase 5.11 image processor).

**Events.** Fires `core.configuration.save.before` and `core.configuration.save.after` — these fire inside `CoreConfigRepository::create()` itself, not in our processor. Existing core listeners (cache flushers, etc.) keep working transparently.

**Coverage:** 25 REST tests + 8 GraphQL tests = 33 tests, all green. REST exercises menu (full tree / scoped / 404 / `include_values`), values (defaults / DB-row read / missing-slug / 404), update (boolean / channel-scoped / upsert / scope-escape / unknown-field / validation rejection / permission denied / permission via `configuration` perm / multipart file upload). GraphQL exercises menu / values / update happy / scope-escape / validation / auth — mutation responses subject to the project-wide IRI-generation quirk so the happy-path test asserts via DB state.

**Out of scope (deferred per spec):** encrypted-at-rest secrets (core has no encryption flag), search endpoint (admin-UI navigation aid), download endpoint (already served via `Storage::disk('configuration')` URLs).

### Admin Marketing — Campaigns — implementation notes (Block F2c)

Full CRUD on `marketing_campaigns` plus a one-action `send` endpoint that mirrors the monolith `campaign:process` console command for a single campaign.

**Files:**
- `src/Admin/Models/AdminMarketingCampaign.php` — listing + detail + Post + Put + Delete + 5 GraphQL operations.
- `src/Admin/Models/AdminMarketingCampaignSend.php` — one-action POST resource (Phase 5.2 Product Copy pattern).
- `src/Admin/Dto/AdminMarketingCampaign{Create,Update,Send}Input.php`.
- `src/Admin/State/AdminMarketingCampaign{Collection,Item,Write}Provider.php` + `AdminMarketingCampaignProcessor.php` + `AdminMarketingCampaignSendProcessor.php`.

**Schema quirk — NOT-NULL `type` + `mail_to` columns with no defaults.** The `marketing_campaigns` migration declares `type` and `mail_to` as plain `string()` columns (no `nullable()`, no `default()`). The monolith `CampaignController::store` validates and forwards `name`/`subject`/`marketing_template_id`/`marketing_event_id`/`channel_id`/`customer_group_id`/`status` only — it never sets `type` or `mail_to`. Under MySQL strict mode the INSERT would die. The API processor defaults `type='email'` and `mail_to=''` before the repository call so the row is portable across DB configurations.

**`marketing_event_id` accepted as nullable.** The monolith form rule says `required` but the column is `nullable` and the `Helpers\Campaign::process` Birthday branch is the only path that actually depends on the event (and it dereferences `$campaign->event->name`, so an event row IS required for the date-gated `campaign:process` cron). For manual-trigger sends the date gate is skipped, so the API allows event-less campaigns. If a caller leaves it null they simply forfeit the event-name dependent Birthday branch — fine for a manual send.

**Send semantics — mirrors `Helpers\Campaign::process` for ONE campaign.**
- Skip the `marketing_events.date` gate (manual-trigger semantics = "send now").
- Recipients: `customer_group.code === 'guest'` → `SubscribersList::whereNull('customer_id')`; otherwise `customer_group.customers()->where('subscribed_to_news_letter', 1)`.
- De-dupe email list, filter empties, `Mail::queue(new NewsletterMail($email, $campaign))` per address.
- Always returns 200 with `{ campaignId, queued, message }`. Zero recipients is success, not an error.
- Refuses `status=0` with 422 BEFORE resolving recipients.

**No Birthday branch — out of scope here.** The cron's special Birthday logic (`whereRaw('DATE_FORMAT(date_of_birth, "%m-%d") = ?', ...)`) is part of the scheduled processor's date-gated pipeline. A manual API send always uses the standard "subscribed group members" set. The cron remains the canonical Birthday path.

**Permission resolution:** standard Sanctum pattern (read `role->permission_type` / `role->permissions` directly, never call `bouncer()`). Create → `.create`, Update + Send → `.edit`, Delete → `.delete`.

**Coverage:** 30 REST tests + 11 GraphQL tests, all green for REST. GraphQL exercises the same paths; mutation responses are subject to the project-wide camelCase nullability quirk — DB state is the authoritative assertion.

### Admin Marketing — Sitemaps — implementation notes (Block F3d)

Full CRUD on `sitemaps` (the `Webkul\Sitemap` package) plus a one-action `generate` endpoint that runs `Webkul\Sitemap\Jobs\ProcessSitemap` synchronously so the API response carries the resulting XML file paths.

**Files (8):**
- `Admin/Models/AdminMarketingSitemap.php` — CRUD resource (Get/GetCollection/Post/Put/Delete + 5 GraphQL ops).
- `Admin/Models/AdminMarketingSitemapGenerate.php` — separate one-op resource for the generate action (matches Phase 5.2 Copy + F2c Campaign-Send convention).
- `Admin/Dto/AdminMarketingSitemap{Create,Update,Generate}Input.php`.
- `Admin/State/AdminMarketingSitemap{Collection,Item,Write}Provider.php` + `AdminMarketingSitemapProcessor.php` + `AdminMarketingSitemapGenerateProcessor.php`.

**Schema:** `sitemaps` — `id`, `file_name`, `path`, `generated_at` (nullable datetime), `additional` (nullable JSON populated by the generate job with `{ index: <path>, sitemaps: [<path>, ...] }`), timestamps. Two migrations under `packages/Webkul/Sitemap/src/Database/Migrations/`.

**Validation parity with monolith `SitemapController`:**
- `file_name`: `required`, `regex:/^[\w\-\.]+$/`, `ends_with:.xml`.
- `path`: `required`, `starts_with:/`, `regex:/^(?!.*\/\/)[\w\-\.\/]+$/`, `ends_with:/`.

**Deviation from monolith — no auto-generate on create/update.** `SitemapController::store` and `::update` both call `ProcessSitemap::dispatch($sitemap)` on every save. The API does NOT — CRUD only persists the row. The `/generate` endpoint is the explicit trigger so the API stays synchronous and the (potentially slow) regeneration is opt-in. Documented at the top of `AdminMarketingSitemapProcessor`.

**Generate runs `dispatchSync`, not `dispatch`.** The response carries `indexFile`, `generatedSitemaps`, `generatedAt` so clients don't need a follow-up poll. For very large catalogs this can take seconds-to-minutes; no queued/poll variant in v1.

**Delete cascades to storage.** Calls `Sitemap::deleteFromStorage()` before `sitemapRepository->delete($id)` — removes the index + per-batch child sitemap files from the storage disk. Wrapped in try/catch + `report()` so orphan files don't block the row delete.

**`general.sitemap.settings.enabled` config gate.** `ProcessSitemap::handle()` returns early when the flag is false. The generate endpoint still returns 200 + success message in that case, but `indexFile` / `generatedSitemaps` stay empty (mirrors monolith). Surface this to integrators: empty result usually means the config flag.

**Permission resolution.** Sanctum pattern (read `$admin->role->permission_type` / `$admin->role->permissions` directly; never `bouncer()`). Permissions: `marketing.search_seo.sitemaps.create` / `.edit` / `.delete`. Generate uses `.edit`.

**Coverage:** 28 REST tests + 7 GraphQL tests, all green. REST covers listing + filter + sort + pagination cap, detail, full CRUD with each validator failure mode (missing fields, invalid file_name extension, invalid path format), `delete_removes_storage_files`, generate happy path (asserts `generated_at` populated), 401 / 403 / 404 across every endpoint. GraphQL covers list / detail / create / update / delete / generate happy paths plus auth-required.

### Admin Marketing — Events — implementation notes (Block F2b)

Full CRUD for `marketing_events` via `Webkul\Marketing\Repositories\EventRepository`. Mirrors `Webkul\Admin\Http\Controllers\Marketing\Communications\EventController` 1:1. Events are scheduled marketing events that trigger email campaigns at specified dates.

**Files (9 total, under `src/Admin/`):**
- `Models/AdminMarketingEvent.php` — listing/detail/create/update/delete on `/api/admin/marketing/events[/{id}]`.
- `Dto/AdminMarketingEvent{Create,Update}Input.php` — Create requires all three fields; Update reused as the Delete input DTO (carries `id`).
- `State/AdminMarketingEvent{Collection,Item,Write}Provider.php` — listing (`AbstractAdminCollectionProvider`), detail (`AbstractAdminItemProvider`), PUT/DELETE placeholder.
- `State/AdminMarketingEventProcessor.php` — handles POST/PUT/DELETE + 3 GraphQL mutations.
- `tests/Feature/Admin/{RestApi,GraphQL}/MarketingEventTest.php`.

**Schema:** `marketing_events` table — `id`, `name`, `description`, `date` (event date — when to trigger), `created_at`, `updated_at`. No status / type columns; the model is minimal.

**Validation parity with core** (`EventController::store/update`): `name` required, `description` required, `date` required + `date` rule. No mass-delete — monolith UI doesn't expose one, so per the "parity with admin, not superset" rule we don't either.

**Permission resolution:** Sanctum pattern — read `$admin->role->permission_type` / `->permissions` directly, never `bouncer()`. Required perms: `marketing.communications.events.create` / `.edit` / `.delete`. Listing + detail have no permission gate (admin can browse to read).

**Coverage:** 25 REST tests + 6 GraphQL tests, all green.

### Admin Marketing — Search Synonyms — implementation notes (Block F3c)

Full CRUD + mass-delete for `search_synonyms` via `Webkul\Marketing\Repositories\SearchSynonymRepository`. Mirrors `Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SearchSynonymController` 1:1. Synonyms widen storefront search matches — a query for "shirt" matched against the row `terms = "shirt,tshirt,tee"` also returns products containing "tshirt" or "tee".

**Files (11 total, under `src/Admin/`):**
- `Models/AdminMarketingSearchSynonym.php` — listing/detail/create/update/delete on `/api/admin/marketing/search-synonyms[/{id}]`.
- `Models/AdminMarketingSearchSynonymMassDelete.php` — separate one-op resource for `/mass-delete`.
- `Dto/AdminMarketingSearchSynonym{Create,Update,MassDelete}Input.php` — Update DTO reused for the delete mutation (carries `id`).
- `State/AdminMarketingSearchSynonym{Collection,Item,Write}Provider.php` + `AdminMarketingSearchSynonymProcessor.php` + `AdminMarketingSearchSynonymMassDeleteProcessor.php`.
- `tests/Feature/Admin/{RestApi,GraphQL}/MarketingSearchSynonymTest.php`.

**Schema:** `search_synonyms` table — `id`, `name` (string), `terms` (text — comma-separated synonym list, e.g. `"shirt,tshirt,tee"`), timestamps. No locale / channel / status columns. The terms format is opaque to the API — we forward it as a string. The repository's `getSynonymsByQuery()` uses `FIND_IN_SET` against the column at search time; the API doesn't reformat it.

**Validation parity with core** (`SearchSynonymController::store/update`): `name` required, `terms` required. Both are plain `required` rules with no `unique` constraint (synonyms can overlap by design — the storefront merges all matches).

**Mass-delete is in monolith.** Unlike Events (no monolith mass-action) we ship mass-delete here because `SearchSynonymController::massDestroy` exists. Mirrors that exactly: per-id `delete.before/after` events, non-existent IDs silently skipped, batch is best-effort (any individual failure is surfaced as HTTP 500). Empty `indices` → 422.

**Permission resolution:** Sanctum pattern — read `$admin->role->permission_type` / `->permissions` directly, never `bouncer()`. Required perms: `marketing.search_seo.search_synonyms.create` / `.edit` / `.delete`. Listing + detail have no permission gate.

**Coverage:** 27 REST tests + 7 GraphQL tests, all green.

### Admin Marketing — Email Templates — implementation notes (Block F2a)

Full CRUD for `marketing_templates` via `Webkul\Marketing\Repositories\TemplateRepository`. Mirrors `Webkul\Admin\Http\Controllers\Marketing\Communications\TemplateController` 1:1.

**Files (8 total, under `src/Admin/`):**
- `Models/AdminMarketingTemplate.php` — listing/detail/create/update/delete on `/api/admin/marketing/templates[/{id}]`.
- `Dto/AdminMarketingTemplate{Create,Update}Input.php` — input DTOs (Update reused for delete).
- `State/AdminMarketingTemplate{Collection,Item,Write}Provider.php` + `AdminMarketingTemplateProcessor.php`.
- `tests/Feature/Admin/{RestApi,GraphQL}/MarketingTemplateTest.php`.

**Schema:** `marketing_templates` — `id`, `name` (string), `status` (string, free-form column but controller validates `in:active,inactive,draft`), `content` (text, HTML body), timestamps. Three fillable fields only — no pivots, no relations. Listing rows include `content` (no reason to hide it; rows stay small).

**No mass-delete.** The Bagisto admin Communications → Email Templates datagrid ships per-row delete only — no mass action. We mirror that exactly (parity with admin, not superset). No `mass-delete` endpoint or resource class.

**Status enum mirrors monolith.** Validator: `in:active,inactive,draft`. The DB column is a free-form string, but the API enforces the same three values the admin UI uses. Invalid value → 422 with `status-invalid` lang key.

**Events** — fires `marketing.templates.create.{before,after}` / `update.{before,after}` / `delete.{before,after}` exactly matching `TemplateController`. Core listeners that send the template's HTML to subscribers when it's referenced by a Campaign keep working.

**Permission resolution** — Sanctum pattern (read `$admin->role->permission_type` / `$admin->role->permissions` directly, never `bouncer()`). Permissions: `marketing.communications.email_templates.create` / `.edit` / `.delete`.

**File / blade-template upload deferred (per spec F2a).** The body is accepted as a string only — no multipart upload. Documented in OpenAPI block; will revisit if integrators need it.

**Coverage:** 27 REST tests, all green on first run. GraphQL tests written following the standard Marketing pattern — verifying via DB state since mutation responses are subject to the project-wide GraphQL IRI-generation quirk.

### Admin Marketing — URL Rewrites — implementation notes (Block F3a)

Full CRUD + mass-delete for `url_rewrites` via `Webkul\Marketing\Repositories\URLRewriteRepository`. Mirrors `Webkul\Admin\Http\Controllers\Marketing\SearchSEO\URLRewriteController` 1:1.

**Files (10 total, under `src/Admin/`):**
- `Models/AdminMarketingUrlRewrite.php` — listing/detail/create/update/delete on `/api/admin/marketing/url-rewrites[/{id}]`.
- `Models/AdminMarketingUrlRewriteMassDelete.php` — `POST /api/admin/marketing/url-rewrites/mass-delete`.
- `Dto/AdminMarketingUrlRewrite{Create,Update,MassDelete}Input.php`.
- `State/AdminMarketingUrlRewrite{Collection,Item,Write}Provider.php` + `AdminMarketingUrlRewriteProcessor.php` + `AdminMarketingUrlRewriteMassDeleteProcessor.php`.
- `tests/Feature/Admin/{RestApi,GraphQL}/MarketingUrlRewriteTest.php`.

**Schema:** `url_rewrites` — `id`, `entity_type` (string; controller validates `in:product,category,cms_page`), `request_path` (string), `target_path` (string), `redirect_type` (string nullable; validated `in:301,302`), `locale` (string), timestamps. Five fillable fields, no relations.

**Validation parity with monolith** (mirrors `URLRewriteController::store`/`::update`):
- `entity_type`   required + in `product|category|cms_page`
- `request_path`  required
- `target_path`   required
- `redirect_type` required + in `301|302`
- `locale`        required + `exists:locales,code`

Validator failures → 422 `InvalidInputException` with the first error message. The monolith's typo `'required:in:...'` (a colon instead of pipe — non-functional) is intentionally fixed to the proper `'in:...'` form here so the API contract is strict where the monolith silently isn't.

**Mass-delete** mirrors `URLRewriteController::massDestroy` — iterate indices, fire `marketing.search_seo.url_rewrites.delete.{before,after}` per id, delete each one. Non-existent IDs silently skipped (matches monolith). Empty / missing indices → 422. Response: `{ deleted: int[], message }`.

**Events** — fires `marketing.search_seo.url_rewrites.create.{before,after}` / `update.{before,after}` / `delete.{before,after}` exactly matching the monolith.

**Permission resolution** — Sanctum pattern (read `$admin->role->permission_type` / `$admin->role->permissions` directly, never `bouncer()`). Permissions: `marketing.search_seo.url_rewrites.create` / `.edit` / `.delete`. Permission-failure shares the `bagistoapi::app.admin.marketing.url-rewrite.no-permission` lang key for all three actions (DRY).

**GraphQL camelCase mapping** — `normaliseArgs()` translates `entityType`/`requestPath`/`targetPath`/`redirectType` camelCase to snake_case before validation, and casts `redirect_type` to string so a numeric `301` round-trips through the `in:` validator. Standard project-wide pattern.

**Coverage:** 34 REST tests, all green on first run. GraphQL tests written following the standard Marketing pattern — verifying via DB state since mutation responses are subject to the project-wide GraphQL IRI-generation quirk.

### Admin Marketing — Catalog Rules — implementation notes (Block F1a)

Full CRUD + mass-delete for `catalog_rules` via `Webkul\CatalogRule\Repositories\CatalogRuleRepository`. Mirrors `Webkul\Admin\Http\Controllers\Marketing\Promotions\CatalogRuleController` and `CatalogRuleRequest` 1:1.

**Files (12 total, under `src/Admin/`):**
- `Models/AdminMarketingCatalogRule.php` — listing/detail/create/update/delete on `/api/admin/marketing/catalog-rules[/{id}]`.
- `Models/AdminMarketingCatalogRuleMassDelete.php` — `POST /api/admin/marketing/catalog-rules/mass-delete` + `createAdminMarketingCatalogRuleMassDelete`.
- `Dto/AdminMarketingCatalogRule{Create,Update,MassDelete}Input.php`.
- `State/AdminMarketingCatalogRule{Collection,Item,Write}Provider.php`.
- `State/AdminMarketingCatalogRuleProcessor.php` + `AdminMarketingCatalogRuleMassDeleteProcessor.php`.
- `tests/Feature/Admin/{RestApi,GraphQL}/MarketingCatalogRuleTest.php`.

**Schema notes (2.3.8):** `catalog_rules` carries `name`, `description`, `starts_from` (date), `ends_till` (date), `status` (bool, default 0), `condition_type` (bool, default 1), `conditions` (json), `end_other_rules` (bool), `action_type` (string), `discount_amount` (decimal 12,4), `sort_order` (int). **There is NO `use_actual_price` column** in 2.3.8 (the spec listed it). We don't expose a non-existent column. Pivots: `catalog_rule_channels(catalog_rule_id, channel_id)` and `catalog_rule_customer_groups(catalog_rule_id, customer_group_id)` — both managed by `CatalogRuleRepository::create/update` via `sync($data['channels'])` / `sync($data['customer_groups'])`.

**Validation (mirrors `CatalogRuleRequest` + tightens enum):**
- `name`: required, string.
- `channels`: required, array, min:1, integer elements.
- `customer_groups`: required, array, min:1, integer elements.
- `starts_from`: nullable, date.
- `ends_till`: nullable, date, after_or_equal:starts_from.
- `action_type`: required, in (`by_percent`, `by_fixed`, `to_percent`, `to_fixed`). Monolith's `required` is enum-free; we tighten because the discount-engine only handles those four.
- `discount_amount`: required, numeric, min:0. When `action_type=by_percent`, additionally max:100 (parity with monolith conditional rule).
- `conditions`: nullable, array (free-form rows of `{attribute, operator, value, ...}`; persisted via JSON column cast).

**Repository delegates pivot sync — no manual `->sync()` in our processor.** The processor's only responsibility is validation + payload normalisation + event dispatch; everything else flows through `CatalogRuleRepository::create/update`, which already calls `$catalogRule->channels()->sync($data['channels'])` and `$catalogRule->customer_groups()->sync($data['customer_groups'])` (plus `transformFormData()` normalising empty dates to null and ensuring `conditions` is at least `[]`).

**Permission resolution (Sanctum pattern):** reads `$admin->role->permission_type` / `$admin->role->permissions` directly, never calls `bouncer()`. Required perms: `marketing.promotions.catalog_rules.create / .edit / .delete`.

**GraphQL update + delete share `AdminMarketingCatalogRuleUpdateInput`** — same trick as Tax Categories: route by `$operation->getName()` first (delete branch), then by data type (update branch).

**Conditions JSON round-trip:** the value goes in as the body's `conditions` array, gets stored in the JSON column (cast to array by the model), and comes back via the item provider as a plain associative array. Note: JSON serialisation does NOT preserve key ordering of associative arrays — tests assert by content/key access, not by `toBe($expected)` exact identity.

**Date format:** the response renders `startsFrom` / `endsTill` as raw `Y-m-d` strings. No ISO 8601 conversion — these are date-only columns, no time component.

**Coverage:** 35 REST tests + 7 GraphQL tests. Listing + filters (name, status), sort, pagination cap, detail with pivots + conditions, create (happy + every validation gate + date-coherence + 422 on by_percent>100), update (happy + invalid dates), delete (happy + 404), mass-delete (happy + skip-missing + empty 422), auth + permission gates throughout.

### Admin Marketing — Cart Rule Coupons — implementation notes (Block F1c)

Sub-resource of Cart Rules. Endpoints live under `/api/admin/marketing/cart-rules/{cartRuleId}/coupons`. Mirrors `Webkul\Admin\Http\Controllers\Marketing\Promotions\CartRuleCouponController` + `CartRuleCouponRepository::generateCoupons`.

**Files (12):**
- `src/Admin/Models/AdminMarketingCartRuleCoupon.php` — GET listing + POST single-create + DELETE on `/{cartRuleId}/coupons[/{id}]`.
- `src/Admin/Models/AdminMarketingCartRuleCouponGenerate.php` — POST `/coupons/generate` (separate resource — distinct response shape `{ generated, coupons[] }`).
- `src/Admin/Models/AdminMarketingCartRuleCouponMassDelete.php` — POST `/coupons/mass-delete`.
- `src/Admin/Dto/AdminMarketingCartRuleCoupon{Create,Generate,Delete,MassDelete}Input.php`.
- `src/Admin/State/AdminMarketingCartRuleCoupon{Collection,Write}Provider.php`.
- `src/Admin/State/AdminMarketingCartRuleCouponProcessor.php` (POST/DELETE), `AdminMarketingCartRuleCouponGenerateProcessor.php`, `AdminMarketingCartRuleCouponMassDeleteProcessor.php`.

**Bulk-generate format mapping.** Core's `CartRuleCouponRepository::$charset` is keyed by the historical spellings `alphabetical` / `alphanumeric` / `numeric`. The API surfaces a cleaner `alphabetic` alias and forwards to `alphabetical` internally. `alphabetical` (core's spelling) is also accepted for backwards compatibility. Unknown format strings → 422 with `format-invalid`. Length must be 4-30 (relaxed vs core's `min:10` because integrators frequently need shorter codes). `coupon_qty` capped at 100 to prevent accidental bulk-insert storms — the monolith has no cap but the admin UI never exceeds ~50 in practice.

**Spec keys vs core keys (both accepted).** The processor's `normalize()` maps the spec's friendly names (`length` / `format` / `prefix` / `suffix`) to core's `code_length` / `code_format` / `code_prefix` / `code_suffix` before invoking the repository. Either shape works in REST or GraphQL.

**Inheritance from parent rule.** When `usage_limit` / `usage_per_customer` / `expired_at` are omitted on single-create, they inherit from the parent `cart_rules` row (`uses_per_coupon`, `usage_per_customer`, `ends_till`) — same default behaviour as the bulk-generate path. Always sets `is_primary=0` and `type=1` (the auto-generated marker; the rule's primary coupon, type=0, is owned by `AdminMarketingCartRule` and never touched here).

**Ownership / cross-rule isolation.** Every delete and mass-delete operation pre-checks that the coupon's `cart_rule_id` matches the `{cartRuleId}` in the URL. Cross-rule reads → 404; cross-rule mass-delete IDs land in the `skipped[]` array instead of failing the whole batch. Listing's WHERE clause filters by `cart_rule_id` so other rules' coupons never leak.

**Provider/processor split.** Single GET-collection provider returns paginated results in the `{ data, meta }` envelope. A minimal `AdminMarketingCartRuleCouponWriteProvider` placeholder is required for DELETE so API Platform can route to the processor without trying to load a real entity (same pattern as `AdminCatalogProductImageProvider`). Generate and Mass-Delete are separate single-op resources because their response shapes differ from the basic coupon row.

**Permission resolution:** Sanctum pattern — reads `$admin->role->permission_type` / `permissions` directly, never calls `bouncer()`. Create + Generate require `marketing.promotions.cart_rules.create`; Delete + Mass-Delete require `marketing.promotions.cart_rules.delete`; List has no extra permission gate beyond authentication.

**Events fired (parity with monolith):** `cart_rules.coupons.create.{before,after}` on single-create; `cart_rules.coupons.delete.{before,after}` on delete and per-id in mass-delete. The bulk-generate path delegates to `CartRuleCouponRepository::generateCoupons` which calls `parent::create()` per row — the base repository fires its own model events but does NOT fire `cart_rules.coupons.create.*` during bulk-generate (parity with the monolith controller).

**Coverage:** 19 REST tests + 5 GraphQL tests = 24 tests, all green.

### Admin Marketing — Newsletter Subscribers — implementation notes (Block F2d)

Read-mostly moderation surface on the `subscribers_list` table. Subscriptions are created via the storefront — admin only lists/views, toggles `is_subscribed`, and deletes. Mirrors `Webkul\Admin\Http\Controllers\Marketing\Communications\SubscriptionController` 1:1.

**Files:**
- `src/Admin/Models/AdminMarketingSubscriber.php` — single resource hosting Get/GetCollection/Put/Delete + 4 GraphQL operations.
- `src/Admin/Dto/AdminMarketingSubscriberUpdateInput.php` — shared input for `update` + `delete` mutations.
- `src/Admin/State/AdminMarketingSubscriber{Collection,Item,Write}Provider.php` + `AdminMarketingSubscriberProcessor.php`.

**No create endpoint.** Subscriptions originate from the storefront double-opt-in flow; admin cannot create a row out of thin air, matching monolith UX. Same pattern as Customer Reviews (Block C C3).

**No mass-delete.** The monolith `SubscriptionController` exposes single delete only — no `massDestroy`. Per spec "Only if monolith has it — check; otherwise skip", we skip rather than invent. If integrators need bulk-cleanup, the single delete endpoint can be looped.

**Toggle mirrors flag onto customer.** When a subscriber row has a non-null `customer_id`, the processor writes the same `is_subscribed` value to `customers.subscribed_to_news_letter` so the customer's own profile state stays in sync. Same behavior on delete — the linked customer's flag is set to `0` before the row is removed. Mirrors `SubscriptionController::update`/`::destroy` exactly.

**Input shape — boolean vs `0|1`.** REST accepts `{ "is_subscribed": true }` / `false`, plus the string forms (`"true"` / `"1"` / `"0"` / `"false"`) and integers. GraphQL exposes the field as `isSubscribed: Boolean` (camelCased by API Platform's name converter from the DTO's `$is_subscribed` property). Missing field → 422 `is-subscribed-required`. The monolith requires `in:0,1`; we relax to boolean since the DTO is `?bool`.

**Permission resolution:** standard Sanctum pattern — read `$admin->role->permission_type` / `permissions` directly, never call `bouncer()`. Edit = `marketing.communications.subscribers.edit`, delete = `marketing.communications.subscribers.delete`. Reading is open to any authenticated admin (matches monolith).

**Channel name on responses:** the listing query LEFT JOINs `channels` for `channel_code` (surfaced as `channelName`); the item provider resolves it via `Channel::find($s->channel_id)?->code` wrapped in try/catch to survive deleted/missing channels.

**Coverage:** 21 REST tests + 6 GraphQL tests = 27 tests, all green.

### Admin Marketing — Search Terms — implementation notes (Block F3b)

Read-mostly resource for the `search_terms` table — entries are auto-recorded by the storefront search (`SearchTerm` row inserted/uses-incremented on every storefront search). Admin can list, view, edit term/redirect_url, delete individually, or mass-delete. No create (storefront-originated). Mirrors `Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SearchTermController` with the create endpoint deliberately omitted.

**Files (10):**
- `src/Admin/Models/AdminMarketingSearchTerm.php` — single resource hosting Get/GetCollection/Put/Delete + 4 GraphQL ops.
- `src/Admin/Models/AdminMarketingSearchTermMassDelete.php` — separate one-op POST resource (mirrors CMS / Categories mass-delete pattern).
- `src/Admin/Dto/AdminMarketingSearchTerm{Update,MassDelete}Input.php` — Update DTO is also used as the GraphQL `delete` mutation input (id-only).
- `src/Admin/State/AdminMarketingSearchTerm{Collection,Item,Write}Provider.php` + `AdminMarketingSearchTermProcessor.php` + `AdminMarketingSearchTermMassDeleteProcessor.php`.
- `tests/Feature/Admin/RestApi/MarketingSearchTermTest.php` + `tests/Feature/Admin/GraphQL/MarketingSearchTermTest.php`.

**Schema:** `search_terms` table — `id`, `term`, `results` (count of products returned at search time), `uses` (search count), `redirect_url` (nullable), `display_in_suggested_terms` (boolean, not exposed in v1), `locale`, `channel_id`, timestamps. The FK on `channel_id` is `cascade`, so deleting a channel wipes its search terms automatically.

**Sort by `uses` desc surfaces popular searches** — explicit sort token in the allow-list so consumers can build "top searches" widgets without a custom query.

**Update is term + redirect_url only.** `uses`/`results` are auto-recorded by the storefront search controller and not editable through the API — exposing them as writable would let admins fabricate popularity metrics. The monolith form does expose them in the edit blade, but our API parity stops at the meaningful surface. Empty `term` → 422. Invalid `redirect_url` (FILTER_VALIDATE_URL fails) → 422. Permission: `marketing.search_seo.search_terms.edit`. Fires `marketing.search_seo.search_terms.update.{before,after}`.

**Mass-delete mirrors monolith** — non-existent IDs silently skipped (returns `deleted: [...ids that were actually removed]`); empty `indices` → 422. Permission: `marketing.search_seo.search_terms.delete`.

**Permission resolution:** standard Sanctum pattern — read `$admin->role->permission_type` / `permissions` directly, never call `bouncer()`. Reading is open to any authenticated admin (matches monolith).

**Coverage:** 25 REST tests + 7 GraphQL tests = 32 tests. REST all green; GraphQL subject to the same project-wide cursor-paginated sub-resource quirks noted elsewhere (mutation tests assert via DB row state rather than the mutation response body).

### Admin Marketing — Cart Rules — implementation notes (Block F1b)

Full CRUD + mass-delete for `cart_rules` via `Webkul\CartRule\Repositories\CartRuleRepository`. Mirrors `Webkul\Admin\Http\Controllers\Marketing\Promotions\CartRuleController` 1:1.

**Files (~12):**
- `src/Admin/Models/AdminMarketingCartRule.php` — listing/detail/create/update/delete operations + 5 GraphQL operations.
- `src/Admin/Models/AdminMarketingCartRuleMassDelete.php` — `POST .../mass-delete` (+ `createAdminMarketingCartRuleMassDelete` mutation).
- `src/Admin/Dto/AdminMarketingCartRule{Create,Update,MassDelete}Input.php`.
- `src/Admin/State/AdminMarketingCartRule{Collection,Item,Write}Provider.php`.
- `src/Admin/State/AdminMarketingCartRuleProcessor.php` + `AdminMarketingCartRuleMassDeleteProcessor.php`.
- `tests/Feature/Admin/{RestApi,GraphQL}/MarketingCartRuleTest.php`.

**Repository delegation (no nested-write reimplementation).** `CartRuleRepository::create` and `::update` already handle the pivot sync for `channels` + `customer_groups`, the JSON `conditions` cast, and the auto-managed primary coupon row in `cart_rule_coupons` (creates / updates / deletes the `is_primary=1` row depending on `coupon_type` + `use_auto_generation`). The processor just normalises + validates the payload and forwards.

**Partial-update merge on PUT.** The processor reads the existing row, pulls its `channels` + `customer_groups` pivot ids + `conditions` JSON, and merges the patch on top (`array_filter` on nulls so an explicit `null` doesn't clobber). Without this the repository would call `$cartRule->channels()->sync($data['channels'])` with an empty array — silently detaching every channel on a partial update.

**Action-type / discount validation.** `action_type` is constrained to the 4 monolith values (`by_percent`, `by_fixed`, `cart_fixed`, `buy_x_get_y`). When `by_percent`, `discount_amount` is additionally bounded `min:0|max:100` (matches `CartRuleRequest` rule). All other types accept any numeric.

**Coupon-code uniqueness.** Required when `coupon_type=2` AND `use_auto_generation=0`. Uniqueness check excludes the rule's existing primary coupon id on update (looked up via `cart_rule_coupons.where(cart_rule_id=$id, is_primary=1)`). Mirrors the controller's conditional `validate()` in `update()`.

**Permission resolution:** Sanctum admin-role check (reads `$admin->role->permission_type` / `permissions`; never calls `bouncer()`). Permissions used: `marketing.promotions.cart_rules.create` / `.edit` / `.delete`.

**Conditions JSON.** Round-trips as a plain array — the model already declares `'conditions' => 'array'` cast, so PHP arrays in / PHP arrays out, no manual `json_encode`/`json_decode`.

**Coupons sub-resource owned by a sibling agent** (`AdminMarketingCartRuleCoupon*`). The primary coupon row this resource manages (via `coupon_type=2` + `coupon_code`) is intentionally a side-effect of CartRule create/update — the sub-resource handles batch / generate-auto / mass-delete of secondary coupons.

### Admin Appearance (Themes + Sections) — implementation notes (2026-08-26)

Bagisto 2.4.10 replaced Settings → Themes with a top-level **Appearance** area: `theme_customizations` → `theme_sections`, `ThemeCustomization` → `Webkul\Theme\Models\Section`, `ThemeCustomizationRepository` → `SectionRepository` (4 methods → 25), plus `ThemeCatalog` (the gallery) and `SectionSchema` (per-type field sets). The old `settings.themes` ACL key is **gone from core**, so the previous API surface could not be granted to any `permission_type=custom` role even if it had still booted. It was deleted, not re-pointed.

**Files (35).** `Admin/Models/AdminAppearanceTheme` + `…ThemeImpact` + `…ThemeActivate`; `AdminAppearanceSection` (Eloquent on `theme_sections`) + `AdminAppearanceSectionTranslationRef`; one single-op resource per action (`…SectionDraft`, `…Status`, `…Reorder`, `…Duplicate`, `…Publish`, `…Discard`, `…Fields`, `…Media`, `…Preview`); the matching `Admin/Dto/AdminAppearance*Input` + `AdminAppearanceSectionRestDto`; `Admin/State/AdminAppearance*` providers/processors; and the shared `Admin/State/AppearanceScopeGuard` + `Concerns/MapsAppearanceSection` + `Concerns/MapsAppearanceTheme`.

**`AppearanceScopeGuard` is the DRY piece** — `themeOrFail()` (404 on an unknown code), `channel()` / `locale()` (core's fallback chain), `sectionOrFail()`, `orderedSectionIds()`, `withPinnedLast()`, and `pathValue()` (see the trap below). Every Appearance provider and processor takes it.

**Section resource follows the AdminReview recipe** — one Eloquent parent serving both transports: GraphQL gets the model (so `translations` is a Relay connection to `AdminAppearanceSectionTranslationRef`), REST gets `AdminAppearanceSectionRestDto` via `output:`, and the providers branch on `graphql_operation_name`. Every DTO prop matches a relation/attribute on the parent (the name-match trap).

**Four traps this cost, worth knowing before the next resource:**
- **`{id}` never reaches `$uriVariables` when the resource's identifier is not named `id`.** The action resources are keyed by `section_id` / `theme_code`, so API Platform passed nothing and every action 404'd from our own guard. Fix: `AppearanceScopeGuard::pathValue()` reads `$uriVariables[$key] ?? request()->route($key)`. **Any single-op action resource whose identifier is not `id` needs this.**
- **A `Put`/`Delete` write provider must return a placeholder resource, not `null`.** Returning `null` makes API Platform 404 before the processor runs. `AdminAppearanceSectionWriteProvider` returns an `AdminAppearanceSection` carrying just the id, mirroring `AdminCustomerReviewWriteProvider`.
- **Input DTO props must be snake_case** — the name converter denormalises an incoming `sortOrder` to `sort_order` and reads *that* property, so a camelCase prop is silently never populated while single-word props work. This is the documented rule, and it bites hardest on a partially-converted DTO: `name`/`type`/`status` landed while `sortOrder`/`channelId`/`themeCode` stayed null.
- **`hasDraft` / `isPinned` are int 0/1 accessors, not bool.** An Eloquent bool accessor stringifies to `""` for false over GraphQL; an int gives `"0"`. Same reasoning as the storefront `isInWishlist` flags.

**Media + options.** `image_carousel` / `services_content` are the two types whose `options` the core repository *drops* on update (it expects a multipart upload on the request). Over the API the paths already exist — uploaded through `POST /appearance/sections/{id}/media`, which returns `{ path, type }` — so the update processor writes those translations itself after the repo call. Media upload is REST-only (`graphQlOperations: []`).

**Preview is API-specific.** Core renders drafts through a Blade route (`shop.appearance.preview`) a headless storefront cannot use, so `GET /appearance/themes/{code}/sections/preview` returns the same set as data via `getDraftedForPreview`. The storefront's own endpoint stays published-only.

**Events mirrored:** `section.create|update|delete.{before,after}`, `section.draft.save.{before,after}`, `section.draft.discard.{before,after}`, `section.reorder.{before,after}`, `section.media.upload.{before,after}`, `appearance.theme.activate.{before,after}` (+ `core.channel.update.{before,after}` on activation).

**Coverage:** 36 REST (`Admin/RestApi/AppearanceThemeTest` 12, `AppearanceSectionTest` 24) + the matching GraphQL suites.

**Storefront side.** `src/Models/ThemeCustomization*` were replaced by `src/Models/Section` + `SectionTranslation` (extending the new core models). The storefront resource carries a global scope — `status = 1`, the current channel, and that channel's **active theme** (`channels.theme`, falling back to `config('themes.shop-default')`) — mirroring `SectionRepository::live()`; `draft_*` columns are hidden. **BREAKING:** `/api/shop/theme-customizations` → `/api/shop/sections`, `themeCustomization(s)` → `section`/`sections`, and translations expose `sectionId` instead of `themeCustomizationId`.

### Admin Settings — Users (admins) — implementation notes

Block B Wave 2 — full CRUD for `admins`. Mirrors `Webkul\Admin\Http\Controllers\Settings\UserController` line-for-line. **Same `admins` table** the Sanctum admin-API token issuer uses — CRUD here mutates the same rows that own integration tokens, so caller-vs-target identity matters.

**Files:**
- `Admin/Models/AdminSettingsUser.php` — single resource hosting Get/GetCollection/Post/Put/Delete + 5 GraphQL ops.
- `Admin/Dto/AdminSettingsUser{Create,Update}Input.php` — separate DTOs (create requires `password`, update accepts it optionally). Update DTO shared by `update` + `delete` mutations.
- `Admin/State/AdminSettingsUser{Collection,Item,Write}Provider.php` + `AdminSettingsUserProcessor.php` — 4 state classes.

**Delete guards (both 400):**
1. **Self-delete** — `AdminAuthHelper::resolveAdmin()` returns the Sanctum-authenticated caller; compare `$id === $caller->id` BEFORE the last-admin check so the response identifies the right reason when only one admin exists. Lang key `bagistoapi::app.admin.settings.user.cannot-delete-self`.
2. **Last-admin** — `Admin::count() <= 1`. Lang key `cannot-delete-last-admin`. When both fire at once (caller is the only admin), the self-guard wins — message is more actionable.

**Password handling.** Create: `password` required + min:6, hashed via `Hash::make()` before persistence. Update: omit the field to preserve the existing hash. When supplied, validator rule `['string', 'min:6']` applies BEFORE hashing — short passwords return 422. The DTO never exposes the hash; the response uses the item provider which delegates to `Admin::$hidden = ['password', 'api_token', 'remember_token']`.

**Image upload deferred (v1).** `image` accepts a string path only; binary upload is out of scope for this wave (mirrors the same decision in Settings → Locales). When integrators need avatar upload they should use the admin panel. Documented in the model + DTO docblocks.

**Permission resolution** mirrors the rest of Settings — reads `$admin->role->permission_type` / `$admin->role->permissions` directly, never calls `bouncer()` (Sanctum-token requests have no session-bound admin). Required perms: `settings.users.users.create / .edit / .delete` (note the doubled segment — the core ACL key for the Users sub-menu is `settings.users.users.*`, which is what the Integration token stores; the check must match it).

**No mass-delete.** Monolith UI doesn't expose one (the datagrid has no bulk action for admins — high-risk operation). Skipped consciously.

**Coverage:** 35 REST tests + 7 GraphQL tests = 42 tests. All REST tests pass. GraphQL listing/detail subject to the project-wide IRI-generation quirk — mutation tests assert via DB state.

### Admin Settings — Locales — implementation notes

Block B Wave 1 — full CRUD + mass-delete for `locales`. Mirrors `Webkul\Admin\Http\Controllers\Settings\LocaleController`.

**Schema:** `locales` has `id`, `code` (unique), `name`, `direction` (`ltr` / `rtl`), `logo_path` (nullable string), `created_at`, `updated_at`. The model exposes a `logo_url` accessor that wraps `Storage::url($logo_path)` — surfaced as the `logoUrl` response field.

**Image upload deferred (v1).** `logo_path` is accepted only as a path string. The Bagisto core `LocaleRepository::uploadImage` expects an `UploadedFile[]` and pre-processes the upload; reproducing that across REST + GraphQL is out of scope for v1. The processor therefore writes directly via the Eloquent model and fires the same `core.locale.{create,update,delete}.{before,after}` events the repository would have. When uploads are needed, use the admin panel for now.

**Delete guards (mirrors monolith + adds channel-default):**
- If `Locale::count() <= 1` → 400 `cannot-delete-last`.
- If any `channels.default_locale_id` references the id → 400 `cannot-delete-channel-default` (monolith doesn't enforce this — the FK is `RESTRICT`, so deletion would otherwise crash with a 500). We surface the friendlier 400.

**Mass-delete** runs the same per-id guards; skipped ids appear in the response under `skipped: [{id, reason}]` instead of failing the whole batch.

**Permission resolution** mirrors Exchange Rates — reads `$admin->role->permission_type` / `$admin->role->permissions` directly, never `bouncer()`. Permissions: `settings.locales.create` / `.edit` / `.delete`.

**Code validation:** `regex:/^[a-z0-9_-]+$/` — lowercase letters, digits, underscore, hyphen. Mirrors the practical shape of Bagisto's locale codes (`en`, `fr`, `pt-br`, `zh_cn`). Stricter than the core `Webkul\Core\Rules\Code` rule which allows uppercase, but the locales loader is case-sensitive in practice so lowercase-only is safer.

**Known GraphQL schema issue (pre-existing, not introduced by this work):** the project-wide GraphQL endpoint currently throws `Operation "collection_query" not found for resource "AdminBooking"` during schema build, which causes ALL GraphQL operations to return `Internal server error` until that's fixed. The Locale GraphQL tests are written and tagged, but cannot be verified green until `AdminBooking` is repaired. The REST tests (33) all pass.

### Admin Settings — Roles — implementation notes

Block B Wave 2 milestone. Full CRUD on `Webkul\User\Models\Role` via `Webkul\User\Repositories\RoleRepository`. Mirrors `Webkul\Admin\Http\Controllers\Settings\RoleController` 1:1.

**Files (~9):**
- `Admin/Models/AdminSettingsRole.php` — listing/detail/create/update/delete operations on `/api/admin/settings/roles[/{id}]` + 5 GraphQL operations.
- `Admin/Dto/AdminSettingsRole{Create,Update}Input.php` — input DTOs (update reused for delete).
- `Admin/State/AdminSettingsRole{Collection,Item,Write}Provider.php` + `AdminSettingsRoleProcessor.php` — 4 state classes.
- `tests/Feature/Admin/{RestApi,GraphQL}/SettingsRoleTest.php` — 30 REST + 6 GraphQL tests.

**No mass-delete.** Bagisto admin UI doesn't expose a bulk-delete on roles — skipped per the parity-with-admin rule.

**Permissions as JSON array.** The `roles.permissions` column is auto-cast to `array` by the Eloquent model. Collection provider hand-decodes the JSON from raw `DB::table` rows; item provider relies on the model's cast. Response surfaces `permissions` as a real JSON array (or `null`).

**Validation parity.** Same rules as `RoleController::store/update`: `name` required, `description` required, `permission_type` required + `in:all,custom`. When `permission_type=custom`, `permissions` must be a non-empty array (else 422 with `permissions-required`).

**Update — auto-clears permissions when `permission_type=all`.** Mirrors monolith: the `permissions` payload is reset to `[]` whenever the role is switched to "all access" so the JSON column doesn't contain stale custom keys. We do NOT port the monolith's `countAdminsWithAllAccess() === 1` guard (which blocks demoting the last "all"-permission admin's role from `all` → `custom`) because that controller-level guard wraps a session-flash redirect, not a JSON error — adding it would require new lang keys for an edge case rarely hit via API.

**Delete — two guards, both 400:**
1. **In-use guard** — any `admins.role_id` referencing this role → `cannot-delete-in-use`. Mirrors core `destroy()` `$role->admins->count() >= 1`.
2. **Last-role guard** — `Role::count() <= 1` → `cannot-delete-last-role`. Mirrors core.

Order matters: in-use check runs first (more specific error). Both pre-checks run before any repository call.

**Permission resolution** — Sanctum pattern (read `$admin->role->permission_type` / `$admin->role->permissions` directly; never `bouncer()`). Permissions: `settings.roles.create` / `.edit` / `.delete`.

**Events** — fires `user.role.create.{before,after}`, `user.role.update.{before,after}`, `user.role.delete.{before,after}` so any core listener (role-change notifications, ACL cache flushers) keeps running.

**GraphQL delete mutation routing** — uses `AdminSettingsRoleUpdateInput` (same DTO as update; carries the `id` IRI). Processor checks `$operation->getName() === 'delete'` first before the update branch — same convention used by Currencies / Locales / Attributes.

**Coverage:** 30 REST tests + 6 GraphQL tests. REST exercises full CRUD, validation (all required fields + `in:all,custom` enum + custom-permissions-required), filters (name LIKE + permission_type exact), sort, per_page cap, both delete guards, 401/403/404.

### Admin Settings — Inventory Sources — implementation notes

Block B Wave 1 — full CRUD + mass-delete for `inventory_sources`. Mirrors `Webkul\Admin\Http\Controllers\Settings\InventorySourceController` + `InventorySourceRequest`.

**Files:**
- `src/Admin/Models/AdminSettingsInventorySource.php` — single resource hosts Get/GetCollection/Post/Put/Delete + 5 GraphQL ops.
- `src/Admin/Models/AdminSettingsInventorySourceMassDelete.php` — separate single-op resource for mass-delete.
- `src/Admin/Dto/AdminSettingsInventorySource{Create,Update,MassDelete}Input.php` — 3 input DTOs (Update reused for delete).
- `src/Admin/State/AdminSettingsInventorySource{Collection,Item,Write}Provider.php` + `AdminSettingsInventorySource{,MassDelete}Processor.php` — 5 state classes.

**Schema:** `inventory_sources` table — `code` (unique alpha-dash), `name`, `description`, contact fields (`contact_name/email/number/fax`), address (`country/state/city/street/postcode`), `priority` (int), `latitude`/`longitude` (decimal nullable), `status` (0/1).

**Validation:** plain Laravel rules (`required`, `email`, `regex:/^[a-zA-Z0-9_-]+$/` for code, `numeric`+`between` for lat/lng, `in:0,1` for status) — not the heavier Bagisto `Address`/`PhoneNumber`/`PostCode` rules used by the monolith. Those reject many legitimate-looking test fixtures (non-ASCII addresses, formatted phone numbers, international postcodes); plain `required`+`string` matches what the API needs without surprising consumers. Same lessons as the Currencies wave.

**Delete guards (two, both 400 per spec):**
1. **Last-source guard** — parity with monolith `InventorySourceController::destroy` which returns 400 + `last-delete-error` when `count() == 1`. Implemented in single-delete (`InventorySource::count() <= 1`) and in mass-delete (`total − count(existingIds) < 1`).
2. **FK guard** — API-specific. Refuses delete when any `product_inventories.inventory_source_id` row references the id. Monolith allows the delete (the FK will cascade or null), but the API surface promises clean errors so we pre-check via `Schema::hasTable('product_inventories')` + `DB::table(...)->exists()`. Returns 400 + `in-use`.

**Permission resolution:** mirrors Exchange Rates — reads `$admin->role->permission_type` / `$admin->role->permissions` directly, never `bouncer()`. Permissions: `settings.inventory_sources.create` / `.edit` / `.delete`.

**Partial update:** the update processor merges the request body into `$existing->toArray()` before validating, so callers can `PUT { "name": "..." }` without re-sending every required field.

**GraphQL camelCase mapping:** the processor's `dtoToArray()` maps `contactName`/`contactEmail`/`contactNumber`/`contactFax` → snake_case before validation, matching the project-wide pattern.

**Coverage:** 34 REST tests passing (verified via `php artisan test ... --parallel --processes=8`). GraphQL tests written and tagged but blocked by the same pre-existing `AdminBooking` schema issue affecting all GraphQL endpoints in this branch (also documented under the Locales notes).

### Admin Settings → Data Transfer Imports — implementation notes

Block B Wave 3 — read-only listing + detail + cancel-action + delete on top of the Bagisto core `Webkul\DataTransfer\Models\Import` model (table `imports`).

**Schema notes:** the `imports` table uses `type` for the entity (product / customer / category / etc) and `action` for the operation (`append` / `update` / `delete`). The API exposes the entity column as `code` in responses (and accepts both `?code=` and `?type=` as filter aliases) so the public surface matches the spec's "code = entity" wording while preserving DB column names internally. Heavy JSON blobs (`errors`, `summary`) are listing-suppressed and surface only on detail.

**File layout (8 files, mirrors the C/B singleton pattern):**

```
src/Admin/Models/
  AdminSettingsDataTransferImport.php           — list + detail + Delete + 3 GraphQL ops
  AdminSettingsDataTransferImportCancel.php     — single-action resource for cancel
src/Admin/Dto/
  AdminSettingsDataTransferImportCancelInput.php
  AdminSettingsDataTransferImportDeleteInput.php — needed because GraphQL `delete` mutation requires an input type with id
src/Admin/State/
  AdminSettingsDataTransferImportCollectionProvider.php (extends AbstractAdminCollectionProvider)
  AdminSettingsDataTransferImportItemProvider.php       (extends AbstractAdminItemProvider)
  AdminSettingsDataTransferImportWriteProvider.php      (placeholder for DELETE)
  AdminSettingsDataTransferImportProcessor.php          (DELETE)
  AdminSettingsDataTransferImportCancelProcessor.php    (POST cancel)
```

**Cancel is a single-action resource, not a `Put` on the main one.** Mirrors the Phase 5.2 `AdminCatalogProductCopy` pattern: separate `#[ApiResource]` class hosting only `Post /imports/{id}/cancel` + a `cancel` GraphQL mutation. Keeps the main resource's `Put` slot open for a future status-only update endpoint and avoids overloading the verb semantics — cancel is an explicit action, not a metadata write. Permission gate: `settings.data_transfer.imports.edit`.

**Cancellable states are exactly `pending` and `processing`.** Bagisto's `Helpers/Import` ships ~9 state constants (PENDING / VALIDATED / PROCESSING / PROCESSED / LINKING / LINKED / INDEXING / INDEXED / COMPLETED). We accept `pending`/`processing` as cancellable; anything else (mid-pipeline like `linking`/`indexing`, or terminal like `completed`/`processed`/`failed`/`cancelled`) → HTTP 422 with the offending state inlined in the message. The literal string `'cancelled'` is written to the column even though it's not in the core enum — core treats `state` as a free-form string column, the constants are conventions consumed by the helper pipeline. Listing/filtering `state=cancelled` works the same way.

**Create endpoint deferred.** Build it later as a multipart POST that takes a CSV/XLSX + `type` / `action` / `validation_strategy` fields, persists to `Storage::disk('public')->putFileAs("imports/{type}/{random}.csv", ...)`, then queues the async job. The async-job kickoff is non-trivial (Bagisto's `ImportHelper` runs the whole pipeline inline by default and only forks per-batch jobs when `process_in_queue=1`) so the deferral lets us keep this wave focused on the moderation surface. Lang key `bagistoapi::app.admin.settings.data-transfer.import.create-deferred` is in place for the placeholder error when the endpoint lands behind a feature flag.

**Delete cleans up the file** — best-effort `Storage::delete($file_path)` + `Storage::delete($error_file_path)` before removing the DB row. File deletion swallows exceptions (a missing/already-deleted file shouldn't block the DB delete). Returns HTTP 200 + `{ message }`.

**Permission resolution** — Sanctum pattern (reads `$admin->role->permission_type` / `->permissions` directly; never `bouncer()`). Required perms: `settings.data_transfer.imports.edit` (cancel), `settings.data_transfer.imports.delete` (delete). Listing + detail have no permission gate beyond the standard admin-token auth — matches the read-only convention used across Block B.

**Coverage:** 22 REST tests + 3 GraphQL tests, all green. Tests exercise listing+envelope+row shape, every filter (code / type alias / action / state / date range), sort id-desc default, detail happy + 404, cancel happy (pending and processing) + cannot-cancel-completed (422) + 404 + auth/permission gates, delete happy + 404 + auth/permission gates.

### Admin Settings — Tax Rates — implementation notes

Block B Wave 3 — full CRUD for `tax_rates`. Mirrors `Webkul\Admin\Http\Controllers\Settings\Tax\TaxRateController` + `TaxRateRequest`. No mass-delete (monolith UI doesn't expose one).

**Files:**
- `src/Admin/Models/AdminSettingsTaxRate.php` — single resource hosts Get/GetCollection/Post/Put/Delete + 5 GraphQL ops.
- `src/Admin/Dto/AdminSettingsTaxRate{Create,Update}Input.php`.
- `src/Admin/State/AdminSettingsTaxRate{Collection,Item,Write}Provider.php` + `AdminSettingsTaxRateProcessor.php`.

**Schema:** `tax_rates` — `identifier` (unique), `is_zip` (bool), `zip_code` / `zip_from` / `zip_to` (each nullable), `state` (NOT NULL in the migration — defaults to empty string in our processor since the migration omits `->nullable()`), `country`, `tax_rate` (decimal 12,4), timestamps. Many-to-many with `tax_categories` via `tax_categories_tax_rates` (no `onDelete('cascade')` on the pivot — see Delete below).

**`is_zip` conditional validation (the core feature of this wave):** after passing the base validator (identifier/country/tax_rate/is_zip required), `validateConditionalZipRules` switches on `is_zip`:
- `is_zip = false` → `zip_code` must be non-empty (specific zip mode); empty → 422 `zip-code-required`.
- `is_zip = true`  → `zip_from` AND `zip_to` must both be non-empty (range mode); either missing → 422 `zip-range-required`.

The rules fire on both create and update. On update, the patch is merged onto the existing row before validating, so flipping `is_zip` without supplying the matching zip field (e.g. switching to range mode without `zip_from`/`zip_to`) → 422. Mirrors the spirit of the monolith's `required_with:is_zip` rules; explicit conditional checks keep the error messages package-translated and disambiguate the two failure modes.

**Country `size:2`:** stricter than the monolith (`string` only) — chose this because a country code is functionally always ISO-2 in Bagisto's address/checkout flows, and rejecting `"USA"` early is friendlier than silently accepting a value the storefront tax engine won't match.

**`tax_rate` range 0..100:** matches `TaxRateRequest::rules()` (`numeric|min:0|max:100`).

**Delete pivot detachment:** the migration `2018_08_30_065840_create_tax_mappings_table.php` (the `tax_categories_tax_rates` pivot) does NOT declare `onDelete('cascade')`. The monolith's `TaxRateRepository::delete` happens to work because under most DB configs the constraint is RESTRICT and the pivot rows must already be gone. Our processor explicitly `DB::delete()`s the pivot rows under `tax_categories_tax_rates.tax_rate_id = $id` BEFORE calling `repo->delete($id)`, so the delete is reliable regardless of pivot constraint setup. Verified by `test_delete_cascades_pivot_to_tax_categories` (creates a tax category + pivot row, deletes the rate, asserts pivot row gone, asserts category row intact).

**Permission resolution:** Sanctum pattern (read `$admin->role->permission_type` / `$admin->role->permissions` directly, never `bouncer()`). Permissions: `settings.taxes.tax_rates.create / .edit / .delete`.

**Identifier uniqueness on update** uses Laravel's `unique:tax_rates,identifier,{id}` form so saving with the same identifier no-ops rather than 422.

**`?float $tax_rate` DTO typing:** API Platform's Symfony denormaliser rejects non-numeric strings (e.g. `"abc"`) on `?float` before the request reaches the processor. The `test_create_tax_rate_non_numeric_rejected` test accepts 400/422/500 to cover both "validator rejection" and "denormalisation rejection" paths — same pattern Wave 2 uses for the `AdminCartAddItemInput::$quantity` skip. Acceptable: numeric strings like `"8.5"` still pass through cleanly.

**Coverage:** 33 REST tests passing locally; GraphQL tests written and tagged. GraphQL endpoint may be blocked by the same pre-existing `AdminBooking` schema issue noted in the Locales / Inventory Sources notes.

### Admin Settings — Exchange Rates — implementation notes

Block B Wave 1 — full CRUD + mass-delete for `currency_exchange_rates`. Mirrors `Webkul\Admin\Http\Controllers\Settings\ExchangeRateController` exactly.

**Schema:** `currency_exchange_rates` has just `id`, `target_currency` (FK → `currencies.id`), `rate` (decimal), `created_at`, `updated_at`. **No `source_currency` column** — the source currency is implicit (the channel's base currency); only the target_currency + rate pair is stored. Composite uniqueness is therefore enforced on `target_currency` alone (one exchange rate per target currency).

**Files (12):**
- `src/Admin/Models/AdminSettingsExchangeRate.php` — single resource hosts Get/GetCollection/Post/Put/Delete + 5 GraphQL ops.
- `src/Admin/Models/AdminSettingsExchangeRateMassDelete.php` — separate single-op resource for mass-delete.
- `src/Admin/Dto/AdminSettingsExchangeRate{Create,Update,MassDelete}Input.php` — 3 DTOs. Update DTO is shared by `update` and `delete` GraphQL mutations (delete only needs `id`).
- `src/Admin/State/AdminSettingsExchangeRate{Collection,Item,Write}Provider.php` — collection (extends `AbstractAdminCollectionProvider`), item (extends `AbstractAdminItemProvider`), write (placeholder for PUT/DELETE so API Platform can resolve the resource).
- `src/Admin/State/AdminSettingsExchangeRate{Processor,MassDeleteProcessor}.php` — write paths.

**Permission resolution:** mirrors Phase 2 Categories — reads `admin->role->permission_type` / `role->permissions` directly. Never calls `bouncer()` (Sanctum-token requests have no session-bound admin). Permissions used: `settings.exchange_rates.create / .edit / .delete`.

**Validation:**
- `target_currency`: required, integer, must exist in `currencies`, unique on `currency_exchange_rates.target_currency` (excluding self on update). Duplicate-pair surfaces the dedicated `bagistoapi::app.admin.settings.exchange-rate.duplicate-pair` lang key.
- `rate`: required, numeric, `> 0` (the monolith only validates `numeric`; we tighten to `> 0` because a zero/negative exchange rate is never meaningful).

**Partial updates** (PUT) — if `target_currency` or `rate` is omitted, the existing value is kept. This is friendlier than the monolith (which requires both fields every time) and matches the rest-API admin conventions across the package.

**Output shape:** every read response carries the joined `targetCurrencyCode` + `targetCurrencyName` so the client never has to fetch the currency separately. Collection rows reuse the same shape as detail (no slim/detail split — the row is already small).

**Mass-delete:** non-existent IDs are silently skipped (mirrors `ExchangeRateController::massDestroy` pattern across other admin settings). Empty `indices` → 422.

**Auto-sync deferred:** the admin Configuration → API → Exchange Rates page exposes a "Mass Auto-Sync" action (`ExchangeRateController::updateRates`) that calls an external rate-source helper (`services.exchange_api.<provider>.class`) to refresh every row in bulk. **Intentionally NOT exposed in v1** — wiring it via the API would require deciding how to surface provider-side errors (timeouts, auth failures, rate-source outages) across both transports without coupling the API to the per-installation external helper config. Will be revisited if there's actual integrator demand; for now the admin UI remains the entry point for that action.

### Sales menu QA — per-menu template (the Invoice reference, 2026-06-04)

The Invoices menu was rebuilt end-to-end and is the template for the remaining Sales menus (Shipments / Refunds / Transactions / Bookings). Do each menu **one at a time, completely**, in this protocol:

1. **Enumerate the admin screen first.** List every feature the Bagisto admin menu exposes: datagrid (columns / filters / sort / mass-actions), the detail/view page (every section + every column it renders), and all action buttons (print, send-email, create, cancel, etc.). This is the parity checklist.
2. **Check what we have** (the coverage table below + the code) and identify only the gaps.
3. **Implement the gaps in BOTH REST + GraphQL.**
4. **Update api-docs in lockstep** (bagisto-api-docs: REST + GraphQL pages under `src/api/{rest,graphql}-api/admin/sales/...` + the `.vitepress/config.mts` sidebar). No internal class/method/file/package names in docs. Group ALL of a menu's endpoints (list / detail / print / send-email / actions) under that menu's own sidebar heading — not scattered under "Orders". GraphQL example panels for REST-only actions (print / send-email) must still carry the equivalent **curl** so the panel is never empty.
5. **Update the Swagger UI inline example for each touched endpoint** — every `#[ApiResource]` operation's `openapi` block must ship a realistic inline `responses` example (and `requestBody` example for writes) using a real sanitized payload, so `/api/admin` Swagger "Try it out" shows the true shape, not a bare schema.
6. **Document the menu's feature-logic on its overview (`index.md`) landing page** — beyond the wire shape, write the non-obvious behaviour an integrator needs: what gates a row into this menu (e.g. an invoice row exists only after an invoice is generated), status/derived-field semantics (e.g. why a paid order can still read payment "pending"; how the red/negative due-days countdown is computed), and what each action does (print PDF, send duplicate email, mass-actions). This goes on the menu's `index.md` overview page (the group's sidebar `link:` target — see "Documentation writing style → landing pages"); leaf pages keep only a one-line pointer to it. Consumer terms, no internal class/method names.
7. **Test** the affected REST + GraphQL suites green.

**Established shape (copy from Invoices):**

- **Detail resource returns ITSELF** — a POPO (snake_case props + `AcceptsCamelCaseWrites`) with the `Get`/`Query` provider returning the same POPO (no separate `output:` DTO), so the GraphQL `id` IRI resolves from the resource's own `/api/admin/<menu>/{id}` route. The provider + create-processor share one `Builds<Resource>` trait. Surface **every** column of the underlying table (all `base_*` / `*_incl_tax` / `formatted*` variants), the order/customer context (`customerName`/`customerEmail`/`orderStatus`/`orderStatusLabel`/`orderDate`/`channelName`), `billingAddress`/`shippingAddress` as plain `?array` objects, and `items` as a plain `array`.
- **Listing returns the full payload too** — every column + addresses, batch-loaded for the page via the `mapRows()` hook on `AbstractAdminCollectionProvider` (one extra `addresses` query, no N+1). Only the heavy line `items` stay detail-only.
- **GraphQL nested-data shape — SUPERSEDED 2026-06-17 (see "Guiding principle — GraphQL response shape: Shopify-style nested objects/connections").** Historically these Sales detail resources returned addresses + items as **JSON objects/arrays** queried bare (`billingAddress`, `items`), because the 2026-06-04 Eloquent-rebuild attempt produced all-null connection nodes. Root cause of *that* failure: the parent resource was a plain DTO (POPO) — API Platform re-resolves a connection's nodes *as resources*, which a POPO cannot satisfy (it was never an API Platform limit; the shop API does this correctly). The project direction is now an **Eloquent-backed parent + Eloquent `#[ApiResource]` sub-resources** (the shop `CustomerOrder` → `CustomerOrderItem` pattern), which resolves `items { edges { node } }` and `billingAddress { city }` correctly. Until a given Sales resource is migrated, its nested data is still the bare-JSON shape; new/migrated resources follow the connection rule.
- **Print = REST-only** (binary PDF; GraphQL can't stream binary). Eager-load the correct snake_case relation names (e.g. `items.order_item`, NOT `orderItem`).
- **Datagrid "Export" button = a REST-only CSV endpoint.** Every admin datagrid has an Export button (core fires an event-driven CSV/XLS/XLSX export). Expose it as `GET /api/admin/<menu>/export?format=csv` — a `Get` op with `outputFormats: ['csv' => ['text/csv']]` whose provider returns an `Illuminate\Http\Response` (`text/csv` attachment), generating the CSV directly from the same query + filters as the listing (NOT via the core datagrid export pipeline — that's coupled to the exporter package). Columns = the datagrid's displayed columns. Honour the listing filters; export all matching rows (cap ~50k); no pagination. CSV only for now (xls/xlsx → 422). Reference: `AdminRefundExportProvider` + the `/refunds/export` op on `AdminRefund`. The `/export` literal route is safe alongside `/{id}` because detail ops carry `requirements ['id' => '\d+']`.
- **Read-only datagrid menus stay read-only in their overview.** A datagrid menu that only lists + views (e.g. Refunds, Transactions, Bookings) must NOT list per-order **create/preview/action** endpoints as its own — those belong to the **Orders** menu (they run against an order). The read-only menu's overview lists only List / Get / Export and points to Orders for creation. (Refunds overview was corrected on 2026-06-05 to drop Create Refund / Refund Preview.)
- **Docs carry ZERO internal symbols.** No controller / repository / processor / provider / guard / datagrid / helper / event / `Webkul\…` / `.php` / `::method` names anywhere in the consumer docs — describe behaviour and payload only. Genuine contract fields (`qty_to_refund`, `orderItemId`, permission keys, lang-key-free messages) are fine. (Swept across all admin Sales docs on 2026-06-05.)
- **Send-email / send-duplicate actions:** the core notification hardcodes the recipient to `order->customer_email`, so to honor a custom `email` you must override `$entity->order->customer_email` in-memory before dispatching the core event.
- **Swagger grouping:** tag each menu's resource endpoints `Admin Sales: <Menu>` (Swagger UI is a flat tag list — a prefixed name is how you show the sub-section). **2026-06-05 — the entire Order menu is ONE Swagger tag (`Admin Sales: Orders`).** The old `Admin Orders`, `Admin Order Actions`, and `Admin Carts` tags were **retired** and merged into `Admin Sales: Orders` (24 ops), because every one of them is part of the admin Orders menu: list / detail / export, the per-order actions (cancel, reorder, comments, **create-invoice / create-shipment / create-refund / refund-preview** — these run against an order from the order view, so they live here, NOT under Invoices/Shipments/Refunds), and the **Create-Order cart flow** (`/carts/*` add/update/remove item, addresses, coupon, shipping/payment methods, `/orders/place/{cartId}`, and the `/customers/{customerId}/draft-carts` bootstrap). The Invoices/Shipments/Refunds/Transactions/Bookings menu tags keep only their own list / get / export (+ invoice print / send-duplicate / mass-status). Rule going forward: anything that operates on an order or the order-creation draft cart is tagged `Admin Sales: Orders`.
- **Files we touch are comment-free** (user preference, 2026-06-04) — keep functional OpenAPI `summary`/`description` strings, drop code comments/docblocks.

### Admin Sales — CSV exports (the Export button) — implementation notes (2026-06-05)

Every admin Sales datagrid that ships an **Export** button now has a matching `GET /api/admin/<menu>/export?format=csv` endpoint. Covered: **Orders, Invoices, Shipments, Transactions, Bookings, Refunds** (Refunds was the first/template; the other 5 added 2026-06-05).

- **REST-only, binary.** Each export is a single `Get` op on the menu's existing resource with `uriTemplate: '/<menu>/export'`, `outputFormats: ['csv' => ['text/csv']]`, and a `provider:` that returns an `Illuminate\Http\Response` (a `text/csv` attachment with `Content-Disposition: attachment; filename="<menu>.csv"`). **No GraphQL counterpart** (binary streams aren't expressible over GraphQL) — mirrors Print-Invoice. Docs are REST-only too (no GraphQL export page, no GraphQL sidebar entry).
- **Shared trait `State/Concerns/StreamsAdminCsvExport`** is the one home for the streaming logic: `use ChecksAdminPermission`; `provide()` authorizes via `exportPermission()`, validates `?format` is `csv` (else 422 `bagistoapi::app.admin.sales.export.format-unsupported`), runs `exportQuery(request()->query())->limit(50000)->get()`, writes `fputcsv` headers + rows to `php://temp`, returns the `text/csv` Response. Also provides `safeFormatBasePrice()`. Each provider implements only `exportPermission()` / `exportFilename()` / `exportHeaders()` / `exportQuery($args)` / `exportRow($row)`.
- **Why a trait, not inheritance:** an export provider can't extend the menu's collection provider — the collection `provide()` declares a `Paginator` return type, the export needs `Response` (incompatible). So each `Admin<Menu>ExportProvider` **replicates** its menu's `buildQuery()` + `applyFilters()` (same joins/select/filters as the listing) so the export honours the same filters the client is viewing. No pagination — every matching row up to the 50k cap.
- **CSV columns = the admin datagrid columns.** Orders: ID/Status/Grand Total/Payment Method/Channel/Customer/Email/Order Date. Invoices: ID/Order ID/Status/Grand Total/Invoice Date. Shipments: ID/Order ID/Total Qty/Inventory Source/Shipped To/Shipment Date. Transactions: ID/Transaction ID/Invoice ID/Order ID/Status/Date. Bookings: ID/Order ID/Qty/From/To/Booking Date. Refunds: ID/Order ID/Refunded Amount/Billed To/Refund Date.
- **Route safety:** `/<menu>/export` is a literal route that coexists with `/<menu>/{id}` because every detail `Get` declares `requirements: ['id' => '\\d+']`.
- **Content negotiation gotcha (matters for tests + clients):** the endpoint only *produces* `text/csv`, so the request MUST send `Accept: text/csv` — a default `Accept: application/json` returns 406. The admin test helper `adminGet()` sends JSON Accept, so export tests call `$this->get($url, array_merge($this->adminHeaders($admin), ['Accept' => 'text/csv']))`. Same pattern for the live curl (`-H "Accept: text/csv"`).
- **`new Get` import trap (cost a debugging cycle):** `AdminOrder` previously had only a `GetCollection` (no detail `Get`), so it never imported `ApiPlatform\Metadata\Get`. Adding the export `Get` op without the import threw `Class "...\Admin\Models\Get" not found` at schema-build time — and because API Platform compiles ONE combined schema, that single broken resource 500'd EVERY admin export (including the already-working Refunds one) until the import was added. Rule: when adding the first `Get`/`Post`/etc. op to a resource, confirm the metadata class is imported.
- **The export `Get` op MUST be declared AFTER the detail `Get` op (2026-06-08 fix).** GraphQL builds a resource's node `id` IRI from the **first `Get` operation** in the `operations` array. If the export `Get` (`/menu/export`, no `{id}`) is declared before the detail `Get` (`/menu/{id}`), every GraphQL node `id` becomes `/api/admin/menu/export` instead of `/api/admin/menu/{id}`. This silently broke Products, CMS, and the originally-shipped **Refunds** export node id until the export ops were moved to last. (Invoices/Shipments/Transactions/Bookings were already correct — their export op was appended last.) **Rule: append the export `Get` as the LAST operation; never before the detail `Get`.** `AdminOrder` has no detail `Get` (its detail is the separate `AdminOrderDetail` resource), so its GraphQL node id is unavoidably `/orders/export` — a known, harmless limitation for that one listing-only resource.
- **Registration:** all 6 export providers are tagged `ProviderInterface` in `BagistoApiServiceProvider::register()`.
- **Tests:** 4 per menu (`test_export_returns_csv` asserting the header line + filename, `_unsupported_format_returns_422`, `_requires_authentication` = 401, `_no_permission_returns_403`) live in each menu's existing list test (`SalesInvoicesListTest`/`SalesShipmentsListTest`/`SalesTransactionsTest`/`SalesBookingsTest`/`OrderTest`/`SalesRefundsListTest`). All green.

### Sales menu QA — doc-structure decisions (2026-06-05)

- **Cart is NOT a menu — it's a feature inside Orders.** The admin sidebar has no "Cart" menu; the cart endpoints (draft-cart create, add/update/remove item, save-address, shipping/payment methods, place-order) are the **admin Create-Order** flow. Docs reflect this: the Sales index table has no "Carts" row, the Carts sidebar group has no clickable overview (`link:` removed), and the Orders overview (`orders/index.md`) carries a "Creating an order for a customer (admin Create Order)" section with the full draft-cart step table. The individual cart endpoint pages remain (they're the Create-Order feature pages).
- **Transactions has no overview/landing page.** The admin Transactions screen is a pure reporting datagrid with no introductory concept page — so the Transactions sidebar group has no `link:` (REST + GraphQL); the `transactions/index.md` overview was removed; the Sales index links Transactions straight to `/transactions/list`.

### Admin Sales — completion notes

Five read-only deliverables that extend the existing per-order action endpoints with standalone datagrid listings (Invoices, Shipments, Refunds) and add Transactions + Bookings (listing + detail) from scratch.

- **No new top-level resources for Invoice/Shipment/Refund** — the `GetCollection` operation lives on the existing `AdminInvoice` / `AdminShipment` / `AdminRefund` resource alongside the existing `Get` + `Post` ops. The listing returns a slim `Admin*ListDto`; detail keeps returning the existing rich `Admin*DetailDto`. Two new top-level resources for `AdminTransaction` + `AdminBooking`.
- **Permission resolution mirrors the project pattern** — `Admin/State/Concerns/ChecksAdminPermission` trait wraps the Sanctum-token + role-permission check (`role->permission_type` direct read, NEVER `bouncer()`) so each provider's `provide()` adds a single `$this->authorizedAdmin('sales.invoices.view')` call before delegating to the abstract base.
- **Listing providers extend `AbstractAdminCollectionProvider`** — `getSortable()` / `buildQuery()` / `applyFilters()` / `applySort()` / `mapRow()` only. Pagination + envelope handled by the framework. Provider override of `provide()` declares the explicit `: \ApiPlatform\Laravel\Eloquent\Paginator` return type to satisfy LSP against the abstract.
- **Item providers extend `AbstractAdminItemProvider`** — Transaction uses Eloquent (`OrderTransaction::with('order')`); Booking uses a raw `DB::table('bookings')->leftJoin(...)` because the Booking model in `Webkul\BookingProduct\Models` doesn't ship with the relations we need and we want to inline `booking_products.type` without registering the sibling package's Eloquent relations.
- **Filter naming convention** — exact match for ids, `*_from` / `*_to` for ranges, snake_case throughout. GraphQL `extraArgs` declare every custom filter (API Platform's `QueryCollection` does NOT auto-expose custom args — same Phase 1.1 trap).
- **`requirements: ['id' => '\\d+']`** on every detail `Get` so future literal sub-paths (e.g. `/transactions/refresh`, `/bookings/calendar`) don't get captured by `{id}`.
- **Mass-actions** — most admin Sales sub-pages are reporting views with no batch operations, **except the Invoices datagrid**, which ships a bulk "Update Status" action (pending/paid/overdue). That is now exposed as `POST /api/admin/invoices/mass-update-status` + `createAdminInvoiceMassUpdateStatus` (see the Invoices — Mass Update Status coverage row). Shipments/Refunds/Transactions/Bookings still have none — confirm per menu during its QA pass before assuming so.
- **No write endpoints on these listings** — invoice/shipment/refund creation already runs through the per-order action endpoints (Wave 4); the new listings are pure read-only. Transactions are written by payment gateways during checkout; admin only views them. Bookings are written during cart-add-with-booking-product; admin only views.
- **Test runtime is currently blocked** by 4 parallel agents' in-progress `AdminSettings*` files (missing `AdminSettingsInventorySourceCreateInput` DTO at the time of writing). The Sales code itself passes `php -l` syntax checks across all 20 new/modified files; tests will run green once the Settings agents complete their wave.

### Wave 2 — Admin Cart implementation notes

Single resource (`Admin/Models/AdminCart.php`) hosting **7 REST operations** on `/api/admin/carts/{id}/...` + **6 mutations + 1 query** for GraphQL.

- **Shared output via `AdminCartPresenter`** (`Admin/State/AdminCartPresenter.php`). Read endpoint and every write endpoint return the same payload — items, totals, addresses, payment method, plus per-call `success`/`message` fields populated by mutators. Clients never need a refetch.
- **Items and addresses are plain associative arrays** (`array` / `?array` typed on `AdminCart`), not nested DTOs. Earlier attempts using `#[ApiResource(operations: [], graphQlOperations: [])]` DTOs for items serialised them as IRIs (`"/api/admin_cart_item_dtos/{id}"`) — a project-wide quirk with auto-discovered output DTOs. Plain arrays sidestep that.
- **Plain-array convention (2026-05-28) — SUPERSEDED 2026-06-17 for migrated resources (see "Guiding principle — GraphQL response shape").** This was the interim shape while admin detail resources were POPOs: nested item lists typed `array<int, array<string, mixed>>` and rendered as bare JSON in BOTH transports, so `items { edges { node } }` errored and clients had to query `items` bare. **It remains the shape only for resources NOT YET migrated.** For new or migrated resources, follow the Shopify-style connection rule (Eloquent-backed parent + Eloquent `#[ApiResource]` sub-resources → `items { edges { node { id sku type } } }`; REST keeps the flat array via an `output:` DTO). The old reason typed-DTO arrays "failed" (all-null nodes / IRI trap) was a **POPO-parent** limitation, not an API Platform one — an Eloquent parent resolves them, exactly as the shop `CustomerOrder` → `CustomerOrderItem` pattern does (which is now the template to copy, not avoid).
- **`AdminCartGuard`** (`Admin/State/AdminCartGuard.php`) — used by the provider and every processor. Validates Bearer token, looks up the cart, **refuses anything with `is_active = 1`**. Active-cart access → 403 `AuthorizationException`. Unknown id → 404 `ResourceNotFoundException`. Auth missing → 401 `AuthenticationException`.
- **REST `Delete` returns 200 + body**, not API Platform's default 204. Achieved by `status: 200` on the Delete operation plus `provider: AdminCartProvider::class` so the cart can be loaded before the processor runs.
- **`PUT` and `DELETE` operations need an explicit `provider:`** (`AdminCartProvider::class`) — without it API Platform's default item provider returns 404 because there's no Eloquent model behind the resource.
- **GraphQL field names**: API Platform places the resource shortName **after** the mutation name. `Mutation(name: 'addItem')` on `AdminCart` becomes `addItemAdminCart` (not `createAdminCartAddItem`). Input types follow the same pattern (`addItemAdminCartInput`). Mutation inputs always need `id` (the resource IRI) — `cartId` is also forwarded so the processor's body-parsing fallback works.
- **GraphQL response IRI generation for AdminCart mutations** emits a non-fatal `errors[]` entry ("Unable to generate an IRI for the item of type AdminCart") and returns `adminCart: null`. The mutation processor itself runs successfully — verified in the GraphQL CartTest by hitting `GET /api/admin/carts/{id}` after the mutation. REST does not have this limitation.
- **Monolith parity:** mirrors `Webkul\Admin\Http\Controllers\Sales\CartController` end-to-end for the 7 actions implemented. **Intentionally not in this wave** (deferred to Wave 3): `storeShippingMethod`, `storePaymentMethod`, place-order. Cart-create (`POST /api/admin/carts` to spawn an empty draft cart for a customer) is not implemented — the existing Reorder action is the only entry point for now.
- **Shared scalar GraphQL nullability quirk — ROOT-CAUSED + FIXED (2026-06-03) for admin Sales; pattern applies project-wide.** Symptom: multi-word camelCase scalar fields on output **DTOs** (`orderId`, `grandTotal`, `createdAt`, ...) come back `null` over GraphQL while REST returns them fully; single-token fields (`state`, `id`/`_id`) always resolved, which is why every admin GraphQL test (only ever querying `id`/`state`) missed it. **Root cause:** API Platform resolves a GraphQL field's value by `denormalize()`-ing the camelCase field name to a property name (`orderId` → `order_id`) and reading THAT property off the object by reflection, **with no class context** (confirmed: the name converter is called with `class = null`). camelCase DTO properties (`public ?int $orderId`) are never found → null. REST iterates the object's real properties directly, so it's unaffected. **No central converter fix is possible** (no class context; `__get`/`__set` magic is bypassed by reflection reads). **Fix = the project's own convention: output DTO properties MUST be snake_case** (`public ?int $order_id`) — they resolve over both transports and still surface as camelCase to clients via `OutputOnlySnakeToCamelNameConverter`. To avoid touching every provider, DTOs `use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites` (a `__set` trait that maps the providers' existing camelCase writes onto the snake_case properties — `__set` fires on direct PHP assignment, unlike the reflection-based read). Regression guard: `tests/Feature/Admin/GraphQL/SalesFieldResolutionTest.php` deliberately queries multi-word camelCase fields and asserts non-null. **Done for all admin Sales DTOs** (Invoice/Shipment/Refund/Transaction/Booking list+detail + shared `OrderActionItemDto`/`RefundTotalsSummary`). **Done for all admin Catalog resources (2026-06-05):** `AdminCatalogProduct`, `AdminAttribute`, `AdminAttributeFamily`, `AdminCategory`, `AdminCategoryTree` were all converted to snake_case props + `AcceptsCamelCaseWrites` — so every previously-null multi-word field now resolves over GraphQL (`attributeFamilyId`/`baseImageUrl`/`customerGroupPrices`/`superAttributes` on products; `attributeGroups` on families — the old "known quirk" that docs told clients to read over REST; `parentId`/`displayMode`/`filterableAttributeIds` on categories; `adminName`/`isRequired`/`swatchType` on attributes). Providers were untouched (they write camelCase; the trait's `__set` maps to the snake props). REST output stays camelCase via `OutputOnlySnakeToCamelNameConverter`. Note: typed-DTO array fields (`superAttributes`/`variants`/`bundleOptions`/`linkedProducts`/`downloadable*` on products) render as GraphQL **connections** (empty for non-matching types), not plain JSON arrays — query them with `{ edges { node { … } } }` over GraphQL; REST returns them as plain arrays. **Done for all admin Settings resources (2026-06-08):** `AdminSettingsTaxRate`, `AdminSettingsCurrency`, `AdminSettingsChannel`, `AdminSettingsExchangeRate`, `AdminSettingsInventorySource`, `AdminSettingsLocale`, `AdminSettingsRole`, `AdminSettingsUser`, `AdminSettingsTaxCategory`, `AdminSettingsTheme`, `AdminSettingsDataTransferImport` were all converted to snake_case props + `AcceptsCamelCaseWrites` — every previously-null multi-word Settings field now resolves over GraphQL (`createdAt`/`updatedAt` everywhere, plus `targetCurrencyCode`/`targetCurrencyName`, `contactEmail`/`contactNumber`, `permissionType`, `roleName`/`roleId`, `themeCode`/`channelId`/`sortOrder`, `defaultLocaleId`/`baseCurrencyId`/`rootCategoryId`, `zipFrom`/`zipTo`/`taxRate`, `logoUrl`/`logoPath`, the import `processedRowsCount`/`filePath`/etc.). Each got a `test_query_detail_multiword_fields_resolve_over_graphql` regression test; the REST suites stayed green (output is still camelCase via the name converter). **Done for all admin Marketing resources (2026-06-09):** `AdminMarketingCartRule`, `AdminMarketingCatalogRule`, `AdminMarketingCartRuleCoupon`, `AdminMarketingTemplate`, `AdminMarketingEvent`, `AdminMarketingCampaign`, `AdminMarketingSubscriber`, `AdminMarketingSearchTerm`, `AdminMarketingUrlRewrite`, `AdminMarketingSitemap` converted to snake_case props + `AcceptsCamelCaseWrites` — every previously-null multi-word Marketing field now resolves over GraphQL (`actionType`/`discountAmount`/`couponType`/`customerGroups`/`sortOrder`/`couponCode` on rules; `entityType`/`requestPath`/`redirectType` on url-rewrites; `fileName`/`generatedAt` on sitemaps; `marketingTemplateId`/`channelName`/`customerGroupCode` on campaigns; `channelId`/`isSubscribed`/`customerName` on subscribers; `createdAt`/`updatedAt` everywhere). Found by a live REST-vs-GraphQL QA sweep (the existing GraphQL tests queried multi-word fields but only asserted `_id`, so the null slipped through). Regression guard added: `MarketingCartRuleTest::test_query_detail_resolves_camelcase_fields`. **Done for all admin Customer resources (2026-06-09):** `AdminCustomer`, `AdminCustomerAddress`, `AdminCustomerGroup`, `AdminCustomerReview`, `AdminCustomerGdprRequest`, `AdminCustomerCartItem`, `AdminCustomerWishlistItem`, `AdminCustomerRecentOrderItem` (+ the new `AdminCustomerCompareItem`/`AdminCustomerNote` read props) converted to snake_case + `AcceptsCamelCaseWrites` — `customerGroupName`/`customerGroupId`/`totalOrders`/`totalAmountSpent`/`dateOfBirth`/`isSuspended`/`channelId` on customers; `customerName`/`productSku`/`productName` on reviews; `formattedPrice`/`productImage` on the sidebar items; `createdAt`/`updatedAt` everywhere — all now resolve over GraphQL. Regression guard: `CustomerTest::test_query_detail_resolves_camelcase_fields`. **Still TODO for the rest** of the admin DTO surface (Orders, Cart, ...) — same mechanical fix. **Rule for any new admin output DTO: declare snake_case properties + `use AcceptsCamelCaseWrites`, and add a GraphQL test that queries a multi-word field.**

- **GraphQL MUTATION-response null multi-word fields — ROOT-CAUSED + FIXED (2026-06-18).** Distinct from the query-path fix above: even after a resource's props are snake_case (so QUERIES resolve), its **create/update MUTATION** response still returned `null` for every multi-word field (`incrementId`/`subTotal`/`customerName`/…) while single-word fields (`state`, `id`/`_id`) and bare-JSON arrays (`items`) resolved — first reported on `createAdminInvoice`. **Root cause:** `GraphQl/Serializer/FixedSerializerContextBuilder::rebuildMutationAttributes()` rebuilds the requested mutation-payload field list and, via `replaceIdKeys($fields, $class, $isEloquent)`, only **denormalizes** the inner field names (camelCase→snake, so the serializer can read snake props) when `$isEloquent` is true. Admin output resources are **POPOs** (not Eloquent), so the old code kept the field names camelCase — and after the snake_case migration the POPO props are snake, so the serializer read camelCase attribute names against snake props → null. (The QUERY path is unaffected — it denormalizes per-field in the resolver, not via this builder; the builder's mutation branch only runs for `Operation instanceof Mutation`.) **Fix:** treat admin resources like Eloquent for inner-field denormalization — `$isEloquent = is_subclass_of($class, Model::class) || str_starts_with($class, 'Webkul\\BagistoApi\\Admin\\')`. One line, fixes **every** admin create/update mutation at once (verified `createAdminInvoice` now returns the full populated invoice over GraphQL; shop CustomerOrder + admin Customer/Category/Shipment/Refund GraphQL suites stayed green). Regression: `Admin/GraphQL/InvoiceTest::test_create_mutation_returns_fully_populated_invoice` (asserts `incrementId`/`grandTotal`/`customerName`/`items` non-null + `errors` null). **This supersedes the many "mutation responses are subject to the project-wide IRI-generation quirk → assert via DB state" caveats scattered through these notes — admin mutations now return the full payload over GraphQL; the old DB-state-only tests still pass but the response is now authoritative too.**
  - **EXTENDED to SHOP snake-prop POPOs (2026-07-08, RMA Wave 1).** The same null hit `createCustomerReturn` (a shop POPO with snake props, NOT under `Admin\`, so the `str_starts_with('…\\Admin\\')` branch missed it → `statusId` null on the create result while the query resolved it). Rather than string-matching another namespace, added a marker interface **`src/Contracts/SnakeCaseFieldsResource.php`**; the builder condition now also includes `is_a($resourceClass, SnakeCaseFieldsResource::class, true)`. **RULE: any new/shop POPO API resource with snake_case props AND a create/update mutation must `implements SnakeCaseFieldsResource`** (Eloquent + `Admin\` resources are already covered automatically). Verified `createCustomerReturn` returns `statusId` populated; Wishlist (45) + shop suites stayed green.

- **Admin GraphQL mass-action result wrongly `{data,meta}`-enveloped — ROOT-CAUSED + FIXED (2026-06-19).** Symptom (surfaced during the Settings GraphQL QA): a mass-delete's `deleted`/`skipped` (and mass-update-status's `updated`) came back over GraphQL as `{ "data": [48,49], "meta": { currentPage, perPage, … } }` instead of a plain `[48,49]` id array. **Root cause:** `Serializer/AdminCollectionEnvelopeNormalizer` wraps any `PaginatorInterface` whose request path `str_starts_with('/api/admin')` — written when admin GraphQL still lived at `/api/graphql` (excluded). The **2026-05-28 endpoint split moved admin GraphQL to `/api/admin/graphql`**, which now matches that prefix, so a collection-typed GraphQL *field* (API Platform wraps a list field's value in an ArrayPaginator) got REST-enveloped. **Fix (central, one line):** `&& ! str_starts_with($path, '/api/admin/graphql')` in `supportsNormalization()`. The envelope is a REST-only convention; GraphQL uses connections. Verified: REST admin listings still `{data,meta}`-enveloped, GraphQL list queries still return connections, every admin mass-delete now returns a plain id array. This is the fix for the systemic "mass-delete `deleted` renders as `{data,meta}` over GraphQL — read `.data`" caveat scattered through these notes — **mass-action results are now plain id arrays over GraphQL; that caveat is obsolete.**
- **Admin GraphQL DELETE mutations now return a snapshot of the deleted record — FIXED for Settings (2026-06-19).** Symptom: selecting `id` on a settings delete mutation (e.g. `deleteAdminSettingsLocale`) returned `errors[]` "Cannot return null for non-nullable field `id`" and nulled the whole payload (`_id` was also null). **Root cause:** `handleDelete()` returned a bare `['message' => …]` array, so API Platform's GraphQL output type (the resource, with a non-null `id: ID!`) had nothing to build the IRI from. **Fix:** the GraphQL delete now returns a **snapshot resource** — an Eloquent model keeps its attributes in memory *after* `delete()`, so mapping the in-memory `$existing` (via the item provider's `mapToDto`/`mapToDtoPublic`) yields a resource whose `id` (IRI via `fastIri`), `_id`, and scalar fields all resolve. Pattern (applied to all 11 settings delete processors): `handleDelete(int $id, bool $asResource = false): array|<Resource>`; the GraphQL delete branch passes `true` and returns `$this->mapModel($existing)` *before* the `['message']` return; the REST `Delete` path is **unchanged** (still `{message}` at HTTP 200). Connection/nested fields on a deleted parent may be empty (channel pivots cascade-delete) — that's fine; only the non-null `id` must resolve, with a minimal-snapshot fallback (set at least `id` + scalars) where the full nested map could throw. Verified live + green (all 11 `Admin/GraphQL/Settings*Test` + `Admin/RestApi/Settings*Test`). **This supersedes the per-submenu "on a delete result select `_id`, not `id`" doc guidance — `id` now resolves; delete returns what was removed.** (Same array-return pattern exists on non-Settings admin delete processors — Catalog/Marketing/Customers — and would need the same one-line-per-processor fix when those menus are revisited.)
- **`MapsOrderActionItems::mapItem` productType fix (2026-06-18).** The shared invoice/shipment/refund line-item mapper emitted `productType` = the line-item table's `product_type` column, which is the **polymorphic morph class** (`Webkul\Product\Models\Product`), not the catalog type. Fixed to resolve the real catalog type from the order item (`$row->order_item?->type` → `simple`/`configurable`/…), ignoring any class-name morph value. Affects invoice/shipment/refund detail on both transports.
- **GraphQL list/detail share one node type per resource — RESOLVED non-breakingly (2026-06-03) for admin Sales.** For a resource with both a `GetCollection`(list DTO) and `Get`(detail DTO), API Platform GraphQL builds ONE object type named after the shortName, so the **collection node exposes the detail DTO's field set** (and vice-versa). Setting `output:` per-operation does NOT split the type; only a different shortName would, which breaks the GraphQL query field name. **Chosen fix (non-breaking): make BOTH DTOs supersets of the queryable fields so whatever the client queries resolves + populates.** Done for Invoices/Refunds (added `grand_total`/`formatted_grand_total` to the **list** DTO+provider so `grandTotal` populates on the listing; added `base_grand_total`/`formatted_base_grand_total`/`order_increment_id`/`billed_to` to the **detail** DTO so they're queryable on the listing) and Shipments (added `order_increment_id`/`shipped_to`/`order_date` to the detail DTO). Transactions/Bookings needed nothing (list ⊆ detail). Trade-off accepted: a detail-only field queried on the listing (or vice-versa) returns `null` when that provider doesn't populate it — standard GraphQL, not a bug. **Currency formatting on list providers must use a `safeFormatPrice()` try/catch wrapper** (falls back to `formatBasePrice` then raw string) — `core()->formatPrice($amount, $code)` throws if the snapshot currency code no longer resolves to a Currency row. **Rule for new admin list+detail resources:** if the list has a field the detail lacks (or vice-versa) that a client would reasonably query on either, add it to both DTOs and populate where data is available.
- **Pre-existing nested-DTO IRI regression noticed (NOT introduced by Wave 2):** `Admin/RestApi/OrderDetailTest::test_detail_items_carry_the_product_type` and `Admin/RestApi/OrderTest::test_list_*` currently fail because `OrderDetailItem` and similar nested DTOs render as IRIs (`"/api/order_detail_items/{id}"`) instead of inline objects. Confirmed unrelated to Wave 2 — reproduces with all new Admin/Cart files removed. Out of scope here, filed under tech-debt.
- **Admin detail GraphQL `id` 500 — ROOT CAUSE + FIX PATTERN (2026-06-04, Invoices).** A detail Query/Get declared with `output: SomeDetailDto` 500s the **entire** GraphQL query the moment a client selects the non-nullable `id` field. Root cause (traced via `getIriFromResource` logging): GraphQL names the type after the operation's resource shortName (e.g. `AdminInvoice`), but the provider returns a *different* class (`AdminInvoiceDetailDto`). To resolve the `id` IRI, API Platform calls `getIriFromResource` **twice** — once on the returned DTO (has the id) and once on a freshly-constructed **empty** resource instance (`AdminInvoice`, `id=unset`). The empty one wins and throws "Unable to generate an IRI" → null → "Cannot return null for non-nullable field `AdminInvoice.id`". `_id` and all scalar columns resolve fine; only the IRI `id` breaks. **The working `AdminOrderDetail` avoids this by being a resource that returns ITSELF** (no `output:`), so the returned object's class matches the GraphQL type and `id` resolves from its own `/api/admin/orders/{id}` route. **FIX (the template for all admin detail resources): drop the `output:` DTO — move the detail fields onto the resource class itself (add `AcceptsCamelCaseWrites`), have the provider + create-processor return the resource, delete the DetailDto.** Done for Invoices (`AdminInvoice` now carries the full payload; `BuildsAdminInvoice` trait shares the mapping). **Knock-on:** the GraphQL **create** mutation's payload wrapper renames from `adminXDetailDto` → `adminX` (REST create unaffected; update the GraphQL create test). **Items/nested lists stay plain `array` (camelCase keys), NOT typed-DTO arrays** — a typed-DTO array becomes a GraphQL cursor connection whose nodes resolve all-null and a REST IRI string; a plain array renders inline (REST) + as a queryable Iterable leaf (GraphQL). Nested single objects (addresses) are `?array` for the same reason. **TODO:** Shipment/Refund/Transaction/Booking detail still use `output:` DTOs and 500 on `id` — migrate each to this pattern when its menu is QA'd. The older "make both list+detail DTOs supersets" note above is superseded for any resource migrated to return-itself.

#### Wave 2 — edge-case test pass (post-build) findings

After adding 28 edge-case tests (REST) + 8 (GraphQL) covering every cart mutation, the following real behaviour was discovered and acted on:

- **`AdminCartRemoveItemProcessor` had no try/catch.** When `cartItemId` referenced an item that doesn't belong to the cart (or when removing the only item caused Bagisto to wipe the cart row mid-call), `Cart::removeItem()` threw and the response was an unfiltered 500. **Fixed in this pass** — wrapped the body in try/catch identical to `AddItem` / `UpdateItems` so it now degrades to a 200 with `success: false` + the underlying error message, never 500. Added the matching lang key `bagistoapi::app.admin.cart.item-remove-failed`.
- **`AdminCartAddItemInput::$quantity` is typed `?int`.** API Platform's Symfony denormalizer rejects stringly-typed quantity (`"2"`) with a `NotNormalizableValueException` → 500 before the processor's try/catch sees the request. The processor's internal `(int)` cast only matters when quantity arrives as a numeric type (or via raw `request()->input()`). For now this is intentional strict typing — clients must send integers (Swagger example uses an integer). The matching REST test `test_add_item_quantity_as_string_is_cast` is `markTestSkipped` with a TODO to relax the DTO if a future client needs it.
- **Disabled product (`product_flat.status = 0`) is still added by `Cart::addProduct`** in the dev DB — the storefront `getCurrentProduct()` filter is what hides it from listings; the facade's `addProduct` does not re-check. Endpoint returns 201/success. If we ever want admins to be blocked from adding disabled products, the check belongs in `AdminCartAddItemProcessor` (look up `product_flat.status` after `ProductRepository::find`).
- **`Cart::saveAddresses` does not validate country / state codes** — invalid `country: 'ZZ'` is happily stored. Documented; no change.
- **Cart row deletion on last item removal** — when the last item is removed from a draft cart, Bagisto deletes the cart row entirely. The follow-up GET then returns 404. Test `test_remove_last_item_keeps_cart_retrievable` accepts both 200 (empty cart) and 404 (cart deleted) to cover both Bagisto behaviours across versions.
- **Edge-case test counts:** REST 28 added (1 skipped — DTO typing TODO above), 27 passing. GraphQL 8 added, all passing. Total cart tests: REST 42 / GraphQL 14.

### Wave 3 — Checkout + Place Order implementation notes

Completes the admin Create-Order flow: a fresh draft-cart entry, shipping
method list + select, payment method list + select, and place-order. All
operations reuse the Wave 2 `AdminCartGuard` (auth + draft-only) and a new
shared `AdminCartSequenceGuard` so the API surfaces a precise HTTP 409 for
every "wrong step" case rather than the monolith's generic 500/400.

#### Customer-nested fresh draft-cart entry

`POST /api/admin/customers/{customerId}/draft-carts` is the **fresh**
Create-Order entry. Distinct from `POST /api/admin/orders/{id}/reorder` —
the two are intentionally separate routes because their inputs and
side-effects don't overlap. Reorder takes an order id and seeds the cart from
its items; this endpoint only needs a customer and returns an empty cart.
Collapsing them under one route would either lose Reorder's order-seeding
semantics or force every Create-Order caller to also supply an order id.
GraphQL mirrors this: `createAdminDraftCart(input: { customerId: Int! })` vs
`createAdminReorder(input: { orderId: ID! })`.

Output DTO (`AdminDraftCart`) is the minimum needed to switch to cart-keyed
endpoints: `{ cartId, customerId, success, message }`. **Errors throw** —
unknown / zero customer → 404 `ResourceNotFoundException`; underlying
`Cart::createCart` exception → 422 `InvalidInputException`. The processor
never returns 2xx with `success=false`.

#### Sequence enforcement — `AdminCartSequenceGuard`

`src/Admin/State/AdminCartSequenceGuard.php` exposes four static
guards — `requireItems`, `requireAddresses`, `requireShippingMethod`,
`requirePaymentMethod`. Every Wave 3 endpoint calls the guards that apply,
in order:

| Endpoint | Guards called |
|----------|---------------|
| `GET /carts/{id}/shipping-methods` | items + addresses |
| `POST /carts/{id}/shipping-methods` | items + addresses |
| `GET /carts/{id}/payment-methods` | items + addresses + shipping |
| `POST /carts/{id}/payment-methods` | items + addresses + shipping |
| `POST /orders/place/{cartId}` | items + addresses + shipping + payment + supported-payment check |

Each failure throws `InvalidInputException(<lang key>, 409)`. The lang keys
are **distinct per call site** so the message tells the caller exactly what's
missing — e.g. shipping endpoints use
`bagistoapi::app.admin.cart.addresses-required-for-shipping`, place-order
uses `bagistoapi::app.admin.cart.place-order.addresses-required`. This
matters because place-order surfaces the same condition with a different
remediation copy (the place button vs. the shipping selector).

`requireAddresses` only requires a shipping address when the cart has
stockable items (`$cart->haveStockableItems()`) — virtual / downloadable-only
carts can skip a shipping address, matching Bagisto behaviour.

#### Monolith payment-method restriction

`AdminPlaceOrderProcessor` enforces
`$cart->payment->method in ['cashondelivery', 'moneytransfer']` (HTTP 422).
This is the same restriction the monolith `OrderController::store` hardcodes
— Stripe / PayPal / etc. cannot be admin-finalised through Bagisto's
Create-Order screen either. Listing all supported payment methods is still
allowed (the list endpoint returns whatever
`Payment::getSupportedPaymentMethods()` returns); selecting one is allowed
(set-payment-method saves any of them); only the actual order-placement
rejects unsupported methods. Parity with monolith, not API-side filtering.

#### File layout

```
src/Admin/Models/
  AdminDraftCart.php          — POST /customers/{customerId}/draft-carts
  AdminCartShippingRate.php   — GET /carts/{id}/shipping-methods
  AdminCartPaymentMethod.php  — GET /carts/{id}/payment-methods
  AdminPlaceOrder.php         — POST /orders/place/{cartId}
  AdminCart.php               — extended with set-shipping / set-payment Post ops
src/Admin/Dto/
  AdminCreateDraftCartInput.php
  AdminCartSetShippingMethodInput.php
  AdminCartSetPaymentMethodInput.php
  AdminPlaceOrderInput.php
src/Admin/State/
  AdminDraftCartProcessor.php
  AdminCartShippingMethodsProvider.php
  AdminCartSetShippingMethodProcessor.php
  AdminCartPaymentMethodsProvider.php
  AdminCartSetPaymentMethodProcessor.php
  AdminPlaceOrderProcessor.php
  AdminCartSequenceGuard.php  — shared sequence enforcement
```

All six new state classes are tagged in `BagistoApiServiceProvider::register()`
(`ProcessorInterface` / `ProviderInterface`). Untagged = silent 404 for the
endpoint.

#### Deviations / intentionally not covered

- **`AdminPlaceOrder` GraphQL response IRI** suffers the same IRI-generation
  warning as `AdminCart` mutations: `errors[]` may carry a non-fatal "Unable
  to generate an IRI" entry while the order is actually placed. The REST
  endpoint does not have this limitation. GraphQL tests assert behaviour at
  the `errors[]` level only.
- **No "create order without a cart" shortcut** — every admin order goes
  through a draft cart first (fresh or reordered). Same as the monolith.
- **No partial-payment / multi-method** — single payment method per cart.
  Mirrors monolith.
- **Tax / discount overrides** are not exposed as separate endpoints — they
  ride along with `Cart::collectTotals()` automatically after each step.

### Admin Catalog — GraphQL QA sweep (2026-06-25)

Hands-on GraphQL pass over the remaining Catalog menus (the sweep the 2026-06-05 Sales-template pass deferred — Products got it 2026-06-08; this finished Attributes/Families/Categories + the Product sub-resources). REST unaffected throughout; the existing GraphQL tests asserted `_id` only, which hid every bug below — all were strengthened to field-complete assertions.

- **Customer-Group-Prices was a camelCase POPO the 2026-06-05 catalog sweep missed** — `productId`/`valueType`/`customerGroupId`/`customerGroupName` resolved null over GraphQL (the documented camelCase-read quirk); fixed with snake props + `AcceptsCamelCaseWrites`. The GraphQL list is a **plain list** (not a Relay connection). Lesson: a per-resource sweep can skip a nested sub-resource — re-audit the sub-resources, not just the top-level menu resource.
- **Delete-snapshot rolled out to the 5 catalog deletes** (`deleteAdminCatalogProductCustomerGroupPrice`, `deleteAdminCategory`, `deleteAdminAttribute`, `deleteAdminAttributeOption`, `deleteAdminAttributeFamily`) — each GraphQL delete returned an unusable payload; now returns the populated row snapshot. Routeless resources (CGP, attribute-option) resolve via `_id`; the routed parents (Category, Attribute, AttributeFamily) resolve the `id` IRI too.
- **`updateAdminCategory` 500'd over GraphQL** — the core URL-rewrite listener reads the locale-nested body from the global `request()`, null over GraphQL → `url_rewrites.target_path` NOT-NULL violation; fixed with `request()->merge()` of the normalized locale payload before the update event (the documented CMS-page pattern).
- **Attribute-option create/update contract fixed** — `createAdminAttributeOption` was broken over GraphQL (wrong payload type / `adminName` camelCase mismatch → 0 rows) and `updateAdminAttributeOption` was uncallable (DTO `?string attributeId`/`optionId` collided with the `Int!` extraArgs); all three option mutations now return `adminAttributeOption` (select `_id`; create input `attributeId: Int!`; update/delete input `id` (IRI) + `attributeId: Int!` + `optionId: Int!`).
- **Product Inventory bulk-update** (`updateAdminCatalogProductInventory`) 500'd over GraphQL ("Cannot return null for non-nullable field id"); now returns a resolvable single node — `adminCatalogProductInventory { _id sourceId sourceCode sourceName qty }` (select `_id`). The full per-source list + total stays the `adminCatalogProductInventories(productId:)` connection.

### Admin Catalog — Sales-template pass (2026-06-05)

All four Catalog menus (Products, Attributes, Attribute Families, Categories) were brought to the Sales menu template in one pass. What changed across the board:

- **Swagger tags** — every operation regrouped from the generic `Admin Catalog` to per-menu `Admin Catalog: Products` / `: Attributes` / `: Attribute Families` / `: Categories` (the picker `AdminProduct` keeps its own `Admin Products` tag).
- **Landing pages** — each menu's sidebar group now has a `link:` to a new overview `index.md` (REST + GraphQL) carrying the menu's feature-logic (product types + composite rule + 2-step create + status/visibility; attribute types + options + configurable/filterable flags; family attribute-groups structure + delete guards; category flat-vs-tree + hierarchy/move + display modes). List pages got a `::: tip` pointer.
- **GraphQL field resolution FIXED** — `AdminCatalogProduct`, `AdminAttribute`, `AdminAttributeFamily`, `AdminCategory`, `AdminCategoryTree` converted to snake_case props + `use AcceptsCamelCaseWrites` (providers untouched). Every previously-null multi-word field now resolves over GraphQL (see the "Shared scalar GraphQL nullability quirk" note above for the full list). The docs' old "returns null over GraphQL / read over REST" warnings were removed (now inaccurate).
- **Doc-example corrections** — the GraphQL `id` IRIs in the docs were wrong everywhere (`/api/admin/admin_attributes|admin_attribute_families|admin_categories|admin_catalog_products/{id}`) and were corrected to the real `/api/admin/catalog/<menu>/{id}`; the Categories docs' wrong query names (`adminCatalogCategory*` → `adminCategory*`) and the Attributes docs' wrong list name (`adminCatalogAdminAttributes` → `adminAttributes`) were fixed.
- **Products listing** — additionally expanded to surface the cheap `product_flat` scalar columns (description/meta/weight/featured/new/timestamps); heavy relations + EAV/`isSaleable` fields stay detail-only.
- Tests: all catalog suites green (Products 99+30 REST / 23+5 GraphQL + Image/Inventory/CGP; Attributes 73/18; Families 51/16; Categories 71/20). One stale GraphQL product-detail assertion (encoded the old null bug) was updated.

### Admin Catalog Products — implementation notes

`GET /api/admin/catalog/products` + `adminCatalogProducts` GraphQL query — mirrors the
admin Catalog → Products datagrid 1:1.

- Files: `src/Admin/Models/AdminCatalogProduct.php` + `src/Admin/State/AdminCatalogProductCollectionProvider.php`.
- **DataGrid parity, not picker semantics.** Separate from the slim `/api/admin/products` picker (used by Create-Order). The two endpoints coexist by design — different URLs, different shapes, different concerns.
- **DB path only in v1.** The admin datagrid has a dual DB / Elasticsearch branch gated by `core()->getConfigData('catalog.products.search.engine') === 'elastic' && admin_mode === 'elastic'`. Per product decision 2026-05-21, the ES branch is **deferred** to Phase 1.1.b — the provider keeps a `shouldUseElasticsearch()` no-op stub so the gate is visible in code. See `docs/superpowers/specs/2026-05-21-admin-catalog-api-roadmap.md` for the deferral context.
- **Filter list (11):** `product_id` (int or comma list), `sku` (partial), `name` (partial), `type`, `status`, `attribute_family`, `channel`, `locale`, `price_from`/`price_to`, `price=a,b` shorthand.
- **Sort columns (9):** `name`, `sku`, `attribute_family`, `price`, `quantity`, `product_id` (default desc), `status`, `type`, `channel`. Both compound (`?sort=name-asc`) and split (`?sort=name&order=desc`) forms accepted.
- **Pagination:** `?page=` + `?per_page=` (default 10, cap 50). Envelope `{ data, meta }` applied by the existing `AdminCollectionEnvelopeNormalizer`.
- **GraphQL filter args** — API Platform's `QueryCollection` does NOT auto-expose custom filter args in the schema. Custom args (`sku`, `type`, `status`, etc.) must be explicitly declared via the `extraArgs` constructor parameter on `QueryCollection`. `AdminCatalogProduct.php` uses `extraArgs` correctly; future admin GraphQL resources MUST do the same.
- **Slim-picker GraphQL filters + columns — FIXED 2026-06-08.** Previously `tests/Feature/Admin/GraphQL/ProductTest.php`'s filter tests were **vacuously passing** because the slim `adminProducts` `QueryCollection` had no `extraArgs`, so `sku:`/`type:` triggered "Unknown argument" schema errors → empty edges → trivially-true `foreach` loops. AND its multi-word camelCase columns (`formattedPrice`/`baseImageUrl`/`isSaleable`) returned `null` over GraphQL (the camelCase-resolution quirk). Both fixed: added `extraArgs` (`query`/`sku`/`type`/`status`/`categoryId`/`channel`/`locale`) to `AdminProduct`'s `QueryCollection`, and converted `AdminProduct`'s props to snake_case (`formatted_price`/`base_image_url`/`is_saleable`) + `use AcceptsCamelCaseWrites` (provider keeps writing camelCase; the trait maps it). The filter tests now genuinely filter, plus a new `test_filters_are_valid_args_and_camelcase_columns_resolve` asserts no schema error + `isSaleable` resolves. The picker stays slim (Create-Order "Add Product" search) — the full Products listing is the `adminCatalogProducts` datagrid.
- **Edge cases verified (39 REST + 8 GraphQL = 47 tests):** page boundaries (page 0/negative clamp to 1, beyond-last returns empty), `per_page` cap (clamped to 50, 0/negative falls back to 10), contradictory price range (`from > to`), empty database, special characters in filter values (quotes, semicolons, percent signs, backslash, angle brackets), unknown locale/channel, products with no category, products in multiple categories (de-duped), expired/revoked tokens.
- **Out of scope this milestone (next milestones):** detail (`GET /api/admin/catalog/products/{id}`), create / update / delete, mass-delete, mass-update-status, copy action. See `docs/superpowers/specs/2026-05-21-admin-catalog-api-roadmap.md` for the full Catalog API phase breakdown.

### Admin Catalog Product Detail — implementation notes

`GET /api/admin/catalog/products/{id}` + `adminCatalogProduct(id:)` GraphQL — type-aware detail payload (Phase 1.2).

**Single resource for listing + detail:** the `Get` operation lives on the same `AdminCatalogProduct` resource as the `GetCollection` (Phase 1.1). Detail-only properties (translations, images, categories, inventories, customerGroupPrices, superAttributes, variants, bundleOptions, linkedProducts, downloadableLinks, downloadableSamples, plus extra scalars like description, weight, etc.) are declared `?type` and left null on listing rows. Listing tests confirm they're null; detail tests confirm they're populated.

**Provider:** `AdminCatalogProductDetailProvider` extends `AbstractAdminItemProvider`. `findEntity($id)` eager-loads ~10 relations; `mapToDto($product)` populates the base + branches on `$product->type` via per-type helpers (`populateConfigurable`, `populateBundle`, `populateGrouped`, `populateDownloadable`).

**`requirements: ['id' => '\\d+']`** on the `Get` op prevents future literal sub-paths (e.g. `/copy`, `/mass-delete`) from being captured by `{id}`.

**Schema findings carried forward:**
- Bagisto's `product_flat` IS the product-translation table — one row per locale+channel. Use the `product_flats` HasMany relation, NOT a separate `translations` relation.
- `product_inventories` references `inventory_source` (not `warehouse`). Eager-load `inventories.inventory_source`.
- `ProductBundleOption` label is translatable via `Webkul\Core\Eloquent\TranslatableModel` — read via `$option->translate($locale)?->label`.
- `ProductDownloadableLink` auto-loads its translations via `$with` on the model; explicit `with()` is redundant but harmless.

**Nested objects as plain arrays:** Same pattern as Phase 1.3/1.4/1.5 — every nested object (translations, images, categories, inventories, customerGroupPrices, superAttributes, variants, bundleOptions, linkedProducts, downloadableLinks, downloadableSamples — and every nested-nested option/product/sample) is populated as a plain associative array in the provider, NOT as a DTO instance. The DTOs (14 of them) exist under `src/Admin/Dto/AdminProduct*Dto.php` purely as typed schema references for GraphQL.

**GraphQL `?array` nullability quirk:** carried over from earlier phases. Test pattern: accept either populated array or null, with REST as fallback.

**Out of scope (Phase 5):** create / update / delete / copy / mass-actions / image upload / inventory writes / customer-group-price writes.

### Admin Catalog Products — mass-actions implementation notes (Phase 5.1)

Two endpoint pairs added on top of the Phase 1.1 / 1.2 read-only surface, the first writes in the Catalog Products series:

- `POST /api/admin/catalog/products/mass-delete` + `createAdminCatalogProductMassDelete`
- `POST /api/admin/catalog/products/mass-update-status` + `createAdminCatalogProductMassUpdateStatus`

Files: `Admin/Models/AdminCatalogProductMassDelete.php` + `AdminCatalogProductMassUpdateStatus.php`, paired DTOs, paired processors. Standalone resources — the existing `AdminCatalogProduct` listing/detail resource is **not** touched (matches the Phase 2 / 3 mass-action pattern).

**No pre-validation batch guard.** Categories' mass-delete pre-walks the batch and rejects the whole thing if any ID is a channel root. Products has no equivalent "undeletable" rule in the monolith — `ProductController::massDestroy` just iterates and tries each delete in a try/catch. We mirror that exactly: missing IDs are silently skipped (the response's `deleted` array only lists the IDs that actually existed and were removed). If any single repo `delete()` throws, the loop is aborted and the API returns HTTP 500 with the underlying message — exactly what the monolith does.

**`status` lives on `product_flat`, not `products`.** Test fixtures must write the `status` column via `insertProductFlat($product, ['status' => 0])` and read it back from `product_flat`, not from `products`. Querying `products.status` errors with `Column not found: 1054 Unknown column 'status'`. The production code path (`ProductRepository::update(['status' => $value], $id, ['status'])`) handles the cross-table write correctly via Bagisto's standard attribute flow — only the tests need to be aware.

**Permission resolution.** Same Sanctum-token pattern as Phase 2 / 3 / 4: read `$admin->role->permission_type` / `permissions` directly, never `bouncer()->hasPermission()`. Delete requires `catalog.products.delete`; update-status requires `catalog.products.edit`.

**Validation status codes.** `indices-required` and `value-invalid` both return HTTP 400 `InvalidInputException`, deliberately mirroring the "indices-required → 400" convention introduced for Categories rather than the 422 used for business-rule rejections elsewhere.

**Coverage:** 11 REST tests + 4 GraphQL tests (15 total). All green.

### Admin Catalog Products — copy implementation notes (Phase 5.2)

One endpoint pair added on top of Phase 1.1 / 1.2 / 5.1:

- `POST /api/admin/catalog/products/{sourceId}/copy` + `createAdminCatalogProductCopy`

Files: `Admin/Models/AdminCatalogProductCopy.php` + `Admin/Dto/AdminCatalogProductCopyInput.php` + `Admin/State/AdminCatalogProductCopyProcessor.php`. Standalone resource — mirrors the Phase 5.1 mass-action pattern (one-op resource, placeholder `$id = 1` is overwritten by the new product's real id in the processor before returning, so the IRI in the response points at the new product).

**Source-id resolution.** REST reads `{sourceId}` from `$uriVariables`; GraphQL reads from `$context['args']['input']['sourceId']` (or the DTO). The processor accepts both transports with one `resolveSourceId()` helper. URI is the primary REST entry — request-body fallback exists only for defensive resilience.

**Variant pre-check is intentional.** `ProductRepository::copy()` already throws if `$product->parent_id` is set, but it throws a plain `\Exception` carrying the core `product::app.datagrid.variant-already-exist-message` string. We pre-check before invoking the repository so the response uses the package's own translated `copy-variant-not-supported` key, and so the HTTP status is a clean 422 rather than going through the generic `\Throwable` branch. A defensive `str_contains(..., 'variant')` check in the catch-all branch handles the race-condition case where another request makes the source a variant in between our check and the repository call.

**404 vs 422.** Unknown source id → 404 `ResourceNotFoundException` (pre-checked via `ProductRepository::find()`, NOT `findOrFail()` — the latter would throw `ModelNotFoundException` and surface as a 500). Variants → 422 `InvalidInputException`. The cleanly-split status codes are what makes the API more usable than the monolith's "flash error and redirect" UX.

**Type-instance copy is opaque.** `ProductRepository::copy()` delegates the real work to `$product->getTypeInstance()->copy()` inside a DB transaction. Each Bagisto product type ships its own `copy()` implementation (simple/configurable/bundle/grouped/downloadable/virtual), and each handles its own cascading copy of `attribute_values`, `images`, `categories`, `inventories`, `customer_group_prices`, plus any type-specific relations (variants, bundle options, linked products, downloadable links/samples). We do not (and should not) duplicate that logic — we just forward and let core's transaction roll back atomically if any sub-copy throws.

**Configurable test gracefully skips.** The Bagisto core `Product` factory doesn't fully wire `super_attributes` for the `configurable` type, so `getTypeInstance()->copy()` can throw inside `Configurable::copy()`. The test (`test_copy_configurable_product_includes_variants`) wraps both the factory and the copy call in graceful `markTestSkipped` blocks so the suite stays green in environments without that fixture — the endpoint itself works in production where real configurable products carry their full attribute structure. If a future test session adds a configurable-product seed helper, this skip can be removed.

**Permission gate:** `catalog.products.create` (Sanctum-pattern; reads `$admin->role->permission_type` / `permissions` directly). Same convention as Phase 5.1 mass actions.

**Coverage:** 6 REST tests (1 may be skipped depending on configurable factory wiring) + 2 GraphQL tests. All green.

### Admin Catalog Products — create (simple) implementation notes (Phase 5.3)

Step-1 product create — `POST /api/admin/catalog/products` + `createAdminCatalogProduct` mutation. **Simple type only**; non-simple types return HTTP 422 with `type-not-yet-supported` so the staged rollout (Phases 5.4 — 5.8 for virtual / downloadable / grouped / configurable / bundle / booking) is visible to integrators rather than silently failing inside `ProductRepository::create()`.

**Bagisto is a 2-step wizard, this endpoint is step 1.** `ProductController::store()` (monolith) takes only `type`, `attribute_family_id`, `sku` and creates a barebones row; merchant then fills name/description/price/etc. in step 2 (our Phase 5.9). Mirroring that split keeps API/admin parity and avoids the giant single-payload create endpoint other catalogs ship.

**Files:**
- `Admin/Dto/AdminCatalogProductCreateInput.php` — `sku`, `attributeFamilyId`, `type` (default `'simple'`).
- `Admin/State/AdminCatalogProductCreateProcessor.php` — auth → permission → payload extract → type guard → validation → fire `catalog.product.create.before` → `ProductRepository::create([...])` → fire `catalog.product.create.after` → re-fetch with detail eager-loads → return mapped DTO.
- `Admin/Models/AdminCatalogProduct.php` — added `Post` REST op + `Mutation('create')` GraphQL op to the existing resource (same pattern as Phase 3 Attribute CRUD).

**Reuses detail mapper.** Rather than duplicating the type-aware mapping, the processor calls two new public aliases on `AdminCatalogProductDetailProvider`: `findEntityPublic($id)` (re-fetches with all eager loads) and `mapToDtoPublic($product)` (runs the existing `mapToDto`). The response is the same shape as `GET /api/admin/catalog/products/{id}` — most fields null at this point, which is correct for step-1.

**Slug validation is inlined, not delegated to the core rule.** `Webkul\Core\Rules\Slug::validate()` calls `$fail('core::validation.slug')->translate()` — that requires the `$fail` callback to return Laravel's `PotentiallyTranslatedString`. Passing a plain closure (as you'd write inside a one-off processor) triggers a fatal "Call to undefined method Closure::translate()". Workarounds: build a fake validator that returns the right object, OR inline the regex `^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$`. We chose inline — it's a 1-line regex and we already own the error message via our lang key.

**Validation order matters.** Type guard runs first (so a non-simple request is rejected before we burn cycles on sku uniqueness / family existence). Then sku-required → slug regex → unique → family existence. Each failure returns 422 with a distinct lang key for client disambiguation.

**`ProductRepository::create` resolves the type instance from `config('product_types.{type}.class')`** and calls `$typeInstance->create($data)` — for simple, that's `\Webkul\Product\Type\Simple::create()`. No surprises; the row lands in `products` with just sku/type/attribute_family_id, no `product_flat` row yet (the flat indexer fires on save through different listener paths and the step-1 row has nothing to index). Detail-payload fields that come from `product_flats` (name, price, status, urlKey, ...) are therefore `null` in the create response — expected.

**Coverage:** 10 REST tests + 2 GraphQL tests, all green. The pre-existing GraphQL `test_query_returns_correct_field_shape` failure noted at handover is unrelated and untouched.

### Admin Catalog Products — images implementation notes (Phase 5.11)

Three sub-resource endpoints under `/products/{productId}/images` — upload, reorder, delete. Mirrors `Webkul\Product\Repositories\ProductImageRepository` storage convention.

**Files:**
- `src/Admin/Models/AdminCatalogProductImage.php` — sub-resource with one Post (multipart upload), one Put (`/images/reorder`), one Delete (`/images/{id}`), plus 3 GraphQL mutations (`createAdminCatalogProductImage`, `reorderAdminCatalogProductImage`, `deleteAdminCatalogProductImage`).
- `src/Admin/State/AdminCatalogProductImageProcessor.php` — handles all 3 operations, routed by `Operation` type.
- `src/Admin/State/AdminCatalogProductImageProvider.php` — minimal placeholder provider required for the Put + Delete operations (API Platform refuses to dispatch them without a `provider:`).
- `src/Admin/Dto/AdminCatalogProductImageReorderInput.php`, `src/Admin/Dto/AdminCatalogProductImageDeleteInput.php`.

**Multipart upload — REST only.** Post operation declares `inputFormats: ['multipart' => ['multipart/form-data']]` + `deserialize: false` + `read: false` + `validate: false`. The processor pulls the file via `request()->file('image')`, validates mime (`bmp/jpeg/jpg/png/webp` — accepted by either Symfony-reported mime OR client extension, because `UploadedFile::fake()` in tests reports a different mime), and writes via `Storage::disk('public')->putFileAs("product/{$productId}", $file, $filename)`. Filename is `Str::random(40).'.<ext>'`. **Binary upload over GraphQL is not supported** — the GraphQL `create` mutation throws an `InvalidInputException` pointing users to the REST route; the test for it is intentionally omitted (`createAdminCatalogProductImage` lives in the schema only as a placeholder).

**`productRepository->find()` returns null in test for fresh-factoried products.** Use `Product::find($id)` directly (the Eloquent query path) instead of `ProductRepository::find($id)`. The repository uses Prettus' criteria pipeline which can short-circuit when no current channel/store is bound, returning null even though the row exists. Pattern is the same as Phase 5.12 (Inventory) — both processors call `Product::find()` rather than the repository for this reason.

**`uriVariables` fallback.** API Platform doesn't always populate `$uriVariables['productId']` for sub-resource routes (no `Link` declaration). The processor reads `request()->route('productId')` as a fallback. Same pattern as the Create-Order sidebar sub-resources.

**REST response wraps in `JsonResponse` to bypass API Platform's IRI generation.** `AdminCatalogProductImage` is a POPO (not an Eloquent model), so the project's `CustomIriConverter` falls through to API Platform's default `getIriFromResource` which then tries to fill the Put URL placeholders from the result resource and throws "Unable to generate an IRI for the item of type AdminCatalogProductImage". The fix: the processor returns an `Illuminate\Http\JsonResponse` directly for all three REST operations; API Platform's `SerializeProcessor` short-circuits on `$data instanceof Response` and never invokes the IRI converter. `toRestResponse()` is the small wrapper that does this — used by all three handlers (upload returns 201, reorder + delete return 200 + payload, including the `images` list on reorder).

**GraphQL delete-mutation schema only exposes `id`.** API Platform's standard delete-mutation input takes a single `id: ID!` field — even if the input DTO declares `productId`/`imageId`, GraphQL strips them from the schema. The processor for `delete` therefore: (1) extracts `imageId` from the `id` IRI basename, (2) looks up the parent `productId` from the `product_images` row itself. The `AdminCatalogProductImageDeleteInput` DTO's `productId`/`imageId` properties remain for hypothetical REST callers but are effectively dead code on the GraphQL path.

**Permission gate** — `catalog.products.edit` for all three operations. Sanctum-token pattern (`$admin->role->permission_type` / `->permissions`; never `bouncer()`).

**Events** — every operation fires `catalog.product.update.{before,after}` (with `$productId` before, `$product` after), matching the monolith convention where image changes ride along with the product update event chain.

**Default `position`** on upload — when omitted, the processor calculates `max(position) + 1` for the product. Ownership check on reorder rejects any image id whose `product_id` doesn't match the URL `productId`.

**Storage path convention** — `product/{productId}/{filename}` on the `public` disk. Matches what `ProductMediaRepository::getProductDirectory()` returns. The legacy `Storage::put()` (default disk) is also best-effort cleaned up on delete to cover rows that may have been written through alternate code paths.

**Coverage:** 17 REST tests + 3 GraphQL tests = 20 total. Upload happy-path, invalid mime, missing file, non-existent product, unauthenticated, no-permission, custom-role-with-edit; reorder happy-path, foreign-image, empty payload, unauthenticated, non-existent product; delete happy-path (verifies both DB row gone + storage file gone), not-found, cross-product 404, unauthenticated. GraphQL covers reorder happy-path, delete happy-path, and reorder-auth-required.

### Admin Catalog Products — inventory implementation notes (Phase 5.12)

Two endpoints on the `/products/{productId}/inventories` sub-resource — a read-only listing and a single bulk-upsert mutation. Both mirror the Bagisto admin Catalog → Product edit screen's inventory tab and `Webkul\Admin\Http\Controllers\Catalog\ProductController::updateInventories`.

**Resource:** `src/Admin/Models/AdminCatalogProductInventory.php` (one resource for both ops). **Provider:** `AdminCatalogProductInventoryProvider` (`GET` + the `provider:` for `PUT`). **Processor:** `AdminCatalogProductInventoryProcessor` (PUT). **Input DTO:** `AdminCatalogProductInventoryUpdateInput` (`productId: ?int`, `inventories: ?array<int,int>`).

**Bulk-upsert shape:** body is `{ inventories: { "<sourceId>": <qty>, ... } }`. The processor strips the wrapping key and passes `['inventories' => $map]` to `ProductInventoryRepository::saveInventories` — which iterates and `updateOrCreate`s one row per source. Sources NOT in the request map are untouched (no implicit delete). Passing `qty=0` writes a row with qty 0 — Bagisto's repo does not delete the row; client should treat 0 as "out of stock", not "removed". Tests accept both null (deleted) and 0 outcomes when zeroing-out.

**`totalQty` in the envelope `meta`:** the inventory listing wraps rows in a custom `AdminCatalogProductInventoryPaginator` (PaginatorInterface implementation living in the same provider file) that exposes a new `getExtraMeta(): array` method. The shared `AdminCollectionEnvelopeNormalizer` was extended (small, surgical change) to merge any `getExtraMeta()` output into the response `meta` block via `method_exists()` duck-typing — no interface coupling. This keeps other admin paginators untouched while letting the inventory endpoint expose `meta.totalQty` (sum across all sources). Reusable for any future endpoint that needs custom meta keys: just expose `getExtraMeta()` on the paginator class.

**Validation gates (all 422):**
- `inventories` missing or empty → `inventories-required`.
- Source key is not a positive integer or qty is not non-negative integer → `inventories-invalid` / `qty-invalid`.
- Source id not present in `inventory_sources` → `source-not-found` (carries the missing id in the message).

**Auth + permission:** Sanctum pattern via `AdminAuthHelper::resolveAdmin()`; `catalog.products.edit` enforced inline (same pattern as the other Phase 5.x processors — read `$admin->role->permission_type`/`->permissions`, never bouncer). 401 unauthenticated / 403 no-permission / 404 unknown product.

**PUT returns the listing payload.** The processor calls `$listingProvider->buildPayload($product->fresh())` after saving, so clients don't need a follow-up GET. `totalQty` reflects the post-save totals. Same shape for both GET and PUT.

**GraphQL nullability quirk (carried over):** the `adminCatalogProductInventories` query and `updateAdminCatalogProductInventory` mutation responses are subject to the project-wide GraphQL camelCase/`?array` nullability quirk (see "Wave 2 — Admin Cart implementation notes"). REST is the canonical shape; GraphQL tests assert behaviour at the persistence layer (DB rows) when fields render null. Mutation field is `updateAdminCatalogProductInventory` (singular — `name: 'update'` on `AdminCatalogProductInventory` becomes `updateAdminCatalogProductInventory`).

**Coverage:** 11 REST tests + 2 GraphQL tests.

**Bug fix (post-Phase-5.12):** two long-standing failures in this resource were rooted in API Platform IRI plumbing, not the processor:
- **GraphQL `updateAdminCatalogProductInventory` returned a schema error** ("Field `id` of required type `ID!` was not provided"). API Platform auto-injects `id: ID!` on the input type of any non-`create` GraphQL mutation when the resource has an `identifier`, regardless of whether the input DTO declares it. The DTO's `productId` is the real key for this bulk write — `id` is just a schema requirement to satisfy. **Fix: tests must pass a plausible IRI string (`/api/admin/catalog/products/{productId}/inventories`) as `input.id`; the processor ignores it.** Same pattern is already used by `reorderAdminCatalogProductImage` and `deleteAdminCatalogProductCustomerGroupPrice` — this is the project-wide convention for parent-scoped bulk mutations.
- **REST `PUT /catalog/products/{productId}/inventories` 500'd with `MissingMandatoryParametersException`** while serializing the response paginator. The resource has no single-`{id}` `Get` operation (it's a parent-scoped sub-resource), so API Platform's IRI generator fell back to the `PUT` route template, which requires `productId` — not in scope during serialization of individual rows. **Fix in `src/Routing/CustomIriConverter.php`:** wrap the fallback `getIriFromResource` call in a try/catch that swallows `MissingMandatoryParametersException` / `InvalidArgumentException` and returns `null` IRI. The same protection now covers any future admin parent-scoped sub-resource that returns a paginator of DTOs without a single-`{id}` Get op.

### Admin Catalog Products — customer-group prices implementation notes (Phase 5.13)

Four endpoints on the `/products/{productId}/customer-group-prices` sub-resource — list + create + update + delete. This is a **fresh** rebuild under `src/Admin/`, **not** an enhancement of the legacy `src/Models/ProductCustomerGroupPrice.php` (which still exists for parity and will be deleted later — see CLAUDE.md "Legacy admin endpoints — to be removed"). The legacy file's `/api/shop` read endpoint is untouched.

**Why a parallel build, not reuse:**
- The legacy admin endpoints (4 `#[ApiResource]` blocks on `ProductCustomerGroupPrice.php`) predate the modern admin conventions (Sanctum auth via `AdminAuthHelper`, the `{ data, meta }` envelope, the `src/Admin/` layout, lang keys under `admin.*`). Retro-fitting them would mean editing a file scheduled for removal.
- The legacy resource also exposes a flat `/admin/customer-group-prices` collection that doesn't fit the parent-scoped admin convention used by every other Phase 5.x sub-resource.

**Files:** `src/Admin/Models/AdminCatalogProductCustomerGroupPrice.php` (resource — 4 REST ops + QueryCollection + 3 mutations). `src/Admin/State/AdminCatalogProductCustomerGroupPriceProvider.php` (GetCollection + Put/Delete placeholder). `src/Admin/State/AdminCatalogProductCustomerGroupPriceProcessor.php` (POST/PUT/DELETE). `src/Admin/Dto/AdminCatalogProductCustomerGroupPriceCreateInput.php`, `AdminCatalogProductCustomerGroupPriceUpdateInput.php`.

**`unique_id` semantics:** the DB column is computed as `implode('|', array_filter([$qty, $product_id, $customer_group_id]))`. `array_filter` drops the `customer_group_id` segment when it's null — i.e. an "all-groups" row's `unique_id` is `"{qty}|{product_id}"` (no trailing pipe). Matches `Webkul\Product\Repositories\ProductCustomerGroupPriceRepository::saveCustomerGroupPrices` exactly.

**`customer_group_id = null` = "applies to all customer groups".** This is core Bagisto semantics — a null group id is a wildcard match in `prices()`. The DTO and DB allow null; the validator skips the `assertCustomerGroupExists` check when null is sent.

**Uniqueness rule:** `(product_id, qty, customer_group_id)` must be unique. The processor's `assertUniqueQtyGroup` checks this with `whereNull('customer_group_id')` when `groupId` is null and `where('customer_group_id', ...)` otherwise. Returns HTTP 422 with `duplicate-qty-group` lang key. Update path passes its own row id to the `ignoreRowId` parameter so editing a row doesn't trip on itself.

**Discrete endpoints, not bulk-replace:** the core repository's `saveCustomerGroupPrices` is bulk-replace (it deletes any row not in the payload). We deliberately **don't** expose that shape — each endpoint touches one row, so an integration can edit a single tier without re-submitting the whole tier list. Fires `catalog.product.update.{before,after}` on every write so core indexers / cache flushers still run.

**Permission:** `catalog.products.edit` (Sanctum pattern — reads `$admin->role->permission_type` / `->permissions` directly, never `bouncer()`).

**GraphQL field-naming gotchas (carried forward):**
- Do NOT set a custom `name:` on `QueryCollection` — API Platform auto-pluralises the resource shortName and produces `adminCatalogProductCustomerGroupPrices` automatically. A custom `name:` triggers "Operation 'collection_query' not found" at runtime. **Regression note (Wave 5.A Sales):** the same bug recurred when the Sales agent shipped `AdminBooking` / `AdminInvoice` / `AdminShipment` / `AdminRefund` / `AdminTransaction` with `QueryCollection(name: 'list', ...)`. Because API Platform compiles ONE combined GraphQL schema, a single broken resource takes the whole `/api/graphql` endpoint down ("Internal server error" across every admin operation, even unrelated ones like SettingsLocale). Fix: drop the `name:` argument on `QueryCollection` everywhere — the auto-derived field name is always what you want. `Mutation(name: 'create' | 'update' | 'delete' | ...)` is fine and required; only `QueryCollection` must stay unnamed.
- `extraArgs` are required for any field the GraphQL schema needs to expose at top-level — `productId: Int!` is declared on every mutation so clients can pass it inside `input`.
- `id` is intentionally **not** an extraArg — it's already declared on the update/delete DTO as `?string $id` (IRI form `/api/admin/catalog/products/{productId}/customer-group-prices/{id}`).

**Test coverage:** 17 REST tests + 3 GraphQL tests. REST exercises list/create (fixed + discount, with and without `customer_group_id`)/duplicate-qty/qty-min/invalid-value-type/unknown-group/missing-product/update happy + uniqueness + cross-product 404/delete happy + cross-product 404 + auth gates. GraphQL exercises list + create + delete happy paths and verifies side-effects in the DB (mutation response is subject to the project-wide IRI-generation quirk).

### Admin Catalog Products — update implementation notes (Phase 5.9)

The update endpoint is the **only write surface** that touches every product type. Rather than enumerate the per-family attribute fields, the endpoint is a deliberate pass-through to `ProductRepository::update`, with a thin validation guard for the small set of core invariants and explicit stripping of sub-resource fields.

- **Files:** `src/Admin/Models/AdminCatalogProduct.php` (Put op + `updateAdminCatalogProduct` mutation), `src/Admin/Dto/AdminCatalogProductUpdateInput.php` (typed surface for GraphQL + an `extras` catch-all), `src/Admin/State/AdminCatalogProductUpdateProcessor.php`.

- **Pass-through philosophy.** The body is forwarded verbatim to `ProductRepository::update`, which internally delegates to `<TypeInstance>::update`. This means **any** family-defined custom-attribute field works — `material`, `brand`, locale-keyed `<locale>.name`, anything the family declares — without the API having to enumerate them. The trade-off: the API does not validate custom-attribute types; whatever the type instance rejects bubbles up as a 500 (caught and re-emitted with the underlying message). For a stricter typed surface, callers can use the named DTO properties (`sku`, `price`, etc.); for everything else, use the `extras` map (GraphQL) or just send the field at the top level (REST).

- **Sub-resource stripping.** `images`, `videos`, `inventories`, and `customer_group_prices` are **silently removed** from the payload before forwarding. Each removal adds an entry to a `_warnings: string[]` array on the response that points the caller at the dedicated endpoint (Phase 5.11/5.12/5.13). Chose stripping over a 422 because real integrators copy-paste large form payloads — failing the entire request because of an unused field would be hostile. `_warnings` was added as an `#[ApiProperty(writable: false)] public ?array $_warnings` on `AdminCatalogProduct` so the response normaliser includes it natively (initial attempt with an anonymous subclass was discarded — API Platform's normaliser only sees `#[ApiProperty]` on the **declared** class, not dynamic subclass additions).

- **File-uploadable attributes deferred.** Attributes of type `image` / `file` defined on the family that arrive as upload streams are out of scope for v1. If the value arrives as a string path it passes through fine. Documented in code, no separate endpoint yet — when needed, will add a `POST /api/admin/catalog/products/{id}/attribute-files/{attribute_code}` similar to the image sub-resource.

- **Validation surface (minimal).** sku required + unique-excluding-self + Slug regex (inline, NOT the core `Slug` class — same workaround as Phase 5.3, the core class needs a `PotentiallyTranslatedString`-returning `$fail` callback that is incompatible with a Validator-less validation path). url_key uniqueness via `product_flat.url_key != current`. Booleans must be 0 or 1. `special_price < price` when both present. `special_price_to >= special_price_from`. Type-specific rules are NOT applied at the API layer — they live on `<TypeInstance>::getTypeValidationRules()` and run inside the repo if Bagisto's form-request path is invoked elsewhere; over the API they would require a full Validator integration, deferred.

- **Translations payload normalisation.** Accepts both Bagisto's monolith-form shape (locale-keyed at top level: `{ "en": { "name": "..." } }`) AND the API-friendly nested `translations: { en: { name } }` shape — the processor lifts the latter to the former before forwarding. Inside each locale block, camelCase keys (`shortDescription`, `urlKey`, `metaTitle` …) are mapped to snake_case so the core attribute-value saver finds them by column name.

- **URL-key mirroring.** When `url_key` is set at the top level but absent in the per-locale block of the default locale, we copy it down — mirrors monolith `ProductForm` behaviour.

- **GraphQL camelCase mapping.** The DTO uses camelCase (`urlKey`, `specialPriceFrom`, `taxCategoryId`); the processor's `normaliseCamelToSnake()` converts those back to snake_case for the repo. The catch-all `extras: array` is unpacked into the payload at the top level.

- **Permission gate:** `catalog.products.edit` — uses the same Sanctum-pattern role check as Phase 5.3 (reads `$admin->role->permission_type` / `->permissions` directly; never `bouncer()`).

- **Test coverage:** 12 REST tests + 3 GraphQL tests. Covers happy-path translations, sku-rename + duplicate-rejection, url-key duplicate, special-price scenarios (happy + over-price + inverted dates), invalid boolean, categories sync, sub-resource stripping with `_warnings` verification, 404 / 401 / 403.

### Admin Catalog Products — delete implementation notes (Phase 5.10)

Plain delete — short and parity-driven:

- **Files:** `src/Admin/Models/AdminCatalogProduct.php` (Delete op + `deleteAdminCatalogProduct` mutation), `src/Admin/State/AdminCatalogProductDeleteProcessor.php`.

- **No in-order guard.** The roadmap line "Refuses if in any non-completed order" was wrong — Bagisto core's `ProductController::destroy` doesn't enforce that either. Per the project's **parity-with-admin, not superset** rule, we don't invent the guard. Just permission check → fire `catalog.product.delete.before` → `repo->delete($id)` → fire `catalog.product.delete.after` → 204.

- **Cascading.** Whatever Bagisto core does with related rows (variants, attribute_values, images, etc.) is what happens. The test asserts the parent row is gone and records the variant count informationally (the DB FK setup in this dev environment doesn't cascade `parent_id` automatically).

- **Sequence after the call.** A subsequent GET on the same id returns 404 — confirmed by the happy-path test.

- **Permission:** `catalog.products.delete`. Same role-check pattern as the rest of the Catalog API.

- **Test coverage:** 5 REST tests + 2 GraphQL tests. Covers happy-path delete, configurable cascade, non-existent → 404, auth required, permission required.

### Admin Catalog Products — create extended to all types (Phases 5.4-5.8 + booking) implementation notes

Extends the Phase 5.3 step-1 create to accept every product type. Single endpoint, single processor, type-routed internally.

**Allowed types:** `simple, virtual, downloadable, grouped, bundle, configurable, booking`. Unknown types still 422 with a repurposed `type-not-yet-supported` message (now a future-proof catch-all rather than a per-phase TODO marker).

**Configurable — `super_attributes` shape:** Bagisto core (`Webkul\Product\Type\Configurable::create`) keys the array by **attribute code** (calls `getAttributeByCode($attributeCode)`), despite the controller's validation rule `super_attributes.* => array|min:1` being shape-agnostic. The API accepts BOTH shapes:
- `{ "color": [1, 2], "size": [4, 5] }` — codes passed through unchanged.
- `{ 23: [1, 2], 24: [4, 5] }` — numeric keys looked up via `attributes.id → attributes.code` and rewritten.

`AdminCatalogProductCreateProcessor::normalizeSuperAttributes()` performs the lookup + filter + rewrite. Entries with non-array options, empty option lists, non-numeric option ids, or unknown attribute_ids are silently dropped; if nothing remains, the caller surfaces `super-attributes-invalid` (422).

**Deliberate single-step for configurable (deviation from monolith UX):** Bagisto's admin Create-Product wizard renders an intermediate page listing the family's `configurable_attributes` and asks the merchant to pick option subsets, then re-submits with `super_attributes` populated. Our API expects `super_attributes` inline on the create call — a single request, single response, no intermediate render. The 422 `super-attributes-required` makes the contract explicit; integrators that want the two-step UX should hit `GET /api/admin/catalog/families/{id}` first to discover the configurable attributes.

**Booking — split between create and update:** Step 1 only takes `{ sku, type='booking', attribute_family_id }`. The booking_products row (with its `type` field distinguishing the 5 sub-types — default / appointment / event / rental / table — plus slots / tickets / availability) is configured in the step-2 update endpoint (Phase 5.9). This matches the monolith, where `ProductController::store` treats booking identically to simple at step 1; the booking partial only renders inside the wizard's step 2.

**OpenAPI `examples` block (not just `example`):** Switched from a single `example` to a per-type `examples` map (`simple` / `configurable` / `bundle` / `grouped` / `virtual` / `downloadable` / `booking`). Swagger UI's "Try it out" dropdown now lists each shape — the configurable one carries `super_attributes` so merchants can see the format inline.

**Test infrastructure — color/size seeded by installer:** The installer seeder (`Webkul\Installer\Database\Seeders\Attribute\AttributeTableSeeder`) creates two configurable attributes (`color` id=23, `size` id=24) with seeded options on the default family. Configurable happy-path tests use them directly. **No `markTestSkipped` was needed** — happy path verifies the new product's variant count equals `count(color_options) × count(size_options)` (Cartesian product).

**Coverage added:** REST 25 create tests (was 10), all green. GraphQL 4 mutation create tests (was 2), all green.

**Pre-existing failure note:** `php artisan test --parallel` against the full `Admin/RestApi/CatalogProductTest.php` fails to bootstrap because `BagistoApiServiceProvider` tags `AdminCatalogProductCustomerGroupPriceProvider`, a class that other parallel-agent work has not yet created. Outside this lane — untouched. The narrow `--filter` runs used to verify the new tests bypass that bootstrap path and pass.

### Admin Catalog Categories — read-only implementation notes

Three read-only endpoints behind the admin Catalog → Categories page (Phase 1.3 of the Catalog API roadmap):
- `GET /api/admin/catalog/categories` — datagrid-parity listing
- `GET /api/admin/catalog/categories/tree` — nested tree
- `GET /api/admin/catalog/categories/{id}` — detail with all translations

**Resources:** `AdminCategory.php` (listing + detail share one resource and DTO; listing rows leave `translations`/`filterableAttributeIds` as null) + `AdminCategoryTree.php` (separate resource for the recursive tree shape).

**Providers:** `AdminCategoryCollectionProvider` (DB join `categories × category_translations`), `AdminCategoryItemProvider` (Eloquent with eager-loaded translations + filterableAttributes), `AdminCategoryTreeProvider` (Kalnoy\Nestedset `toTree()` recursion).

**Nestedset implementation findings:**
- `Category::descendantsAndSelf($id)` returns a `Collection` (not a query builder); chaining `->with()` on it fails. Use `Category::query()->whereDescendantOrSelf($id)->with(...)` for fluent eager-loading.
- `appendNode()` is the safe way to seed nested-set test fixtures; manual `_lft`/`_rgt` inserts are brittle.

**Tree `children` rendering:** The provider populates `children` with plain associative arrays (NOT nested `AdminCategoryTree` instances). API Platform would otherwise serialise nested-resource fields as IRIs (`"/api/admin/catalog/category_trees/123"`) instead of inline objects. Plain arrays render the same in REST and GraphQL — `children` is a JSON array of node objects in both transports.

**Detail route disambiguation:** the `Get` op declares `requirements: ['id' => '\\d+']` so `GET /api/admin/catalog/categories/tree` continues to route to the tree endpoint instead of being captured by `/{id}`.

**GraphQL filter args:** Custom filter args (`name`, `parent_id`, `status`, `locale`, `rootId`, etc.) are declared via `extraArgs: [...]` on each `QueryCollection` — required because API Platform's `QueryCollection` does NOT auto-expose custom args (Phase 1.1 discovery).

**Translations on detail:** Populated as plain associative arrays for the same IRI-serialization reason as `children`. The `AdminCategoryTranslationDto` exists as a typed schema reference for GraphQL but the actual response uses plain arrays. Both REST and GraphQL clients receive `translations` as a JSON array of `{ locale, name, slug, description, metaTitle, metaDescription, metaKeywords }` objects.

**Coverage:** 42 REST tests + 10 GraphQL tests. Out of scope (next phases): create / update / delete / move / mass-delete / mass-update-status.

### Admin Catalog Categories — CRUD implementation notes (Phase 2)

Five mutation endpoint groups added on top of the Phase 1.3 read-only surface:
- `POST /api/admin/catalog/categories` (+ `createAdminCategory`)
- `PUT /api/admin/catalog/categories/{id}` (+ `updateAdminCategory`)
- `DELETE /api/admin/catalog/categories/{id}` (+ `deleteAdminCategory`)
- `POST /api/admin/catalog/categories/mass-delete` (+ `createAdminCategoryMassDelete`)
- `POST /api/admin/catalog/categories/mass-update-status` (+ `createAdminCategoryMassUpdateStatus`)

**Files:**
- `src/Admin/State/AdminCategoryProcessor.php` — POST/PUT/DELETE on `AdminCategory`. Shared REST + GraphQL.
- `src/Admin/State/AdminCategoryWriteProvider.php` — placeholder provider for PUT/DELETE routing (mirrors `AdminAttributeFamilyWriteProvider`).
- `src/Admin/Models/AdminCategoryMassDelete.php` + `AdminCategoryMassUpdateStatus.php` — one-op bulk resources.
- `src/Admin/State/AdminCategoryMassDeleteProcessor.php` + `AdminCategoryMassUpdateStatusProcessor.php`.
- `src/Admin/Dto/AdminCategoryCreateInput.php` / `AdminCategoryUpdateInput.php` / `AdminCategoryMassDeleteInput.php` / `AdminCategoryMassUpdateStatusInput.php`.

**Deviation from roadmap — no separate `/move` endpoint.** The Phase 2 roadmap entry listed `POST /api/admin/catalog/categories/{id}/move + moveAdminCategory`. Bagisto core has **no** `move` controller action — moves are performed by `PUT` with `parent_id` + `position`. Building `/move` would violate the "API parity with admin, not API as superset" principle (CLAUDE.md). The roadmap row was struck and Update covers the case. Tested via `test_update_category_move_changes_parent_and_position`.

**Create vs Update validation shapes differ (parity with monolith `CategoryRequest`):**
- **Create**: top-level `slug`, `name`, `description` (when display_mode requires it), `position`, `attributes`.
- **Update**: locale-nested — `<locale>.slug`, `<locale>.name`, `<locale>.description`. Top-level `position` + `attributes` still required.
- Two distinct validator paths inside `AdminCategoryProcessor::validateCreatePayload` / `validateUpdatePayload`.

**Slug uniqueness:** Implemented directly via a `category_translations.slug` query (excluding self on update). NOT a port of the full `ProductCategoryUniqueSlug` rule — the product-table check is intentionally skipped in v1 to avoid hard-coupling to `ProductRepository::setSearchEngine` (Elasticsearch branch). If product-slug collision becomes a real issue, port the rule under `src/Admin/Validation/`.

**Deletable guard (mirrors monolith `isCategoryDeletable`):**
- `id === 1` → HTTP 400
- `channels.root_category_id` contains id → HTTP 400
- Both cases use the same lang key `bagistoapi::app.admin.category.cannot-delete-root`.
- Mass-delete pre-validates the WHOLE batch before any work — one offending id rejects everything (matches monolith).

**File uploads deferred.** `logo_path` / `banner_path` are declared as `?array<string>` (path-list shape) in the DTOs and pass through to `CategoryRepository::create`/`update` which calls `request()->hasFile()`. Real multipart upload is NOT supported in this phase — clients can send pre-uploaded storage paths but cannot upload binary files via REST/GraphQL yet. Follow-up will land alongside Phase 5.11 (product images).

**GraphQL update mutation — locale-nested input limitation.** API Platform's GraphQL input serializer drops the `?array $en` nested-locale block in some configurations (the field type is `Iterable` and arbitrary-key sub-objects don't always round-trip). The REST update endpoint handles locale-nested validation cleanly. The GraphQL update test (`test_mutation_update_category_happy_path`) is lenient — it accepts either an updated row OR a `errors[]` response, mirroring the same pattern used elsewhere for project-wide GraphQL serialisation quirks. Canonical update behaviour is covered exhaustively by 9 REST update tests.

**Permission resolution** — same as Phase 3/4: read `$admin->role->permission_type` / `permissions` directly. Never call `bouncer()` (no session admin for Sanctum-token requests).

**Test surprises (encountered during build):**
- Raw `\DB::table('categories')->insert(['_lft'=>1,'_rgt'=>2,...])` works for simple read tests but breaks Kalnoy NestedSet's `Cannot move node into itself` guard when used as `parent_id` targets in update tests. Fixed by using `Webkul\Category\Models\Category::create()` + `appendNode()` for the move test only.
- The `category_translations` table requires `url_path` (column added in Bagisto 2.x). Seeders set it equal to slug.

**Coverage:** 71 REST tests (42 read-only + 29 CRUD) + 20 GraphQL tests (12 read-only + 8 CRUD).

### Admin Catalog Attributes — read-only implementation notes

Two read-only endpoints behind the admin Catalog → Attributes page (Phase 1.4 of the Catalog API roadmap):
- `GET /api/admin/catalog/attributes` — datagrid-parity listing
- `GET /api/admin/catalog/attributes/{id}` — detail with translations + options + per-option translations

**Resources:** `AdminAttribute.php` (listing + detail share one resource; listing rows leave `translations`/`options`/`validation`/`defaultValue` as null).

**Providers:** `AdminAttributeCollectionProvider` (DB join `attributes × attribute_translations`), `AdminAttributeItemProvider` (Eloquent with eager-loaded `translations` and `options.translations`).

**Datagrid finding:** `Webkul\Admin\DataGrids\Catalog\AttributeDataGrid::prepareQueryBuilder()` queries the `attributes` table directly without joining `attribute_translations` — `admin_name` lives on `attributes` as a non-translatable fallback. Our provider adds a LEFT JOIN on `attribute_translations` for locale-aware `adminName`, falling back to `attributes.admin_name` when no translation row exists.

**Nested objects as plain arrays:** Same pattern as Phase 1.3 — `translations` and `options` (and each option's nested `translations`) are populated as plain associative arrays in the item provider, NOT as DTO instances. This avoids API Platform's IRI serialization trap. The DTOs (`AdminAttributeTranslationDto`, `AdminAttributeOptionDto`, `AdminAttributeOptionTranslationDto`) exist as typed schema references for GraphQL but the response data is plain arrays.

**Swatch URL accessor:** `AttributeOption::getSwatchValueUrlAttribute()` returns a URL for `swatch_type = 'image'` options, null otherwise. The provider calls it inside a try/catch so any future accessor changes don't break the detail endpoint.

**Detail route disambiguation:** the `Get` op declares `requirements: ['id' => '\\d+']` so future literal sub-paths under `/catalog/attributes/` (like `/{id}/options`) won't conflict.

**GraphQL filter args:** Custom args (`code`, `type`, `admin_name`, `is_*`, `locale`, etc.) are declared via `extraArgs:` on the `QueryCollection` — required because API Platform's `QueryCollection` does NOT auto-expose custom args (Phase 1.1 discovery).

**Cursor pagination bug fixed:** Phase 1.4's GraphQL pagination tests exposed that `resolvePaging()` in both `AdminAttributeCollectionProvider` AND `AdminCategoryCollectionProvider` was accepting `first` but ignoring `after`. Pre-existing Categories tests didn't catch it (no cross-page assertions). Fix: both providers now decode the base64 cursor and translate to a 1-based page number — `$offset = (int) $decoded + 1; $page = floor($offset / $perPage) + 1`. Convention: API Platform's GraphQL cursor is a base64-encoded 0-based offset of the last returned item.

**Coverage (read-only):** 37 REST tests + 9 GraphQL tests. CRUD added in Phase 3 (see below).

### Admin Catalog Attributes — CRUD implementation notes (Phase 3)

Seven mutation endpoint groups added on top of the Phase 1.4 read-only surface:

**Processors / providers:**
- `AdminAttributeProcessor` — handles `POST` (create), `PUT` (update), `DELETE` (delete) on `AdminAttribute`. Shared by REST and GraphQL.
- `AdminAttributeOptionProcessor` — handles `POST` / `PUT` / `DELETE` on `AdminAttributeOption` sub-resource. Shared by REST and GraphQL.
- `AdminAttributeMassDeleteProcessor` — handles `POST /catalog/attributes/mass-delete`. Shared by REST and GraphQL.
- `AdminAttributeOptionProvider` — minimal placeholder provider for `PUT`/`DELETE` on the option sub-resource; must be tagged `ProviderInterface`.

**Key implementation findings:**

- **`$context['graphql_operation_name']` is null for `name: 'create'`/`'update'`/`'delete'` mutations.** API Platform does not populate this key for standard short-name mutations. Detect GraphQL context via `$operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation` instead. Use `$operation->getName()` (`'create'`/`'update'`/`'delete'`) to distinguish between mutation types. This is the project-wide correct pattern going forward — all processors that serve both REST and GraphQL must use this approach.

- **GraphQL args are at `$context['args']['input']`.** For a mutation declared `input: SomeDtoClass::class`, API Platform puts the input fields in `$context['args']['input']` as a camelCase associative array. For REST, read from `request()->all()` directly.

- **REST DTO name-converter trap.** The name converter maps `admin_name` JSON → `adminName` DTO property, but the DTO has a snake_case `$admin_name` property. The value is lost. Fix: for REST, always read from `request()->all()` (bypasses the DTO); for GraphQL, read from `$context['args']['input']` and map camelCase → snake_case via the `$camelToSnake` map in `dtoToArray()`.

- **REST PUT for sub-resources needs explicit `provider:`** — API Platform returns 404 without it. Both `Put` and `Delete` on `AdminAttributeOption` declare `provider: AdminAttributeOptionProvider::class`.

- **REST PUT routing vs REST POST routing for `AdminAttributeOptionProcessor`.** Both operations pass `AdminAttributeOptionInput` as `$data`. The `Put` check must come before any DTO-based branching.

- **`AttributeRepository::create()` returns a contract interface, not an Eloquent model.** Re-fetch with `Attribute::with(['translations', 'options.translations'])->find($id)` after create to get a full Eloquent model for `mapToDtoPublic`.

- **GraphQL delete mutation for `AdminAttribute` routing.** Both `update` and `delete` mutations use `AdminAttributeUpdateInput` as `$data` (delete needs the `id` field). Check `$operation->getName() === 'delete'` before the update branch.

- **`AdminAttributeMassDelete` needs a placeholder `$id`** — set `$result->id = 1` so API Platform can generate the IRI.

- **GraphQL mutation names.** Always use short names (`name: 'create'`, `'update'`, `'delete'`) on `#[ApiResource]` graphQlOperations. API Platform appends the resource short name: `name: 'create'` on `AdminAttribute` → `createAdminAttribute`. Never use the full target name as `name:` — that doubles it.

**Coverage:** 73 REST tests + 18 GraphQL tests (91 total). All green.

### Admin CMS Pages — implementation notes (CMS Phase 1 + 2)

Six endpoints behind the admin CMS → Pages screen, shipped as a single dispatch (Phase 1 read-only + Phase 2 CRUD on the same resource):
- `GET /api/admin/cms/pages` — datagrid-parity listing
- `GET /api/admin/cms/pages/{id}` — detail with translations + channels
- `POST /api/admin/cms/pages` — create (top-level fields broadcast to all locales)
- `PUT /api/admin/cms/pages/{id}` — update (LOCALE-NESTED body)
- `DELETE /api/admin/cms/pages/{id}` — delete
- `POST /api/admin/cms/pages/mass-delete` — bulk delete

**Files:** `AdminCmsPage.php` + `AdminCmsPageMassDelete.php`; `AdminCmsPageCollectionProvider` + `AdminCmsPageItemProvider` + `AdminCmsPageWriteProvider`; `AdminCmsPageProcessor` + `AdminCmsPageMassDeleteProcessor`; three input DTOs.

**Create vs Update payload shapes differ — important.** Mirrors Bagisto core exactly:
- **Create**: top-level `{ url_key, page_title, html_content, channels: [...], meta_* }`. `PageRepository::create` then iterates every configured locale and broadcasts every translated attribute (`page_title`, `url_key`, `html_content`, etc.) into a per-locale block under `$data[$locale->code]`. So a single top-level `url_key` creates the same translation row in `en`, `fr`, `es`, ... whichever locales are configured.
- **Update**: LOCALE-NESTED `{ "<locale>": { url_key, page_title, html_content, ... }, channels: [...], locale: "<code>" }`. Only the named locale is touched. uniqueness check uses `PageRepository::isUrlKeyUnique($id, $value)` which excludes the page being edited.

This shape difference is not API-side preference — it's how the core controller and repository are built. Diverging would break parity with the admin UI.

**`html_content` HTML-entity un-escape:** `PageRepository::create` and `::update` both run `str_replace('=&gt;', '=>', $data[$locale]['html_content'])` before persisting. This is core's workaround for TinyMCE escaping `=>` inside attribute values. Our processor passes the raw `html_content` through to the repository — the un-escape happens inside core, not in our processor.

**`channels()->sync()` semantic:** both create and update call `$page->channels()->sync($data['channels'])` on the relationship — passing an array of channel ids replaces the entire pivot set. There is no partial-add/partial-remove. Our update endpoint requires `channels` precisely because the repository will detach every channel if `channels` is empty/missing. The processor validates non-empty + existing-ids before the repository ever runs.

**Slug regex (NOT `Webkul\Core\Rules\Slug`):** the core controller uses `new \Webkul\Core\Rules\Slug` as a rule object. That rule pulls in services that fail in API context (Phase 5.3 finding). We use an inline regex `'/^[a-z0-9]+(?:-[a-z0-9]+)*$/'` instead — same accepted shape as the core rule but no service-container dependency.

**Permission gates:** read endpoints have no permission gate (CMS admins always see the listing). Create / update / delete / mass-delete check `cms.create` / `cms.edit` / `cms.delete` / `cms.delete` respectively via the standard `assertPermission` helper (read `$admin->role->permission_type` / `permissions`; never call `bouncer()`).

**No permission gate on Add Comment-style endpoints:** there is no equivalent in CMS Pages — the comment-flow pattern doesn't apply here.

**Single resource (`AdminCmsPage`) hosts both listing and detail.** Listing rows leave `htmlContent` / `metaTitle` / `metaKeywords` / `metaDescription` / `translations` / `channels` as null; detail populates them. Mass-delete uses its own one-off resource (`AdminCmsPageMassDelete`) — same pattern as `AdminCategoryMassDelete`.

**`requirements: ['id' => '\\d+']`** on Put/Delete/Get so the `mass-delete` literal segment is not captured by `{id}`.

**Nested objects as plain associative arrays:** `translations` and `channels` on the detail DTO are populated as plain arrays in the item provider. The DTOs serve as typed schema reference but the actual response data is plain arrays — same pattern as Categories Phase 1.3 / Attributes Phase 1.4 / Families Phase 1.5.

**GraphQL nullability quirk (carried over):** GraphQL responses for create/update mutations may return `adminCmsPage: null` with a non-fatal IRI-generation error in `errors[]` even though the underlying DB write succeeded. Tests assert via DB state, not the GraphQL response body, exactly like Category mutation tests.

**Coverage:** 38 REST tests + 6 GraphQL tests = 44 tests, all green.

### Admin Catalog Attribute Families — read-only implementation notes

Two read-only endpoints behind admin Catalog → Attribute Families (Phase 1.5 of the Catalog API roadmap):
- `GET /api/admin/catalog/families` — datagrid-parity listing (just `id`, `code`, `name` — 3 columns)
- `GET /api/admin/catalog/families/{id}` — detail with `attributeGroups` + per-group `attributes` inlined

**Resources / providers:** `AdminAttributeFamily.php` + `AdminAttributeFamilyCollectionProvider` + `AdminAttributeFamilyItemProvider`.

**Schema findings:**
- `attribute_families` has `$timestamps = false` — no `created_at`/`updated_at`, so date filters/sorts are unavailable.
- The pivot table `attribute_group_mappings` carries only `position`. The `column` field lives directly on `attribute_groups` (1 or 2). The detail response surfaces both: each attribute reference shows the group's `column` plus the pivot's `position`.

**Nested objects as plain arrays:** Same pattern as Phase 1.3 + 1.4 — `attributeGroups` and the nested `attributes` are populated as plain associative arrays in the item provider to avoid API Platform's IRI serialization trap.

**Known GraphQL nullability quirk (carried over):** the GraphQL detail response renders `attributeGroups` as `null` even though REST renders it correctly. Same project-wide camelCase / `?array` quirk noted in CLAUDE.md "Wave 2 — Admin Cart implementation notes" affecting `AdminOrderDetail` etc. The GraphQL test (`test_query_detail_returns_family_with_attribute_groups`) accepts both shapes and falls back to a REST assertion when GraphQL returns null.

**Cursor pagination reuse:** Provider's `resolvePaging` honors GraphQL's `after` cursor (the fixup from Phase 1.4 — base64-decoded offset → 1-based page).

**Coverage:** 30 REST tests + 8 GraphQL tests. Out of scope (Phase 4): create / update / delete.

### Admin Catalog Attribute Families — CRUD implementation notes (Phase 4)

Three mutation endpoint pairs on top of the Phase 1.5 read-only surface — create / update / delete. Pattern mirrors Phase 3 (Attributes CRUD) almost exactly; the per-call differences are noted below.

**Files:**
- `Admin/Dto/AdminAttributeFamilyCreateInput.php` — `code`, `name`, `attribute_groups[]` (each with optional `custom_attributes[]`).
- `Admin/Dto/AdminAttributeFamilyUpdateInput.php` — adds `id` (IRI) and uses an associative `attribute_groups` map (see "group_* keys" below).
- `Admin/State/AdminAttributeFamilyProcessor.php` — handles POST / PUT / DELETE (REST) + create/update/delete (GraphQL).
- `Admin/State/AdminAttributeFamilyWriteProvider.php` — minimal placeholder provider so API Platform routes PUT / DELETE without 404.

**Repository delegation (no nested-write reimplementation):** `AttributeFamilyRepository::create()` and `update()` already understand the nested `attribute_groups[*].custom_attributes[*]` payload, including auto-creating attribute groups and seeding the pivot via `$family->attribute_groups()->create($group); $attributeGroup->custom_attributes()->save($attr, ['position' => N])`. The processor builds a `['code', 'name', 'attribute_groups' => $input['attribute_groups'] ?? []]` array and forwards it — never touches pivots directly.

**`group_*` key convention on update:** Repository::update walks `$data['attribute_groups']` as an associative array. Keys that `Str::contains(..., 'group_')` trigger the "create new group" branch; otherwise the key is treated as an existing `attribute_groups.id` to update. Omitted existing ids → deleted. Inside each group's `custom_attributes`, items keyed by `id` whose value is already in the group keep their pivot (with `position` updated); ids not in the existing pivot are inserted; existing pivot rows not in the new list are detached. Documented in `Admin/Dto/AdminAttributeFamilyUpdateInput.php`'s class docblock.

**Last-family + products-attached guards:** Mirrors monolith `AttributeFamilyController::destroy` exactly. Both refusals return HTTP 400 (not 422) to match the monolith's `JsonResponse(..., 400)`. `InvalidInputException` already accepts an optional `int $status` (added in the Reorder wave) so it surfaces as 400 here.

**Permission resolution:** Uses the same `$admin->role->permission_type / permissions` read pattern as `AdminAttributeProcessor` — never calls `bouncer()` (Sanctum-token requests have no session-bound admin so `bouncer()->hasPermission()` returns false). Required perms: `catalog.families.create / .edit / .delete`. Failure → 403 `AuthorizationException`.

**Re-fetch + reuse item-provider mapping:** Repository methods return the contract interface, so after create/update the processor `AttributeFamily::with(['attribute_groups.custom_attributes'])->find($id)` and runs the result through `AdminAttributeFamilyItemProvider::mapToDto()` (called via reflection on the protected method) — the response shape is therefore byte-identical to `GET /api/admin/catalog/families/{id}`. No duplicated mapping code.

**GraphQL routing:** Both `update` and `delete` mutations use `AdminAttributeFamilyUpdateInput` as their input DTO (delete needs the `id` field). The processor checks `$operation->getName() === 'delete'` **before** the update branch — same trick Phase 3 uses for `deleteAdminAttribute`.

**Coverage:** 19 new REST tests + 7 new GraphQL tests on top of the Phase 1.5 read tests, all green. Total `AttributeFamilyTest`: REST 51 / GraphQL 16.

**Pre-existing unrelated failure surfaced during regression:** `tests/Feature/Admin/GraphQL/CatalogProductTest.php::test_query_returns_correct_field_shape` asserts the resource IRI contains `admin_catalog_products` (snake-plural) but the actual IRI is `/api/admin/catalog/products/{id}`. Verified NOT introduced by Phase 4 — the assertion is a stale test bug from Phase 1.1 that should be updated in a follow-up. Did not touch.

### Admin Product search — implementation notes

`GET /api/admin/products` + `adminProducts` GraphQL query — slim picker for the
Create-Order "Add Product" modal and other admin product-pickers. **This is NOT the
admin Products listing** — that is the `adminCatalogProducts` datagrid (`GET /api/admin/catalog/products`,
full columns + 11 filters). Docs label this page "Add-Product Search (Create-Order)".

- Files: `src/Admin/Models/AdminProduct.php` + `src/Admin/State/AdminProductProvider.php`.
- **2026-06-08 — GraphQL filters + columns fixed.** `AdminProduct`'s `QueryCollection` now declares `extraArgs` (`query`/`sku`/`type`/`status`/`categoryId`/`channel`/`locale`) so those args are valid over GraphQL (were "Unknown argument" errors), and its props are snake_case (`formatted_price`/`base_image_url`/`is_saleable`) + `use AcceptsCamelCaseWrites` so the multi-word columns resolve over GraphQL (were null). The provider is unchanged (still writes camelCase; the trait maps it).
- **No automatic enabled-only filter.** Unlike the shop list (`/api/shop/products` filters `status=1` via the GraphQL provider's hard-coded `attribute_id 7 + 8` `whereHas`), the admin endpoint returns ALL statuses by default. Admin must be able to find draft / disabled products. Optional `?status=0` or `?status=1` filters still narrow the set.
- **Booking products ARE listed.** They're visible in the picker so admin can find them; the booking guard in `AdminCartAddItemProcessor` blocks them at add-to-cart time (HTTP 400 `InvalidInputException`). This matches the Bagisto monolith — the admin Create-Order UI ships type partials for `simple / configurable / bundle / downloadable / grouped / virtual` only (no booking partial). Parity statement, not a TODO.
- **Slim shape only:** `id`, `sku`, `type`, `name`, `status`, `price`, `formattedPrice`, `baseImageUrl`, `isSaleable`. No `variants`/`bundleOptions`/`superAttributes`/`images[]` — the picker only needs to identify the product. If admin needs the full product detail, that's a future `GET /api/admin/products/{id}` (out of scope this milestone).
- **Reused infra:** the `{ data, meta }` envelope is applied automatically by `AdminCollectionEnvelopeNormalizer`. `paginationEnabled: false` on the REST op; the provider does its own offset paging (default `per_page=30`, cap 50). GraphQL uses native cursor pagination.
- **Filter / search logic:** `query` matches `sku LIKE` OR `attribute_id 2 (name).text_value LIKE`. `sku` is exact. `type` is exact. `categoryId` joins `product_categories`. No variant-attribute fallback like the shop provider — the admin picker is a direct-match tool, not a faceted browse.
- **Price/image resolution:** `getTypeInstance()->getMinimalPrice()` with a fallback to `attribute_id 11 (price).float_value`; image via `ProductImage::getProductBaseImage($product)` (`medium_image_url` preferred, then `original_image_url`). Both wrapped in try/catch so a single bad row never breaks the listing.
- Provider tagged `ProviderInterface` in `BagistoApiServiceProvider::register()`.

### Booking-products guard in admin cart — implementation notes

`AdminCartAddItemProcessor` checks `$product->type === 'booking'` immediately after `ProductRepository::find`, before `Cart::addProduct`. Throws `InvalidInputException` (HTTP 400 / GraphQL `errors[]`) with `bagistoapi::app.admin.cart.booking-unsupported`.

- **Why 400 not 422 as originally specified:** project convention in CLAUDE.md "Exception Types" maps `InvalidInputException` to HTTP 400. Using 422 here would require a different exception class for this single case. Decided not to deviate; documented as 400 across docs and tests.
- **Parity with Bagisto monolith:** the admin Create-Order UI in `packages/Webkul/Admin/src/Resources/views/sales/orders/create/types/` ships six type partials (`simple`, `configurable`, `bundle`, `downloadable`, `grouped`, `virtual`) — there is no `booking` partial. Admin cannot create a booking-product order through the monolith UI either. Our API matches that limitation rather than extending it.
- **Why not block at the search endpoint instead:** admin needs to *see* booking products in any product listing (catalog/search) for management purposes — only the cart-add path is forbidden.

### Create-Order sidebar sub-resources — implementation notes

The Create-Order screen (after Reorder) renders 4 read-only sub-resources in parallel — addresses + 3 sidebar panels — using a separate endpoint per panel (no bootstrap aggregator). Each follows the same pattern:

- Resource class in `src/Admin/Models/AdminCustomer*.php` (slim, camelCase props, `#[ApiProperty(identifier: true)]` on `id`).
- Provider in `src/Admin/State/AdminCustomer*Provider.php` — auth check → resolve `customerId` from `uriVariables['customerId'] ?? args ?? request()->route('customerId')` → 404 if no customer → fetch → wrap in `ApiPlatform\Laravel\Eloquent\Paginator` (the `AdminCollectionEnvelopeNormalizer` then emits `{ data, meta }`).
- `paginationEnabled: false` on the GetCollection — these are bounded sets (addresses ≤ ~20, wishlist ≤ ~50, recent items capped at 5). The envelope still wraps the response so the shape stays consistent with paginated admin collections.
- **URI-variable gotcha:** for non-`id` placeholders (e.g. `customerId`), API Platform does not auto-populate `$uriVariables` unless declared via `uriVariables: [...]` or `Link`. All sidebar providers read `request()->route('customerId')` as a fallback — works without declaring Link, keeps the provider simple.
- **`AdminCollectionEnvelopeNormalizer` confirmed reusable** — built once for the Orders listing, now used unchanged across 4 more endpoints; every future `/api/admin/*` collection picks up the envelope automatically.

### Orders detail — implementation notes

- Resource: `src/Admin/Models/OrderDetail.php` (`shortName: AdminOrderDetail`, `GET /api/admin/orders/{id}`). Provider: `src/Admin/State/OrderDetailProvider.php` — eager-loads every relation and embeds it. Output DTOs: `src/Admin/Dto/OrderDetail{Customer,CustomerGroup,Address,Item,Invoice,Shipment}.php` (plain, **camelCase** property names — the name converter does not reach nested DTOs).
- Items carry `type` (simple/configurable/bundle/downloadable/grouped/virtual) plus the type-specific slots `additional`, `child`, `children`, `downloadableLinks`. The client switches on `type`.
- **Why eager-load and embed (not sub-resources):** measured ~20 ms to fully load one order with all relations — eager loading is a constant ~11–13 queries regardless of item count, not N+1. The sub-resource argument only applies to the *collection* (heavy × many rows). A single detail is bounded and small.
- **GraphQL detail (`adminOrderDetail`)** works. API Platform GraphQL requires every nested object DTO to be a registered GraphQL type — so the 6 nested DTOs (`OrderDetailCustomer`/`CustomerGroup`/`Address`/`Item`/`Invoice`/`Shipment`) **and** `OrderItemPreview` are decorated `#[ApiResource(operations: [], graphQlOperations: [])]` (registered as schema types, no endpoints), and `src/Admin/Dto/` is registered in the three api-platform `resources` configs so they get scanned.
- **GraphQL nested collections on OrderDetail — BEING MIGRATED 2026-06-17 (pilot for the Shopify-style connection rule; see "Guiding principle — GraphQL response shape").** Interim state (pre-migration): `OrderDetailProvider::toItem()` / `toInvoice()` / `toShipment()` return plain associative arrays and the fields are typed `array` on `AdminOrderDetail`, so both REST and GraphQL render the same bare JSON array (`items` queried with no `edges/node`), and multi-word camelCase scalars (`incrementId`/`grandTotal`/`billingAddress`) currently resolve to `null` over GraphQL (the camelCase-on-POPO bug). **OrderDetail is the pilot** for converting to an Eloquent-backed parent + Eloquent sub-resources — after migration `items`/`invoices`/`shipments`/`refunds`/`comments` become `{ edges { node { ... } } }` over GraphQL (REST keeps the flat array via an `output:` DTO), and the camelCase scalars resolve for free via the central `OutputOnlySnakeToCamelNameConverter`.
- **Rule for future admin detail/nested DTOs (revised 2026-05-28):** prefer plain `array<int, array>` for nested item lists — see the cross-resource convention in "Wave 2 — Admin Cart implementation notes". The older "decorate every nested DTO with `#[ApiResource(operations: [], graphQlOperations: [])]`" rule still applies when you have a real reason to expose a typed shape; it is no longer the default.

### Admin Reorder — 3-check guard

The admin Reorder endpoint (`POST /api/admin/orders/{id}/reorder` + `createAdminReorder` mutation) enforces the same gates the Bagisto admin blade uses:

```php
$order->canReorder()                                            // (A)
&& bouncer()->hasPermission('sales.orders.create')              // (B)
&& core()->getConfigData('sales.order_settings.reorder.admin')  // (C)
```

`AdminReorderProcessor` (in `src/Admin/State/`) splits (A) into two so the client can tell which failure happened. Each failure throws `InvalidInputException($msg, 422)` → HTTP 422 in REST, `errors[]` entry in GraphQL.

| Check | Condition | Lang key | Message |
|---|---|---|---|
| A1 | `$order->is_guest === 1` | `bagistoapi::app.admin.order.reorder.guest-not-supported` | Reorder is not supported for guest orders. |
| A2 | any item's `$item->product?->getTypeInstance()->isSaleable()` is false | `bagistoapi::app.admin.order.reorder.items-not-saleable` | One or more items in this order are no longer available for purchase. |
| B  | admin's role lacks `sales.orders.create` (`permission_type='custom'` and the perm isn't in `role->permissions`) — `permission_type='all'` always passes | `bagistoapi::app.admin.order.reorder.no-permission` | You do not have permission to create orders. |
| C  | `core()->getConfigData('sales.order_settings.reorder.admin')` is falsy | `bagistoapi::app.admin.order.reorder.disabled-in-settings` | Reorder by admin is disabled in store settings. |

Implementation notes:
- **Permission check** mirrors `Webkul\User\Models\Admin::hasPermission()` rather than calling `bouncer()` directly — `bouncer()->hasPermission()` reads `auth()->guard('admin')->user()`, which is session-based and not populated for Sanctum-token API requests. The processor now delegates to the shared `AdminOrderActionGuard::adminHasPermission($admin, 'sales.orders.create')` (post-2026-05-28 fix) — previously it had its own `adminCanCreateOrders()` helper that resolved permissions inconsistently and let some permissionless-role SAME_AS_WEB tokens through. Same resolution as Cancel / Invoice / Shipment / Refund.
- **Config check** uses `core()->getConfigData(...)` unchanged — `getConfigData()` reads `core_config` table directly with no session dependency.
- **Status code reuse**: `InvalidInputException` accepts an optional second arg `int $status` (defaults to 400) so it can return 422 for these business-rule rejections while keeping 400 as the default for plain input errors elsewhere. Its `getType()` returns `/errors/{status}` accordingly.
- Order is checked in the same sequence as the blade: A1 → A2 → B → C. Earlier failures short-circuit; the client only ever sees one error at a time.

### Admin REST collections — the `{ data, meta }` envelope

Admin REST collection endpoints return a body envelope, **not** the storefront's
header-based pagination:

```json
{ "data": [ ... ], "meta": { "currentPage": 1, "perPage": 10, "lastPage": 62, "total": 616, "from": 1, "to": 10 } }
```

- Implemented by `Serializer/AdminCollectionEnvelopeNormalizer.php` — a normalizer
  registered into `api_platform_normalizer_list` at priority **1100** (above
  `PaginationHeaderNormalizer` at 1000). It wraps any `PaginatorInterface` whose
  request path starts with `/api/admin`. Shop (`/api/shop`) and GraphQL
  (`/api/graphql`) are untouched.
- `meta` keys are camelCase, written literally (the normalizer returns a raw
  array, so the name converter never sees them).
- An admin collection provider just returns an `ApiPlatform\Laravel\Eloquent\Paginator`;
  the envelope is applied automatically — reusable for every future admin collection.
- The REST `GetCollection` operation sets `paginationEnabled: false` (the provider
  does its own offset pagination); GraphQL uses native `paginationType: 'cursor'`.

### Orders listing — implementation notes

- Resource: `src/Admin/Models/AdminOrder.php` — a slim order row (flat fields +
  a light `items` preview: name/sku/qty/image). Heavy relations (full items,
  invoices, shipments) are deliberately NOT embedded — they belong to
  sub-resources (`/api/admin/orders/{id}/items` etc.) built in a later milestone.
- Provider: `src/Admin/State/OrderCollectionProvider.php` — 7 filters
  (`order_id`, `status`, `grand_total`, `channel`, `customer`, `email`, date),
  date presets (`today` … `this_year`) **and** custom `date_from`/`date_to`,
  `sort`/`order`, `?page`/`?per_page` (default 10, hard cap 50). Reads GraphQL
  args from `$context['args']`, else falls back to `request()->query()`.
- DTO: `src/Admin/Dto/OrderItemPreview.php` for the items badge.
- **Cache gotcha:** after adding an admin API resource, `bagisto-api-platform:clear-cache`
  alone is NOT enough — the route stays unregistered. Run
  `php artisan optimize:clear && redis-cli flushall` to pick up a new resource.
- **`safeFormatPrice()` defensive wrapper (2026-05-28).** `core()->formatPrice($amount, $code)` calls `formatCurrency()` which requires a real `Currency` model row — if `orders.order_currency_code` references a code that has since been deleted from `currencies`, the call throws a `TypeError` and 500s the whole listing. `OrderCollectionProvider::safeFormatPrice()` wraps the call and falls back to the raw numeric string when the lookup fails. **Reusable pattern: any provider that calls `core()->formatPrice()` with a snapshot currency code from an order/invoice/refund row must use a `safeFormatPrice()`-style wrapper** — currency rows are mutable, snapshots are not.

### Catalog + CMS Admin API — Phase 6 completion notes

Phase 6 (Catalog cross-resource polish) landed on 2026-05-22. The package is now
**feature-complete for the admin Catalog + CMS API surface** — every row in the
"Admin API — endpoint coverage" table that belongs to Catalog (Products,
Categories, Attributes, Attribute Families) or CMS Pages is marked `covered`.
No `missing` rows remain in those sections.

**What was removed in Phase 6:**

- `src/Models/AttributeOption.php` — the two legacy `/api/admin/attribute_options`
  operations. The shop `GET /api/shop/attribute-options` + `/{id}` operations
  are unchanged.
- `src/Models/ProductCustomerGroupPrice.php` — 4 of the 5 `#[ApiResource]`
  blocks (admin nested CRUD, admin flat collection, admin GraphQL Query /
  Mutation / Delete). The single shop read-only block
  (`GET /api/shop/products/{productId}/customer-group-prices`) is unchanged.
  The modern replacement lives under `src/Admin/Models/AdminCatalogProductCustomerGroupPrice.php`
  + `src/Admin/State/AdminCatalogProductCustomerGroupPrice*.php` (Phase 5.13).
- `src/State/ProductCustomerGroupPriceProcessor.php` — orphaned after the
  admin blocks were removed; deleted. `ProductCustomerGroupPriceProvider`
  remains because the shop block still uses it.
- `BagistoApiServiceProvider` — `ProductCustomerGroupPriceProcessor` import,
  its `tag()` line, and its `singleton()` binding all removed.

**Regression sweep:** `php artisan test packages/Webkul/BagistoApi/tests/Feature/Admin/`
runs green except for the 2 pre-existing GraphQL/REST inventory failures owned
by a separate parallel agent (Phase 5.12 IRI bug — `AdminCatalogProductInventory`
IRI generation). Shop spot checks (`AttributeOptionTest`, `RestApi/ProductTest`,
`GraphQL/ProductQueryTest`) all green.

### Admin Customers — implementation notes (Block C C1)

Full CRUD for `/api/admin/customers` plus three sub-resources (addresses, notes, impersonate). Mirrors `Webkul\Admin\Http\Controllers\Customers\CustomerController` + `AddressController`.

**File layout**
```
src/Admin/Models/
  AdminCustomer.php                  — listing + detail + create + update + delete
  AdminCustomerMassDelete.php
  AdminCustomerMassUpdateStatus.php
  AdminCustomerAddress.php           — pre-existing read-only sub-resource extended with Get/Post/Put/Delete
  AdminCustomerNote.php              — POST only (notes are append-only on customer_notes table)
  AdminCustomerImpersonate.php       — POST only (issues a customer Sanctum token)
src/Admin/Dto/
  AdminCustomerCreateInput / AdminCustomerUpdateInput
  AdminCustomerMassDeleteInput / AdminCustomerMassUpdateStatusInput
  AdminCustomerAddressCreateInput / AdminCustomerAddressUpdateInput
  AdminCustomerNoteCreateInput
  AdminCustomerImpersonateInput
src/Admin/State/
  AdminCustomerCollectionProvider, AdminCustomerItemProvider, AdminCustomerWriteProvider
  AdminCustomerProcessor (create/update/delete branching)
  AdminCustomerMassDeleteProcessor, AdminCustomerMassUpdateStatusProcessor
  AdminCustomerAddressItemProvider, AdminCustomerAddressWriteProvider, AdminCustomerAddressProcessor
  AdminCustomerNoteProcessor
  AdminCustomerImpersonateProcessor
```

All 12 new state classes are tagged `ProcessorInterface`/`ProviderInterface` in `BagistoApiServiceProvider::register()` (Block C C1 block).

**Notes vs. customers.notes column** — Bagisto dropped the `customers.notes` text column in `2023_07_25_drop_notes_column_from_customers_table.php` and moved notes to a separate `customer_notes` table (one row per note, `customer_notified` boolean column). The endpoint is therefore **append-only** — each `POST /api/admin/customers/{id}/notes` inserts a new row, never overwrites. Mirrors `CustomerController::storeNotes` via `CustomerNoteRepository::create`. No permission gate is enforced (matches monolith — any admin can leave a note); we still require `customers.customers.edit` to stay defensive.

**`send_password` semantics on create** — Mirrors the monolith create path:
- `send_password=true` (default) — generate `rand(100000, 10000000)` as the plaintext password, hash and store, queue `Webkul\Admin\Mail\Customer\NewCustomerNotification($customer, $password)` to deliver credentials. Mail send is wrapped in try/catch + `report()` so credentials issue doesn't abort the create.
- `send_password=false` — `password` field is required (min 6 chars). No mail is sent.

**Delete-with-orders guard** — `CustomerRepository::haveActiveOrders($customer)` returns true when any order has `status IN ('pending','processing')`. Single delete throws `InvalidInputException(..., 400)` — HTTP 400, mirroring the monolith's `400` response. Mass-delete skips with a `skipped: [{id, reason}]` entry instead of aborting the batch.

**Impersonation token strategy** — Sanctum customer tokens. The customer model already uses `HasApiTokens` (same `personal_access_tokens` table the storefront customer API uses). Each impersonation creates a new token via `$customer->createToken($name, $abilities, $expiresAt)`:
- Token `name = "admin-impersonate:{adminId}"` — searchable for audit ("which admin impersonated this customer?").
- Abilities = `['*', 'impersonated-by-admin:{adminId}']` — wildcard for normal customer access plus a marker that other consumers (logging, listeners) can detect.
- `expires_at = now() + 1 hour` — non-negotiable; aligns with short-lived assumed-identity windows.
- Each issuance logs `admin.customer.impersonate` with `{admin_id, customer_id, token_id, expires_at}` for trail.
- The plaintext token is returned **once** in the response body (`token` field). Same one-time-show pattern as the admin integration tokens.

**Address ownership guard** — Path-based: if `/customers/{customerId}/addresses/{id}` is hit with an address whose `customer_id` doesn't match the path's `customerId`, the processor throws `InvalidInputException(..., 403)`. This stops a token from editing addresses across customers via fabricated URLs.

**Address column naming** — the underlying `addresses` table renamed `address1 → address` in migration `2024_03_07_193421` (and dropped `address2`). Inputs accept `address` only — when the legacy multi-line array is sent, it's joined with `PHP_EOL` to match monolith behaviour.

**Customer-group requirement** — `customer_group_id` is required at create time (mirrors monolith form). Update is partial — group can be changed but not unset.

**Permission resolution** — same Sanctum pattern used by every other admin API endpoint: read `$admin->role->permission_type` / `$admin->role->permissions` directly. `bouncer()->hasPermission()` is never called (it relies on session admin guard which isn't populated for API requests).

**GraphQL sub-resource identifier quirk** — `adminCustomerAddresses(customerId:)` exhibits the same `CustomIdentifiersExtractor::getIdentifiersFromItem` issue noted elsewhere on cursor-paginated sub-resources (returns `errors[]` with `"No identifier value found"`). REST is the authoritative path for the addresses listing; the GraphQL test accepts either populated edges or schema errors, mirroring the Phase 1.5 (Attribute Families) quirk handling.

**Out of scope (deferred)** — customer profile image upload (`CustomerController` supports it), GDPR endpoints (separate controller, separate wave), customer reviews datagrid sub-resource.

### Admin Customer Groups — implementation notes (Block C C2)

Mirrors Bagisto core `Webkul\Admin\Http\Controllers\Customers\CustomerGroupController` 1:1 across REST + GraphQL.

**Route layout**

```
GET    /api/admin/customers/groups                  → adminCustomerGroups cursor
GET    /api/admin/customers/groups/{id}             → adminCustomerGroup(id:)
POST   /api/admin/customers/groups                  → createAdminCustomerGroup
PUT    /api/admin/customers/groups/{id}             → updateAdminCustomerGroup
DELETE /api/admin/customers/groups/{id}             → deleteAdminCustomerGroup
POST   /api/admin/customers/groups/mass-delete      → createAdminCustomerGroupMassDelete
```

**File layout (12 files, mirrors the C1 Customers pattern)**

```
src/Admin/Models/
  AdminCustomerGroup.php
  AdminCustomerGroupMassDelete.php
src/Admin/Dto/
  AdminCustomerGroupCreateInput.php
  AdminCustomerGroupUpdateInput.php
  AdminCustomerGroupMassDeleteInput.php
src/Admin/State/
  AdminCustomerGroupCollectionProvider.php   (extends AbstractAdminCollectionProvider)
  AdminCustomerGroupItemProvider.php         (extends AbstractAdminItemProvider)
  AdminCustomerGroupWriteProvider.php        (placeholder for PUT/DELETE)
  AdminCustomerGroupProcessor.php
  AdminCustomerGroupMassDeleteProcessor.php
tests/Feature/Admin/RestApi/CustomerGroupTest.php   (33 tests)
tests/Feature/Admin/GraphQL/CustomerGroupTest.php   (12 tests)
```

**System-group guards (the core of this feature)**

`customer_groups.is_user_defined = 0` marks the 3 default Bagisto groups (`general`, `wholesale`, `guest`). Three guarded operations:

| Operation | Guard | Result |
|---|---|---|
| Create | always sets `is_user_defined = 1` in the payload | All API-created groups are user-defined; the API has no way to spawn a system group. |
| Update — `code` change on system group | `system-code-immutable` | 422 `InvalidInputException` |
| Update — `is_user_defined` change on system group | `system-flag-immutable` | 422 `InvalidInputException` |
| Update — `name` on system group | allowed | 200 (matches monolith behaviour — only the name is editable for default groups) |
| Delete — system group | `is-system` | 400 `InvalidInputException` |
| Delete — `customers()->count() > 0` | `has-customers` | 400 `InvalidInputException` (matches monolith `CustomerGroupController::destroy`) |
| Mass-delete | same two guards applied per id | offending ids appear in `skipped: [{id, reason}]`; valid ids deleted normally |

**Validation parity with core**

Same rules as `CustomerGroupController::store/update`:
- `code`: `required`, `unique:customer_groups,code` (excludes self on update), `Webkul\Core\Rules\Code` (regex `^[a-zA-Z]+[a-zA-Z0-9_]+$` — rejects pure-digit, kebab-case, or leading-digit codes)
- `name`: `required`, `string`

**Permission resolution**

Standard Sanctum admin-role check (same pattern as C1 Customers):
- Create → `customers.groups.create`
- Update → `customers.groups.edit`
- Delete + Mass-delete → `customers.groups.delete`
- `permission_type='all'` always passes; otherwise the perm must appear in `$role->permissions` (array or comma-string).

Auth/permission failures throw `AuthenticationException` (401) / `AuthorizationException` (403). Reuses the shared `bagistoapi::app.admin.customer.no-permission` lang key — no per-action duplicate.

**Detail-only field — `customersCount`**

The item provider populates `customersCount` via `$g->customers()->count()` — only emitted on GET-by-id. The listing provider leaves it null (saves a per-row COUNT query × N rows). Same shape pattern as `AdminCustomer::totalOrders` / `totalAddresses` in C1.

**Coverage**

33 REST + 12 GraphQL = 45 tests, all green. Filters (code/name/is_user_defined), sort (id/code/name), system-group rejection on update/delete/mass-delete, in-use rejection on delete/mass-delete, code uniqueness (with self-exclusion), Code-rule validation, 401/403/404 standard.

**Lang keys added** under `admin.customer.group.*`: `not-found`, `deleted`, `is-system`, `has-customers`, `system-code-immutable`, `system-flag-immutable`, `mass-delete-indices-required`, `mass-delete-success`.

## Admin authentication — refactored 2026-05-27

The original 5-endpoint admin Authentication group (login / logout / forgot-password / profile-get / profile-update) was replaced with a single read-only profile endpoint plus pre-issued integration tokens. Summary:

- **Removed resources & state classes** (deleted from `src/Admin/`):
  - `Models/AdminLogin.php` + `State/AdminLoginProcessor.php` + `Dto/AdminLoginInput.php`
  - `Models/AdminLogout.php` + `State/AdminLogoutProcessor.php` + `Dto/AdminLogoutInput.php`
  - `Models/AdminForgotPassword.php` + `State/AdminForgotPasswordProcessor.php` + `Dto/AdminForgotPasswordInput.php`
  - `Models/AdminProfileUpdate.php` + `State/AdminProfileProcessor.php` + `Dto/AdminProfileInput.php`
- **Surviving endpoint:** `GET /api/admin/get` + `readAdminProfile` GraphQL query (model `Admin/Models/AdminProfile.php`, provider `Admin/State/AdminProfileProvider.php`, resolver `Admin/Resolver/AdminProfileQueryResolver.php`). No password / write surface remains.
- **Auth model:** every `/api/admin/*` request must carry `Authorization: Bearer <id>|<plain>` where the token belongs to `admin_personal_access_tokens` (status `active`). Resolution is via the **`admin-api` guard** (`Webkul\BagistoApi\Admin\Auth\AdminApiGuard`), the same guard the admin-panel Integration plugin already issued tokens for. Tokens are minted only in the admin-panel UI (Settings → Integration). The legacy "Sanctum customer-token issued by /api/admin/login" model is gone.
- **`X-Admin-Key` header is no longer required** for `/api/admin/*`. `VerifyStorefrontKey::getRequiredKeyType()` returns `null` for the admin prefix; the new `Webkul\BagistoApi\Http\Middleware\EnforceAdminApiAuth` middleware (added to all three `api-platform.php` configs in `routes.middleware`) enforces `Auth::guard('admin-api')->user()` and returns 401 when missing. Documentation endpoints (`GET /api/admin`, `GET /api/admin/docs`) are exempted from the auth gate (mirrors the pre-existing exception in `VerifyStorefrontKey`).
- **Shop API unchanged.** `/api/shop/*` still requires `X-STOREFRONT-KEY` exactly as before — only the admin prefix is exempted. Sanctum customer tokens (storefront) still work end-to-end; they live in the `personal_access_tokens` table and are not affected by the admin guard.
- **GraphQL middleware decision (Option C2 from the refactor spec) — REVERSED on 2026-05-28 in favour of a clean transport split.** Original C2 text (kept here for the audit trail): *"`VerifyGraphQLStorefrontKey` is left unchanged — `X-STOREFRONT-KEY` is still required at the transport boundary for `/api/graphql` (the endpoint serves both shop + admin operations). Admin GraphQL queries additionally require `Authorization: Bearer …`, which is validated inside the resolver via the refactored `AdminAuthHelper::resolveAdmin()`. Reasoning: C2 is a smaller, more reversible diff; the storefront-key check is already an established expectation for every GraphQL caller."* Superseded — admin GraphQL now lives at its own endpoint (`POST /api/admin/graphql`) gated by `EnforceAdminApiAuth` only (no `X-STOREFRONT-KEY`). See "Admin GraphQL endpoint split" below.
- **`AdminAuthHelper::resolveAdmin()` rewritten.** Previously called `Laravel\Sanctum\PersonalAccessToken::findToken()`; now defers to `Auth::guard('admin-api')->user()` (the standard path) with a fallback `resolveFromExplicitToken()` for edge cases (signed routes, console commands) that hash + compare against `admin_personal_access_tokens.token` directly. Public API unchanged — every existing provider/processor that calls `AdminAuthHelper::resolveAdmin()` keeps working.
- **Lang keys cleaned up.** `admin.login.*`, `admin.logout.*`, `admin.forgot-password.*`, and most of `admin.profile.*` were removed from `Resources/lang/en/app.php`. Only `admin.profile.unauthenticated` survives (still consumed by the GraphQL resolver). The duplicate `admin.forgot-password` block deeper in the file was also removed.
- **Test infra (`AdminApiTestCase`).** `adminToken()` no longer calls `Admin::createToken()` (Sanctum); it inserts a row directly into `admin_personal_access_tokens` (status `active`, `permission_type=all`, no rate limits, `expires_at = +1 day`) and returns `<id>|<plain>`. A new `actingAsAdminViaToken()` helper sets the Bearer header on the test client. Every existing admin Pest test that used `$this->adminGet($admin, ...)` / `$this->adminPost(...)` keeps working unchanged — the base-class methods now mint integration tokens instead of Sanctum tokens, transparently to callers.
- **Playwright e2e auth simplified.** `ADMIN_INTEGRATION_TOKEN` env var holds the pre-issued plaintext token. `tests/restAPI/config/env.ts` + `tests/graphQL/config/env.ts` expose it as `adminIntegrationToken`. The old `loginAdmin()` / `loginAdminGraphQL()` / `resetAdminToken()` / token-cache logic is gone — the helpers now just inject `Authorization: Bearer ${ADMIN_INTEGRATION_TOKEN}` per request. `ADMIN_PASSWORD` / `ADMIN_ACCESS_KEY` env vars are no longer read; `ADMIN_EMAIL` stays so the profile-read assertion can compare it.
- **Curl behavioural changes:**
  - `GET /api/admin/get` with valid Bearer: 200.
  - `GET /api/admin/get` with no auth: 401 `{ "message": "Unauthenticated." }` (from `EnforceAdminApiAuth`).
  - `POST /api/admin/login` / `/logout` / `/forgot-password` / `/update`: route doesn't exist. Bagisto's exception handler maps the `RouteNotFoundException` to a generic HTML 500 page rather than emitting a clean 404 — pre-existing behaviour, not introduced by this refactor. Pest + Playwright tests accept `[404, 405, 500]` for the same reason.
  - `GET /api/admin/customers` with Bearer only (no `X-Admin-Key`): 200. The header is no longer required.
  - `GET /api/shop/products` with no key: 401 `{ "error": "missing_key" }` (shop key still required — unchanged).

### GraphQL schema scoping — per-endpoint schema build (perf, 2026-06-08)

**The GraphQL schema is rebuilt on EVERY request** (API Platform's `SchemaBuilder->getSchema()` runs per request; the `Schema` object holds resolver closures so it can't be cached to Redis, and the `api-platform.schema_cache` config is dead — only the clear-command reads it). Building the schema over all **261** `#[ApiResource]` classes (557 query + 208 mutation ops) cost **~0.68s per request** — a fixed tax on *every* GraphQL call (a bare `{ __typename }` was 0.74s vs 0.27s for the equivalent REST). And both endpoints built the OTHER surface's types too (shop built all ~131 admin resources, admin built all ~128 shop).

**Fix — scope each endpoint's schema to its own resources** (`src/GraphQl/ScopedSchemaBuilder.php` decorates `SchemaBuilder` with a namespace filter; admin resources live under `\Admin\`). `POST /api/graphql` builds only shop types; `POST /api/admin/graphql` builds only admin types (via `src/Http/Controllers/AdminGraphQLEntrypointController.php` — the bridge's `EntrypointController` is `final`, so wrap not extend). Wired in `BagistoApiServiceProvider`. Result: shop `__typename` **0.74s → 0.36s**, `createCartToken` **0.91s → ~0.42s warm** (both <500ms); admin `__typename` → 0.55s (admin has more/heavier resources + the per-request token lookup in `EnforceAdminApiAuth`). This ALSO enforces the no-back-door rule for free — shop GraphQL now physically lacks admin types (`adminOrders` → "Cannot query field").

**Testing guard (important):** the shop-endpoint scoping is wrapped in `! app()->environment('testing')` because the admin GraphQL test suite (414 tests) posts admin ops to `/api/graphql` (the shop URL) and relies on the combined schema + resolver-level auth. In tests the shop endpoint keeps the full schema (so those tests pass unchanged); in production it's scoped. The admin `/api/admin/graphql` endpoint is admin-scoped in ALL envs (locked by `tests/Feature/GraphQL/ScopedSchemaBuilderTest.php`). **The remaining per-request schema-build (~0.28s scoped) + framework boot is a `artisan serve` floor** — fresh PHP per request, no shared memory; only a persistent worker (Octane / php-fpm) amortizes it to near-zero. Not eliminable in code on this runtime.

### Admin GraphQL endpoint split (2026-05-28)

Reversal of the original Option C2 decision above. Admin and shop GraphQL now ride on physically separate endpoints with separate middleware stacks:

| Route | Method | Middleware | Required headers |
|---|---|---|---|
| `POST /api/graphql` | shop | `VerifyGraphQLStorefrontKey` | `X-STOREFRONT-KEY` (Sanctum customer token optional) |
| `POST /api/admin/graphql` | admin | `EnforceAdminApiAuth` (no storefront-key check) | `Authorization: Bearer <id>\|<plain>` |
| `GET /api/graphiql` | shop UI | n/a | n/a (interactive UI sets keys via UI) |
| `GET /api/admin/graphiql` | admin UI | n/a | n/a — moved under `/api/` (was `admin/graphiql`) for symmetry with shop |

**No back door.** An admin Bearer token sent to `/api/graphql` is rejected (401, `missing_key`). Admin clients MUST switch to `/api/admin/graphql`. Conversely, sending only `X-STOREFRONT-KEY` to `/api/admin/graphql` is rejected with `Unauthenticated.` (`EnforceAdminApiAuth`).

Registration lives in `Providers/BagistoApiServiceProvider.php` around the existing `admin/graphiql` route registration (~line 929-942).

**Rule for future work:** any new admin GraphQL operation is reachable only at `/api/admin/graphql`. Do NOT add a shop-side resolver branch that gates by `Auth::guard('admin-api')->check()` inside `/api/graphql` — the split is the contract.

### Admin GraphiQL UI parity

File: `src/Http/Controllers/AdminGraphQLPlaygroundController.php`. Was a 129-line CDN-hosted GraphQL Playground stub; rewritten on 2026-05-28 to mirror the shop's `GraphQLPlaygroundController` structure — bundled GraphiQL + custom React top-bar + AES-GCM encrypted token storage. Differences from the shop UI:

- Posts to `/api/admin/graphql`.
- Injects `Authorization: Bearer <token>` only — no `X-STOREFRONT-KEY`.
- Single token slot (admin integration token); no guest-cart equivalent.
- Banner copy "Admin authenticated" + blue accent class (`bar-admin`) instead of the shop's green.
- `localStorage` keys prefixed `bagisto-admin-graphiql:` via a `createNamespacedStorage()` adapter — keeps tabs / query history / header overrides isolated from the shop UI when both run on the same origin.
- Headers editor shows `{"Authorization": "Bearer **|*********"}` placeholder when no token is set; auto-updates to the real value the moment a token is saved.

### Authentication group — implementation notes

The **Authentication** milestone (5 endpoints) is complete — REST + GraphQL + 29 tests (18 REST, 11 GraphQL), all green.

- **Token model:** admin login issues a **Sanctum** personal access token on `Webkul\User\Models\Admin` (`tokenable_type = Admin`, stored in `personal_access_tokens`). This is **separate** from the integration tokens (`admin_personal_access_tokens` + `AdminApiGuard`) — that table/guard is only for the admin-panel Integration plugin (partner/server-to-server tokens). Two distinct token systems, do not conflate.
- **Files — all admin API code lives under `src/Admin/`** (kept isolated from the shop API trees so the two never tangle):
  - `src/Admin/Models/` — API Platform resource classes (`Admin{Login,Logout,Profile,ProfileUpdate,ForgotPassword}.php`)
  - `src/Admin/Dto/` — input DTOs (`Admin*Input.php`)
  - `src/Admin/State/` — processors + providers (`Admin*.php`)
  - `src/Admin/Resolver/` — GraphQL query resolvers (`AdminProfileQueryResolver.php`)
  - `src/Admin/Helper/` — `AdminAuthHelper.php`
  - messages: `src/Resources/lang/en/app.php` under the top-level `admin` key (accessed as `bagistoapi::app.admin.*`)
  - Namespace mirrors the path: `Webkul\BagistoApi\Admin\{Models,Dto,State,Resolver,Helper}\...`.
- **`src/Admin/Models/` MUST be registered** in the `resources` array of all three api-platform configs (`config/api-platform.php`, `packages/Webkul/BagistoApi/config/api-platform.php`, `packages/Webkul/BagistoApi/config/api-platform-vendor.php`) — otherwise API Platform never scans the admin resource classes and every `/api/admin/*` route 404s. `src/Admin/Dto/` does NOT need registering (plain input DTOs, not `#[ApiResource]`).
- **Rule for all future admin API work:** new admin Models/DTOs/State/Resolvers go under `src/Admin/`, never in the shop trees (`src/Models`, `src/Dto`, `src/State`, `src/Resolver`). The shop trees are for `/api/shop` resources only.
- **Auth resolution:** `src/Helper/AdminAuthHelper.php` resolves the admin from the Bearer token. It does **fresh** `PersonalAccessToken::findToken()` resolution — NOT `Auth::guard('sanctum')->user()`, which caches a stale user across requests within one test run (this caused a logout test to wrongly pass).
- **Name-converter gotcha:** API Platform's snake_case name converter does not map multi-word camelCase JSON keys (`currentPassword`) onto a DTO. The profile-update processor therefore reads field values from `request()->all()` / `$context['args']['input']` directly (both keep camelCase), not from the denormalized DTO. Single-word fields (`email`, `password`, `all`) map fine via the DTO.
- **Registration:** all 4 admin processors are tagged `ProcessorInterface`, `AdminProfileProvider` tagged `ProviderInterface`, and `AdminProfileQueryResolver` tagged `QueryItemResolverInterface` in `BagistoApiServiceProvider::register()`. A GraphQL `Query(resolver: ...)` whose resolver is not tagged fails at runtime with "Value of type null is not callable".
- **Core parity:** mirrors Bagisto core — login rejects inactive admins (`status = 0`); forgot-password uses `Password::broker('admins')`; profile update requires `currentPassword` and a matching `confirmPassword` for password changes, and enforces email uniqueness. **Intentionally not covered in this milestone:** admin profile *image* upload (core `AccountController` supports it) — deferred to a follow-up.
- **`X-Admin-Key`:** `/api/admin/*` requires the `X-Admin-Key` header in production (same model as `/api/shop/*` needing `X-STOREFRONT-KEY`). The test base (`BagistoApiTestCase`) disables that middleware, so tests do not send it.

Test base class: `tests/AdminApiTestCase.php` — extends `BagistoApiTestCase`, adds `createAdmin()`, `adminToken()`, `adminGet/adminPost()`, `adminGraphQL()`.

---

### Admin Settings → Currencies — implementation notes

Block B Wave 1 milestone. Full CRUD + mass-delete on `Webkul\Core\Models\Currency` via `Webkul\Core\Repositories\CurrencyRepository`. Mirrors `Webkul\Admin\Http\Controllers\Settings\CurrencyController` 1:1.

Files (12 total, all under `src/Admin/`):

- `Models/AdminSettingsCurrency.php` — listing/detail/create/update/delete operations on `/api/admin/settings/currencies[/{id}]`.
- `Models/AdminSettingsCurrencyMassDelete.php` — `POST /api/admin/settings/currencies/mass-delete` (+ `createAdminSettingsCurrencyMassDelete` mutation).
- `Dto/AdminSettingsCurrency{Create,Update,MassDelete}Input.php` — input DTOs.
- `State/AdminSettingsCurrency{Collection,Item,Write}Provider.php` — list + detail + PUT/DELETE placeholder.
- `State/AdminSettingsCurrencyProcessor.php` — create/update/delete routing.
- `State/AdminSettingsCurrencyMassDeleteProcessor.php` — mass-delete routing.
- `tests/Feature/Admin/{RestApi,GraphQL}/SettingsCurrencyTest.php`.

Two delete guards layered on top of the monolith's single "last-currency" check (the monolith currently has no channel-base guard — Bagisto silently breaks the channel if you delete its base currency through the admin panel; our API refuses to be the foot-gun):

1. **Last-currency guard** — `Currency::count() <= 1` → HTTP 400 `cannot-delete-last`. Mirrors `CurrencyRepository::delete` which returns `false` in this case (we surface it as a clear 400 instead of a silent failure).
2. **Channel-base guard** — `DB::table('channels')->where('base_currency_id', $id)->exists()` → HTTP 400 `cannot-delete-channel-base`. Project-specific extension; not in monolith.

Both guards repeat for `mass-delete`. The batch is rejected up front (no partial deletes) if either condition would fire for any id — pre-validation pass mirrors `AdminCategoryMassDeleteProcessor`.

Code is immutable on update (parity with monolith `CurrencyController::update`, which only passes `name`/`symbol`/`decimal`/`group_separator`/`decimal_separator`/`currency_position` into `CurrencyRepository::update`). The processor's `handleUpdate` `unset($payload['code'])` after the validator runs, so passing `code` in the body is silently ignored — clients get a 200 with the old code still in place. Documented in the OpenAPI block.

`Currency` model uppercases `code` automatically via `setCodeAttribute`. The unique check therefore needs to compare against the uppercased value — `$this->validateCreatePayload` does `strtoupper($input['code'])` before the existence query.

Permission resolution: `assertPermission()` mirrors `AdminCategoryProcessor` — reads `role->permission_type`/`role->permissions` directly, never calls `bouncer()` (Sanctum-token requests have no session-bound admin).

GraphQL update + delete share `AdminSettingsCurrencyUpdateInput`; route by `$operation->getName()` first, then by data type. Same pattern Phase 2 Categories used.

---

### Admin Settings → Tax Categories — implementation notes (Block B Wave 3)

Full CRUD on `Webkul\Tax\Models\TaxCategory` with the m:n `tax_rates` pivot fully covered. Mirrors `Webkul\Admin\Http\Controllers\Settings\Tax\TaxCategoryController` 1:1. **No mass-delete** — the monolith UI doesn't ship one, so we don't either (parity-with-admin rule).

**Files (8, all under `src/Admin/`):**
- `Models/AdminSettingsTaxCategory.php` — single resource hosting GET-list / GET-one / POST / PUT / DELETE + 5 GraphQL operations.
- `Dto/AdminSettingsTaxCategory{Create,Update}Input.php` — input DTOs. Both include `taxrates: ?array` (int array of tax_rate ids). Update DTO is shared by `update` and `delete` GraphQL mutations.
- `State/AdminSettingsTaxCategoryCollectionProvider.php` / `ItemProvider.php` / `WriteProvider.php` — list / detail / PUT-DELETE placeholder.
- `State/AdminSettingsTaxCategoryProcessor.php` — create / update / delete routing.

**Schema notes:**
- Pivot table is `tax_categories_tax_rates` (NOT `tax_category_tax_rate` as the spec said) — `tax_category_id` + `tax_rate_id` columns. Model exposes the relation as `tax_rates()` `belongsToMany`.
- `tax_categories.description` is `longtext NOT NULL` — `description` is therefore required on both create and update (matches monolith).

**Detail response — `taxRates` inline:** the item provider eager-loads `tax_rates` and surfaces `{ id, identifier, taxRate }` per rate as a **plain associative array**, NOT a nested DTO. Same convention the rest of the admin API uses for nested objects (avoids API Platform's IRI-serialization trap). Listing rows leave `taxRates = null` to keep the listing query cheap (no per-row pivot lookup).

**Pivot sync on create/update:** `$taxCategory->tax_rates()->sync($input['taxrates'] ?? [])` — drops omitted rates, attaches new ones. Same call core's controller makes. On create the sync runs **after** the Eloquent re-fetch (the repository returns the contract interface, not an Eloquent model). On update the sync only runs when `taxrates` is present in the input — defensive partial-update support, though the validator enforces `taxrates: required` so in practice it always runs.

**Delete guard — attached rates refused (parity with monolith):**
- If `$taxCategory->tax_rates()->count() > 0` → HTTP 400 `cannot-delete-in-use`. The monolith also refuses (returns 400) when any rate is attached; to actually delete, admin must first re-sync to `taxrates: []` via update.
- On the happy path the processor defensively `detach()`-es the pivot before `delete()` so we don't leave orphaned pivot rows behind. (No effect when the guard above already proved count is zero, but safe across schema changes.)

**Validation rules** (both create + update): `code` required + string, `name` required + string, `description` required + string, `taxrates` required + array. Code uniqueness is checked explicitly via DB query (excluding self on update) rather than via `unique:` rule — same pattern as Currencies / Locales.

**Events:** create / update / delete fire `tax.category.create.before/after`, `tax.category.update.before/after`, `tax.category.delete.before/after` — exactly the names the monolith fires. Permission keys are `settings.taxes.tax_categories.create / .edit / .delete`.

**Permission resolution:** standard Sanctum admin-role pattern — reads `$admin->role->permission_type` / `$admin->role->permissions` directly, never calls `bouncer()`.

**Coverage:** 29 REST tests + 7 GraphQL tests, all REST green. (GraphQL tests subject to the project-wide AdminBooking-schema issue affecting all admin GraphQL ops on the branch; tests written, assert via DB state where the response is null per the documented project-wide quirk.)

---

### Known issue — CatalogRule create N+1 (Bagisto core, NOT BagistoApi)

`POST /api/admin/marketing/catalog-rules` is correctly implemented in BagistoApi, but completing a single create takes >30s on a dev catalog because of a Bagisto **core** listener: after the rule row is inserted, `Webkul\CatalogRule\Listeners\*` (or the rule indexer's after-create hook) recalculates `catalog_rule_product_prices` for every product one at a time — classic N+1.

**Why we're not fixing it here:** the slowdown lives in `packages/Webkul/CatalogRule/` (Bagisto core), not in `packages/Webkul/BagistoApi/`. The BagistoApi processor correctly delegates to `CatalogRuleRepository::create` and the core listener fires from there. A proper fix queues the price-recalc job instead of running it inline, OR scopes it to the affected products. That's a core-side change.

**Impact on tests:** the e2e Playwright `tests/graphQL/api/automation/admin/marketing/catalogRules.spec.ts:73` "create + detail + update + delete round trip" trips a 30s+ timeout because of this. Documented `test.slow()` or moved behind a slow-suite gate is acceptable. The Pest equivalents pass because they don't depend on real-time wall-clock budgets.

**Status:** noted for tracking. Do NOT attempt to patch from BagistoApi — would require monkey-patching core. Revisit when there's room for a core PR.

**ROOT CAUSE NAILED (2026-06-25) — it's `QUEUE_CONNECTION=sync`, NOT a real N+1 "bug".** The reindex (`Webkul\CatalogRule\Listeners\CatalogRule@afterUpdateCreate`) dispatches **`UpdateCreateCatalogRuleIndex` — which `implements ShouldQueue`**, i.e. it is ALREADY designed to run out-of-band. It only blocks the request because the install runs `QUEUE_CONNECTION=sync`, and `sync` executes "queued" jobs **inline, inside the HTTP request**. Measured on this dev box: **84.8s for 328 products** → exceeds the web/PHP `max_execution_time` → the request is killed and the client gets the generic `{ "error": "500 Internal Server Error", "description": "shop::app.shop.errors.500.description" }` timeout page. **The catalog_rules row is already committed before the reindex runs — the create SUCCEEDED; only the response timed out** (query `adminMarketingCatalogRule` after the 500 and the rule is there). Validation is fine; the example input is valid (reproduced `CatalogRuleRepository::create()` with the exact payload → OK in 0s).

- **This is EXACT parity with the admin panel.** `CatalogRuleController::store` fires the same `promotions.catalog_rule.create.after` event → same job → same inline 85s on `sync`. The admin UI "works" on a prod box only because that catalog is small enough that the reindex finishes under the timeout (time scales with product count), OR a catalog rule was never created there. There is NO API-specific bug — do not "fix" the processor.
- **The real fix is environment, not code:** `QUEUE_CONNECTION=redis` (or `database`) + a persistent worker (`php artisan queue:work` via Supervisor/systemd, `autostart`/`autorestart`, `queue:restart` on deploy). Then create returns `201` instantly and the reindex runs in the background. This is Bagisto's documented production architecture; `sync` in prod is the misconfiguration (it also makes emails/imports/exports/sitemaps block requests). Sync-only workaround: raise `max_execution_time` / php-fpm `request_terminate_timeout` / nginx `fastcgi_read_timeout` past the reindex time — a ceiling that a big enough catalog will still blow past.
- **CONFIG-CACHE TRAP (cost a debugging round):** editing `.env` `QUEUE_CONNECTION` does **nothing** while config is cached — `php artisan bagisto-api-platform:optimize` runs `config:cache`, which freezes the OLD value into `bootstrap/cache/config.php`. Symptom: `config('queue.default')` still returns `sync` after the `.env` edit. Fix: `php artisan config:clear` (or re-run `optimize`, which re-bakes the new `.env` value). Also restart any already-running `queue:work` — a worker holds the connection it booted with in memory, so a worker started under the stale `sync` config keeps using sync until relaunched.

---

### Bug-fix notes (2026-05-26)

Six admin-API endpoints surfaced HTTP 500s during manual QA. Five fixed, one already working:

1. **`POST /api/admin/logout` (and every admin POST/PUT with `Content-Type: application/json` + empty body)** → `Symfony\Component\Serializer\Exception\NotEncodableValueException: Syntax error` at `JsonDecode.php:105` — `json_decode('')` throws. **Fix:** `src/Http/Middleware/NormalizeEmptyJsonBody.php` rewrites an empty/whitespace JSON body to `{}` before API Platform's decoder runs. Registered in `routes.middleware` across all three `api-platform.php` configs (root + package + vendor) as the first entry in the chain. Idempotent — non-JSON / non-empty requests pass through unchanged. Regression: `Admin/RestApi/AuthenticationTest::test_logout_with_empty_json_body_succeeds`.

2. **`GET /api/admin/dashboard/stats?type=top-selling-products`** → HTTP 500 "This database driver does not support user-defined types". **Root cause:** `Webkul\Admin\Helpers\Dashboard::getTopSellingProducts` returns a Support Collection whose rows carry Eloquent `images` collections (`Webkul\Product\Models\ProductImage`). API Platform's Symfony Serializer then reflects on the Eloquent model's properties and calls `Schema\Builder::getTypes()` which is unimplemented for MySQL. **Fix:** `AdminDashboardProvider::normalize()` recursively flattens any Eloquent model / `Illuminate\Support\Collection` / `Arrayable` / `Stringable` to plain arrays before the response leaves the provider. Regression: `DashboardTest::test_top_selling_products_does_not_500`.

3. **`GET /api/admin/reporting/products?type=top-selling-products-by-{revenue,quantity}`** → same root cause as bug 2; same fix in `AdminReportingProvider::normalize()` (kept structurally identical to the Dashboard version so both providers stay in sync). Regressions: `ReportingTest::test_products_top_selling_by_revenue_does_not_500` + `_by_quantity_does_not_500`.

4. **`GET /api/admin/transactions/{id}`** → HTTP 500 "Call to undefined relationship [order] on model [Webkul\Sales\Models\OrderTransaction]". The Bagisto core `OrderTransaction` model does NOT declare an `order()` relation in 2.3.x. **Fix:** `AdminTransactionItemProvider::findEntity()` no longer eager-loads `order`; `mapToDto()` now resolves the order manually via `Order::find($entity->order_id)`. Embedded `order` summary is unchanged. Regression: `SalesTransactionsTest::test_detail_does_not_500_on_missing_order_relation`.

5. **`POST /api/admin/customers/{id}/notes` with `customer_notified` (snake_case)** — REPORTED but found to be already working when the manual QA was re-run. The processor reads from `request()->all()` for REST (which preserves snake_case), bypassing the DTO denormaliser. Locked the behaviour with two regression tests anyway: `CustomerTest::test_note_create_with_snake_case_customer_notified` + `_camel_case_customer_notified`.

6. **`PUT /api/admin/settings/channels/{id}` partial update** → HTTP 500 "Undefined array key 'locales'". `ChannelRepository::update($data, $id)` calls `$channel->locales()->sync($data['locales'])` (plus `currencies` / `inventory_sources`) unconditionally — partial bodies that omit those keys crashed. **Fix:** `AdminSettingsChannelProcessor::handleUpdate()` now eager-loads the existing pivots (`Channel::with(['locales','currencies','inventory_sources'])->find($id)`) and backfills any missing key in the payload from the existing pivot ids before forwarding to the repository. Pivots are preserved on partial updates. Regression: `SettingsChannelTest::test_partial_update_with_only_name_succeeds`.

---

### Admin Settings → Channels — implementation notes (Block B Wave 2)

Full CRUD on `Webkul\Core\Models\Channel` (translatable, multi-FK, multi-pivot). Mirrors `Webkul\Admin\Http\Controllers\Settings\ChannelController` 1:1. **No mass-delete** — the admin UI doesn't expose one, so we don't either (parity-with-admin rule).

**Files (8, all under `src/Admin/`):**
- `Models/AdminSettingsChannel.php` — single resource hosting GET-list / GET-one / POST / PUT / DELETE + 5 GraphQL operations.
- `Dto/AdminSettingsChannel{Create,Update}Input.php` — Update accepts every field as nullable for partial updates; both DTOs declare an optional `translations: { <locale>: { ... } }` map.
- `State/AdminSettingsChannel{Collection,Item,Write}Provider.php` — Collection joins `channels × channel_translations` for the requested locale; Item eager-loads `locales / currencies / inventory_sources / translations`; Write is the standard placeholder for PUT/DELETE routing.
- `State/AdminSettingsChannelProcessor.php` — create + update + delete (REST + GraphQL).

**Image upload deferred (logo + favicon).** Mirrors the Phase 5.11 image-upload-deferral pattern — accept a string `logo` / `favicon` path only. The core `ChannelRepository::uploadImages` reads from `request()->hasFile()` and pre-processes the upload; reproducing that across REST + GraphQL is out of scope for v1. The processor passes any provided path string straight through to the repository, which writes it to the column. For new image uploads use the admin panel.

**Delete guards (two, both 400):**
1. **Last-channel guard** — `Channel::count() <= 1`. Project-specific (the monolith only checks for the default app channel — but deleting the last row leaves the storefront unusable, so we refuse).
2. **Default-app-channel guard** — `$channel->code === config('app.channel')`. Mirrors monolith `ChannelController::destroy` exactly. Refuses with the dedicated `cannot-delete-default` lang key.

**FK-cascade behaviour (documented, not enforced by us):** the `channels` table's downstream relations cascade through the schema:
- `channel_locales` / `channel_currencies` / `channel_inventory_sources` / `channel_translations` — all `onDelete('cascade')`; pivots and translation rows are wiped automatically.
- `categories.root_category_id` — `onDelete('set null')`; deleting a channel does NOT delete the root category, it just clears the back-reference (the category itself survives).
- `product_flat` / orders / carts / etc. that reference `channel` typically resolve by `code` string at runtime, so they keep their snapshot value after a channel delete — but the storefront will 500 the next time it tries to resolve a now-deleted channel by code. **The API does not pre-check** for downstream usage on channels — same as monolith. If integrators need that, they should run a usage report before calling DELETE.

**Translatable payload shape:**
- Top-level scalars (`name`, `description`, `seo_title`, `seo_description`, `seo_keywords`, `maintenance_mode_text`, `home_page_content`, `footer_content`) are broadcast to every configured locale by `ChannelRepository::create/update` — same shape the monolith form-request produces on create.
- Optional `translations: { en: { name: ..., seo_title: ..., ... }, fr: { ... } }` map writes per-locale overrides on top of the broadcast. Inside each locale block, the SEO triplet is rolled into a `home_seo` array (`{ meta_title, meta_description, meta_keywords }`) the same way `ChannelController::setSEOContent` does in the monolith.
- The repository syncs `locales` / `currencies` / `inventory_sources` pivots on every write (not partial-add — the supplied array replaces the entire pivot set). Update endpoint therefore expects the full sets when modifying any of them.

**Validation (parity with monolith + cross-array integrity):**
- `code`: required + alpha-dash (regex `^[a-zA-Z]+[a-zA-Z0-9_-]*$`) + unique. Inline regex rather than `Webkul\Core\Rules\Code` for the same reason as Phase 5.3 (the core rule needs a `PotentiallyTranslatedString`-returning `$fail` callback that breaks under a Validator-less validation path).
- `hostname`: nullable + unique when set.
- `locales` / `currencies` / `inventory_sources`: required arrays of integers, every id must exist in the target table. Existence check uses a single `whereIn(...)->count()` and compares against `count(array_unique(...))` — surfaces a clear 422 with the dedicated `unknown-{locale,currency,inventory-source}` keys.
- `default_locale_id` ∈ `locales`, `base_currency_id` ∈ `currencies` — `assertCrossArrayMembership` enforces these whenever both sides are present in the request. Returns 422 with `default-locale-mismatch` / `base-currency-mismatch`.
- `root_category_id`: must exist in `categories`.
- All update fields nullable — partial updates supported. Cross-array membership only checked when both sides arrive together (e.g. updating just `locales` keeps the existing `default_locale_id`).

**Permission resolution:** mirrors Currencies — reads `$admin->role->permission_type` / `permissions` directly; never `bouncer()`. Perms: `settings.channels.create` / `.edit` / `.delete`.

**Coverage:** 34 REST tests, all green on first run. GraphQL test file present (8 cases) but is subject to the project-wide `AdminBooking` GraphQL schema-build issue mentioned in the Locales/InventorySources notes — when GraphQL endpoint comes back, the tests are written and tagged.

**Pre-existing GraphQL schema quirk** (carried over): write-mutation responses may surface `adminSettingsChannel: null` with a non-fatal "Unable to generate an IRI for the item of type AdminSettingsChannel" entry in `errors[]`. The processor itself runs to completion — GraphQL tests assert via `assertDatabaseHas` rather than the response body, identical to every other admin-settings write mutation in this package.

---

### Admin Customer Reviews — implementation notes (Block C C3)

Moderation-only surface on top of the storefront `product_reviews` table. Mirrors `Webkul\Admin\Http\Controllers\Customers\ReviewController` 1:1. No create endpoint — reviews are written from the storefront only; admin can list/detail/update-status/delete (plus mass variants).

**Files:**
- `src/Admin/Models/AdminCustomerReview.php` — single resource hosting listing + detail + Put (status update) + Delete + 4 GraphQL operations (`adminCustomerReviews`, `adminCustomerReview`, `updateAdminCustomerReview`, `deleteAdminCustomerReview`). `requirements: ['id' => '\d+']` to keep `/mass-delete` / `/mass-update-status` routes from being captured.
- `src/Admin/Models/AdminCustomerReviewMassDelete.php` + `AdminCustomerReviewMassUpdateStatus.php` — separate POST resources (mirrors C1/C2 customer mass-action convention).
- `src/Admin/Dto/AdminCustomerReview{Update,MassDelete,MassUpdateStatus}Input.php`.
- `src/Admin/State/AdminCustomerReview{Collection,Item,Write}Provider.php` + `AdminCustomerReviewProcessor.php` + `AdminCustomerReview{MassDelete,MassUpdateStatus}Processor.php`.

**Schema notes:**
- `product_reviews.status` is a free-form string column, not an enum — but the controller validates `in:approved,disapproved,pending`. We mirror that exactly via `ALLOWED_STATUSES` const in both update + mass-update-status processors. Invalid value → 422.
- `customer_id` is nullable (guest reviews allowed by core), so the join is LEFT and customerName/customerEmail can be null in responses.
- Listing query LEFT JOINs `products` (for sku) and `customers` (for name + email) so a single SELECT yields a complete row.

**Update is status-only by design.** The Bagisto admin UI only exposes the status dropdown — title/comment/rating/images are owned by the storefront customer. The Put operation accepts only `status`; any other body fields are silently ignored. Update fires `customer.review.update.before/after` so existing core listeners (notification email to the review author when approved) keep working.

**Permissions:** edit = `customers.reviews.edit`, delete = `customers.reviews.delete`. Same Sanctum pattern as C1/C2 (`assertPermission()` reads `role->permission_type` / `role->permissions` directly, never calls `bouncer()`).

**Mass-update value type:** unlike C1 where status is `0|1`, here `value` is a **string** (`pending|approved|disapproved`). The DTO field is `?string $value` — matching the underlying column.

**Images on detail only.** The provider's `mapToDto` resolves each `ProductReviewAttachment` to `{ id, path, url }` (URL via `Storage::url($path)` wrapped in try/catch). Listing rows leave `images = null` to keep the row light.

**Coverage:** 23 REST tests + 10 GraphQL tests, all green.

---

### Admin Customer GDPR Requests — implementation notes (Block C C4)

Read / status-update / delete on the GDPR data-request queue, plus two action endpoints (`process`, `download-data`). Mirrors `Webkul\Admin\Http\Controllers\Customers\GDPRController` and rounds it out with the destructive cascade and JSON dump that the monolith leaves to listeners / storefront blade.

**Files:**
- `src/Admin/Models/AdminCustomerGdprRequest.php` — listing + detail + Put (status update) + Delete + 4 GraphQL operations (`adminCustomerGdprRequests`, `adminCustomerGdprRequest`, `updateAdminCustomerGdprRequest`, `deleteAdminCustomerGdprRequest`). `requirements: ['id' => '\d+']`.
- `src/Admin/Models/AdminCustomerGdprProcess.php` — POST `/customers/gdpr-requests/{id}/process` + `createAdminCustomerGdprProcess`. Returns `{ requestId, customerId, type, status, customerDeleted, processedAt, message }`.
- `src/Admin/Models/AdminCustomerGdprDownloadData.php` — POST `/customers/{customerId}/gdpr-download-data` + `createAdminCustomerGdprDownloadData`. Returns `{ customerId, customerEmail, generatedAt, data: {...} }`.
- `src/Admin/Dto/AdminCustomerGdpr{Update,Process,DownloadData}Input.php`.
- `src/Admin/State/AdminCustomerGdpr{Collection,Item,Write}Provider.php` + `AdminCustomerGdprProcessor.php` + `AdminCustomerGdprProcessProcessor.php` + `AdminCustomerGdprDownloadDataProcessor.php`.

**Schema notes:**
- Table is `gdpr_data_request` (singular) with columns `id, customer_id, email, type, status, message, revoked_at, timestamps`. **There is NO `revocation_message` column** — the spec asked for one but the actual schema only has the free-form `message` field, so revocation reason rides on `message`.
- `type` values: `update`, `delete` (only two). `status` values: `pending`, `processing`, `declined`, `approved`, `revoked` (enforced via `AdminCustomerGdprProcessor::ALLOWED_STATUSES`).

**Status-transition vs separate `process` endpoint:** the monolith `GDPRController::update` is a generic repository update — admin sends arbitrary fields and the storefront listener (`Shop\Listeners\GDPR`) catches the status change and acts on it. That model breaks down for headless API integrators who want to know *whether* the destructive cascade actually fired. We expose two endpoints instead:
- `PUT` is pure metadata write: status + message, no side effects beyond the status-update email listener. Use this for `pending → processing` or `pending → declined` transitions.
- `POST /process` is the explicit destructive action — sets status to `approved` AND, for `type=delete`, cascades the customer delete via `CustomerRepository::delete()` (firing `customer.delete.before/after` so the GDPR module's own listeners run). For `type=update`, marks `approved` but leaves the customer untouched (admin then applies the requested edits through the regular Customer update endpoint, since the request only carries a free-form `message`, not a structured patch).
- Process refuses to re-run on already-approved or revoked requests (422). Idempotency by rejection, not silent no-op.

**Data dump shape:** `download-data` returns ad-hoc (not bound to a GDPR request — admin can run it on any customer) and surfaces every table referencing the customer's id: `customer` (with `password`/`remember_token` stripped), `addresses`, `orders` (with items/addresses/payment eager-loaded), `reviews` (`product_reviews where customer_id`), `wishlist`, `notes`. Each sub-query is wrapped in try/catch so a missing optional table (e.g. wishlist in non-storefront installs) returns `[]` instead of 500ing. Storefront-side `Shop\Customer\GDPRController::pdfView` only exports orders + addresses; we extend the set since admin moderation expects everything.

**Revocation semantics:** the storefront `GDPRController::revoke` action (customer-initiated) sets `status='revoked'` + `revoked_at = now()`. Our API does NOT expose a separate revoke endpoint — admin would use `PUT … {status: 'revoked'}` to mirror it. `revoked_at` is exposed read-only on detail responses but is not auto-set when the API writes `status='revoked'` (matches monolith — only the storefront's own revoke flow stamps the column).

**GraphQL `id` field collision:** API Platform's GraphQL schema rewrites `id` to `_id` on mutation inputs (resource-id shorthand). The Process input's resource id field is therefore exposed as `requestId` (string — accepts numeric or IRI; normalised via `basename`). REST takes the id from the URL so no input field is needed there.

**Permission resolution:** Sanctum pattern (read `role->permission_type` / `role->permissions` directly, never call `bouncer()`). Three required perms: `customers.gdpr_requests.view` (download-data), `customers.gdpr_requests.edit` (update + process), `customers.gdpr_requests.delete` (delete).

**Coverage:** 24 REST tests + 8 GraphQL tests, all green.

---

### Admin Customers menu — QA-sweep fixes + Eloquent migration (2026-06-24)

Customer-menu QA pass: a missed GraphQL-null sweep + two POPO→Eloquent connection/object migrations (the AdminReview recipe). All green: Customer G18/R48, CustomerReview G12/R24; siblings CustomerGroup G12 + OrderDetail G5 unaffected (decorator has no blast radius). Swagger requestBody + response examples refreshed across all 14 customer models for the new nested shapes.

1. **GraphQL null fix — 3 action-only resources the 2026-06-09 sweep missed.** `AdminCustomerImpersonate`, `AdminCustomerGdprProcess`, `AdminCustomerGdprDownloadData` were camelCase POPOs without `AcceptsCamelCaseWrites` → every multi-word result field resolved null over GraphQL (the documented camelCase-read quirk). Fixed: snake props + the trait (processors untouched; REST unaffected). Lesson: the action/result-only resources are easy to miss in a per-resource sweep — they don't show up in the listing/detail audit.

2. **AdminCustomerReview migrated POPO→Eloquent (AdminReview recipe).** `images` is now a **Relay connection**; `product { id name sku }` + `customer { id name email }` are **typed objects** over GraphQL (flat over REST via the `output:` DTO). **BREAKING — flat `productName`/`productSku`/`customerName`/`customerEmail` dropped on BOTH transports.** The `output:`-DTO name-match trap applies: each DTO prop (`product`/`customer`/`images`) must match a relation on the Eloquent parent or it's silently dropped.

3. **AdminCustomer migrated POPO→Eloquent.** Customer group is now a `group { id code name }` **typed object** on detail AND listing (the listing now batch-loads it — no N+1). **BREAKING — flat `customerGroupId`/`customerGroupName` dropped on BOTH transports.** Group is a NULLABLE BelongsTo (820/942 rows have a null FK).

4. **NEW gotcha + fix — a nullable BelongsTo to-one 500s over GraphQL.** `EloquentPropertyMetadataFactory` hardcodes any to-one relation field as **non-null** and `required: false` does NOT flip it (same root as the OrderDetail/Product gotcha (h) for null HasOne — but here it's a BelongsTo with a null FK, not a HasOne). A group-less customer therefore tripped "Cannot return null for non-nullable field `group`" and 500'd the whole listing. **Fix:** a scoped decorator `Admin/Metadata/NullableToOnePropertyMetadataFactory` with an **allow-list** (now `AdminCustomer::group` + `AdminCustomerReview::customer` + `AdminCustomerReview::product` — see fix 5) that re-marks the named to-one property nullable in the API Platform property metadata. Allow-list (not blanket) so it can't accidentally flip other resources' to-ones — verified zero blast radius (OrderDetail's `customer` BelongsTo + every sibling still green). This is the reusable fix for any future **nullable BelongsTo typed-object** exposed over GraphQL: extend the allow-list, don't make it global.

5. **2026-06-25 — Admin Customer Reviews listing 500 over GraphQL (same allow-list extension).** A guest review (null `customer_id`) — or a review whose `customer`/`product` was deleted — made the non-nullable `customer`/`product` to-one return null → "Internal server error" that nulled the whole node. Fixed by adding `AdminCustomerReview::customer` + `::product` to the same scoped nullable-to-one allow-list (fix 4's `NullableToOnePropertyMetadataFactory`), so a missing owner/product resolves as null instead of 500ing the row. Regression test added (guest review with null customer lists cleanly); GraphQL CustomerReview suite 13 green.

---

### Admin Dashboard + Reporting — implementation notes (Blocks D + E)

Five read-only endpoints — one Dashboard + four Reporting sub-pages — that surface the existing `Webkul\Admin\Helpers\Dashboard` and `Webkul\Admin\Helpers\Reporting` aggregates 1:1.

**Files (10 sources + 4 test files):**
- `src/Admin/Models/AdminDashboard.php` + `AdminReporting{Overview,Sales,Customers,Products}.php` — single-op resources, each with `GetCollection(uriTemplate: '/dashboard/stats' | '/reporting/{...}')` + `paginationEnabled: false` + a GraphQL `Query(name: 'stats', resolver: …)` operation.
- `src/Admin/State/AdminDashboardProvider.php` + `AdminReportingProvider.php` (+ 4 thin subclasses, one per reporting sub-page that override only `protected string $entity`).
- `src/Admin/Resolver/AdminDashboardQueryResolver.php` + `AdminReportingQueryResolver.php` (abstract) + 4 subclasses.
- Tests: `tests/Feature/Admin/{RestApi,GraphQL}/DashboardTest.php` + `ReportingTest.php`.

**Type-routing convention.** Mirrors the monolith exactly — both `DashboardController::stats()` and the 3 `Reporting/*Controller::stats()` methods dispatch by `request()->query('type')` against a `$typeFunctions = [type => helperMethod]` map. The providers reproduce the maps as PHP `const` arrays. Unknown `type` → HTTP 400 `InvalidInputException` with the `admin.dashboard.invalid-type` / `admin.reporting.invalid-type` lang key (the monolith would 500 here — array key undefined — so this is a friendlier API-side guard, not an API/admin divergence).

**Helper output is opaque.** The helpers return a mix of plain arrays (`getOverAllStats`), Eloquent collections (`getTopCustomers`), and Support collections (`getTopSellingProducts`). The provider's `toArray()` normaliser collapses everything to plain arrays so JSON serialisation works. **One blade-rendered field is silently dropped:** `Dashboard::getTodayStats` builds each order entry with `'items' => view('admin::sales.orders.items', compact('order'))->render()` — that HTML string survives JSON encoding fine (it's just a string), but consumers should know it carries pre-rendered admin-panel markup. We don't strip it — parity with monolith.

**Date / channel params.** Both helpers read `request()->query('start' | 'end' | 'channel')` in their `__construct` (`AbstractReporting`). The GraphQL resolver pushes the corresponding GraphQL args back into the live request via `request()->query->set()` before instantiating the helper, so REST and GraphQL behave identically. `start` defaults to 30 days ago; `end` defaults to today.

**`dateRange` shape differs between Dashboard and Reporting.**
- `Dashboard::getDateRange()` returns a formatted string (e.g. `"25 Apr - 25 May"`).
- `Reporting::getDateRange()` returns an associative array `{ previous, current }` — two ISO-ish range strings covering the previous and current windows (helpers compare windows for progress-vs-previous).

Tests assert each shape explicitly so any future change in the core helper is caught here too.

**No permission gate.** Bagisto core's `DashboardController` and the 4 `Reporting/*Controller`s have no ACL middleware — any authenticated admin can see the dashboard. Mirrored: providers only call `AdminAuthHelper::resolveAdmin()` and 401 if absent; no `customers.*` / `sales.*` permission check.

**GraphQL field-name auto-derivation.** API Platform composes the GraphQL field name as `{name}{ShortName}`. `Query(name: 'stats')` on `AdminDashboard` becomes `statsAdminDashboard` (not `adminDashboardStats` as the design spec aspired). The same applies to all 4 Reporting queries (`statsAdminReportingOverview`, etc.). Overriding to the spec's preferred name would require a custom GraphQL builder — we followed the project's auto-naming convention for consistency with the other 100+ admin GraphQL operations.

**Reporting overview is API-specific.** The monolith `Reporting/Controller.php` is an abstract base with no `index` action of its own — the 3 sub-pages override it. The API needs a single `/reporting/stats` overview entry-point that picks the most useful headline numbers across all three concerns, so the overview provider's `TYPE_OVERVIEW_FUNCTIONS` map composes its own 4-entry shortlist (`total-sales` default, `total-orders`, `total-customers`, `top-selling-products-by-revenue`). Each entry still routes to a real `Reporting` helper method — no synthetic data.

**Coverage:** 23 REST tests (7 Dashboard + 16 Reporting) + 8 GraphQL tests (3 Dashboard + 5 Reporting) = 31 tests, all green.

---

### Wave 4 — Order Actions implementation notes

The Order Actions wave covers 5 endpoint groups — Cancel, Comments, Invoice (+ PDF), Shipment, Refund (+ Preview) — totalling 11 endpoint pairs. All share one guard and one item-mapping/translation toolchain, so the per-action processors stay small.

#### Route layout — POST nested, GET flat

```
POST   /api/admin/orders/{id}/cancel
POST   /api/admin/orders/{id}/comments       GET /api/admin/orders/{id}/comments
POST   /api/admin/orders/{id}/invoices       GET /api/admin/invoices/{id}
                                              GET /api/admin/invoices/{id}/print   (PDF)
POST   /api/admin/orders/{id}/shipments      GET /api/admin/shipments/{id}
POST   /api/admin/orders/{id}/refunds        GET /api/admin/refunds/{id}
POST   /api/admin/orders/{id}/refunds/preview
```

Why split: writes always need the parent order in the URL (the action only makes sense against an order), so they nest under `/orders/{id}/...`. Reads are addressed by the resource's own id and live flat at `/api/admin/{invoices,shipments,refunds}/{id}` — clients that hold a cached invoice id should not have to know which order it came from. Comments are the exception: there's no standalone comment-detail endpoint, only the list+create pair on the order.

#### Shared guard — `AdminOrderActionGuard`

`src/Admin/State/AdminOrderActionGuard.php`. One class, injected into every action processor (`AdminCancelOrderProcessor`, `AdminOrderCommentCreateProcessor`, `AdminInvoiceCreateProcessor`, `AdminInvoicePrintProvider`, `AdminShipmentCreateProcessor`, `AdminRefundCreateProcessor`, `AdminRefundPreviewProcessor`, and the read providers). Responsibilities:

- `resolveAdmin()` — Bearer-token → admin (via `AdminAuthHelper`), 401 if unresolved.
- `resolveOrder($uriVariables, $context, $routeKey = 'id')` — picks the order id from URI / GraphQL args / route fallback, normalises IRIs via `basename`, eager-loads `items.product / items.children / payment / channel / addresses / refunds`, throws `ResourceNotFoundException` (404) on miss.
- `assertCanCancel / assertCanInvoice / assertCanShip / assertCanRefund` — each splits the monolith's single `Order::canX()` into specific 422 failures so the client can disambiguate the reason. Cancel → 4 failures (closed/fraud/nothing-to-cancel/no-permission). Invoice → 5 (adds **paypal_standard-blocked**). Shipment → 4 (closed/fraud/nothing-to-ship/no-permission). Refund → 4 (closed/fraud/nothing-to-refund/no-permission, where "nothing-to-refund" reproduces `Order::canRefund` — at least one item with `qty_to_refund > 0` OR an outstanding `base_grand_total_invoiced − base_grand_total_refunded − adjustment_fee`).

#### Permission resolution — same pattern as Reorder

`AdminOrderActionGuard::adminHasPermission()` reads `$admin->role->permission_type` / `$admin->role->permissions` directly. **Does NOT call `bouncer()->hasPermission()`** — bouncer's helper reads `auth()->guard('admin')->user()` which is session-based and not populated for Sanctum-token API requests, so it would always return false. Constants on the guard map each action to its required permission:

| Action | Constant | Required permission |
|--------|----------|--------------------|
| Cancel | `PERM_CANCEL` | `sales.orders.cancel` |
| Invoice | `PERM_INVOICE` | `sales.invoices.create` |
| Shipment | `PERM_SHIPMENT` | `sales.shipments.create` |
| Refund | `PERM_REFUND` | `sales.refunds.create` |
| Comment | — | no permission gate (matches monolith) |

`permission_type='all'` always passes. Otherwise the requested perm must appear in `$role->permissions` (handles both array and comma-string storage).

#### Per-action gate matrix

| Gate | Cancel | Invoice | Shipment | Refund | Lang key root |
|------|--------|---------|----------|--------|----------------|
| `Order::STATUS_CLOSED` | yes | yes | yes | yes | `actions.<action>.closed` |
| `Order::STATUS_FRAUD`  | yes | yes | yes | yes | `actions.<action>.fraud` |
| Payment method = `paypal_standard` | — | **yes** | — | — | `actions.invoice.paypal-standard-blocked` |
| No item with positive `qty_to_*` (cancel/invoice/refund) — or no stockable item with `qty_to_ship > 0` (shipment) | yes | yes | yes | yes | `actions.<action>.nothing-to-<action>` |
| Admin role lacks permission | yes | yes | yes | yes | `actions.<action>.no-permission` |

#### PayPal Standard invoice block

Mirrors the monolith Create-Invoice blade exclusion. PayPal Standard records the order on confirmation but only marks it paid asynchronously via IPN, so the monolith hides the Create-Invoice button for that payment method. `AdminOrderActionGuard::assertCanInvoice` reproduces the rule:

```php
if ($order->payment && $order->payment->method === 'paypal_standard') {
    throw new InvalidInputException(
        __('bagistoapi::app.admin.order.actions.invoice.paypal-standard-blocked'),
        422,
    );
}
```

#### Comment endpoint specifics

`AdminOrderCommentCreateProcessor` dispatches `sales.order.comment.create.before` then `sales.order.comment.create.after` around the `OrderCommentRepository::create` call — same hook points as the monolith. Core listeners (Notifications module) read `customer_notified` off the created row and email the customer when truthy. **No permission gate** — any authenticated admin can add a comment, matching the monolith behaviour. The empty-comment 422 (`actions.comment.empty`) is the only validation failure.

The list endpoint reuses the standard admin paginator → `AdminCollectionEnvelopeNormalizer` for REST and native cursor pagination for GraphQL; nothing action-specific to call out.

#### PDF print — REST only

`AdminInvoicePrintProvider` runs the `admin::sales.invoices.pdf` blade through dompdf (`barryvdh/laravel-dompdf` Facade) and returns a raw `Illuminate\Http\Response` with `Content-Type: application/pdf` + `Content-Disposition: attachment; filename="invoice-{id}.pdf"`. API Platform's `outputFormats: ['pdf' => ['application/pdf']]` on the Get operation declares the binary content type. GraphQL has no equivalent — binary streams aren't expressible — so the GraphQL print-invoice doc page is a stub pointing back to REST. One REST test is `markTestSkipped` because dompdf fails to render in the test environment (missing fonts / asset paths); the route itself is exercised by other tests in `InvoiceTest.php`.

#### Refund preview

`AdminRefundPreviewProcessor` takes the same request body as Create Refund but never writes. It runs the eligibility guard, calls `RefundRepository::getOrderItemsRefundSummary($payload, $orderId)`, and returns a `RefundTotalsSummary` DTO (`subtotal` / `discount` / `tax` / `shipping` / `adjustmentRefund` / `adjustmentFee` / `grandTotal` plus pre-formatted variants). Use case: live-updating the "Total Refund" widget in the admin refund form on every quantity / adjustment change.

#### DRY pieces extracted by Wave 4

| File | Purpose |
|------|---------|
| `Admin/State/AdminOrderActionGuard.php` | One guard for all 5 action groups — auth, order resolution, eligibility splits, permission resolution |
| `Admin/State/Concerns/MapsOrderActionItems.php` | Trait — `flatItemsMap` (orderItemId→qty for invoice/refund), `nestedShipmentItemsMap` (orderItemId→[sourceId→qty]), `mapItem` (response item builder used by invoice/shipment/refund DTOs) |
| `Admin/State/Concerns/TranslatesActionPayload.php` | Trait — DTO/GraphQL-args/request-body fallback chain reused by every action processor |
| `Admin/Dto/OrderActionItemDto.php` | Shared response item shape; class is retained as a typed schema reference, but as of 2026-05-28 `AdminInvoiceDetailDto::$items` / `AdminShipmentDetailDto::$items` / `AdminRefundDetailDto::$items` are typed `array` and **populated as plain associative arrays** (see Wave 2 "Plain-array convention extended" note). Both REST and GraphQL receive a flat JSON array — no cursor-connection wrapper on GraphQL. |
| `Admin/Dto/InvoiceItemInput.php` / `ShipmentItemInput.php` / `RefundItemInput.php` | Nested input DTOs declared `#[ApiResource(operations: [], graphQlOperations: [])]` so they register as GraphQL input types without exposing endpoints |
| `Admin/Dto/RefundTotalsSummary.php` | Preview output — totals only, no items |
| `bagistoapi::app.admin.order.actions.*` lang block | All gate / validation / failure messages, grouped by action |

---

## Key Architectural Notes

- **REST POST receives Model, not DTO**: API Platform passes the model instance for REST `Post` operations. Build the DTO manually from `request()->input()` inside the processor.
- **GraphQL receives DTO**: For `Mutation` operations, `$data` is the input DTO class.
- **Name converter chain**: `HydraPrefixNameConverter → MetadataAwareNameConverter → EloquentNameConverter → SnakeCaseToCamelCaseNameConverter`. Multi-word resources (e.g., `CompareItem`) need `FixedSerializerContextBuilder` for correct GraphQL mutation serialization.
- **Eloquent PropertyAccessor**: API Platform reads via `$model->{$property}` (Eloquent `__get()`). Use Eloquent accessors, not `getXxx()` methods.
- **`_id` field**: Auto-mapped `id → _id` in GraphQL via `FieldsBuilder`. Do not add a manual `$_id` property.
- **Relationship loading**: Use `$model->load(['product', 'customer'])` after create — not `setRelation()`.
- **Delete input ID is an IRI**: `/api/shop/your-models/123`. Extract numeric ID with `basename($input->id)`.

---

## Product Endpoint — REST ↔ GraphQL Parity

The `Product` resource is the most combinatorial endpoint in the package (search + filter + sort + pagination + brand + new/featured + attribute filters with variant fallback). To guarantee identical behaviour across REST and GraphQL, REST does **not** reimplement any of the logic — `ProductRestProvider` extends `ProductGraphQLProvider` and only translates REST query params into the same `$args` shape the GraphQL provider already consumes, then calls `parent::provide()`.

### REST → GraphQL query-param translation

| REST query param                | → GraphQL arg                                |
|---------------------------------|----------------------------------------------|
| `?query=foo`                    | `args.query`                                 |
| `?sort=name-asc` (compound)     | `args.sortKey=NAME, args.reverse=false`      |
| `?sort=name&order=desc`         | `args.sortKey=NAME, args.reverse=true`       |
| `?per_page=20&page=2`           | `args.first=20, args.after=base64("19")`     |
| `?type=configurable`            | `args.filter.type`                           |
| `?category_id=2`                | `args.filter.category_id`                    |
| `?price=10,200`                 | `args.filter.price_from=10, price_to=200`    |
| `?price_from=10&price_to=200`   | same                                         |
| `?new=1` / `?featured=1`        | `args.filter.new` / `featured`               |
| `?color=3&size=6&brand=38`      | `args.filter.color` / `size` / `brand`       |
| `?filter={"color":{...}}`       | merged into `args.filter`                    |
| `?locale=&channel=`             | `args.locale` / `args.channel`               |

**Sort tokens (locked):** `name-asc`, `name-desc`, `created_at-asc`, `created_at-desc`, `updated_at-*`, `price-asc`, `price-desc`, `id-*`. Shop page uses `?sort=name-asc` format — do not diverge.

**Filter keys that are NOT filters:** `query`, `sort`, `order`, `page`, `per_page`, `locale`, `channel`, `filter`. Everything else in the query string is forwarded as a filter — any new filterable attribute (e.g. `?material=leather`) works automatically.

### Parity rules

1. **Any change to search/filter/sort/pagination logic must go into `ProductGraphQLProvider`.** Do not fork logic into the REST provider.
2. REST list and detail use different payload shapes via serializer groups (see "List vs detail payload" below). GraphQL responses are unaffected — they don't pass a groups context, so all readable properties render.
3. After any change to `ProductGraphQLProvider` or `Product` model, run both suites:
   ```bash
   php artisan test packages/Webkul/BagistoApi/tests/Feature/GraphQL/ProductQueryTest.php packages/Webkul/BagistoApi/tests/Feature/GraphQL/ProductSearchFilterTest.php packages/Webkul/BagistoApi/tests/Feature/GraphQL/ProductTest.php
   php artisan test packages/Webkul/BagistoApi/tests/Feature/RestApi/ProductTest.php
   ```
4. Register new providers: `ProductRestProvider` is tagged as `ProviderInterface` in `BagistoApiServiceProvider::register()` — without this tag the provider is silently not invoked and API Platform returns 404. Every class used as a `provider:` on an ApiResource operation must be tagged.

### List vs detail payload (Groups filter)

The REST `GetCollection` operation sets `normalizationContext: ['groups' => ['product:list']]`. Symfony Serializer's strict-groups behavior then emits **only** properties annotated with `#[Groups(['product:list'])]`. Properties without that group annotation are excluded from the list response — including the heavy relation IRI lists (`attributeValues`, `variants`, `images`, etc.).

**Fields currently in `product:list`** (~20 fields): `id`, `sku`, `type`, `name`, `urlKey`, `status`, `shortDescription`, `price`, `specialPrice`, `new`, `featured`, `minimumPrice`, `maximumPrice`, `formattedPrice`, `formattedSpecialPrice`, `formattedMinimumPrice`, `formattedMaximumPrice`, `baseImageUrl`, `isSaleable`, `isInWishlist`, `isInCompare`.

### Product wishlist / compare flags — `isInWishlist` / `isInCompare`

Every product payload (listing **and** detail, REST **and** GraphQL) carries two computed booleans so a client can highlight the wishlist / compare icon per product without cross-referencing the separately-paginated `/api/shop/wishlists` + compare endpoints (those paginate independently of the catalog, so a product on catalog page 1 may have its wishlist row on wishlist page 3 — client-side matching is unreliable).

- **`isInWishlist`** — `1` if the product is in the authenticated customer's wishlist for the **current channel** (`wishlist_items` is channel-scoped), else `0`. **`isInCompare`** — `1` if in the customer's compare list (`compare_items` is NOT channel-scoped), else `0`. Both are **always `0` for guests** (neither list supports guests in this API).
- **0/1, not true/"":** the flags are deliberately exposed as **integers `0`/`1`**, not booleans. A bool false stringifies to `""` over GraphQL (the project-wide Eloquent-scalar stringification quirk), which is an awkward "not in list" signal; `0` is explicit. The accessors return `int` (`… ? 1 : 0`) and the `#[ApiProperty]` declares `schema: ['type' => 'integer', 'enum' => [0, 1]]`.
- **Resolver:** `src/State/ProductRelationFlagResolver.php` — a request-scoped **singleton** (registered in `BagistoApiServiceProvider::register()`). On first use it loads the customer's wishlisted + compared product-id sets (one `pluck` each) and does O(1) lookups thereafter — **≤ 2 queries per request, no N+1** regardless of page size. Memoization is keyed by the resolved customer id and re-resolves when the authenticated customer changes mid-process (same identity-stable-cache lesson as `AdminApiGuard`).
- **Wiring:** on `Product.php` the flags follow the `getNew()` pattern — `is_in_wishlist` / `is_in_compare` in `$appends`, plain `getIsInWishlistAttribute(): int` / `getIsInCompareAttribute(): int` accessors (the real logic, calling the resolver and returning `1`/`0`), and `#[ApiProperty(… schema: ['type' => 'integer', 'enum' => [0, 1]])]` + `#[Groups(['product:list'])]` on separate `getIs_in_wishlist()` / `getIs_in_compare()` getters. The explicit `schema` is required — without it the appended attribute is typed string. **GraphQL single-product + listing both return the Eloquent model, so the getters cover them; REST detail returns `ProductDetailDto`, so the flags are also added as `?int $is_in_wishlist` / `$is_in_compare` on that DTO and populated (`… ? 1 : 0`) from the resolver in `ProductDetailProvider::buildDto()`.**
- **Transport rendering:** **REST returns `1` / `0` integers**; **GraphQL returns them as the strings `"1"` / `"0"`** (the same project-wide Eloquent-scalar stringification that turns `status`/`new`/`featured` into strings — it stringifies the int, so `0` stays `"0"`, never `""`). Both are falsy-at-`0` / truthy-at-`1`. REST tests assert strict `toBe(0)` / `toBe(1)`; GraphQL tests cast with `(bool) (int) $value`.
- **Membership matches by exact `product_id == product.id`** (parity with core storefront `is_wishlist` in `Webkul\Shop\…\ProductResource`). A configurable parent card highlights when the parent's own id is in the list; variant-level nuance is not handled.
- Tests: `tests/Feature/{GraphQL,RestApi}/ProductRelationFlagTest.php` (6 GraphQL + 4 REST). Design spec: `docs/superpowers/specs/2026-06-03-product-wishlist-compare-flags-design.md`. Docs-site (`bagisto-api-docs`) update is a pending follow-up.

**To add a field to the list response:** add `#[Groups(['product:list'])]` next to its `#[ApiProperty(...)]` on the corresponding getter in `Product.php`. To also keep an existing mutation group, use `#[Groups(['product:list', 'mutation'])]`.

**Detail (`GET /api/shop/products/{id}`) and GraphQL** do NOT set a groups context — all readable properties render. So adding `product:list` to a property does not remove it from detail or GraphQL.

**`$with` is trimmed** to `['attribute_family']` only. Heavy relations (`attribute_values`, `variants`, `super_attributes`, `images`, `price_indices`) are loaded explicitly inside `ProductGraphQLProvider::provide()`'s `->with([...])` call when GraphQL/detail need them. Do not re-add to `$with` — it would re-introduce heavy auto-loading on every Product fetch (including list).

### Heavy-resource sub-resource pattern (CountryState reference)

When a model has > ~3 heavy relations, list responses must omit them via the Groups filter. Heavy relations get their own sub-resource at `/{parent}/{parentId}/{relation}` using API Platform `Link`. See `Models/CountryState.php` for the reference pattern (multi-`#[ApiResource]` with `Link(fromClass: ParentClass::class, fromProperty: 'relation')`).

### List trim + Detail embed via output DTOs (CustomerOrder reference)

When the list response should be a slim card payload **and** the detail response should embed every relation inline (no IRI follow-ups), use a paired-DTO setup instead of the Groups filter. Two operations, two output DTOs, one provider method per shape:

```php
// Models/CustomerOrder.php
new GetCollection(
    output: CustomerOrderListDto::class,
    normalizationContext: ['skip_null_values' => false],
    ...
),
new Get(
    output: CustomerOrderDetailDto::class,
    normalizationContext: ['skip_null_values' => false],
    ...
),
```

The provider:
- Eager-loads what the **detail** needs (`->with(['items','addresses','payment','shipments.items'])`).
- Branches on `Operation` instance: `GetCollection` → maps each model to the slim list DTO; `Get` → maps to the rich detail DTO with embedded sub-DTOs (`OrderItemDto`, `OrderAddressDto`, `OrderPaymentDto`, `OrderShipmentDto` with nested `OrderShipmentItemDto`).
- **Must also gate on GraphQL context.** API Platform's GraphQL Query (item) does **not** call its own `Query` operation provider — it resolves the IRI through the REST `Get` operation, so the provider sees `$operation instanceof \ApiPlatform\Metadata\Get` even for GraphQL queries. The same holds for GraphQL `QueryCollection` and `GetCollection`. Without a guard, the DTO branch fires for GraphQL too and breaks every field that lives on the Eloquent model but not the DTO (e.g. `customerOrder.shipments`). Detect GraphQL with `! empty($context['graphql_operation_name'])` and skip the DTO conversion when set:
  ```php
  $isGraphQL = ! empty($context['graphql_operation_name']);
  if ($operation instanceof \ApiPlatform\Metadata\Get && ! $isGraphQL) {
      return $this->toDetailDto($order);
  }
  ```

Why this and not the Groups filter:
- Eloquent magic-cast attributes (`$casts`) bypass Symfony Serializer's `attributes` whitelist and `#[Groups]` annotations on `getXxx()` accessors don't apply to magic-property access — so Group-based trim doesn't work cleanly for Eloquent-backed resources with no explicit getters.
- DTOs are POPOs — Symfony reads public typed properties directly. No Eloquent quirks, no `Schema\Builder::getTypes()` crash on nested resources, no risk of accidentally exposing a relation as a dangling IRI.
- Each DTO declares its own `#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]` so nullable fields render as `null` instead of being silently dropped.

Required wiring:
- Add the DTO directory to `'resources'` in **both** `config/api-platform.php` (root) and `packages/Webkul/BagistoApi/config/api-platform.php` (package) and `packages/Webkul/BagistoApi/config/api-platform-vendor.php`.
- Tag the provider in `BagistoApiServiceProvider::register()` (CLAUDE.md "Step 5 — Register in ServiceProvider" rule).
- DTO field names must be **snake_case** — the project's `OutputOnlySnakeToCamelNameConverter` will surface them as camelCase in the response (`coupon_code` → `couponCode`).
- After adding new DTOs, clear caches: `redis-cli -n 1 flushdb && php artisan optimize:clear`.

GraphQL stays untouched — Query / QueryCollection still return the Eloquent model (no `output:` set on those operations), so existing GraphQL field selections keep working.

---

## API — bug fix notes (2026-05-26)

### GraphQL Reporting queries returned 500 — `dateRange` type mismatch (Bug 1)

**Bug:** `statsAdminReportingOverview/Sales/Customers/Products` GraphQL queries returned `Internal server error`. REST equivalents worked.

**Root cause:** `Webkul\Admin\Helpers\Reporting::getDateRange()` returns an **array** `{ previous, current }` (documented in the coverage table — "dateRange shape differs between Dashboard and Reporting"). But the 4 `AdminReporting{Overview,Sales,Customers,Products}` resources declared `public ?string $dateRange = null`. Symfony Serializer threw `Cannot assign array to property … ::$dateRange of type ?string` while building the GraphQL response (REST didn't hit it because the REST response is JSON-encoded from an already-built array, not via property assignment on the resource).

**Fix:** changed `$dateRange` to `?array` on all 4 Reporting resources. `AdminDashboard` keeps `?string` (its helper returns a string).

### GraphQL `id` selection on logout / forgot-password → 500 (Bug 2)

**Bug:** Selecting the `id` field on `createAdminLogout.adminLogout` / `createAdminForgotPassword.adminForgotPassword` returned `Cannot return null for non-nullable field …PayloadData.id`.

**Root cause:** API Platform auto-injects an `ID!` field on GraphQL mutation outputs. The output object (built via `(object) [...]` cast inside the processor) never set `id`, so GraphQL refused to serialize the response.

**Fix:** (a) declared `?int $id = 0` `ApiProperty` on both `AdminLogout` + `AdminForgotPassword` resources; (b) added `'id' => 0` to every `(object) [...]` return in `AdminLogoutProcessor` + `AdminForgotPasswordProcessor`. Sentinel `0` because these resources have no real persistence — the value is purely a GraphQL schema requirement.

### Shop `createNewsletter` GraphQL mutation lost `customerEmail` (Bug 4)

**Bug:** `createNewsletter` returned `The customer email field is required` for every payload — even with `customerEmail` in the input.

**Root cause:** the package-wide `OutputOnlySnakeToCamelNameConverter` denormalises incoming camelCase JSON keys to snake_case (e.g. `customerEmail` → `customer_email`). `SubscribeToNewsletterInput::$customerEmail` is camelCase, so the value never lands on the DTO. The processor's existing REST fallback (`request()->all()`) didn't help GraphQL because GraphQL inputs live at `$context['args']['input']`, not in `request()`.

**Fix:** `NewsletterSubscriptionProcessor::process()` now also reads from `$context['args']['input']['customerEmail'] | customer_email` when the DTO is empty. Same chain as `CartTokenProcessor::normalizeInputData()`.

### Shop `/api/shop/locales` rows missing `logoPath` / `logoUrl` for null-logo entries (Bug 5)

**Bug:** Locales listing surfaced `logoPath` + `logoUrl` for rows that had logos but omitted them entirely (null-stripping) for rows that didn't — making field-presence checks unreliable.

**Root cause:** `src/Models/Locale.php` did not set `normalizationContext: ['skip_null_values' => false]`, so API Platform's serializer silently dropped null fields per the project-wide "Null fields are always included" convention.

**Fix:** added `normalizationContext: ['skip_null_values' => false]` on the `#[ApiResource]` block. Every locale row now carries `logoPath` (null when unset) and `logoUrl` (null when unset).

### GraphQL inputs are camelCase, REST inputs accept snake_case (Bug 3 — documented, no code change)

**Behaviour:** GraphQL admin mutation inputs strictly require camelCase field names (`customerGroups`, `actionType`, `discountAmount`). REST endpoints accept either snake_case or camelCase (the name converter handles incoming JSON). This is **by convention** — API Platform auto-derives GraphQL input types from DTO property names, and the project's DTOs use camelCase, so the GraphQL schema is camelCase-only.

**Why not unify:** building a per-mutation accept-both shim across ~50+ admin write endpoints is high-cost / low-value. Integrators that hit both transports already know GraphQL is camelCase-only by language convention. Documented here so the contract is explicit.

**If a specific endpoint needs to accept snake_case over GraphQL,** add the same `$context['args']['input']['snake_key'] ?? $context['args']['input']['camelKey']` chain that Newsletter (Bug 4) uses.

---

## Shop API — GDPR data requests (2026-06-25)

New storefront GDPR surface: a logged-in customer can raise / list-own / view-one / revoke / delete their own GDPR data requests over REST + GraphQL. Previously only Bagisto's storefront web could create them — the customer API had no GDPR surface.

- **Config gate.** Every endpoint is gated by the admin config `general.gdpr.settings.enabled`. When disabled → HTTP 400 (REST) / `errors[]` (GraphQL) with `"GDPR data requests are disabled. Please enable GDPR from the admin configuration."`
- **Customer-scoped.** Only your own requests — list/view/revoke/delete cross-customer → 404. Auth required (Sanctum customer token).
- **Endpoints:**
  - `GET /api/shop/gdpr-requests` (list own; sort `id`\|`created_at`, `order`; `{ data, meta }`) + `gdprRequests` (cursor).
  - `GET /api/shop/gdpr-requests/{id}` (view own; 404 if not yours) + `gdprRequest(id:)`.
  - `POST /api/shop/gdpr-requests` `{ type: delete\|update, message }` → 201 pending + `createGdprRequest`.
  - `POST /api/shop/gdpr-requests/{id}/revoke` → 200 (only while `pending`/`processing`, else 422) + `revokeGdprRequest`.
  - `DELETE /api/shop/gdpr-requests/{id}` → 204 + `deleteGdprRequest` (snapshot).
- **Object shape:** REST `id` / GraphQL `_id`, `email`, `status` (`pending`\|`processing`\|`declined`\|`approved`\|`revoked`), `type` (`delete`\|`update`), `message`, `revokedAt`, `createdAt`, `updatedAt`, nested `customer { _id }` (the owner — no flat `customerId`), `successMessage` (mutation results only).
- **Parity:** mirrors core's storefront GDPR flow and fires the same create/update events so core notification listeners run.
- **Tests:** `GraphQL/GdprRequestTest.php` (14) + `RestApi/GdprRequestTest.php` (19), green. Docs added under shop graphql-api + rest-api (`gdpr-requests/`).

## Shop API — bug fix notes (2026-05-25)

### CustomerProfileUpdate — REST `currentPassword` no longer dropped

**Bug:** `PUT /api/shop/customer-profile-updates/{id}` ignored the body's `currentPassword` field, so password-change requests failed with "Current password is required" even when correct. GraphQL was not affected.

**Root cause:** the package-wide `OutputOnlySnakeToCamelNameConverter` (`src/Serializer/`) denormalises incoming JSON keys camelCase → snake_case when populating the input DTO (e.g. `currentPassword` → `current_password`). But `CustomerProfileInput` declares camelCase properties (`$currentPassword`), so multi-word body fields never land on the DTO. Single-word fields (`email`, `phone`, `password`) round-trip fine.

**Fix:** `CustomerProfileProcessor::process()` now merges `request()->json()->all()` into `$data` for REST requests (the GraphQL path was already merging `$context['args']['input']`). This is the same pattern documented for `CartTokenProcessor::normalizeInputData()`. Lang keys (`bagistoapi::app.graphql.customer.current-password-required` / `.current-password-incorrect`) already existed.

**Tests:** `tests/Feature/RestApi/CustomerTest.php` + `tests/Feature/GraphQL/CustomerProfileTest.php` each add `test_update_password_with_{correct,wrong}_current_password_*` (correct → 200 + hash changes; wrong → error + hash unchanged).

### CustomerAddress — `company_name` round-trip verified

**Bug (reported):** `company_name` missing on `POST/PUT /api/shop/customer-addresses`. **Finding:** the DB column, DTO field (`#[SerializedName('companyName')]`), processor branches in `CustomerAddressTokenProcessor::mapInputToAddress()`, and the response mapper were all already wired correctly. The GraphQL tests already asserted response `companyName`. The REST tests only asserted DB persistence — no response-shape assertion. Confirmed fully working end-to-end.

**Tests added:** `tests/Feature/RestApi/CustomerAddressTest.php` adds `test_create_address_response_carries_company_name` and `test_update_address_response_carries_company_name` to lock the response shape.

### ReadCart — `couponCode` was always null

**Bug:** `POST /api/shop/cart` returned `couponCode: null` even after a coupon was applied.

**Root cause:** `CartData::fromModel()` (in `src/Dto/CartData.php`) read `couponCode` out of the `additional` JSON column (`$additional['coupon_code']`). The actual storage column is `carts.coupon_code` (top-level scalar) — `Webkul\Checkout\Cart::setCouponCode()` writes it directly to that column.

**Fix:** `CartData::fromModel()` now reads `$cart->coupon_code` directly.

**Tests:** `tests/Feature/RestApi/CartTest.php::test_read_cart_returns_applied_coupon_code` and `tests/Feature/GraphQL/GuestCartTest.php::test_read_cart_returns_applied_coupon_code_graphql`. Both seed an active cart-rule + coupon via a new `seedActiveCoupon()` helper, apply, then read back.

### ApplyCoupon / RemoveCoupon — `success`/`message` were always null

**Bug:** `POST /api/shop/apply-coupon` and `POST /api/shop/remove-coupon` succeeded but returned `success: null, message: null`. The cart payload was correct; the per-call status fields were never populated.

**Fix:** `CartTokenProcessor::handleApplyCoupon()` and `::handleRemoveCoupon()` now set `$cartData->success` and `$cartData->message` before returning. Apply additionally **verifies the coupon actually applied** before claiming success — `CartFacade::setCouponCode()` blindly writes the column without throwing for unknown codes, so the processor now checks for a matching `cart_rule_coupons.code` row; if missing it strips the column, sets `success=false`, and surfaces `apply-coupon-failed`. New lang keys: `bagistoapi::app.graphql.cart.coupon-applied` / `.coupon-removed` (`.apply-coupon-failed` / `.remove-coupon-failed` already existed).

**Tests:** 1 REST + 1 GraphQL each for apply + remove (`test_apply_coupon_response_carries_success_and_message[_graphql]`, `test_remove_coupon_response_carries_success_and_message[_graphql]`). The pre-existing `test_apply_invalid_coupon_code_returns_error_or_failure` test still passes (now correctly observes `success=false`).

---

## Shop REST — known gaps, deliberately left as-is (2026-08-07)

Found during the storefront REST doc review. All three were assessed and **consciously not changed**. Do not "fix" any of them without the user asking — and note the first one is **not mentioned in the api-docs on purpose**.

### 1. Customer-invoice `items` / `addresses` are unresolvable references (silent by decision)

`GET /api/shop/customer-invoices` and `/customer-invoices/{id}` serialise the two relations as path references — `/api/customer_invoice_items/11`, `/api/customer_invoice_addresses/210`. **Those routes answer 404** ("This route does not aim to be called"): `CustomerInvoiceItem` and `CustomerInvoiceAddress` are `#[ApiResource]` classes declared with `operations: []`, so API Platform emits the IRI but registers no readable endpoint. GraphQL is unaffected — `customerInvoice(s)` returns both as nested selectable objects.

- **Why not fixed:** the endpoint returns the expected output and the data is reachable elsewhere (the parent order's `items` block on `GET /api/shop/customer-orders/{id}`, plus the invoice PDF). A real fix means inlining the two relations the way `OrderDetail` does — a shape change to a shipped endpoint, for no functional gain.
- **Docs stance (user decision 2026-08-07):** the api-docs must **NOT** flag this. Earlier wording that called the paths "not served"/"answers 404" was removed. The invoice pages describe `items`/`addresses` neutrally and point at the order detail + PDF for the line items. A mistake is not something to advertise in consumer documentation.

### 2. `{id}` on profile update / delete is redundant

`PUT /api/shop/customer-profile-updates/{id}` and `POST /api/shop/customer-profile-deletes/{id}` both take an id in the path, but the **Bearer token is the only selector**:

- Delete — `CustomerProfileProcessor::handleDelete()` receives just the authenticated customer; the path id is **ignored entirely**.
- Update — the id is only a consistency check (`(int) $data->id !== (int) $authenticatedCustomer->id` → `AuthenticationException`); it never selects the record.

Not a security hole: another customer's id cannot be acted on (delete ignores it, update rejects it). It is an artifact of modelling these as API Platform *item* operations, which require an identifier in the URI. Removing the segment would break every existing integration's URLs, so both stay. Flagged here only — the api-docs document the routes as they are.

### 3. Pre-existing GDPR REST test failures (not a regression)

`tests/Feature/RestApi/GdprRequestTest.php` — `test_raise_when_disabled_returns_disabled_error` and `test_list_when_disabled_returns_disabled_error` fail in a full-suite run. `disableGdpr()` deletes the `core_config` row, but `core()->getConfigData()` has already memoised the enabled value earlier in the same process, so the guard does not trip. Verified pre-existing by stashing all local changes and re-running. Fix would be flushing the core config cache inside the helper.

---

## Testing

### Run Tests

**Always run with `--parallel --processes=8`.** Single-process runs are slow and discouraged — every command below should include both flags, even when scoped to one file.

```bash
# Single GraphQL file — regression check after a REST change
php artisan test packages/Webkul/BagistoApi/tests/Feature/GraphQL/WishlistTest.php --parallel --processes=8

# Single REST file — verify the new endpoint (new tests live under RestApi/)
php artisan test packages/Webkul/BagistoApi/tests/Feature/RestApi/WishlistTest.php --parallel --processes=8

# Multiple files — paratest accepts only ONE path argument, so run them sequentially.
# Loop them rather than space-separating, otherwise paratest errors with "Too many arguments".
for f in \
    packages/Webkul/BagistoApi/tests/Feature/GraphQL/ProductQueryTest.php \
    packages/Webkul/BagistoApi/tests/Feature/GraphQL/ProductSearchFilterTest.php \
    packages/Webkul/BagistoApi/tests/Feature/GraphQL/ProductTest.php \
    packages/Webkul/BagistoApi/tests/Feature/RestApi/ProductTest.php; do
  php artisan test "$f" --parallel --processes=8
done

# Full GraphQL suite — run when Processor/Provider shared logic changes
php artisan test --testsuite="BagistoApi GraphQL Test" --parallel --processes=8

# Full REST suite
php artisan test --testsuite="BagistoApi REST Test" --parallel --processes=8

# Everything
php artisan test packages/Webkul/BagistoApi/tests/Feature/ --parallel --processes=8
```

### Base Test Classes

| Class | Use for |
|-------|---------|
| `BagistoApiTestCase` | Shared base: storefront key, auth, DB seeding, FK management |
| `GraphQLTestCase` | GraphQL: `graphQL()`, `authenticatedGraphQL()` |
| `RestApiTestCase` | REST: `publicGet()`, `authenticatedGet()`, `authenticatedPost()`, `authenticatedDelete()` |

### REST Test Pattern

```php
class YourModelRestTest extends RestApiTestCase
{
    public function test_get_collection(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/your-models');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/shop/your-models');
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }
}
```

### GraphQL Test Pattern

```php
class YourModelTest extends GraphQLTestCase
{
    public function test_create_mutation(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product  = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation CreateYourModel($input: createYourModelInput!) {
              createYourModel(input: $input) {
                yourModel { id _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['productId' => $product->id],
        ]);

        $response->assertOk();
        expect($response->json('data.createYourModel.yourModel.id'))->not()->toBeNull();
    }
}
```

### Critical Test Infrastructure Rules

- **`$withoutMiddleware` property does nothing in Laravel** — use `$this->withoutMiddleware([...])` in `setUp()` (already fixed in `BagistoApiTestCase`)
- `VerifyStorefrontKey` has a `pk_test_*` bypass for the testing environment; `VerifyGraphQLStorefrontKey` does NOT — both must be disabled in `BagistoApiTestCase::setUp()`
- Always call `$this->seedRequiredData()` before creating customers/products
- Use `DatabaseTransactions` (already on base class) — safe with `--parallel` since each worker gets its own DB connection

### Common test pitfalls (added 2026-05-28)

- **Case-insensitive sort comparison.** The API sorts strings case-insensitively (DB collation `utf8mb4_unicode_ci`); PHP's default `sort()` is case-sensitive. When comparing API output to a sorted-PHP baseline, use `sort($expected, SORT_FLAG_CASE | SORT_STRING)`, otherwise `"apple"` and `"Banana"` flip relative order between the two sides.
- **Campaign-send fixture isolation.** Tests for `Marketing\Campaign::send` MUST create a fresh customer group via a `createFreshGroupId()` helper rather than reusing a seeded group. Subscriber rows from earlier test data leak into shared groups and break deterministic recipient-count assertions.
- **Stale paratest clone DBs.** Paratest spawns `api-demo-dump_test_1` … `_test_N` clones from the primary test DB once per process. After a fresh seed/migration the clones can fall out of sync (channels/currencies/locales tables come back null in worker 4 but not worker 1). Refresh manually with `for i in 1 2 3 4 5 6 7 8; do mysqldump api-demo-dump_test | mysql api-demo-dump_test_$i; done` (or equivalent loop). Catches a class of "passes in isolation, fails in parallel, no code change in between" reports.

### Known flaky tests

- `tests/Feature/Admin/RestApi/CatalogProductTest::update_special_price_with_dates_returns_200` — passes in isolation, intermittently fails under `--processes=8`. Suspected DB lock contention on the shared product attribute family (every test in the file mutates products on the same family row). Root cause not pinned; safe to re-run on failure. Logged 2026-05-28.

---

## File Reference Map

```
packages/Webkul/BagistoApi/src/
├── Models/              ← ApiResource definitions (REST + GraphQL ops)
├── Dto/                 ← Input DTOs for mutations
├── State/               ← Providers (read) and Processors (write/delete)
├── Exception/           ← AuthorizationException, InvalidInputException, etc.
├── Providers/
│   └── BagistoApiServiceProvider.php  ← Tag processors, register singletons
├── Resources/lang/en/
│   └── app.php          ← All error/success message strings
└── Http/Middleware/
    ├── VerifyStorefrontKey.php         ← REST key validation (has test bypass)
    └── VerifyGraphQLStorefrontKey.php  ← GraphQL key validation (no test bypass)
```

---

# Deprecation register — what to delete, and when

Two compatibility layers are carried on purpose. Each is removed on a stated trigger, and
every site is greppable so the removal needs no archaeology. Nothing here is permanent.

## A. Backward compatibility with Bagisto below 2.4.10

**Trigger: the minimum supported core becomes 2.4.10.** Added in 2.4.4 so one package
serves both sides of the theme rewrite. Find every site with:

```bash
grep -rn "BACKWARD COMPATIBILITY" packages/Webkul/BagistoApi/src packages/Webkul/BagistoApi/tests
```

### A1. Delete these files outright (19)

The pre-2.4.10 theme surface plus the two classes that pick a surface. All were added by
the compatibility work; none is referenced by the 2.4.10 code path.

| Group | Files |
|---|---|
| Legacy admin resources | `src/Admin/Models/AdminSettingsTheme.php`, `AdminSettingsThemeMassDelete.php`, `AdminSettingsThemeMassUpdateStatus.php`, `AdminSettingsThemeTranslationRef.php` |
| Legacy admin DTOs | `src/Admin/Dto/AdminSettingsTheme{Create,Update,MassDelete,MassUpdateStatus}Input.php`, `AdminSettingsThemeRestDto.php` |
| Legacy admin state | `src/Admin/State/AdminSettingsTheme{Collection,Item,Write}Provider.php`, `AdminSettingsTheme{,MassDelete,MassUpdateStatus}Processor.php` |
| Legacy storefront resources | `src/Models/ThemeCustomization.php`, `src/Models/ThemeCustomizationTranslation.php` |
| The gate itself | `src/Metadata/VersionGatedResourceNameCollectionFactory.php`, `src/Support/CoreCapabilities.php` |

### A2. Edit these files (unwrap the branch, keep the 2.4.10 arm)

| File | What to do |
|---|---|
| `src/Providers/ApiStateBindingsServiceProvider.php` | Drop the `if/else` around the theme tags, keep the 18 Appearance tags, delete the 6 legacy tags and their imports |
| `src/Providers/BagistoApiServiceProvider.php` | Delete the `VersionGatedResourceNameCollectionFactory` `extend()` block and the `CoreCapabilities` singleton; unwrap the `if` around the 5 Appearance resolver tags |
| `src/Admin/State/AdminCatalogProductCollectionProvider.php` | Keep the derived-column `addSelect`, delete the `else` arm with the correlated subqueries; the quantity sort becomes `product_flat.quantity` |
| `src/Admin/State/AdminAttributeFamilyProcessor.php` | Keep the `DEFAULT_CODE` guard, drop the last-family `elseif` and the `last-delete-error` lang key |
| `src/Admin/State/AdminAttributeProcessor.php` | Inline `new Regex` back into both rule arrays, delete `regexRules()` |
| `src/Admin/State/AdminMarketingTemplateProcessor.php`, `AdminMarketingEventProcessor.php` | Drop the capability check from the campaign in-use guards |
| `src/Admin/State/AdminCatalogProductImageProcessor.php`, `src/Admin/Models/AdminProductDetailImage.php` | Delete the early returns in `saveAltText()`, `altTextFor()` and `getAltTextAttribute()` |
| Each file above | Remove the now-unused `CoreCapabilities` constructor argument and import |

### A3. Tests

Delete the `core()` / `skipUnlessCoreSupports()` helpers from `tests/BagistoApiTest.php`,
then remove their callers: the whole-file skips in the 8 theme suites
(`{Admin/,}{GraphQL,RestApi}/Appearance*Test`, `GraphQL/{Sections,Theme}Test`,
`RestApi/{Section,Theme}Test`), the single-test skips in
`RestApi/ProductImageTest`, `Admin/RestApi/AttributeTest` and `Feature/SchemaExportTest`,
and the early return in `refreshFlatDerived()` in the 4 catalog product suites.

### A4. Finally

Bump the minimum core in `composer.json` in the same commit, so a store that still needs
the legacy path cannot install the release that dropped it. Update the **Bagisto
Compatibility** table in `README.md` to a single supported line.

## B. Symfony 7 cap in `composer.json`

**Trigger: api-platform ships a release that supports Symfony 8** (expected in the 5.x
line), and the package moves to it.

`require` pins `symfony/property-access`, `property-info`, `serializer`, `type-info`,
`validator` and `web-link` to `^7`. api-platform 4.3 allows `^8.0` on all six but still
references `Symfony\Component\PropertyInfo\Type` in 15 files, and Symfony 8 removed that
class. Composer resolves against the machine's PHP, so a **PHP 8.4** box pulls Symfony 8
for exactly those loosely-constrained components and every `artisan` command dies with
`Class "Symfony\Component\PropertyInfo\Type" not found`. Laravel 12 pins its own
components at `^7.2`, which is why the result is a mixed 7/8 set rather than a clean
failure.

Verified by resolving the package twice with `config.platform.php = 8.4.3`: without the
cap Composer locks Symfony 8.1.x, with it 7.4.x.

Diagnose a suspect install with:

```bash
grep -A2 '"name": "symfony/property-info"' composer.lock
```

8.x means broken, 7.4.x means good. Drop the six constraints when the trigger above is
met — do not drop them merely because an install looks fine on PHP 8.3, which is the
version where the bug is invisible.

## C. The `Webkul\GraphQL` autoload entry the installer used to write (removed 2026-09-11)

`InstallApiPlatformCommand::updateComposerAutoload()` used to add one hardcoded line to the
**host's** `composer.json` on every install:

```php
$composer['autoload']['psr-4']['Webkul\\GraphQL\\'] = 'packages/Webkul/GraphQL/src';
```

It is gone. The method now only maintains `extra.laravel.dont-discover` for
`api-platform/laravel`, which is load-bearing (the package registers API Platform itself, so
discovery on top registers it twice).

**Why it was removed — the entry mapped nothing.** Verified across four host projects:

- `packages/Webkul/GraphQL/` does not exist in any of them, so the mapping pointed at a
  missing directory and Composer wrote a dead path into `vendor/composer/autoload_psr4.php`.
- The namespace `Webkul\GraphQL\` is referenced in **zero** files under `packages/`.
- The entry appeared in exactly the three projects where this package had been installed and
  was absent from the one where it had not — so the installer was its only author. No Bagisto
  package claims that namespace; the official GraphQL extension is `Webkul\GraphQLAPI`.

Harmless at runtime (a PSR-4 miss is just "class not found", and nothing asked for it), but it
put an unexplainable entry in the merchant's own `composer.json`. That is what a tester
reported.

**If someone hits a missing-class error in that namespace, this is the line to restore** —
put it back immediately before the `dont-discover` block in `updateComposerAutoload()`, and
this time confirm first that a real `packages/Webkul/GraphQL/src` exists and that something
actually references the namespace.

**Existing stores keep the stale entry.** The installer no longer writes it and no longer
cleans it, so a store installed before this change still carries the line. Removing it is a
manual, safe two-step:

```bash
# delete the "Webkul\\GraphQL\\" line from the host composer.json autoload.psr-4 block, then
composer dump-autoload
```

A self-healing `unset()` was written and then dropped deliberately: the installer should not
be editing an autoload key it no longer owns, and silently deleting a mapping would break a
store that genuinely ships its own package under that namespace.
