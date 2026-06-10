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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingTemplateCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingTemplateUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateWriteProvider;

/**
 * Admin Marketing → Email Templates endpoints (Block F2a).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\TemplateController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/templates
 *   GET    /api/admin/marketing/templates/{id}
 *   POST   /api/admin/marketing/templates
 *   PUT    /api/admin/marketing/templates/{id}
 *   DELETE /api/admin/marketing/templates/{id}
 *
 * GraphQL:
 *   adminMarketingTemplates           — cursor listing
 *   adminMarketingTemplate(id:)       — detail
 *   createAdminMarketingTemplate
 *   updateAdminMarketingTemplate
 *   deleteAdminMarketingTemplate
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingTemplate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/templates',
            input: AdminMarketingTemplateCreateInput::class,
            processor: AdminMarketingTemplateProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Create an email template',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'status', 'content'],
                                'properties' => [
                                    'name'    => ['type' => 'string', 'example' => 'Welcome Email'],
                                    'status'  => ['type' => 'string', 'enum' => ['active', 'inactive', 'draft'], 'example' => 'active'],
                                    'content' => ['type' => 'string', 'example' => '<p>Welcome to our store!</p>'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Template created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/templates/{id}',
            input: AdminMarketingTemplateUpdateInput::class,
            provider: AdminMarketingTemplateWriteProvider::class,
            processor: AdminMarketingTemplateProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Update an email template',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'    => ['type' => 'string', 'example' => 'Welcome Email'],
                                    'status'  => ['type' => 'string', 'enum' => ['active', 'inactive', 'draft'], 'example' => 'active'],
                                    'content' => ['type' => 'string', 'example' => '<p>Welcome to our store!</p>'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Template updated.'),
                    '404' => new Model\Response(description: 'Template not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/templates/{id}',
            provider: AdminMarketingTemplateWriteProvider::class,
            processor: AdminMarketingTemplateProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Delete an email template',
                responses: [
                    '200' => new Model\Response(description: 'Template deleted.'),
                    '404' => new Model\Response(description: 'Template not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/templates/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingTemplateItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Email template detail',
                responses: [
                    '200' => new Model\Response(description: 'Single template with content.'),
                    '404' => new Model\Response(description: 'Template not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/templates',
            provider: AdminMarketingTemplateCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'List email templates',
                description: 'Filters: name (LIKE), status. Sort: id (default desc), name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Status filter.', false, schema: ['type' => 'string', 'enum' => ['active', 'inactive', 'draft']]),
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
            provider: AdminMarketingTemplateCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'   => ['type' => 'String'],
                'status' => ['type' => 'String'],
                'sort'   => ['type' => 'String'],
                'order'  => ['type' => 'String'],
            ],
            description: 'Admin email templates listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingTemplateItemProvider::class,
            description: 'Admin email template detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingTemplateCreateInput::class,
            processor: AdminMarketingTemplateProcessor::class,
            description: 'Create an email template. Becomes createAdminMarketingTemplate.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingTemplateUpdateInput::class,
            processor: AdminMarketingTemplateProcessor::class,
            description: 'Update an email template. Becomes updateAdminMarketingTemplate.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingTemplateUpdateInput::class,
            processor: AdminMarketingTemplateProcessor::class,
            description: 'Delete an email template. Becomes deleteAdminMarketingTemplate.',
        ),
    ],
)]
class AdminMarketingTemplate
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $content = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
