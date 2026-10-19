<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Laravel\Eloquent\Metadata\ModelMetadata;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\TypeIdentifier;

class DatabaseIndependentColumnTypePropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /**
     * Column types each driver names differently, mapped to the API type they share, as PostgreSQL resolves them.
     */
    private const COLUMN_TYPES = [
        '/^(int|integer)( unsigned)?$/' => 'int',
        '/^(tinyint\(1\)|boolean)( unsigned)?$/' => 'bool',
        '/^(decimal|numeric)\(\d+,\s*\d+\)( unsigned)?$/' => 'string',
        '/^(timestamp|datetime)(\(\d\))?( without time zone)?$/' => 'string',
        '/^(bigint|smallint|tinyint)( unsigned)?$/' => 'string',
        '/^(double|double precision)$/' => 'string',
    ];

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly PropertyMetadataFactoryInterface $decorated,
        private readonly ModelMetadata $modelMetadata,
    ) {}

    /**
     * Create the property metadata, giving a column the same type on every database.
     */
    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);

        $native = $metadata->getNativeType();

        if (
            $native === null
            || ! is_a($resourceClass, Model::class, true)
        ) {
            return $metadata;
        }

        $columnType = $this->uncastColumnType($resourceClass, $property);

        if ($columnType === null) {
            return $metadata;
        }

        $apiType = $this->sharedTypeFor($columnType);

        $inferred = (string) $this->inferredTypeFor($columnType);

        if (
            $apiType === null
            || (string) $this->withoutNull($native) !== $inferred
            || (string) $apiType === $inferred
        ) {
            return $metadata;
        }

        $metadata = $metadata->withNativeType($native->isNullable() ? Type::nullable($apiType) : $apiType);

        $schema = $metadata->getSchema();

        return is_array($schema)
            ? $metadata->withSchema($this->schemaFor($schema, $apiType))
            : $metadata;
    }

    /**
     * The database type of the column behind the property, or null when the model casts it.
     */
    private function uncastColumnType(string $resourceClass, string $property): ?string
    {
        try {
            $attributes = $this->modelMetadata->getAttributes(
                (new \ReflectionClass($resourceClass))->newInstanceWithoutConstructor()
            );
        } catch (\Throwable) {
            return null;
        }

        foreach ($attributes as $attribute) {
            if ($attribute['name'] !== $property) {
                continue;
            }

            if (
                isset($attribute['cast'])
                || ! is_string($attribute['type'] ?? null)
            ) {
                return null;
            }

            return strtolower($attribute['type']);
        }

        return null;
    }

    /**
     * The API type a driver-specific column type shares across databases, or null when it needs none.
     */
    private function sharedTypeFor(string $columnType): ?Type
    {
        foreach (self::COLUMN_TYPES as $pattern => $type) {
            if (preg_match($pattern, $columnType)) {
                return match ($type) {
                    'int' => Type::int(),
                    'bool' => Type::bool(),
                    default => Type::string(),
                };
            }
        }

        return null;
    }

    /**
     * The type API Platform's Eloquent property factory infers from an uncast column type.
     */
    private function inferredTypeFor(string $columnType): Type
    {
        if (str_starts_with($columnType, 'decimal')) {
            $columnType = 'decimal';
        }

        return match ($columnType) {
            'integer' => Type::int(),
            'double', 'real', 'float', 'decimal', 'numeric' => Type::float(),
            'boolean', 'bool' => Type::bool(),
            'datetime', 'date', 'timestamp' => Type::object(\DateTime::class),
            default => in_array($columnType, TypeIdentifier::values(), true)
                ? Type::builtin($columnType)
                : Type::string(),
        };
    }

    /**
     * The JSON schema of the property rewritten to the shared API type, keeping its null alternative.
     */
    private function schemaFor(array $schema, Type $apiType): array
    {
        $jsonType = match ((string) $apiType) {
            'int' => 'integer',
            'bool' => 'boolean',
            default => 'string',
        };

        if (is_array($schema['type'] ?? null)) {
            $schema['type'] = array_map(
                fn ($type) => $type === 'null' ? $type : $jsonType,
                $schema['type']
            );
        } elseif (isset($schema['type'])) {
            $schema['type'] = $jsonType;
        }

        unset($schema['format']);

        return $schema;
    }

    /**
     * The type without its null alternative.
     */
    private function withoutNull(Type $native): Type
    {
        return $native instanceof NullableType
            ? $native->getWrappedType()
            : $native;
    }
}
