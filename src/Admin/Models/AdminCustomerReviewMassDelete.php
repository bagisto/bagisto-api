<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerReviewMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/reviews/mass-delete',
            input: AdminCustomerReviewMassDeleteInput::class,
            processor: AdminCustomerReviewMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer Reviews'],
                summary: 'Mass delete reviews',
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerReviewMassDeleteInput::class,
            processor: AdminCustomerReviewMassDeleteProcessor::class,
        ),
    ],
)]
class AdminCustomerReviewMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /** @var array<int, array{id:int,reason:string}>|null */
    #[ApiProperty(writable: false)]
    public ?array $skipped = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
