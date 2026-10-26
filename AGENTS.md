# AGENTS.md — Instructions for bagisto-api

`bagisto/bagisto-api` is the API Platform package that gives Bagisto a REST and a GraphQL surface:
a storefront API (`/api/shop/*` and `POST /api/graphql`) and an admin API that mirrors the admin
panel menu-for-menu (`/api/admin/*` and `POST /api/admin/graphql`). It is a self-contained Laravel
package — PHP 8.4+, Laravel 13, API Platform 4.3 — that installs into a Bagisto 2.5 store. It runs
on MySQL 8.0, MariaDB 10.11 or PostgreSQL 16; all three are first-class and CI covers each.

This file is the single source of instructions for every coding agent. `CLAUDE.md` imports it.

## Skills — load the relevant one before writing code, not after

The same conventions also ship as skills in the `bagisto/agent-skills` repository. If your harness
has them installed (under `.claude/skills/` or `.agents/skills/`), read them directly — they are
plain markdown — before changing code rather than after. Where a skill and the surrounding code
genuinely disagree, match the surrounding code and say so in your summary.

| Working on | Load |
|---|---|
| Any PHP in this package | `bagisto-coding-standards` |
| An endpoint, resource, provider, processor, DTO or resolver | `bagisto-api-develop` |
| A storefront app or UI on the shop API | `bagisto-api-shop` |
| An admin app or UI on the admin API | `bagisto-api-admin` |
| Pest tests | `bagisto-pest-testing` |
| Reviewing a change | `bagisto-code-review` |

If your harness has no skill loader and the skills are not present, this file is self-contained.

## Do Not Edit

- `vendor/`, `node_modules/`, `composer.lock`
- `schema/generated/` — OpenAPI JSON and GraphQL SDL are generated; regenerate them with
  `php artisan bagisto-api-platform:export-schema`, never hand-edit
- `collections/` and `environments/` — the Postman files are generated; regenerate them with
  `php schema/tools/build-collection.php`
- `tests/e2e-pw/node_modules/`, `tests/e2e-pw/playwright-report/`, `tests/e2e-pw/test-results/`
- `.phpunit.cache`, `storage/` — runtime caches
- `src/Resources/assets/` — built assets; only edit alongside a frontend change

## Repository Map

```
├── src/
│   ├── Admin/                  # ★ the admin surface
│   │   ├── Models/             # #[ApiResource] response shapes (POPOs), plus the Eloquent auth models + proxies
│   │   ├── Dto/                # admin typed input DTOs
│   │   ├── State/              # admin providers + processors (+ Concerns/ base classes)
│   │   ├── Resolver/           # custom admin GraphQL resolvers
│   │   ├── Config/             # acl.php, system.php, menu.php, admin-auth.php, auth/guards.php
│   │   ├── Routes/             # the admin panel routes (API Tokens, API Sessions, API Audit)
│   │   ├── Resources/views/    # the admin panel Blade views for those screens
│   │   ├── Auth/               # AdminApiGuard + AdminApiAuthenticator (the admin-api guard)
│   │   ├── Audit/              # admin API audit recording
│   │   ├── Contracts/, Repositories/  # Eloquent contracts + repositories (sessions, tokens, audits)
│   │   ├── Services/           # token issuing, JWT signing (Services/Jwt), domain binding, 2FA
│   │   └── DataGrids/, Exports/, Helper/, Http/, Metadata/, Observers/, Security/
│   ├── Models/                 # storefront #[ApiResource] models (+ Filter/, Concerns/)
│   ├── Dto/                    # storefront typed input DTOs (CustomerOrder, ProductDetail)
│   ├── State/                  # storefront providers + processors (+ Concerns/)
│   ├── Resolver/               # custom GraphQL query resolvers
│   ├── Http/                   # Controllers, Middleware, Requests
│   ├── Serializer/             # normalizers + the snake→camel name converter
│   ├── GraphQl/                # scoped schema builders (shop + admin)
│   ├── Exception/              # the package's exception → status mapping
│   ├── Console/Commands/       # bagisto-api-platform:* and bagisto-api:* commands
│   ├── Database/Migrations/    # cart_tokens, storefront_keys, admin_api_{sessions,refresh_tokens,tokens,two_factor_challenges,audits}, customer_api_{sessions,refresh_tokens}
│   ├── Providers/              # BagistoApiServiceProvider + binding providers (+ Concerns/)
│   ├── Resources/lang/{locale}/app.php   # the single lang file, 22 locales
│   ├── Attributes/, CacheProfiles/, Contracts/, Facades/, Helper/, Input/, Jobs/,
│   │   Metadata/, OpenApi/, Repositories/, Routing/, Service/, Services/, Support/,
│   │   Traits/, Validators/
│   └── resources/views/        # GraphiQL / docs Blade views
├── config/                     # api-platform.php, api-platform-vendor.php, graphql-auth.php, storefront.php
├── collections/                # generated Postman collections (shop + admin)
├── environments/               # generated Postman environment
├── schema/
│   ├── generated/              # generated OpenAPI + GraphQL SDL + operation maps
│   └── tools/build-collection.php
├── tests/
│   ├── Feature/                # Pest: GraphQL/, RestApi/, Admin/{GraphQL,RestApi,Audit,Web}/
│   ├── Concerns/               # AdminFixtureFactory, CraftsAdminJwts
│   ├── Fakes/, Fixtures/jwt/   # fake DNS resolver, the test JWT key pair
│   ├── Unit/Admin/             # JwtSignerTest, DomainBindingTest
│   └── e2e-pw/                 # Playwright (tests/restAPI, tests/graphQL)
├── CHANGELOG.md
└── UPGRADE.md
```

