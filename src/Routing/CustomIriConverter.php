<?php

namespace Webkul\BagistoApi\Routing;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CustomIriConverter implements IriConverterInterface
{
    private array $adminIriTemplateCache = [];

    public function __construct(
        private IriConverterInterface $decorated,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory
    ) {}

    public function getIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        // Handle non-model API resources that shouldn't generate IRIs
        if (is_object($resource)) {
            $className = class_basename($resource::class);
            if (in_array($className, ['BookingSlot', 'CartToken', 'AddProductInCart', 'OrderItemPreview', 'OrderDetailItem', 'OrderDetailCustomer', 'OrderDetailCustomerGroup', 'OrderDetailAddress', 'OrderDetailInvoice', 'OrderDetailShipment'])) {
                return null;
            }

            if (str_starts_with($resource::class, 'Webkul\\BagistoApi\\Admin\\Models\\')) {
                return $this->fastAdminIri($resource);
            }
        } elseif (is_string($resource) && class_exists($resource)) {
            $className = class_basename($resource);
            if (in_array($className, ['CartToken', 'AddProductInCart', 'BookingSlot', 'OrderItemPreview', 'OrderDetailItem', 'OrderDetailCustomer', 'OrderDetailCustomerGroup', 'OrderDetailAddress', 'OrderDetailInvoice', 'OrderDetailShipment'])) {
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
        } catch (\Symfony\Component\Routing\Exception\MissingMandatoryParametersException|\ApiPlatform\Metadata\Exception\InvalidArgumentException $e) {
            // Some admin resources (e.g. AdminCatalogProductInventory) are
            // exposed only via parent-scoped collection / PUT routes — there
            // is no single-{id} GET, and the response is a paginator of
            // plain DTOs that the IRI generator cannot reach back to a URL
            // because the parent (e.g. productId) URI variable isn't in scope.
            // Emit a null IRI rather than crashing the whole response.
            return null;
        }
    }

    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        $realOperation = $operation ?? ($context['operation'] ?? null);
        $resourceClass = $realOperation?->getClass();

        if ($resourceClass) {
            $className = class_basename($resourceClass);
            if (in_array($className, ['CartToken', 'AddProductInCart', 'BookingSlot', 'OrderItemPreview', 'OrderDetailItem', 'OrderDetailCustomer', 'OrderDetailCustomerGroup', 'OrderDetailAddress', 'OrderDetailInvoice', 'OrderDetailShipment'])) {
                return new \stdClass;
            }
        }

        try {
            $resolvedIri = $this->normalizeIri($iri, $resourceClass);

            return $this->decorated->getResourceFromIri($resolvedIri, $context, $realOperation);
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

    private function fastAdminIri(object $resource): ?string
    {
        $class = $resource::class;

        if (! array_key_exists($class, $this->adminIriTemplateCache)) {
            $this->adminIriTemplateCache[$class] = $this->resolveSingleIdTemplate($class);
        }

        $template = $this->adminIriTemplateCache[$class];

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
