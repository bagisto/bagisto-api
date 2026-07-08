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
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaReason',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/reasons',
            provider: AdminRmaReasonCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'List RMA reasons', description: 'Return/cancel reasons the RMA form offers. Filters: title (LIKE), status. Sort: id (default desc), position, title.'),
        ),
        new Get(
            uriTemplate: '/rma/reasons/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaReasonItemProvider::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Get an RMA reason'),
        ),
        new Post(
            uriTemplate: '/rma/reasons',
            input: AdminRmaReasonCreateInput::class,
            processor: AdminRmaReasonProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Create an RMA reason', description: 'Body `{ title, status, position, resolution_type: ["return","cancel_items"] }`. Permission: sales.rma.reasons.create.'),
        ),
        new Put(
            uriTemplate: '/rma/reasons/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaReasonUpdateInput::class,
            provider: AdminRmaReasonWriteProvider::class,
            processor: AdminRmaReasonProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Update an RMA reason', description: 'Permission: sales.rma.reasons.edit.'),
        ),
        new Delete(
            uriTemplate: '/rma/reasons/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaReasonWriteProvider::class,
            processor: AdminRmaReasonProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Delete an RMA reason', description: 'Permission: sales.rma.reasons.delete.'),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaReasonCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'title'  => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort'   => ['type' => 'String'],
                'order'  => ['type' => 'String'],
                'first'  => ['type' => 'Int'],
                'after'  => ['type' => 'String'],
            ],
        ),
        new Query(provider: AdminRmaReasonItemProvider::class),
        new Mutation(name: 'create', input: AdminRmaReasonCreateInput::class, processor: AdminRmaReasonProcessor::class),
        new Mutation(name: 'update', input: AdminRmaReasonUpdateInput::class, processor: AdminRmaReasonProcessor::class),
        new Mutation(name: 'delete', input: AdminRmaReasonUpdateInput::class, processor: AdminRmaReasonProcessor::class),
    ],
)]
class AdminRmaReason
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $title = null;

    public ?int $status = null;

    public ?int $position = null;

    public ?int $is_admin = null;

    /** @var array<int,string>|null */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $resolution_type = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
