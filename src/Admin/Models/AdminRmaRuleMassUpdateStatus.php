<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaRuleMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(uriTemplate: '/rma/rules/mass-update-status', input: AdminRmaRuleMassUpdateStatusInput::class, processor: AdminRmaRuleMassUpdateStatusProcessor::class, openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Mass-update RMA rule status', description: 'Body `{ indices: int[], value: 0|1 }`. Permission: sales.rma.rules.edit.')),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaRuleMassUpdateStatusInput::class, processor: AdminRmaRuleMassUpdateStatusProcessor::class),
    ],
)]
class AdminRmaRuleMassUpdateStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $updated = null;

    public ?string $message = null;
}
