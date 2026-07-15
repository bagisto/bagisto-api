<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelWriteProvider;

/**
 * Admin Settings → Channels endpoints (Block B Wave 2; objectified 2026-06-22).
 *
 * Bare Eloquent `#[ApiResource]` parent. Nested data is field-selectable:
 *   GraphQL → `translations`/`locales`/`currencies`/`inventorySources` Relay
 *             connections + `homeSeo` typed object.
 *   REST    → the same data as flat arrays of objects (no connections).
 *
 * REST shape stays flat via `output: AdminSettingsChannelRestDto`; GraphQL ops
 * carry NO output so they return this Eloquent model → connections resolve.
 *
 * BREAKING (user-approved): the old scalar `localeIds`/`currencyIds`/
 * `inventorySourceIds` int arrays are REPLACED by the `locales`/`currencies`/
 * `inventorySources` object connections (GraphQL) / object arrays (REST).
 *
 * REST:
 *   GET    /api/admin/settings/channels            — datagrid-parity listing
 *   GET    /api/admin/settings/channels/{id}       — detail
 *   POST   /api/admin/settings/channels            — create
 *   PUT    /api/admin/settings/channels/{id}       — update (translatable via translations map)
 *   DELETE /api/admin/settings/channels/{id}       — delete (guards: last channel, app.channel)
 *
 * GraphQL:
 *   adminSettingsChannels / adminSettingsChannel(id:)
 *   createAdminSettingsChannel / updateAdminSettingsChannel / deleteAdminSettingsChannel
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ChannelController 1:1.
 * Image uploads (logo/favicon) deferred — accept storage paths only.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsChannel',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/channels',
            input: AdminSettingsChannelCreateInput::class,
            output: AdminSettingsChannelRestDto::class,
            processor: AdminSettingsChannelProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Create a new channel',
                description: 'Mirrors Bagisto admin Settings → Channels → Create. Validates code (unique, alpha-dash), hostname (unique), locales/currencies/inventory_sources (non-empty arrays), default_locale_id and base_currency_id (must appear in the respective arrays), root_category_id (must exist).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => [
                                'code' => 'us_store',
                                'name' => 'US Store',
                                'hostname' => 'us.example.com',
                                'theme' => 'default',
                                'timezone' => 'America/New_York',
                                'locales' => [1],
                                'default_locale_id' => 1,
                                'currencies' => [1],
                                'base_currency_id' => 1,
                                'inventory_sources' => [1],
                                'root_category_id' => 1,
                                'seo_title' => 'Welcome to the US store',
                                'seo_description' => 'Best products for the US market',
                                'seo_keywords' => 'shop, us, store',
                                'is_maintenance_on' => false,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Channel created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/channels/{id}',
            input: AdminSettingsChannelUpdateInput::class,
            output: AdminSettingsChannelRestDto::class,
            provider: AdminSettingsChannelWriteProvider::class,
            processor: AdminSettingsChannelProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Update a channel',
                description: 'Code/hostname uniqueness excludes the current id. Use the `translations` map for locale-nested attributes (name, description, seo_*, maintenance_mode_text). Top-level scalar fields broadcast to every configured locale via the repository.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => [
                                'code' => 'us_store',
                                'hostname' => 'us.example.com',
                                'locales' => [1],
                                'default_locale_id' => 1,
                                'currencies' => [1],
                                'base_currency_id' => 1,
                                'inventory_sources' => [1],
                                'root_category_id' => 1,
                                'translations' => [
                                    'en' => [
                                        'name' => 'US Store',
                                        'description' => 'Our US storefront',
                                        'seo_title' => 'Welcome',
                                        'seo_description' => 'Welcome to our shop',
                                        'seo_keywords' => 'shop, us',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Channel updated.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/channels/{id}',
            provider: AdminSettingsChannelWriteProvider::class,
            processor: AdminSettingsChannelProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Delete a channel',
                description: 'Refuses with HTTP 400 if this is the only remaining channel OR if its code matches the application-wide default channel (config("app.channel")).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Channel deleted.'),
                    '400' => new Model\Response(description: 'Cannot delete — last channel or default app channel.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/channels/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsChannelItemProvider::class,
            output: AdminSettingsChannelRestDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Channel detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single channel with translations, locales, currencies, inventory sources (as object arrays) and homeSeo.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/channels',
            provider: AdminSettingsChannelCollectionProvider::class,
            output: AdminSettingsChannelRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'List channels (datagrid parity)',
                description: 'Paginated, filterable, sortable channels list. Filters: code, name, hostname. Sort: id, code, name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('code', 'query', 'Partial code match.', false, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string', 'example' => 'US']),
                    new Model\Parameter('hostname', 'query', 'Partial hostname match.', false, schema: ['type' => 'string', 'example' => 'example.com']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated channels in the { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsChannelCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'code' => ['type' => 'String'],
                'name' => ['type' => 'String'],
                'hostname' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin channels listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsChannelItemProvider::class,
            description: 'Admin channel detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsChannelCreateInput::class,
            processor: AdminSettingsChannelProcessor::class,
            description: 'Create a new channel.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsChannelUpdateInput::class,
            processor: AdminSettingsChannelProcessor::class,
            description: 'Update a channel.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsChannelUpdateInput::class,
            processor: AdminSettingsChannelProcessor::class,
            description: 'Delete a channel. Refused for the last channel or the default app channel.',
        ),
    ],
)]
class AdminSettingsChannel extends EloquentModel
{
    /** @var string */
    protected $table = 'channels';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'default_locale_id' => 'int',
        'base_currency_id' => 'int',
        'root_category_id' => 'int',
        'is_maintenance_on' => 'boolean',
        'allowed_ips' => 'array',
        'home_seo' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array */
    protected $appends = [
        'message',
    ];

    /** Transient action message (e.g. delete confirmation); not a DB column. */
    public ?string $actionMessage = null;

    /** Default-locale code memo for the translatable string accessors. */
    private ?string $defaultLocaleCodeMemo = null;

    private bool $defaultLocaleCodeLoaded = false;

    private ?object $defaultTranslationMemo = null;

    private bool $defaultTranslationLoaded = false;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    /**
     * Per-locale translation rows (GraphQL connection — `translations { edges }`).
     * Standard FK `channel_id`, no pivot gotcha.
     */
    #[ApiProperty(writable: false)]
    public function translations(): HasMany
    {
        return $this->hasMany(AdminSettingsChannelTranslationRef::class, 'channel_id');
    }

    /**
     * Assigned locales (GraphQL connection — `locales { edges }`). belongsToMany
     * over `channel_locales` — pivot has no own id, so the node `_id` is the
     * locale's real id.
     */
    #[ApiProperty(writable: false)]
    public function locales(): BelongsToMany
    {
        return $this->belongsToMany(AdminSettingsChannelLocaleRef::class, 'channel_locales', 'channel_id', 'locale_id');
    }

    /**
     * Assigned currencies (GraphQL connection — `currencies { edges }`).
     */
    #[ApiProperty(writable: false)]
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(AdminSettingsChannelCurrencyRef::class, 'channel_currencies', 'channel_id', 'currency_id');
    }

    /**
     * Assigned inventory sources (GraphQL connection — `inventorySources { edges }`).
     * The relation METHOD is snake_case (`inventory_sources`) so the central
     * converter resolves it; the GraphQL field surfaces as `inventorySources`.
     */
    #[ApiProperty(writable: false)]
    public function inventory_sources(): BelongsToMany
    {
        return $this->belongsToMany(AdminSettingsChannelInventorySourceRef::class, 'channel_inventory_sources', 'channel_id', 'inventory_source_id');
    }

    /**
     * Home-SEO triplet, field-selectable as flat top-level STRING accessors
     * (`seoMetaTitle`/`seoMetaDescription`/`seoMetaKeywords`), read from the
     * channel's own `home_seo` JSON column.
     *
     * NOTE — a nested `homeSeo {}` typed object was NOT feasible: the only way to
     * back it on this resource is a HasOne onto the channels table, whose foreign
     * key would be `id`. API Platform rejects any column matching a relation's
     * foreign key, so a HasOne(... 'id', 'id') would DROP the parent's own `id`
     * attribute → empty link identifiers → every connection 500s. String
     * accessors are the field-selectable equivalent; the per-locale `homeSeo`
     * JSON is still selectable on the `translations` connection nodes.
     */
    #[ApiProperty(writable: false)]
    public function getSeoMetaTitleAttribute(): ?string
    {
        return $this->defaultHomeSeo()['meta_title'] ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getSeoMetaDescriptionAttribute(): ?string
    {
        return $this->defaultHomeSeo()['meta_description'] ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getSeoMetaKeywordsAttribute(): ?string
    {
        return $this->defaultHomeSeo()['meta_keywords'] ?? null;
    }

    private function defaultHomeSeo(): array
    {
        $seo = $this->home_seo;

        if (! is_array($seo) || $seo === []) {
            $seo = $this->defaultTranslation()?->home_seo;
        }

        if (is_array($seo)) {
            return $seo;
        }

        return is_string($seo) ? (array) json_decode($seo, true) : [];
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return $this->defaultTranslation()?->name;
    }

    #[ApiProperty(writable: false)]
    public function getDescriptionAttribute(): ?string
    {
        return $this->defaultTranslation()?->description;
    }

    #[ApiProperty(writable: false)]
    public function getMaintenanceModeTextAttribute(): ?string
    {
        return $this->defaultTranslation()?->maintenance_mode_text;
    }

    #[ApiProperty(writable: false)]
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    #[ApiProperty(writable: false)]
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? Storage::url($this->favicon) : null;
    }

    private function defaultLocaleCode(): ?string
    {
        if (! $this->defaultLocaleCodeLoaded) {
            $this->defaultLocaleCodeLoaded = true;
            $this->defaultLocaleCodeMemo = $this->default_locale_id
                ? DB::table('locales')->where('id', $this->default_locale_id)->value('code')
                : null;
        }

        return $this->defaultLocaleCodeMemo;
    }

    private function defaultTranslation(): ?object
    {
        if (! $this->defaultTranslationLoaded) {
            $this->defaultTranslationLoaded = true;

            $query = DB::table('channel_translations')->where('channel_id', $this->id);
            $localeCode = $this->defaultLocaleCode();
            if ($localeCode) {
                $row = (clone $query)->where('locale', $localeCode)->first();
                $this->defaultTranslationMemo = $row ?: $query->first();
            } else {
                $this->defaultTranslationMemo = $query->first();
            }
        }

        return $this->defaultTranslationMemo;
    }

    #[ApiProperty(writable: false, example: 'Channel deleted successfully.')]
    public function getMessageAttribute(): ?string
    {
        return $this->actionMessage;
    }
}
