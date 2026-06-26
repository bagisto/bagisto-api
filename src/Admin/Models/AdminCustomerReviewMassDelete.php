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
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer']],
                                ],
                            ],
                            'example' => ['indices' => [21, 22, 23]],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Mass-delete result.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [21, 22, 23],
                                    'skipped' => [],
                                    'message' => 'Reviews deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'No indices supplied.'),
                ],
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
