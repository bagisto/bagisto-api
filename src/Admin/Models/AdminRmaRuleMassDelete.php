<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleMassDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaRuleMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(uriTemplate: '/rma/rules/mass-delete', input: AdminRmaRuleMassDeleteInput::class, processor: AdminRmaRuleMassDeleteProcessor::class, openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Mass-delete RMA rules', description: 'Body `{ indices: int[] }`. Permission: sales.rma.rules.delete.')),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaRuleMassDeleteInput::class, processor: AdminRmaRuleMassDeleteProcessor::class),
    ],
)]
class AdminRmaRuleMassDelete
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $deleted = null;

    public ?string $message = null;
}
