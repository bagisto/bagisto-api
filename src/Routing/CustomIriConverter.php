<?php

namespace Webkul\BagistoApi\Routing;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
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
    /**
     * Resources that are never resolved from an IRI, and that expose none apart from a booking slot's id.
     */
    private const HIDDEN_RESOURCES = ['BookingSlot', 'CartToken', 'AddProductInCart', 'OrderDetailItem', 'OrderDetailInvoice', 'OrderDetailShipment'];

    /**
     * Token address resources whose item query takes a bare numeric address id.
     */
    private const TOKEN_ADDRESS_RESOURCES = ['CustomerAddressInput', 'CustomerAddressToken'];

    /**
     * Namespace of the admin API resources.
     */
    private const ADMIN_MODELS_NAMESPACE = 'Webkul\\BagistoApi\\Admin\\Models\\';

    /**
     * Namespace of the storefront API resources.
     */
    private const MODELS_NAMESPACE = 'Webkul\\BagistoApi\\Models\\';

    /**
     * Single-{id} URI templates keyed by resource class, for object resources.
     */
    private array $iriTemplateCache = [];

    /**
     * Single-{id} URI templates keyed by class name, for string-resource calls.
     *
     * @var array<string, ?string>
     */
    private array $classIriTemplateCache = [];

    /**
     * Create a new IRI converter instance.
     */
    public function __construct(
        private IriConverterInterface $decorated,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory
    ) {}

    /**
     * Build the IRI of a resource, using a cached single-id template when one fits.
     */
    public function getIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        if (
            is_string($resource)
            && $operation === null
            && $referenceType === UrlGeneratorInterface::ABS_PATH
        ) {
            $uriVariables = $context['uri_variables'] ?? null;

            if (
                is_array($uriVariables)
                && array_keys($uriVariables) === ['id']
            ) {
                $template = $this->classIriTemplate($resource);

                if ($template !== null) {
                    return str_replace('{id}', (string) $uriVariables['id'], $template);
                }
            }
        }

        return $this->computeIriFromResource($resource, $referenceType, $operation, $context);
    }

    /**
     * Resolve a resource from its IRI, accepting a bare numeric id where supported.
     */
    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        $realOperation = $operation ?? ($context['operation'] ?? null);
        $resourceClass = $realOperation?->getClass();

        if ($resourceClass) {
            if (in_array(class_basename($resourceClass), self::HIDDEN_RESOURCES, true)) {
                return new \stdClass;
            }
        }

        try {
            $resolvedIri = $this->normalizeIri($iri, $resourceClass, $operation);

            return $this->decorated->getResourceFromIri($resolvedIri, $context, $realOperation);
        } catch (ClientAware $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            if (
                $resourceClass
                && class_basename($resourceClass) === 'CustomerOrder'
                && ! $this->isNumericOrIri($iri)
            ) {
                throw new BadRequestHttpException(
                    __('bagistoapi::app.graphql.customer-order.invalid-id-format')
                );
            }

            if (
                $realOperation
                && $resourceClass = $realOperation->getClass()
            ) {
                return app($resourceClass);
            }

            return new \stdClass;
        }
    }

    /**
     * Return the cached single-id IRI template of a class.
     */
    private function classIriTemplate(string $class): ?string
    {
        if (! array_key_exists($class, $this->classIriTemplateCache)) {
            $this->classIriTemplateCache[$class] = in_array(class_basename($class), self::HIDDEN_RESOURCES, true)
                ? null
                : $this->resolveSingleIdTemplate($class);
        }

        return $this->classIriTemplateCache[$class];
    }

    /**
     * Build the IRI of a resource through the decorated converter.
     */
    private function computeIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        if (class_basename(is_object($resource) ? $resource::class : $resource) === 'BookingSlot') {
            return $this->bookingSlotIri($resource, $context);
        }

        if ($this->isHiddenResource($resource)) {
            return null;
        }

        if (is_object($resource)) {
            $class = $resource::class;

            if (class_basename($class) === 'AdminReorder') {
                return isset($resource->id) ? '/api/admin/orders/'.$resource->id : null;
            }

            if (str_starts_with($class, self::ADMIN_MODELS_NAMESPACE)) {
                return $this->fastIri($resource);
            }

            $fastIri = str_starts_with($class, self::MODELS_NAMESPACE) ? $this->fastIri($resource) : null;

            if ($fastIri !== null) {
                return $fastIri;
            }
        }

        $singleIdentifierIri = $this->singleIdentifierIri($resource, $referenceType, $context);

        if ($singleIdentifierIri !== null) {
            return $singleIdentifierIri;
        }

        try {
            return $this->decorated->getIriFromResource($resource, $referenceType, $operation, $context);
        } catch (MissingMandatoryParametersException|InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Tell whether the resource is one that never exposes an IRI.
     */
    private function isHiddenResource(object|string $resource): bool
    {
        if (is_object($resource)) {
            return in_array(class_basename($resource::class), self::HIDDEN_RESOURCES, true);
        }

        return class_exists($resource) && in_array(class_basename($resource), self::HIDDEN_RESOURCES, true);
    }

    /**
     * IRI that identifies a booking slot, from the slot itself or from the identifier GraphQL resolves `id` with.
     */
    private function bookingSlotIri(object|string $resource, array $context): ?string
    {
        $identifier = is_object($resource)
            ? $resource->_id
            : ($context['uri_variables']['_id'] ?? null);

        if (
            $identifier === null
            || $identifier === ''
        ) {
            return null;
        }

        return '/api/shop/booking-slots/'.rawurlencode((string) $identifier);
    }

    /**
     * IRI built from the resource's only single-placeholder Get operation, when it has one.
     */
    private function singleIdentifierIri(object|string $resource, int $referenceType, array $context): ?string
    {
        $isModel = $resource instanceof Model
            || (is_string($resource) && class_exists($resource) && is_subclass_of($resource, Model::class));

        if (! $isModel) {
            return null;
        }

        try {
            foreach ($this->operationsOf(is_string($resource) ? $resource : $resource::class) as $resolvedOperation) {
                if (
                    $resolvedOperation instanceof Get
                    && $this->hasSinglePlaceholder($resolvedOperation->getUriTemplate())
                ) {
                    return $this->decorated->getIriFromResource($resource, $referenceType, $resolvedOperation, $context);
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Tell whether a URI template carries exactly one placeholder.
     */
    private function hasSinglePlaceholder(?string $uriTemplate): bool
    {
        return preg_match_all('/\{([^}]+)\}/', (string) $uriTemplate) === 1;
    }

    /**
     * @return iterable<Operation>
     */
    private function operationsOf(string $class): iterable
    {
        foreach ($this->resourceMetadataFactory->create($class) as $resourceMetadata) {
            yield from $resourceMetadata->getOperations();
        }
    }

    /**
     * Turn a bare numeric id into a routable IRI for the resources that accept one.
     */
    private function normalizeIri(string $iri, ?string $resourceClass, ?Operation $operation = null): string
    {
        if (
            ! $resourceClass
            || ! ctype_digit($iri)
        ) {
            return $iri;
        }

        if (class_basename($resourceClass) === 'CustomerOrder') {
            return '/api/shop/customer-orders/'.$iri;
        }

        if (
            $operation instanceof Query
            && in_array(class_basename($resourceClass), self::TOKEN_ADDRESS_RESOURCES, true)
        ) {
            return '/api/shop/customer-addresses/'.$iri;
        }

        return $iri;
    }

    /**
     * Tell whether a value is a numeric id or an IRI.
     */
    private function isNumericOrIri(string $value): bool
    {
        return ctype_digit($value) || str_contains($value, '/');
    }

    /**
     * Build the IRI of a resource straight from its single-id template.
     */
    private function fastIri(object $resource): ?string
    {
        $class = $resource::class;

        if (! array_key_exists($class, $this->iriTemplateCache)) {
            $this->iriTemplateCache[$class] = $this->resolveSingleIdTemplate($class);
        }

        $template = $this->iriTemplateCache[$class];

        if (
            $template === null
            || ! isset($resource->id)
        ) {
            return null;
        }

        return str_replace('{id}', (string) $resource->id, $template);
    }

    /**
     * Find the single-id item URI template of a class.
     */
    private function resolveSingleIdTemplate(string $class): ?string
    {
        try {
            foreach ($this->operationsOf($class) as $operation) {
                $template = $this->singleIdTemplate($operation);

                if ($template !== null) {
                    return $template;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Return the URI template of an operation when it has a single id placeholder.
     */
    private function singleIdTemplate(Operation $operation): ?string
    {
        if (! $operation instanceof Get) {
            return null;
        }

        $uriTemplate = $operation->getUriTemplate();

        if ($uriTemplate === null) {
            return null;
        }

        $uriTemplate = str_replace('{._format}', '', $uriTemplate);

        if (
            substr_count($uriTemplate, '{') !== 1
            || ! str_contains($uriTemplate, '{id}')
        ) {
            return null;
        }

        return rtrim((string) $operation->getRoutePrefix(), '/').$uriTemplate;
    }
}
