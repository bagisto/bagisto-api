<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaStatusMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/rma/statuses/mass-update-status',
            input: AdminRmaStatusMassUpdateStatusInput::class,
            processor: AdminRmaStatusMassUpdateStatusProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Mass-update RMA status active flag', description: 'Body `{ indices: int[], value: 0|1 }`. Permission: sales.rma.statuses.edit.'),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaStatusMassUpdateStatusInput::class, processor: AdminRmaStatusMassUpdateStatusProcessor::class),
    ],
)]
class AdminRmaStatusMassUpdateStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $updated = null;

    public ?string $message = null;
}
