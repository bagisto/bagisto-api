<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Symfony\Component\TypeInfo\Type;
use Webkul\BagistoApi\Models\CustomerOrder;

/**
 * Gives listed Eloquent accessors their real type.
 *
 * API Platform types every `get*Attribute()` accessor as a nullable string, so a boolean accessor
 * reaches GraphQL as `String` and serialises as "1" or "". Only the accessors listed here are retyped,
 * so the documented shape of every other accessor stays unchanged.
 */
class AccessorNativeTypePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /**
     * Accessors to retype, with their description, keyed by resource class.
     *
     * @var array<class-string, array<string, string>>
     */
    private const BOOLEAN_ACCESSORS = [
        CustomerOrder::class => [
            'has_non_cancelable_booking' => 'True when the order holds a booking item the customer may not cancel.',
        ],
    ];

    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);

        $description = self::BOOLEAN_ACCESSORS[$resourceClass][$property] ?? null;

        if ($description === null) {
            return $metadata;
        }

        return $metadata
            ->withNativeType(Type::bool())
            ->withReadable(true)
            ->withWritable(false)
            ->withDescription($description);
    }
}
