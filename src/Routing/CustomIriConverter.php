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
    private const HIDDEN_RESOURCES = ['BookingSlot', 'CartToken', 'AddProductInCart', 'OrderDetailItem', 'OrderDetailInvoice', 'OrderDetailShipment'];

    private const ADMIN_MODELS_NAMESPACE = 'Webkul\\BagistoApi\\Admin\\Models\\';

    private const MODELS_NAMESPACE = 'Webkul\\BagistoApi\\Models\\';

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

    private function classIriTemplate(string $class): ?string
    {
        if (! array_key_exists($class, $this->classIriTemplateCache)) {
            $this->classIriTemplateCache[$class] = in_array(class_basename($class), self::HIDDEN_RESOURCES, true)
                ? null
                : $this->resolveSingleIdTemplate($class);
        }

        return $this->classIriTemplateCache[$class];
    }

    private function computeIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
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

    private function isHiddenResource(object|string $resource): bool
    {
        if (is_object($resource)) {
            return in_array(class_basename($resource::class), self::HIDDEN_RESOURCES, true);
        }

        return class_exists($resource) && in_array(class_basename($resource), self::HIDDEN_RESOURCES, true);
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
            $resolvedIri = $this->normalizeIri($iri, $resourceClass);

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

    private function normalizeIri(string $iri, ?string $resourceClass): string
    {
        if (
            ! $resourceClass
            || ! ctype_digit($iri)
        ) {
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

        if (
            $template === null
            || ! isset($resource->id)
        ) {
            return null;
        }

        return str_replace('{id}', (string) $resource->id, $template);
    }

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
