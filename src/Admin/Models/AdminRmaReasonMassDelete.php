<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonMassDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaReasonMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/rma/reasons/mass-delete',
            input: AdminRmaReasonMassDeleteInput::class,
            processor: AdminRmaReasonMassDeleteProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Mass-delete RMA reasons', description: 'Body `{ indices: int[] }`. Permission: sales.rma.reasons.delete.'),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaReasonMassDeleteInput::class, processor: AdminRmaReasonMassDeleteProcessor::class),
    ],
)]
class AdminRmaReasonMassDelete
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $deleted = null;

    public ?string $message = null;
}
