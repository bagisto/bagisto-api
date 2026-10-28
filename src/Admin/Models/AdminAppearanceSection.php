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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionItemProvider;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionProcessor;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionWriteProvider;
use Webkul\Theme\Models\Section;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSection',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/appearance/themes/{code}/sections',
            output: AdminAppearanceSectionRestDto::class,
            provider: AdminAppearanceSectionCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'List the sections of a theme',
                description: 'Sections of the given theme for one channel, in render order with footer links pinned last. Scope with `?channel=` and `?locale=`; both fall back the way the appearance editor does.',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code', true, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('channel', 'query', 'Channel ID (defaults to the current channel)', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('locale', 'query', 'Locale code the options are read in', false, schema: ['type' => 'string', 'example' => 'en']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Sections of the theme.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 3, 'name' => 'Categories Collections', 'type' => 'category_carousel',
                                            'themeCode' => 'default', 'channelId' => 1, 'sortOrder' => 3,
                                            'status' => 1, 'draftStatus' => null, 'draftSortOrder' => null,
                                            'hasDraft' => false, 'isPinned' => false,
                                            'createdAt' => '2024-04-16T21:44:15+05:30', 'updatedAt' => '2026-08-21T18:05:39+05:30',
                                            'translations' => [
                                                ['locale' => 'en', 'options' => ['filters' => ['sort' => 'asc', 'limit' => '10']], 'draftOptions' => null],
                                            ],
                                        ],
                                    ],
                                    'meta' => ['total' => 1],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Theme not installed.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/appearance/sections/{id}',
            output: AdminAppearanceSectionRestDto::class,
            provider: AdminAppearanceSectionItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Get a section',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The section.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 3, 'name' => 'Categories Collections', 'type' => 'category_carousel',
                                    'themeCode' => 'default', 'channelId' => 1, 'sortOrder' => 3,
                                    'status' => 1, 'draftStatus' => null, 'draftSortOrder' => null,
                                    'hasDraft' => false, 'isPinned' => false,
                                    'createdAt' => '2024-04-16T21:44:15+05:30', 'updatedAt' => '2026-08-21T18:05:39+05:30',
                                    'translations' => [
                                        ['locale' => 'en', 'options' => ['filters' => ['sort' => 'asc', 'limit' => '10']], 'draftOptions' => null],
                                    ],
                                    'message' => null,
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Section not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/appearance/themes/{code}/sections',
            input: AdminAppearanceSectionCreateInput::class,
            output: AdminAppearanceSectionRestDto::class,
            processor: AdminAppearanceSectionProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Create a section',
                description: 'Creates a section against the theme and channel the request is scoped to. It is created switched off with a pending status draft, exactly as the appearance editor does, so an empty section is never put in front of shoppers before it is built.',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code', true, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('channel', 'query', 'Channel ID (defaults to the current channel)', false, schema: ['type' => 'integer', 'example' => 1]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'type'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Summer Banner'],
                                    'type' => ['type' => 'string', 'enum' => Section::TYPES, 'example' => 'image_carousel'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Section created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12, 'name' => 'Summer Banner', 'type' => 'image_carousel',
                                    'themeCode' => 'default', 'channelId' => 1, 'sortOrder' => 4,
                                    'status' => 0, 'draftStatus' => true, 'draftSortOrder' => null,
                                    'hasDraft' => true, 'isPinned' => false,
                                    'createdAt' => '2026-08-21T18:05:39+05:30', 'updatedAt' => '2026-08-21T18:05:39+05:30',
                                    'translations' => [
                                        ['locale' => 'en', 'options' => ['images' => []], 'draftOptions' => null],
                                    ],
                                    'message' => 'Section created successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Invalid type, or the channel already has a footer links section.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/appearance/sections/{id}',
            input: AdminAppearanceSectionUpdateInput::class,
            output: AdminAppearanceSectionRestDto::class,
            provider: AdminAppearanceSectionWriteProvider::class,
            processor: AdminAppearanceSectionProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Update a section',
                description: 'Writes the published values of a section. Send `options` with `?locale=` to write that locale\'s published content; use the draft endpoint to stage edits instead.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('locale', 'query', 'Locale the options belong to', false, schema: ['type' => 'string', 'example' => 'en']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'type', 'sortOrder', 'channelId', 'themeCode'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Summer Banner'],
                                    'type' => ['type' => 'string', 'enum' => Section::TYPES, 'example' => 'image_carousel'],
                                    'sortOrder' => ['type' => 'integer', 'example' => 2],
                                    'channelId' => ['type' => 'integer', 'example' => 1],
                                    'themeCode' => ['type' => 'string', 'example' => 'default'],
                                    'status' => ['type' => 'boolean', 'example' => true],
                                    'options' => ['type' => 'object', 'example' => ['images' => []]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Section updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12, 'name' => 'Summer Banner', 'type' => 'image_carousel',
                                    'themeCode' => 'default', 'channelId' => 1, 'sortOrder' => 2,
                                    'status' => 1, 'draftStatus' => true, 'draftSortOrder' => null,
                                    'hasDraft' => false, 'isPinned' => false,
                                    'createdAt' => '2026-08-21T18:05:39+05:30', 'updatedAt' => '2026-08-21T18:05:39+05:30',
                                    'translations' => [
                                        ['locale' => 'en', 'options' => ['images' => []], 'draftOptions' => null],
                                    ],
                                    'message' => 'Section updated successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Section not found.'),
                    '422' => new Model\Response(description: 'Invalid payload, or a second footer links section.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/appearance/sections/{id}',
            provider: AdminAppearanceSectionWriteProvider::class,
            processor: AdminAppearanceSectionProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Delete a section',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '204' => new Model\Response(description: 'Section deleted.'),
                    '404' => new Model\Response(description: 'Section not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(provider: AdminAppearanceSectionItemProvider::class),
        new QueryCollection(
            provider: AdminAppearanceSectionCollectionProvider::class,
            paginationEnabled: false,
            extraArgs: [
                'code' => ['type' => 'String!'],
                'channel' => ['type' => 'Int'],
                'locale' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminAppearanceSectionCreateInput::class,
            output: AdminAppearanceSection::class,
            processor: AdminAppearanceSectionProcessor::class,
        ),
        new Mutation(
            name: 'update',
            input: AdminAppearanceSectionUpdateInput::class,
            output: AdminAppearanceSection::class,
            processor: AdminAppearanceSectionProcessor::class,
        ),
        new Mutation(
            name: 'delete',
            processor: AdminAppearanceSectionProcessor::class,
        ),
    ],
)]
class AdminAppearanceSection extends EloquentModel
{
    protected $table = 'theme_sections';

