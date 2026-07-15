<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusMassDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaStatusMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/rma/statuses/mass-delete',
            input: AdminRmaStatusMassDeleteInput::class,
            processor: AdminRmaStatusMassDeleteProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Mass-delete RMA statuses', description: 'Body `{ indices: int[] }`. Default statuses are skipped. Permission: sales.rma.statuses.delete.'),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaStatusMassDeleteInput::class, processor: AdminRmaStatusMassDeleteProcessor::class),
    ],
)]
class AdminRmaStatusMassDelete
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $deleted = null;

    public ?string $message = null;
}
