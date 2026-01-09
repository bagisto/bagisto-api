<?php

namespace Webkul\BagistoApi\Serializer;

use ApiPlatform\GraphQl\Serializer\ItemNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Custom normalizer for ProductReviewOutput DTO
 * Ensures proper serialization to GraphQL response format
 */
class ProductReviewOutputNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $decorated,
    ) {}

    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        if (! $object instanceof ProductReviewOutput) {
            return $this->decorated->normalize($object, $format, $context);
        }

        return [
            'id'                                    => $object->id,
            'productId'                             => $object->productId,
            'customerId'                            => $object->customerId,
            'title'                                 => $object->title,
            'comment'                               => $object->comment,
            'rating'                                => $object->rating,
            'name'                                  => $object->name,
            'status'                                => $object->status,
            'createdAt'                             => $object->createdAt,
            'updatedAt'                             => $object->updatedAt,
            ItemNormalizer::ITEM_RESOURCE_CLASS_KEY => ProductReviewOutput::class,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ProductReviewOutput;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ProductReviewOutput::class => true];
    }
}