    protected $casts = [
        'id' => 'int',
        'name' => 'string',
        'type' => 'string',
        'theme_code' => 'string',
        'channel_id' => 'int',
        'sort_order' => 'int',
        'status' => 'int',
        'draft_status' => 'boolean',
        'draft_sort_order' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['has_draft', 'is_pinned', 'message'];

    public ?string $actionMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function translations(): HasMany
    {
        return $this->hasMany(AdminAppearanceSectionTranslationRef::class, 'section_id');
    }

    /**
     * Whether the section is holding edits the storefront is not showing yet.
     */
    #[ApiProperty(writable: false, schema: ['type' => 'integer', 'enum' => [0, 1]])]
    public function getHasDraftAttribute(): int
    {
        if (
            ! is_null($this->draft_status)
            || ! is_null($this->draft_sort_order)
        ) {
            return 1;
        }

        return $this->translations()
            ->whereNotNull('draft_options')
            ->exists() ? 1 : 0;
    }

    /**
     * Footer links are drawn at the bottom, so they cannot be reordered away from it.
     */
    #[ApiProperty(writable: false, schema: ['type' => 'integer', 'enum' => [0, 1]])]
    public function getIsPinnedAttribute(): int
    {
        return $this->type === Section::FOOTER_LINKS ? 1 : 0;
    }

    #[ApiProperty(writable: false)]
    public function getMessageAttribute(): ?string
    {
        return $this->actionMessage;
    }
}
