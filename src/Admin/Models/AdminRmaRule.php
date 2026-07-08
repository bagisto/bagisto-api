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
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaRule',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/rules',
            provider: AdminRmaRuleCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'List RMA rules', description: 'Return rules (a rule sets the return window for matching products). Filters: name (LIKE), status. Sort: id (default desc), name.'),
        ),
        new Get(
            uriTemplate: '/rma/rules/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaRuleItemProvider::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Get an RMA rule'),
        ),
        new Post(
            uriTemplate: '/rma/rules',
            input: AdminRmaRuleCreateInput::class,
            processor: AdminRmaRuleProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Create an RMA rule', description: 'Body `{ name, description, status, return_period }`. Permission: sales.rma.rules.create.'),
        ),
        new Put(
            uriTemplate: '/rma/rules/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaRuleUpdateInput::class,
            provider: AdminRmaRuleWriteProvider::class,
            processor: AdminRmaRuleProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Update an RMA rule', description: 'Permission: sales.rma.rules.edit.'),
        ),
        new Delete(
            uriTemplate: '/rma/rules/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaRuleWriteProvider::class,
            processor: AdminRmaRuleProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Delete an RMA rule', description: 'Permission: sales.rma.rules.delete.'),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaRuleCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'name'   => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort'   => ['type' => 'String'],
                'order'  => ['type' => 'String'],
                'first'  => ['type' => 'Int'],
                'after'  => ['type' => 'String'],
            ],
        ),
        new Query(provider: AdminRmaRuleItemProvider::class),
        new Mutation(name: 'create', input: AdminRmaRuleCreateInput::class, processor: AdminRmaRuleProcessor::class),
        new Mutation(name: 'update', input: AdminRmaRuleUpdateInput::class, processor: AdminRmaRuleProcessor::class),
        new Mutation(name: 'delete', input: AdminRmaRuleUpdateInput::class, processor: AdminRmaRuleProcessor::class),
    ],
)]
class AdminRmaRule
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?int $status = null;

    public ?int $return_period = null;

    public ?int $default = null;

    public ?string $message = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
