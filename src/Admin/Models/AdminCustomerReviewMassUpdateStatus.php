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
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices', 'value'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer']],
                                    'value' => ['type' => 'string', 'enum' => ['pending', 'approved', 'disapproved']],
                                ],
                            ],
                            'example' => ['indices' => [21, 22, 23], 'value' => 'approved'],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Mass-update result.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'updated' => [21, 22, 23],
                                    'value' => 'approved',
                                    'message' => 'Review status updated successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Missing indices or invalid value.'),
                ],
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