## Architecture

### Package form

The package is one Composer package, `bagisto/bagisto-api`, namespace `Webkul\BagistoApi` → `src/`.
It installs into a Bagisto app one of two ways:

- **Composer (stable)** — `composer require bagisto/bagisto-api`, so the code lives at
  `vendor/bagisto/bagisto-api/`.
- **Path / manual (newest)** — the package sits at `packages/Webkul/BagistoApi/`, registered by the
  root `composer.json` path repository or by hand.

The service provider is auto-discovered either way via the package `composer.json`
`extra.laravel.providers` → `Webkul\BagistoApi\Providers\BagistoApiServiceProvider`. The provider's
`boot()` loads translations from `Resources/lang`, migrations from `Database/Migrations`, views,
and the admin panel routes, and registers the model substitution, the `admin-api` guard, the
rate limiter, the API documentation routes, the console commands and the hourly token prune.

Because the two layouts put the package at different paths, `config/api-platform.php` and
`config/api-platform-vendor.php` differ only in the `resources` paths, and the provider publishes
the matching one (`bagistoapi-config`).

### Two surfaces × two transports

| Surface | REST prefix | GraphQL endpoint | Auth |
|---------|-------------|------------------|------|
| **Storefront (shop)** | `/api/shop/*` | `POST /api/graphql` | `X-STOREFRONT-KEY` header (+ a customer or guest-cart Bearer token for cart/account calls) |
| **Admin** | `/api/admin/*` | `POST /api/admin/graphql` | `Authorization: Bearer <jwt>` — an access token from login, or an API token from *Settings → API Tokens* |

The shop and admin GraphQL endpoints are **physically separate** — separate middleware, auth and a
scoped schema (`QueryScopedSchemaBuilder` / `ScopedSchemaBuilder`). A change to one surface must not
leak types into the other. Storefront auth is Laravel Sanctum; admin auth is the custom
**`admin-api` guard** (`src/Admin/Auth/AdminApiGuard.php`), which accepts ES256 JWT access tokens
and API tokens.

### The 5-file pattern per resource

| Piece | Storefront path | Admin path | Responsibility |
|-------|-----------------|------------|----------------|
| **Model** | `src/Models/*.php` | `src/Admin/Models/Admin*.php` | The `#[ApiResource]` declaration — REST + GraphQL operations + OpenAPI. Admin models are POPOs: response-shape definitions, not Eloquent tables. |
| **DTO** | `src/Dto/*.php` | `src/Admin/Dto/*Input.php` | Typed input contract — the validation surface + GraphQL input type. |
| **Provider** | `src/State/*Provider.php` | `src/Admin/State/*Provider.php` | Read path — auth, query, envelope. |
| **Processor** | `src/State/*Processor.php` | `src/Admin/State/*Processor.php` | Write path — validation, repository call, events, permission gate. |
| **Resolver** | `src/Resolver/` | `src/Admin/Resolver/` | Custom GraphQL resolvers, only when the default item/collection resolver will not do. |
| **Lang** | `src/Resources/lang/en/app.php` | same file | All user-facing strings — **one file**, namespaced by top-level key (`app.admin.*`). Never create a second lang file. |

Storefront resources live under `src/`; admin resources under `src/Admin/`. Keep the two trees
separate.

### Registering state classes (mandatory)

Every Provider (`ProviderInterface`) and Processor (`ProcessorInterface`) must be tagged before it
resolves. The tagging lives in `src/Providers/ApiStateBindingsServiceProvider::register()` (a tagged
list), split across the provider concerns `RegisterStorefrontState`, `RegisterAdminProcessors`,
`RegisterGraphQlServices` and `RegisterCoreServices` used by `BagistoApiServiceProvider`. An
untagged Provider is silently bypassed — API Platform falls back to the default Eloquent provider
and you get a 404 or wrong data; an untagged Processor cannot be resolved for DI. New
`src/Admin/{Models,Dto}` directories must also be listed in the `resources` array of both
`config/api-platform.php` and `config/api-platform-vendor.php`.

