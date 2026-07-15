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
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/statuses',
            provider: AdminRmaStatusCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'List RMA statuses', description: 'Custom return statuses. Filters: title (LIKE), status. Sort: id (default desc), title. Default statuses cannot be deleted.'),
        ),
        new Get(
            uriTemplate: '/rma/statuses/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaStatusItemProvider::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Get an RMA status'),
        ),
        new Post(
            uriTemplate: '/rma/statuses',
            input: AdminRmaStatusCreateInput::class,
            processor: AdminRmaStatusProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Create an RMA status', description: 'Body `{ title, status, color }`. Title must be unique. Permission: sales.rma.statuses.create.'),
        ),
        new Put(
            uriTemplate: '/rma/statuses/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaStatusUpdateInput::class,
            provider: AdminRmaStatusWriteProvider::class,
            processor: AdminRmaStatusProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Update an RMA status', description: 'Permission: sales.rma.statuses.edit.'),
        ),
        new Delete(
            uriTemplate: '/rma/statuses/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaStatusWriteProvider::class,
            processor: AdminRmaStatusProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Delete an RMA status', description: 'Only non-default statuses can be deleted (422 otherwise). Permission: sales.rma.statuses.delete.'),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaStatusCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'title' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Query(provider: AdminRmaStatusItemProvider::class),
        new Mutation(name: 'create', input: AdminRmaStatusCreateInput::class, processor: AdminRmaStatusProcessor::class),
        new Mutation(name: 'update', input: AdminRmaStatusUpdateInput::class, processor: AdminRmaStatusProcessor::class),
        new Mutation(name: 'delete', input: AdminRmaStatusUpdateInput::class, processor: AdminRmaStatusProcessor::class),
    ],
)]
class AdminRmaStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $title = null;

    public ?int $status = null;

    public ?string $color = null;

    public ?int $default = null;

    public ?string $message = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
