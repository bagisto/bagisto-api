<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerReviewMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/reviews/mass-update-status',
            input: AdminCustomerReviewMassUpdateStatusInput::class,
            processor: AdminCustomerReviewMassUpdateStatusProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer Reviews'],
                summary: 'Mass update review status',
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerReviewMassUpdateStatusInput::class,
            processor: AdminCustomerReviewMassUpdateStatusProcessor::class,
        ),
    ],
)]
class AdminCustomerReviewMassUpdateStatus
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $updated = null;

    #[ApiProperty(writable: false)]
    public ?string $value = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
