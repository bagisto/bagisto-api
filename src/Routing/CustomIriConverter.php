<?php

namespace Webkul\BagistoApi\Routing;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use Illuminate\Database\Eloquent\Model;

class CustomIriConverter implements IriConverterInterface
{
    public function __construct(
        private IriConverterInterface $decorated,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory
    ) {}

    public function getIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        if ($resource instanceof Model || (is_string($resource) && class_exists($resource) && is_subclass_of($resource, Model::class))) {
            try {
                $resourceClass = is_string($resource) ? $resource : $resource::class;
                $metadata = $this->resourceMetadataFactory->create($resourceClass);

                foreach ($metadata as $resourceMetadata) {
                    foreach ($resourceMetadata->getOperations() as $op) {
                        if ($op instanceof Get) {
                            $uriTemplate = $op->getUriTemplate();

                            preg_match_all('/\{([^}]+)\}/', $uriTemplate, $matches);

                            if (count($matches[1]) === 1) {
                                return $this->decorated->getIriFromResource($resource, $referenceType, $op, $context);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return $this->decorated->getIriFromResource($resource, $referenceType, $operation, $context);
    }

    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        $realOperation = $operation ?? ($context['operation'] ?? null);

        $resourceClass = $realOperation?->getClass();
        if ($resourceClass) {
            $className = class_basename($resourceClass);
            if (in_array($className, ['CartToken', 'AddProductInCart'])) {
                return new \stdClass;
            }
        }

        try {
            return $this->decorated->getResourceFromIri($iri, $context, $realOperation);
	} catch (\Throwable $e) {
	     if ($realOperation && $resourceClass = $realOperation->getClass()) {
               return app($resourceClass);
             }

            return new \stdClass;
        }
    }
}
