<?php

namespace Webkul\BagistoApi\Serializer\Mapping\Loader;

use ApiPlatform\Laravel\Eloquent\Metadata\ModelMetadata;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;

/**
 * Safe relation metadata loader that skips abstract models
 */
final class SafeRelationMetadataLoader implements LoaderInterface
{
    public function __construct(private readonly ModelMetadata $modelMetadata)
    {
    }

    public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
    {
        if (Model::class === $classMetadata->getName()) {
            return false;
        }

        if (!is_a($classMetadata->getName(), Model::class, true)) {
            return false;
        }

        $refl = $classMetadata->getReflectionClass();

        /**
         * Skip abstract classes to prevent instantiation errors
         */
        if ($refl->isAbstract()) {
            return false;
        }

        try {
            /**
             * Attempt to create instance without constructor
             */
            $model = $refl->newInstanceWithoutConstructor();
        } catch (\Throwable $e) {
            /**
             * Skip models that cannot be instantiated
             */
            return false;
        }

        $attributesMetadata = $classMetadata->getAttributesMetadata();

        foreach ($this->modelMetadata->getRelations($model) as $relation) {
            $methodName = $relation['method_name'];
            if (!$refl->hasMethod($methodName)) {
                continue;
            }

            $reflMethod = $refl->getMethod($methodName);
            $propertyName = $relation['name'];

            if (!isset($attributesMetadata[$propertyName])) {
                $attributesMetadata[$propertyName] = new \Symfony\Component\Serializer\Mapping\AttributeMetadata($propertyName);
                $classMetadata->addAttributeMetadata($attributesMetadata[$propertyName]);
            }

            $attributeMetadata = $attributesMetadata[$propertyName];

            foreach ($reflMethod->getAttributes() as $a) {
                $attribute = $a->newInstance();

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
