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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleWriteProvider;

/**
 * Admin Marketing → Catalog Rules endpoints (Block F1a).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Promotions\CatalogRuleController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/catalog-rules
 *   GET    /api/admin/marketing/catalog-rules/{id}
 *   POST   /api/admin/marketing/catalog-rules
 *   PUT    /api/admin/marketing/catalog-rules/{id}
 *   DELETE /api/admin/marketing/catalog-rules/{id}
 *
 * GraphQL:
 *   adminMarketingCatalogRules           — cursor listing
 *   adminMarketingCatalogRule(id:)       — detail
 *   createAdminMarketingCatalogRule
 *   updateAdminMarketingCatalogRule
 *   deleteAdminMarketingCatalogRule
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCatalogRule',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/catalog-rules',
            input: AdminMarketingCatalogRuleCreateInput::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Create a catalog rule',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'channels', 'customer_groups', 'action_type', 'discount_amount'],
                                'properties' => [
                                    'name'            => ['type' => 'string', 'example' => 'Summer 10% off'],
                                    'description'     => ['type' => 'string', 'example' => 'Sitewide 10% off summer collection'],
                                    'starts_from'     => ['type' => 'string', 'example' => '2026-06-01'],
                                    'ends_till'       => ['type' => 'string', 'example' => '2026-08-31'],
                                    'status'          => ['type' => 'integer', 'example' => 1],
                                    'sort_order'      => ['type' => 'integer', 'example' => 0],
                                    'condition_type'  => ['type' => 'integer', 'example' => 1],
                                    'conditions'      => ['type' => 'array', 'items' => ['type' => 'object'], 'example' => []],
                                    'end_other_rules' => ['type' => 'integer', 'example' => 0],
                                    'action_type'     => ['type' => 'string', 'example' => 'by_percent'],
                                    'discount_amount' => ['type' => 'number', 'example' => 10],
                                    'channels'        => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'customer_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 2]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Catalog rule created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/catalog-rules/{id}',
            input: AdminMarketingCatalogRuleUpdateInput::class,
            provider: AdminMarketingCatalogRuleWriteProvider::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Update a catalog rule',
                description: 'Re-syncs channels + customer_groups pivots to the supplied lists.',
                responses: [
                    '200' => new Model\Response(description: 'Catalog rule updated.'),
                    '404' => new Model\Response(description: 'Catalog rule not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/catalog-rules/{id}',
            provider: AdminMarketingCatalogRuleWriteProvider::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Delete a catalog rule',
                responses: [
                    '200' => new Model\Response(description: 'Catalog rule deleted.'),
                    '404' => new Model\Response(description: 'Catalog rule not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/catalog-rules/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingCatalogRuleItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Catalog rule detail',
                responses: [
                    '200' => new Model\Response(description: 'Single catalog rule with channels + customer_groups + conditions.'),
                    '404' => new Model\Response(description: 'Catalog rule not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/catalog-rules',
            provider: AdminMarketingCatalogRuleCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'List catalog rules',
                description: 'Filters: name (LIKE), status. Sort: id (default desc), name, sort_order.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Enabled flag (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name', 'sort_order']]),
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
            provider: AdminMarketingCatalogRuleCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'   => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort'   => ['type' => 'String'],
                'order'  => ['type' => 'String'],
            ],
            description: 'Admin catalog rules listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingCatalogRuleItemProvider::class,
            description: 'Admin catalog rule detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingCatalogRuleCreateInput::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            description: 'Create a catalog rule. Becomes createAdminMarketingCatalogRule.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingCatalogRuleUpdateInput::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            description: 'Update a catalog rule. Becomes updateAdminMarketingCatalogRule.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingCatalogRuleUpdateInput::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            description: 'Delete a catalog rule. Becomes deleteAdminMarketingCatalogRule.',
        ),
    ],
)]
class AdminMarketingCatalogRule
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $startsFrom = null;

    #[ApiProperty(writable: false)]
    public ?string $endsTill = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;

    #[ApiProperty(writable: false)]
    public ?int $conditionType = null;

    /**
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $conditions = null;

    #[ApiProperty(writable: false)]
    public ?int $endOtherRules = null;

    #[ApiProperty(writable: false)]
    public ?string $actionType = null;

    #[ApiProperty(writable: false)]
    public ?float $discountAmount = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $customerGroups = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
