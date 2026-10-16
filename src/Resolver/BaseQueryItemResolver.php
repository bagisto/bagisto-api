<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerAddress;
use Webkul\BagistoApi\Models\CustomerDownloadableProduct;
use Webkul\BagistoApi\Models\CustomerInvoice;
use Webkul\BagistoApi\Models\CustomerOrderShipment;
use Webkul\BagistoApi\Models\CustomerReview;
use Webkul\BagistoApi\Service\GenericIdNormalizer;
use Webkul\Customer\Models\Customer;

/**
 * Global Generic Query Resolver for all GraphQL single item queries
 *
 * Automatically handles both numeric IDs and IRI formats:
 * - Numeric: "123" or 123
 * - IRI: "/api/resource/123" or "https://example.com/api/resource/123"
 *
 * Works for ANY model without needing individual resolver classes
 * Infers model from GraphQL return type and converts class name to translation key
 */
class BaseQueryItemResolver implements QueryItemResolverInterface
{
    private ?string $currentResourceClass = null;

    public function __invoke(?object $item, array $context): object
    {
        $args = $context['args'] ?? [];

        $id = $args['id'] ?? null;

        $this->currentResourceClass = $this->extractResourceClassName($context);

        if ($id === null || $id === '') {
            throw new BadRequestHttpException(
                __($this->getErrorMessagePrefix().'.id-required')
            );
        }

        $numericId = GenericIdNormalizer::extractNumericId($id);

        if ($numericId === null) {
            GenericIdNormalizer::validateAndExtract($id, $this->classToKebabCase($this->currentResourceClass).'.invalid-id-format');
        }

        $modelClass = $this->getModel($context);

        if ($this->isCustomerOwned($modelClass)) {
            return $this->findOwnedResource($modelClass, (int) $numericId, $id);
        }

        $resource = $modelClass::find($numericId);

        if (! $resource) {
            throw new BadRequestHttpException(
                __($this->getErrorMessagePrefix().'.not-found', [
                    'id' => $id,
                    'resource' => $this->currentResourceClass,
                ])
            );
        }

        return $resource;
    }

    /**
     * Whether the model holds a single customer's data and must be scoped to its owner.
     */
    protected function isCustomerOwned(string $modelClass): bool
    {
        return in_array($modelClass, [
            CustomerInvoice::class,
            CustomerOrderShipment::class,
            CustomerDownloadableProduct::class,
            CustomerAddress::class,
            CustomerReview::class,
        ], true);
    }

    /**
     * Resolve a customer-owned resource only when it belongs to the authenticated customer.
     *
     * @throws AuthenticationException
     * @throws ResourceNotFoundException
     */
    protected function findOwnedResource(string $modelClass, int $numericId, mixed $id): object
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer instanceof Customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $resource = $this->scopeToCustomer($modelClass::query(), $modelClass, $customer->id)
            ->whereKey($numericId)
            ->first();

        if (! $resource) {
            throw new ResourceNotFoundException($this->ownedNotFoundMessage($modelClass, $id));
        }

        return $resource;
    }

    /**
     * Restrict a customer-owned query to the given customer.
     */
    protected function scopeToCustomer(Builder $query, string $modelClass, int $customerId): Builder
    {
        if (in_array($modelClass, [CustomerInvoice::class, CustomerOrderShipment::class], true)) {
            return $query->whereHas('order', function (Builder $order) use ($customerId) {
                $order->where('customer_id', $customerId)
                    ->where('customer_type', Customer::class);
            });
        }

        return $query->where('customer_id', $customerId);
    }

    /**
     * Not-found message for a customer-owned resource.
     */
    protected function ownedNotFoundMessage(string $modelClass, mixed $id): string
    {
        if ($modelClass === CustomerAddress::class) {
            return __('bagistoapi::app.graphql.address.address-not-found');
        }

        return __($this->getErrorMessagePrefix().'.not-found', [
            'id' => $id,
            'resource' => $this->currentResourceClass,
        ]);
    }

    /**
     * Extract the resource class name from GraphQL context
     *
     * @param  array  $context  GraphQL resolver context
     * @return string|null Resource class name (e.g., "Resource")
     */
    protected function extractResourceClassName(array $context): ?string
    {
        /** @var ResolveInfo $info */
        $info = $context['info'] ?? null;

        if (! $info) {
            return null;
        }

        return $info->returnType->name ?? null;
    }

    /**
     * Get the Eloquent model class from GraphQL context
     *
     * Maps GraphQL type name to Laravel model class:
     * - "Resource" -> "Webkul\BagistoApi\Models\Resource"
     *
     * @param  array  $context  GraphQL resolver context
     * @return string Fully qualified model class name
     *
     * @throws BadRequestHttpException If model class cannot be determined or doesn't exist
     */
    protected function getModel(array $context): string
    {
        /** @var ResolveInfo $info */
        $info = $context['info'] ?? null;

        if (! $info) {
            throw new BadRequestHttpException(
                'GraphQL ResolveInfo not available in context. Cannot determine model class.'
            );
        }

        $resourceClass = $info->returnType->name ?? null;

        if (! $resourceClass) {
            throw new BadRequestHttpException(
                'Unable to determine resource class from GraphQL return type'
            );
        }

        $modelClass = "Webkul\\BagistoApi\\Models\\{$resourceClass}";

        if (! class_exists($modelClass)) {
            throw new BadRequestHttpException(
                "Model class {$modelClass} not found for GraphQL type {$resourceClass}. ".
                'Please ensure the model exists in Webkul\\BagistoApi\\Models namespace.'
            );
        }

        return $modelClass;
    }

    /**
     * Get the translation key prefix for error messages
     *
     * Automatically converts GraphQL type name to kebab-case:
     * - Resource -> bagistoapi::app.graphql.resource
     * - ProductBundleOption -> bagistoapi::app.graphql.product-bundle-option
     *
     * @return string Translation key prefix
     */
    protected function getErrorMessagePrefix(): string
    {
        if ($this->currentResourceClass) {
            return 'bagistoapi::app.graphql.'.$this->classToKebabCase($this->currentResourceClass);
        }

        return 'bagistoapi::app.graphql.unknown-resource';
    }

    /**
     * Convert PascalCase class name to kebab-case for translation keys
     *
     * Examples:
     * - Resource -> resource
     * - ProductBundleOption -> product-bundle-option
     * - CountryState -> country-state
     *
     * @param  string  $class  Class name to convert
     * @return string Kebab-case version
     */
    private function classToKebabCase(string $class): string
    {
        $class = class_basename($class);

        $kebab = preg_replace('/([A-Z])/', '-$1', $class);

        return strtolower(ltrim($kebab, '-'));
    }
}
