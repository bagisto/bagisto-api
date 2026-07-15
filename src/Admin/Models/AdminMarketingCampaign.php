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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignWriteProvider;

/**
 * Admin Marketing → Campaigns CRUD (Block F2c).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\CampaignController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/campaigns
 *   GET    /api/admin/marketing/campaigns/{id}
 *   POST   /api/admin/marketing/campaigns
 *   PUT    /api/admin/marketing/campaigns/{id}
 *   DELETE /api/admin/marketing/campaigns/{id}
 *
 * GraphQL:
 *   adminMarketingCampaigns         — cursor listing
 *   adminMarketingCampaign(id:)     — detail
 *   createAdminMarketingCampaign
 *   updateAdminMarketingCampaign
 *   deleteAdminMarketingCampaign
 *
 * The four FK scalars are exposed as to-one objects (GraphQL typed objects +
 * REST objects): channel { id _id code name }, customerGroup { id _id code name },
 * marketingTemplate { id _id name status }, marketingEvent { id _id name date }
 * (nullable). Each belongsTo consumes its FK column, replacing the old *Id /
 * *Name scalars. Create/update request inputs stay id-based.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCampaign',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/campaigns',
            input: AdminMarketingCampaignCreateInput::class,
            output: AdminMarketingCampaignRestDto::class,
            processor: AdminMarketingCampaignProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Create a marketing campaign',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'subject', 'marketing_template_id', 'marketing_event_id', 'channel_id', 'customer_group_id'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'July Newsletter'],
                                    'subject' => ['type' => 'string', 'example' => 'Big July deals inside!'],
                                    'marketing_template_id' => ['type' => 'integer', 'example' => 1],
                                    'marketing_event_id' => ['type' => 'integer', 'example' => 1],
                                    'channel_id' => ['type' => 'integer', 'example' => 1],
                                    'customer_group_id' => ['type' => 'integer', 'example' => 1],
                                    'status' => ['type' => 'integer', 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Campaign created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 5,
                                    'name' => 'Holiday Newsletter',
                                    'subject' => 'Big Holiday Sale Inside',
                                    'status' => 1,
                                    'channel' => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                    'customerGroup' => ['id' => 2, 'code' => 'general', 'name' => 'General'],
                                    'marketingTemplate' => ['id' => 16, 'name' => 'Holiday Template', 'status' => 'active'],
                                    'marketingEvent' => null,
                                    'createdAt' => '2026-05-26T16:51:08+05:30',
                                    'updatedAt' => '2026-05-26T16:51:28+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/campaigns/{id}',
            input: AdminMarketingCampaignUpdateInput::class,
            output: AdminMarketingCampaignRestDto::class,
            provider: AdminMarketingCampaignWriteProvider::class,
            processor: AdminMarketingCampaignProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Update a marketing campaign',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'July Newsletter'],
                                    'subject' => ['type' => 'string', 'example' => 'Big July deals inside!'],
                                    'marketing_template_id' => ['type' => 'integer', 'example' => 1],
                                    'marketing_event_id' => ['type' => 'integer', 'example' => 1],
                                    'channel_id' => ['type' => 'integer', 'example' => 1],
                                    'customer_group_id' => ['type' => 'integer', 'example' => 1],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Campaign updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 5,
                                    'name' => 'Holiday Newsletter',
                                    'subject' => 'Big Holiday Sale Inside',
                                    'status' => 1,
                                    'channel' => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                    'customerGroup' => ['id' => 2, 'code' => 'general', 'name' => 'General'],
                                    'marketingTemplate' => ['id' => 16, 'name' => 'Holiday Template', 'status' => 'active'],
                                    'marketingEvent' => null,
                                    'createdAt' => '2026-05-26T16:51:08+05:30',
                                    'updatedAt' => '2026-05-26T16:51:28+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Campaign not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/campaigns/{id}',
            provider: AdminMarketingCampaignWriteProvider::class,
            processor: AdminMarketingCampaignProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Delete a marketing campaign',
                responses: [
                    '200' => new Model\Response(
                        description: 'Campaign deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Campaign deleted.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Campaign not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/campaigns/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingCampaignItemProvider::class,
            output: AdminMarketingCampaignRestDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Campaign detail',
                responses: [
                    '200' => new Model\Response(
                        description: 'Campaign with embedded channel / customerGroup / marketingTemplate / marketingEvent objects.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 5,
                                    'name' => 'Holiday Newsletter',
                                    'subject' => 'Big Holiday Sale Inside',
                                    'status' => 1,
                                    'channel' => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                    'customerGroup' => ['id' => 2, 'code' => 'general', 'name' => 'General'],
                                    'marketingTemplate' => ['id' => 16, 'name' => 'Holiday Template', 'status' => 'active'],
                                    'marketingEvent' => ['id' => 3, 'name' => 'Holiday Sale', 'date' => '2026-12-25'],
                                    'createdAt' => '2026-05-26T16:51:08+05:30',
                                    'updatedAt' => '2026-05-26T16:51:28+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Campaign not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/campaigns',
            provider: AdminMarketingCampaignCollectionProvider::class,
            output: AdminMarketingCampaignRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'List marketing campaigns',
                description: 'Filters: name (LIKE), status (0/1), marketing_template_id, marketing_event_id, channel_id, customer_group_id. Sort: id (default desc), name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Enabled flag (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('marketing_template_id', 'query', 'Filter by template id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('marketing_event_id', 'query', 'Filter by event id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('channel_id', 'query', 'Filter by channel id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('customer_group_id', 'query', 'Filter by customer group id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list in the { data, meta } envelope. The channel / customerGroup / marketingTemplate / marketingEvent objects are detail-only and null on list rows.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 5,
                                            'name' => 'Holiday Newsletter',
                                            'subject' => 'Big Holiday Sale Inside',
                                            'status' => 1,
                                            'channel' => null,
                                            'customerGroup' => null,
                                            'marketingTemplate' => null,
                                            'marketingEvent' => null,
                                            'createdAt' => '2026-05-26T16:51:08+05:30',
                                            'updatedAt' => '2026-05-26T16:51:28+05:30',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 2,
                                        'from' => 1,
                                        'to' => 2,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminMarketingCampaignCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'marketing_template_id' => ['type' => 'Int'],
                'marketing_event_id' => ['type' => 'Int'],
                'channel_id' => ['type' => 'Int'],
                'customer_group_id' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin marketing campaigns listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingCampaignItemProvider::class,
            description: 'Admin marketing campaign detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingCampaignCreateInput::class,
            processor: AdminMarketingCampaignProcessor::class,
            description: 'Create a marketing campaign. Becomes createAdminMarketingCampaign.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingCampaignUpdateInput::class,
            processor: AdminMarketingCampaignProcessor::class,
            description: 'Update a marketing campaign. Becomes updateAdminMarketingCampaign.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingCampaignUpdateInput::class,
            processor: AdminMarketingCampaignProcessor::class,
            description: 'Delete a marketing campaign. Becomes deleteAdminMarketingCampaign.',
        ),
    ],
)]
class AdminMarketingCampaign extends EloquentModel
{
    /** @var string */
    protected $table = 'marketing_campaigns';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'status' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * `marketing_event` registers the REST-only name-match accessor (see
     * getMarketingEventAttribute) so the RestDto's marketingEvent object renders.
     *
     * @var array
     */
    protected $appends = ['marketing_event'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    /**
     * Channel this campaign targets (GraphQL to-one object —
     * `channel { id _id code name }`). The belongsTo on `channel_id` consumes the
     * FK column, replacing the old channelId / channelName scalars.
     */
    #[ApiProperty(writable: false)]
    public function channel(): BelongsTo
    {
        return $this->belongsTo(AdminMarketingChannelRef::class, 'channel_id');
    }

    /**
     * Customer group this campaign targets (`customerGroup { id _id code name }`).
     */
    #[ApiProperty(writable: false)]
    public function customer_group(): BelongsTo
    {
        return $this->belongsTo(AdminMarketingCustomerGroupRef::class, 'customer_group_id');
    }

    /**
     * Email template (`marketingTemplate { id _id name status }`).
     */
    #[ApiProperty(writable: false)]
    public function marketing_template(): BelongsTo
    {
        return $this->belongsTo(AdminMarketingTemplateRef::class, 'marketing_template_id');
    }

    /**
     * Marketing event — REST-only `marketingEvent` object `{id, name, date}` (or
     * null). It is NOT exposed as a GraphQL belongsTo: `marketing_event_id` is
     * nullable, and API Platform types a to-one relation field non-null, so a
     * null event 500s the GraphQL query (gotcha h, same as Product `booking`).
     *
     * This null `?string` accessor exists ONLY to satisfy the REST `output:`-DTO
     * name-match (the RestDto's `marketing_event` object value passes through for
     * REST); over GraphQL the field is a harmless null scalar — query the
     * detail via REST to read the event object.
     */
    #[ApiProperty(writable: false)]
    public function getMarketingEventAttribute(): ?string
    {
        return null;
    }
}
