<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaCustomFieldMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(uriTemplate: '/rma/custom-fields/mass-update-status', input: AdminRmaCustomFieldMassUpdateStatusInput::class, processor: AdminRmaCustomFieldMassUpdateStatusProcessor::class, openapi: new Model\Operation(tags: ['Admin Sales: RMA'], summary: 'Mass-update RMA custom field status', description: 'Body `{ indices: int[], value: 0|1 }`. Permission: sales.rma.custom-fields.edit.')),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaCustomFieldMassUpdateStatusInput::class, processor: AdminRmaCustomFieldMassUpdateStatusProcessor::class),
    ],
)]
class AdminRmaCustomFieldMassUpdateStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $updated = null;

    public ?string $message = null;
}
