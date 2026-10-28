<?php

namespace Webkul\BagistoApi\Serializer\Mapping\Loader;

use ApiPlatform\Laravel\Eloquent\Metadata\ModelMetadata;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;

final class SafeRelationMetadataLoader implements LoaderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(private readonly ModelMetadata $modelMetadata) {}

    /**
     * Load class metadata.
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
    {
        if ($classMetadata->getName() === Model::class) {
            return false;
        }

        if (! is_a($classMetadata->getName(), Model::class, true)) {
            return false;
        }

        $reflection = $classMetadata->getReflectionClass();

        if ($reflection->isAbstract()) {
            return false;
        }

        try {
            $model = $reflection->newInstanceWithoutConstructor();
        } catch (\Throwable $exception) {
            return false;
        }

        $attributesMetadata = $classMetadata->getAttributesMetadata();

        foreach ($this->modelMetadata->getRelations($model) as $relation) {
            $methodName = $relation['method_name'];
            if (! $reflection->hasMethod($methodName)) {
                continue;
            }

            $reflMethod = $reflection->getMethod($methodName);
            $propertyName = $relation['name'];

            if (! isset($attributesMetadata[$propertyName])) {
                $attributesMetadata[$propertyName] = new AttributeMetadata($propertyName);
                $classMetadata->addAttributeMetadata($attributesMetadata[$propertyName]);
            }

            $attributeMetadata = $attributesMetadata[$propertyName];

            foreach ($reflMethod->getAttributes() as $attributeReflection) {
                $attribute = $attributeReflection->newInstance();

                match (true) {
                    $attribute instanceof Groups => array_map([$attributeMetadata, 'addGroup'], $attribute->groups),
                    $attribute instanceof MaxDepth => $attributeMetadata->setMaxDepth($attribute->maxDepth),
                    $attribute instanceof SerializedName => $attributeMetadata->setSerializedName($attribute->name),
                    $attribute instanceof SerializedPath => $attributeMetadata->setSerializedPath($attribute->path),
                    $attribute instanceof Ignore => $attributeMetadata->setIgnore(true),
                    $attribute instanceof Context => $attributeMetadata->setSerializationContext($attribute->context ?? []),
                    default => null,
                };
            }
        }

        return true;
    }
}
