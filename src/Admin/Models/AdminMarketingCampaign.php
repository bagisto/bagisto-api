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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignCreateInput;
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
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCampaign',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/campaigns',
            input: AdminMarketingCampaignCreateInput::class,
            processor: AdminMarketingCampaignProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Create a marketing campaign',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'subject', 'marketing_template_id', 'marketing_event_id', 'channel_id', 'customer_group_id'],
                                'properties' => [
                                    'name'                  => ['type' => 'string', 'example' => 'July Newsletter'],
                                    'subject'               => ['type' => 'string', 'example' => 'Big July deals inside!'],
                                    'marketing_template_id' => ['type' => 'integer', 'example' => 1],
                                    'marketing_event_id'    => ['type' => 'integer', 'example' => 1],
                                    'channel_id'            => ['type' => 'integer', 'example' => 1],
                                    'customer_group_id'     => ['type' => 'integer', 'example' => 1],
                                    'status'                => ['type' => 'integer', 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Campaign created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/campaigns/{id}',
            input: AdminMarketingCampaignUpdateInput::class,
            provider: AdminMarketingCampaignWriteProvider::class,
            processor: AdminMarketingCampaignProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Update a marketing campaign',
                responses: [
                    '200' => new Model\Response(description: 'Campaign updated.'),
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
                tags: ['Admin Marketing'],
                summary: 'Delete a marketing campaign',
                responses: [
                    '200' => new Model\Response(description: 'Campaign deleted.'),
                    '404' => new Model\Response(description: 'Campaign not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/campaigns/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingCampaignItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Campaign detail',
                responses: [
                    '200' => new Model\Response(description: 'Campaign with embedded template/event/channel/customer_group meta.'),
                    '404' => new Model\Response(description: 'Campaign not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/campaigns',
            provider: AdminMarketingCampaignCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
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
                    '200' => new Model\Response(description: 'Paginated list in the { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminMarketingCampaignCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'                  => ['type' => 'String'],
                'status'                => ['type' => 'Int'],
                'marketing_template_id' => ['type' => 'Int'],
                'marketing_event_id'    => ['type' => 'Int'],
                'channel_id'            => ['type' => 'Int'],
                'customer_group_id'     => ['type' => 'Int'],
                'sort'                  => ['type' => 'String'],
                'order'                 => ['type' => 'String'],
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
class AdminMarketingCampaign
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $subject = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?int $marketingTemplateId = null;

    #[ApiProperty(writable: false)]
    public ?int $marketingEventId = null;

    #[ApiProperty(writable: false)]
    public ?int $channelId = null;

    #[ApiProperty(writable: false)]
    public ?int $customerGroupId = null;

    #[ApiProperty(writable: false, description: 'Detail-only: template name (null on listing rows).')]
    public ?string $marketingTemplateName = null;

    #[ApiProperty(writable: false, description: 'Detail-only: event name.')]
    public ?string $marketingEventName = null;

    #[ApiProperty(writable: false, description: 'Detail-only: channel name.')]
    public ?string $channelName = null;

    #[ApiProperty(writable: false, description: 'Detail-only: customer group code.')]
    public ?string $customerGroupCode = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