### The two non-negotiables

1. **A REST change can break GraphQL.** They share the same Provider/Processor, routed by the `$data`
   type and the `$operation` instance. A new REST branch can shadow the GraphQL branch. **Always run
   the resource's GraphQL test before the REST test**, and confirm it is green before the change is
   done. This is the single most important rule.

2. **Parity with the Bagisto admin panel, not a superset.** Mirror what the admin UI (or storefront)
   can do, no more. Where core lacks a feature, block the gap with a clear error rather than
   half-extending, so the API does not own surface area that breaks when core catches up.

### Exceptions → HTTP status

| Exception | REST status | GraphQL | Use for |
|-----------|-------------|---------|---------|
| `AuthenticationException` | 401 | `errors[]` | Missing/invalid token |
| `AuthorizationException` | 403 | `errors[]` | Authenticated but not permitted / cross-user |
| `InvalidInputException` | 400 (or an explicit status, e.g. 409/422) | `errors[]` | Missing/invalid input, business-rule violation, wrong-step sequence |
| `ResourceNotFoundException` | 404 | `errors[]` | Entity not found |
| `ValidationException` | 422 | `errors[]` | Form-level validation failure |
| `OperationFailedException` | 500 | `errors[]` + `null` data | A GraphQL remove that must return `null` with a message |

### Response conventions

- GET collection → 200; GET single → 200; POST create → 201; DELETE → 204 (or 200 + body when
  returning data).
- **Null fields are always included** — every Eloquent-backed `#[ApiResource]` sets
  `normalizationContext: ['skip_null_values' => false]`.
- Pagination is `?per_page=N` (+ `?page=N`), default 10, cap 50. Admin collections wrap as
  `{ data, meta }` (via `AdminCollectionEnvelopeNormalizer`); both surfaces expose the `X-Total-*`
  headers.
- Output properties are **snake_case** with `use AcceptsCamelCaseWrites`; output still surfaces as
  camelCase through `OutputOnlySnakeToCamelNameConverter`. A multi-word field that skips this
  returns **null over GraphQL** — always add a GraphQL test that queries a multi-word field.

### Admin authentication

Two kinds of Bearer token reach the admin surface, over REST and GraphQL alike:

- **Access tokens** for apps that log in. `POST /api/admin/auth/login` (`createAdminLogin`) starts a
  session (`admin_api_sessions`) and returns a JWT access token (15 minutes by default) and an opaque
  refresh token (`admin_api_refresh_tokens`, 30 days). `POST /api/admin/auth/refresh` rotates both;
  a refresh token presented twice revokes the whole session. Admins with 2FA get a challenge first
  (`/auth/two-factor`). Logout, logout-all, own sessions, forgot and reset password and
  `PUT /api/admin/account` sit beside them under `src/Admin/Models/Admin{Login,TokenRefresh,…}.php`.
- **API tokens** for server integrations, created in the admin panel (*Settings → API Tokens*) or with
  `bagisto-api:api-token:create`, never through the API. Each belongs to one admin, is bound to one
  domain (`DomainBinding`: `dns` or `origin` mode) and may have no expiry. The API only lists, reads
  and revokes them.

`AdminApiGuard` verifies the JWT (`Services/Jwt/JwtVerifier`, ES256 only, known `kid`), then
`AdminApiAuthenticator` loads the session by `sid` or the API token by `jti` and rejects a revoked,
expired or inactive one. Nothing about permissions is in a token. `EnforceAdminApiAuth` keeps a
public allowlist: the login, two-factor, refresh, forgot and reset paths and GraphQL documents made
only of those five mutations. Errors carry a code in the REST `error` field and the GraphQL
`extensions.code` (`access_token_expired`, `refresh_token_invalid`, `api_token_revoked`, …), thrown as
`Exception/AdminAuthException`. Configuration lives in `Admin/Config/admin-auth.php`, overridden by
*Configuration → API → Admin Authentication* (read through `AdminApiAuthSettings`).

The package also ships its admin panel screens under `src/Admin/`: **Settings → API Tokens**,
**Settings → API Sessions** and **Settings → API Audit**. It merges its ACL (`Admin/Config/acl.php`),
system settings (`Admin/Config/system.php`) and menu (`Admin/Config/menu.php`) into core's. Admin
token requests carry no session admin, so resolve permissions from the resolved admin's role via the
`ChecksAdminPermission` trait — **never call `bouncer()`**, which reads the session admin a token
request does not have.

## Commands

### Install and configure

