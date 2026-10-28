<?php

namespace Webkul\BagistoApi\Routing;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use GraphQL\Error\ClientAware;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class CustomIriConverter implements IriConverterInterface
{
    private array $iriTemplateCache = [];

    public function __construct(
        private IriConverterInterface $decorated,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory
    ) {}

    /**
     * Single-{id} URI templates keyed by class name, for string-resource calls.
     *
     * @var array<string, ?string>
     */
    private array $classIriTemplateCache = [];

    public function getIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        if (is_string($resource)
            && $operation === null
            && $referenceType === UrlGeneratorInterface::ABS_PATH
        ) {
            $uriVariables = $context['uri_variables'] ?? null;

            if (is_array($uriVariables) && array_keys($uriVariables) === ['id']) {
                $template = $this->classIriTemplate($resource);

                if ($template !== null) {
                    return str_replace('{id}', (string) $uriVariables['id'], $template);
                }
            }
        }

        return $this->computeIriFromResource($resource, $referenceType, $operation, $context);
    }

    private function classIriTemplate(string $class): ?string
    {
        if (! array_key_exists($class, $this->classIriTemplateCache)) {
            $this->classIriTemplateCache[$class] = in_array(class_basename($class), [
                'CartToken', 'AddProductInCart', 'BookingSlot',
                'OrderDetailItem', 'OrderDetailInvoice', 'OrderDetailShipment',
            ], true)
                ? null
                : $this->resolveSingleIdTemplate($class);
        }

        return $this->classIriTemplateCache[$class];
    }

    private function computeIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        if (is_object($resource)) {
            $className = class_basename($resource::class);
            if (in_array($className, ['BookingSlot', 'CartToken', 'AddProductInCart', 'OrderDetailItem', 'OrderDetailInvoice', 'OrderDetailShipment'])) {
                return null;
            }

            if ($className === 'AdminReorder') {
                return isset($resource->id) ? '/api/admin/orders/'.$resource->id : null;
            }

            if (str_starts_with($resource::class, 'Webkul\\BagistoApi\\Admin\\Models\\')) {
                return $this->fastIri($resource);
            }

            if (str_starts_with($resource::class, 'Webkul\\BagistoApi\\Models\\')) {
                $fast = $this->fastIri($resource);

                if ($fast !== null) {
                    return $fast;
                }
            }
        } elseif (is_string($resource) && class_exists($resource)) {
            $className = class_basename($resource);
            if (in_array($className, ['CartToken', 'AddProductInCart', 'BookingSlot', 'OrderDetailItem', 'OrderDetailInvoice', 'OrderDetailShipment'])) {
                return null;
            }
        }

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

        try {
            return $this->decorated->getIriFromResource($resource, $referenceType, $operation, $context);
        } catch (MissingMandatoryParametersException|InvalidArgumentException $e) {
            return null;
        }
    }

    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        $realOperation = $operation ?? ($context['operation'] ?? null);
        $resourceClass = $realOperation?->getClass();

        if ($resourceClass) {
            $className = class_basename($resourceClass);
            if (in_array($className, ['CartToken', 'AddProductInCart', 'BookingSlot', 'OrderDetailItem', 'OrderDetailInvoice', 'OrderDetailShipment'])) {
                return new \stdClass;
            }
        }

        try {
            $resolvedIri = $this->normalizeIri($iri, $resourceClass);

            return $this->decorated->getResourceFromIri($resolvedIri, $context, $realOperation);
        } catch (ClientAware $e) {
            throw $e;
        } catch (\Throwable $e) {
            if ($resourceClass && class_basename($resourceClass) === 'CustomerOrder' && ! $this->isNumericOrIri($iri)) {
                throw new BadRequestHttpException(
                    __('bagistoapi::app.graphql.customer-order.invalid-id-format')
                );
            }

            if ($realOperation && $resourceClass = $realOperation->getClass()) {
                return app($resourceClass);
            }

            return new \stdClass;
        }
    }

    private function normalizeIri(string $iri, ?string $resourceClass): string
    {
        if (! $resourceClass || ! ctype_digit($iri)) {
            return $iri;
        }

        if (class_basename($resourceClass) !== 'CustomerOrder') {
            return $iri;
        }

        return '/api/shop/customer-orders/'.$iri;
    }

    private function isNumericOrIri(string $value): bool
    {
        return ctype_digit($value) || str_contains($value, '/');
    }

    private function fastIri(object $resource): ?string
    {
        $class = $resource::class;

        if (! array_key_exists($class, $this->iriTemplateCache)) {
            $this->iriTemplateCache[$class] = $this->resolveSingleIdTemplate($class);
        }

        $template = $this->iriTemplateCache[$class];

        if ($template === null || ! isset($resource->id)) {
            return null;
        }

        return str_replace('{id}', (string) $resource->id, $template);
    }

    private function resolveSingleIdTemplate(string $class): ?string
    {
        try {
            $metadata = $this->resourceMetadataFactory->create($class);

            foreach ($metadata as $resourceMetadata) {
                foreach ($resourceMetadata->getOperations() as $op) {
                    if ($op instanceof Get) {
                        $uriTemplate = $op->getUriTemplate();

                        if ($uriTemplate !== null) {
                            $uriTemplate = str_replace('{._format}', '', $uriTemplate);
                        }

                        if ($uriTemplate !== null
                            && substr_count($uriTemplate, '{') === 1
                            && str_contains($uriTemplate, '{id}')) {
                            return rtrim((string) $op->getRoutePrefix(), '/').$uriTemplate;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }
}
