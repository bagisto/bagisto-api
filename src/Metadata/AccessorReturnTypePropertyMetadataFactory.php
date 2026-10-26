<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

class AccessorReturnTypePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /**
     * The namespace prefix constant.
     */
    private const NAMESPACE_PREFIX = 'Webkul\\BagistoApi\\';

    /**
     * The scalar type per accessor, or false when the accessor keeps the type it was given.
     *
     * @var array<string, Type|false>
     */
    private static array $decisionCache = [];

    /**
     * Create a new instance.
     */
    public function __construct(private readonly PropertyMetadataFactoryInterface $decorated) {}

    /**
     * Type an accessor-backed property by the accessor's native return type instead of as a string.
     */
    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);

        if (
            ! str_starts_with($resourceClass, self::NAMESPACE_PREFIX)
            || ! is_a($resourceClass, Model::class, true)
        ) {
            return $propertyMetadata;
        }

        $type = self::$decisionCache[$resourceClass.'::'.$property] ??= $this->accessorType($resourceClass, $property);

        if (
            $type === false
            || ! $this->isStringOrUntyped($propertyMetadata->getNativeType())
        ) {
            return $propertyMetadata;
        }

        return $propertyMetadata->withNativeType($type);
    }

    /**
     * The nullable scalar type the property's accessor returns, or false when it returns no int, float or bool.
     */
    private function accessorType(string $resourceClass, string $property): Type|false
    {
        $method = 'get'.Str::studly($property).'Attribute';

        if (! method_exists($resourceClass, $method)) {
            return false;
        }

        $returnType = (new ReflectionMethod($resourceClass, $method))->getReturnType();

        if (
            ! $returnType instanceof ReflectionNamedType
            || ! $returnType->isBuiltin()
        ) {
            return false;
        }

        return match ($returnType->getName()) {
            'int' => Type::nullable(Type::int()),
            'float' => Type::nullable(Type::float()),
            'bool' => Type::nullable(Type::bool()),
            default => false,
        };
    }

    /**
     * Whether the property was typed as a string, or not typed at all.
     */
    private function isStringOrUntyped(?Type $type): bool
    {
        return $type === null
            || $type->isIdentifiedBy(TypeIdentifier::STRING);
    }
}