```bash
composer require bagisto/bagisto-api     # or install at packages/Webkul/BagistoApi
php artisan bagisto-api-platform:install # publish config/assets, migrate, create storefront key, cache
php artisan bagisto-api:generate-key     # issue a storefront key (X-STOREFRONT-KEY)
php artisan bagisto-api:key:manage       # rotate / expire / manage storefront keys
php artisan bagisto-api:key:maintain     # maintenance pass over the keys
```

Admin API credentials (see [Admin authentication](#admin-authentication)):

```bash
php artisan bagisto-api:jwt-keys [--rotate] [--reissue-api-tokens]   # the ES256 signing keys (the installer runs it)
php artisan bagisto-api:api-token:create --admin=<id|email> --name= --domain= [--expires=YYYY-MM-DD|never] [--only-token]
php artisan bagisto-api:prune-admin-tokens                           # scheduled hourly
```

Apps log in with an admin's email and password. Server integrations use an API token from
**Settings → API Tokens** (or the command above); the token is shown once. On several servers set
`BAGISTO_API_JWT_PRIVATE_KEY` / `BAGISTO_API_JWT_PUBLIC_KEY` so every node signs with the same key.

### Cache cycle

```bash
php artisan bagisto-api-platform:optimize      # clear → config:cache → route:cache → warm metadata
php artisan bagisto-api-platform:clear-cache   # clear the API Platform caches
php artisan bagisto-api-platform:warm-cache    # pre-warm the metadata cache
php artisan optimize:clear                     # clear all Laravel caches
```

The route cache is the fast steady state, but it **hides a new endpoint** until it is rebuilt. After
editing a resource: `php artisan optimize:clear && php artisan bagisto-api-platform:clear-cache`,
live-test the endpoint (the first GraphQL run may be stale — re-run if so), then finish with
`php artisan bagisto-api-platform:optimize`. `php artisan test` itself clears the route and config
cache, so re-optimise after a test session. **Never leave the working copy with caches cleared.**

### Schema and collections

```bash
php artisan bagisto-api-platform:export-schema   # writes schema/generated/{openapi,shop,admin,…}
php schema/tools/build-collection.php            # rebuild the Postman collections from the schema
```

`export-schema` scopes each spec to its own surface and refuses to write one that leaks the other.
The `Validate` workflow rebuilds the collections and fails a push whose committed copies no longer
match, so regenerate and commit both after any API change.

### Maintenance

```bash
php artisan bagisto-api:prune-audits         # prune the admin API audit log
php artisan bagisto-api:prune-cart-uploads   # scheduled every two hours
```

## Testing

Tests use **Pest**. Feature tests live under `tests/Feature/`, one `<Resource>Test.php` per resource
per transport:

```
tests/Feature/
├── GraphQL/                 shop GraphQL tests
├── RestApi/                 shop REST tests
└── Admin/
    ├── GraphQL/             admin GraphQL tests
    ├── RestApi/             admin REST tests
    ├── Audit/               admin API audit-trail tests
    └── Web/                 admin-panel (Blade) tests
```

Run from the package repository root:

```bash
vendor/bin/pest --parallel --processes=4 tests/Feature/GraphQL
vendor/bin/pest --parallel --processes=4 tests/Feature/RestApi
vendor/bin/pest --parallel --processes=4 tests/Feature/Admin/GraphQL
vendor/bin/pest --parallel --processes=4 tests/Feature/Admin/RestApi
vendor/bin/pest --parallel --processes=4 tests/Feature/Admin/Audit
vendor/bin/pest --parallel --processes=4 tests/Feature/Admin/Web
vendor/bin/pest --parallel --processes=4 tests/Unit
vendor/bin/pest tests/Feature/SchemaExportTest.php
vendor/bin/pest --filter="test name"
```

When the package is installed inside a Bagisto app, prefix the paths (`vendor/bagisto/bagisto-api/tests/…`)
and run from the app root; CI does exactly this.

- **The golden rule first:** after any REST change, run the affected resource's **GraphQL test
  before the REST test** — REST and GraphQL share the Provider/Processor, so a REST change can
  silently break GraphQL.
- **Always run `--parallel`.** Match `--processes` to CPU cores; oversubscribing slows it down.
  Disable xdebug for a big speedup: `XDEBUG_MODE=off`.
- Paratest accepts **one path argument** — loop files sequentially rather than space-separating them.
- Base classes live in `tests/` (key/auth/seed), including `AdminFixtureFactory` for admin fixtures.
- Admin tests authenticate through `AdminApiTestCase`: `adminHeaders($admin)` sends a real session
  access token, `adminAccessToken()`, `adminRefreshToken()`, `adminTokenPair()`, `adminApiToken()`
  and `expireAccessToken()` cover the rest. JWTs are signed with the key pair in
  `tests/Fixtures/jwt/` (test-only, never used by a store), and DNS lookups for domain binding go
  through the `FakeDomainResolver` in `$this->domainResolver` (`localhost` → `127.0.0.1`, `::1`).
- Don't run unprompted full regression sweeps — run the targeted tests for what changed.

## End-to-End Tests (Playwright)

The Playwright suite lives in `tests/e2e-pw/` (`playwright.config.ts`; specs under `tests/restAPI/`
and `tests/graphQL/`). Admin auth logs in once in `globalSetup` with `ADMIN_EMAIL` and `ADMIN_PASSWORD`
and caches the pair in `.admin-session.json`; the REST and GraphQL admin clients refresh on
`access_token_expired` and log in again on `refresh_token_invalid`. `ADMIN_API_TOKEN` (domain
`localhost`), `ADMIN_API_TOKEN_OTHER_DOMAIN` (domain `example.com`) and
`ADMIN_API_TOKEN_REVOCABLE_{REST,GRAPHQL}` are API tokens CI creates with
`bagisto-api:api-token:create`. See `tests/e2e-pw/TEST_SUITE_OVERVIEW.md`.

```bash
cd tests/e2e-pw
npm install
npx playwright install --with-deps chromium
npx playwright test tests --shard=1/10 --reporter=list,html
```

## Coding Standards

The canonical rules for all code in this package, adapted from the Bagisto **coding-standards**
skill so a change to an endpoint is held to the same bar as a change to core. **Pint decides the
mechanical questions** — run `vendor/bin/pint` and trust it for spacing, import order, trailing
commas and the rest. What follows is what Pint cannot see, and what a reviewer will otherwise send
back.

The skill's reference files carry the detail; load the matching one before writing that kind of code:

| Reference (in the skill) | Load when |
|--------------------------|-----------|
| `code-style.md` | writing any PHP — multi-clause conditions, class member order |
| `comments.md` | any comment or docblock, in any language |
| `laravel.md` | framework idiom — events, migrations, config, helpers, validation, queues |
| `data-access.md` | reading or writing the database — the repository rule and its one exception |
| `database-compatibility.md` | any query, migration or raw SQL |
| `security.md` | output, input, uploads, SQL, secrets, payments |
| `security-authorization.md` | routes, permissions, guards, one user's access to another's data |
| `localization.md` | any user-facing string, or a `Resources/lang/` change |

**Where the code and these rules disagree, the checkout wins.** Follow the surrounding code, say so
in your summary, and raise the drift. That is different from a file that is simply wrong: a missing
docblock in an untouched class is debt, not a convention — match the *rule*, not the worst example
of it.

### The rules in one screen

- **Every method and property carries a docblock**, whatever its visibility, and it is a capitalised
  sentence ending in a full stop of **at most two lines**. Type information belongs in the signature;
  add `@param` / `@return` only for what a native type cannot express.
- **No docblock above the class**, ever — the docblock belongs on the method, property or constant,
  never on the class, interface, trait or enum.
- **Class members run constants → properties → constructor → abstract declarations → public →
  protected → private**, each visibility one contiguous block.
- **A condition with more than one clause goes multiline**, the boolean operator leading each line.
  Single-clause conditions stay inline. The rule keys off the number of clauses, not line length.
- **No comments inside method bodies**, object literals or markup — not even a one-line "why". A
  non-obvious reason belongs in the method's docblock or the commit message; if a line needs prose
  to be understood, extract a named method instead.
- **All database access goes through a repository.** No `DB::` and no model queries in a state class,
  listener, job or service. The single exception is a DataGrid's `prepareQueryBuilder()`.
- **Every query has to run on MySQL, MariaDB and PostgreSQL.** Reach for `db_grammar()` rather than a
  literal `'like'` or a hand-written SQL function, and never resolve a conflict by catching a
  constraint violation — a failed statement aborts the whole PostgreSQL transaction.
- **Events are dot-delimited strings**, not classes — `catalog.product.update.after` — and fire in
  `before`/`after` pairs.
- **Authorize on the server and escape at the point of interpolation.** Hiding a control is
  presentation; the route, the provider and the processor decide what is allowed.
- **Scope every storefront query to its owner.** An id from the request never selects a row on its
  own — that is the easiest real vulnerability to introduce here.
- **Every user-facing string goes through `trans()` / `__()`**, with the key added to all 22 locales.
- **Translation keys are kebab-case, route names are snake_case** — never rename one by searching for
  the other's spelling. A URL path is governed by neither.
- **Fix what you touch.** A pre-existing violation in a file you edit is yours — scan the whole
  class's member order and docblocks, not just your own lines.

### Multi-clause conditions go multiline

A condition with **more than one clause** (two or more operands joined by `&&` / `||`) is broken
across lines: the `(` opens alone, each clause sits on its own line at one indent, the boolean
operator **leads** the line it joins, and `) {` closes back at the statement's indent.

```php
// Good — each clause is one scannable line, and the operator is the first thing you read.
if (
    $customer->is_approved
    && ! $order->is_canceled
) {
    return $order->items;
}

// Bad — clauses run together, and the operators hide at the end of the line.
if ($customer->is_approved && ! $order->is_canceled) {
    return $order->items;
}
```

A **single-clause** condition stays inline — wrapping it adds noise and hides nothing. The rule keys
off the number of clauses, not line length: two short clauses still go multiline. It applies equally
to `elseif`, `while`, and a `return` of a compound boolean.

### Order members by visibility

Lay a class out so a new member lands where a reader expects it: constants → properties → the
constructor → abstract method declarations → public methods → protected methods → private methods.
Each visibility forms one contiguous block, and it cuts both ways — a `protected` helper never sits
above or between the public methods, and a `public` method is never buried among the protected ones.
When you add a helper an existing public method calls, put it in the protected block at the bottom
rather than right after its caller. **Check the whole file, not just your own lines:** whenever you
edit a class, fix any member already out of place, and leave no property without a docblock.

### Comments and docblocks

Inside a method body, an array literal or markup the default is **no comment** — not even a one-line
"why". The comment this codebase wants is the docblock above a method, a property or a constant, and
**not above the class**. A genuinely non-obvious reason belongs in that docblock or in the commit
message; if a line needs prose to be followed, extract a well-named method instead. A comment inside
a body is *earned* only when it encodes a constraint that is invisible locally — the rest of this
section says how, and what form it takes in each layer.

```php
// Bad — explains a statement inside the body
public function provide(Operation $operation): array
{
    // Re-fetch the cart because collectTotals swapped the instance
    $cart = Cart::getCart();
}

// Good — docblock only, body speaks for itself
/**
 * Provide the bearer customer's cart.
 */
public function provide(Operation $operation): array
{
    $cart = Cart::getCart();
}
```

A docblock is a **label, not an explanation** — a capitalised sentence ending in a full stop, one
line the norm and two the hard ceiling. The same ceiling applies to a property, a constant, a Blade
`@php` closure and a Vue `method` / `computed`. Every property and constant gets its **own** docblock,
even when several sit together; keep `@var` only on an untyped property, and skip documenting
constructor-promoted properties, which the constructor's docblock already covers.

A comment *inside* a body is warranted only when a reader who understands the code would still act
wrongly without it — a constraint that is invisible locally (a load-bearing but removable-looking
line, a non-obvious ordering, a workaround for core or Laravel). State the constraint, not the war
story, in two or three lines. Never narrate code that already reads clearly.

When a comment is warranted, the syntax belongs to its layer: PHP and `@php` blocks use PHP comments;
Blade uses `{{-- --}}` (a sentence, or a Title-Cased label); markup uses a short `<!-- -->` label; and
JavaScript and CSS use `/** … */` blocks only — **no `//` line comments** in the Vue/CSS layer.

### Data access

Every read and write goes through a repository, the same one the core admin controller uses —
reaching for `DB::table(...)` or the model's query builder from a provider, processor, listener, job
or service bypasses the layer the codebase is built on and makes the operation impossible to reuse or
override. If the repository has no method for what you need, **add one**; that is the extension
point. The single sanctioned exception is a DataGrid's `prepareQueryBuilder()`, which is built on the
query builder by design.

Where a model query is allowed — a repository, a factory, a console command, a job, a DataGrid or a
test — begin it with `Model::query()` rather than forwarding statically (`Order::query()->…`), so the
builder is explicit and resolves for static analysis.

**Scope every storefront query to its owner.** A repository method that touches customer-owned data
filters on `customer_id` (or the resolved cart token), and an id from the request never selects a row
on its own:

```php
/**
 * One of a customer's orders, or null when it is not theirs.
 */
public function findForCustomer(int $customerId, int $orderId): ?Order
{
    return $this->model
        ->where('customer_id', $customerId)
        ->where('id', $orderId)
        ->first();
}
```

### Laravel idiom

Bagisto tracks recent Laravel, but in several places its own convention differs from the framework
default, and following the framework instead is the most common way generated code looks wrong.

- **Events are dot-delimited strings**, not classes — `<domain>.<entity>.<action>.<before|after>`,
  lowercase and dotted — and fire in `before`/`after` pairs. `before` receives the input, `after` the
  resulting model. Do not create an event class; fire the same events core fires so core listeners,
  indexers and the full-page cache keep working.
- **Migrations** are anonymous classes (`return new class extends Migration`), live in
  `src/Database/Migrations/`, and **write a `down()`** — a migration that cannot be rolled back
  blocks everyone.
- **`env()` is only called inside `config/`.** Anywhere else it returns null once the config is
  cached, which is the state of any production install. Admin-configurable settings are read through
  `core()->getConfigData(...)`.
- **Use the global helpers** rather than resolving from the container by hand: `core()`, `bouncer()`,
  `acl()`, `datagrid()`, `image_manager()`.
- **Validate in a FormRequest**, so the rules are one object a reviewer can read. A mass action
  validates too, since its `indices` array arrives from the request.

### Security

Load this when the change touches authorization, rendered output, input, uploads, raw SQL, secrets or
payments.

- **Never weaken an existing defence to make a feature work.** Adding a route to an unrestricted
  list, a path to a CSRF exclusion, or `svg` to a `mimes:` allowlist each needs a stated reason.
- **Authorize on the server, every time.** Hiding a button or omitting a menu entry changes what is
  shown, not what is allowed.
- **Scope every query to its owner** — storefront reads of orders, addresses, downloads and reviews
  filter on the authenticated customer, never on an id from the request alone.
- **Escape at the point of interpolation.** The same value is safe in element text and dangerous
  inside an attribute; escape anything a Blade view or a grid closure interpolates.
- **Every upload needs an explicit `mimes:` allowlist and a `max:`** — do not admit an active type
  (`html`, `svg`, `php`), prefer a generated name, and remember a file name echoed back is
  user-controlled text.
- **Raw SQL**: bindings for values, an allowlist for identifiers (column and direction cannot be
  bound — a DataGrid sort arrives from the request).
- **Never log or return a secret** — storefront keys, JWTs, refresh tokens, API tokens, signing keys,
  card data, password hashes.
  A token must not be selected into a resource.
- **Treat a webhook or redirect callback as anonymous and hostile** until its signature verifies;
  confirm amounts and status against the gateway or the stored order, and make it idempotent (the
  Stripe checkout confirm returns an existing order rather than placing a second).

### Authorization

Admin token requests carry no session admin, so `bouncer()->hasPermission()` always returns false.
Resolve permissions from the resolved admin's role via the `ChecksAdminPermission` trait —
**never call `bouncer()`** in this package. A role `permission_type` of `all` passes everything;
`custom` checks the permission in the role's `permissions`. The role is read on every request and no
permission is carried in a token, so a role change applies on the next request for sessions and API
tokens alike. Creating an API token for another admin needs `settings.api_tokens.manage_others` and a
role whose permissions cover the target admin's (`AdminApiTokenIssuer::canCreateFor`).

Storefront requests have no roles — authorization is **ownership**, enforced by scoping the query to
the authenticated customer or the bearer cart token (see [Data access](#data-access)). This applies to
orders, invoices, shipments, addresses, downloads, reviews, RMA, EU withdrawal and the cart alike.
Mass actions take an array of ids from the request, so each needs the same ownership scope and the
same permission as the single-record path.

### Code style (Pint)

```bash
vendor/bin/pint          # fix
vendor/bin/pint --test   # check only (CI uses this)
```

Pint uses the `laravel` preset (`pint.json`). The PHP rules above apply inside an `@php … @endphp`
block in a Blade file too, which Pint cannot reach. **Always run Pint on the files you change before
calling the work complete.**

## Localization

Every user-facing string goes through `trans()` / `__()` and lives in the single file
`src/Resources/lang/{locale}/app.php`, namespaced by top-level key (`app.admin.*`, `app.graphql.*`).
Never create a second lang file. A mis-indented duplicate key is silently kept at the wrong path and
leaks the raw key, so mind indentation.

**Every segment of a translation key is kebab-case** (`app.admin.return-requests.title`, never
`return_requests` or `returnRequests`), at every level of the nested array. Two exceptions are data
rather than names: currency codes and locale codes (`pt_BR`) keep their own casing. Do not confuse
this with **route names, which are snake_case** (`admin.sales.eu_withdrawals.index`) — never rename
one by searching for the other's spelling.

When adding a key, provide it for **all 22 locales**: ar, bn, ca, de, en, es, fa, fr, he, hi_IN, id,
it, ja, nl, pl, pt_BR, ro, ru, sin, tr, uk, zh_CN.

## CI Workflows (.github/workflows/)

| Workflow | Trigger | What it does |
|----------|---------|--------------|
| `pest_tests.yml` | push, pull request | Installs Bagisto v2.5.0, wires the package via a path repository, runs the Pest suites (GraphQL, RestApi, Admin GraphQL/RestApi/Audit/Web, Unit, schema export) in parallel on PHP 8.4 × MySQL 8.0 |
| `pint_tests.yml` | push, pull request | Runs `pint --test` |
| `validate.yml` | push, pull request, manual | Rebuilds the Postman collections from the schema and fails if the committed copies differ |
| `push-to-postman.yml` | push to `main`, manual | Publishes the collections to the Postman workspace when they change |
| `sync-postman.yml` | release published, manual | Pulls edits made in Postman back into the repository |
| `bagisto-api-playwright.yml` | push, pull request | Runs the Playwright suites sharded across jobs |

The Postman jobs skip with a notice while their repository secrets are missing rather than failing
the build.

## Database Compatibility

All code must work on MySQL 8.0, MariaDB 10.11 and PostgreSQL 16. Use the existing abstractions:

- **Case-insensitive LIKE**: use `db_grammar()->caseInsensitiveLike()` instead of a hardcoded
  `'like'` (it returns `LIKE` on MySQL and `ILIKE` on PostgreSQL); `db_grammar()->caseSensitiveLike()`
  for exact case.
- **Empty strings → NULL/default**: MySQL coerces `""` to `0`/`NULL`; PostgreSQL rejects it. Use
  model set mutators paired with `$casts`, never sanitize in the state class.
- **Boolean columns**: add `$casts` with `'boolean'`, and handle the write side with a mutator.
- **DB-specific SQL**: use the `db_grammar()` helpers (`concat`, `groupConcat`, `findInSet`,
  `dateFormat`, `jsonExtractText`, `jsonExtractNumeric`, …) rather than raw SQL. The abstraction is
  `Webkul\Core\Contracts\DatabaseGrammar`, implemented by `MySqlGrammar` and `PgSqlGrammar` under
  `packages/Webkul/Core/src/Helpers/Database/Grammar/` — read the contract before hand-rolling SQL.
- **CASE expression types**: both branches must return the same type; use `CAST(id AS VARCHAR(255))`
  (`CAST(... AS CHAR)` truncates to one character on PostgreSQL).
- **GROUP BY**: PostgreSQL requires every non-aggregated SELECT column in the GROUP BY.
- **`DB::raw()` in `updateOrCreate()`** fails on INSERT; split into find + update/create.
- **A failed statement aborts the whole PostgreSQL transaction.** MySQL lets a transaction continue
  after a duplicate-key error, so `try { insert } catch (UniqueConstraintViolationException) { … }`
  looks like it works; on PostgreSQL every later statement fails with `25P02 current transaction is
  aborted`. Resolve the conflict in one statement the database settles itself (`upsert()`, which
  compiles to `ON CONFLICT … DO UPDATE` / `ON DUPLICATE KEY UPDATE`) rather than catching the
  violation, and qualify any column the update clause reads (PostgreSQL rejects a bare column as
  ambiguous).

Every list, search, sort and export must behave identically on all three engines.

## Safety Rails

- **Install and remove only when explicitly asked.** Detecting the package does not mean you should
  install, reinstall, optimise or uninstall anything.
- **Never leave the working copy with caches cleared** — finish with `bagisto-api-platform:optimize`.
- **Tag every Provider/Processor**; an untagged one is silently bypassed.
- **A REST change can break GraphQL** — run the GraphQL test first.
- **Never weaken a defence to make a feature work** — a new unrestricted route, CSRF exclusion or
  `mimes:` entry needs a stated reason.
- **Fix what you touch.** A pre-existing coding-standards violation in a file you edit is yours.
- **Translations are 22 files per key.** Missing a locale fails review.
- **Tests must pass.** Run the affected package tests after changes; do not delete tests without
  approval.
- **Do not add or remove Composer dependencies without approval.**
- **Do not create documentation files unless explicitly requested.**

## Validation Checklist (Before Marking Complete)

1. `vendor/bin/pint --test` — no style violations
2. The resource's **GraphQL test first**, then its REST test — both green
3. `php artisan bagisto-api-platform:optimize` — caches restored (never leave them cleared)
4. Regenerate `schema/generated/` + the Postman collections if an endpoint changed, and commit both
5. Translation keys exist in all 22 locales
6. No `env()` calls outside `config/` files
7. The coding standards hold for every file touched — a docblock on every method, property and
   constant (and none above the class), members ordered by visibility, multi-clause conditions split
   across lines, database access through a repository, output fields snake_case +
   `AcceptsCamelCaseWrites`
8. A `CHANGELOG.md` entry under `[Unreleased]` for any user-facing change

## Further Reading

- API documentation: [api-docs.bagisto.com](https://api-docs.bagisto.com) (+ `/llms.txt` index)
- API demo: [api-demo.bagisto.com/api](https://api-demo.bagisto.com/api)
- Repository: [github.com/bagisto/bagisto-api](https://github.com/bagisto/bagisto-api)
- Release history: [`CHANGELOG.md`](CHANGELOG.md)
- Upgrading from the 2.4 line: [`UPGRADE.md`](UPGRADE.md)
