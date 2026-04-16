<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\DeleteAllCompareItemsInput;
use Webkul\BagistoApi\State\DeleteAllCompareItemsProcessor;

/**
 * Delete All Compare Items Response Model
 *
 * Response object for bulk delete compare item operations
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'DeleteAllCompareItems',
    description: 'Delete all compare items for the authenticated customer',
    operations: [
        new Post(
            uriTemplate: '/delete-all-compare-items',
            input: DeleteAllCompareItemsInput::class,
            processor: DeleteAllCompareItemsProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: DeleteAllCompareItemsInput::class,
            output: self::class,
            processor: DeleteAllCompareItemsProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class DeleteAllCompareItems
{
    #[ApiProperty(identifier: true, writable: false, readable: true)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $deletedCount = null;

    public function __construct(?string $message = null, ?int $deletedCount = null, ?int $id = null)
    {
        $this->message = $message;
        $this->deletedCount = $deletedCount;
        $this->id = $id ?? 1;
    }
}
