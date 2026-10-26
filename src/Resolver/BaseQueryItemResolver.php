<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Category;
use Webkul\BagistoApi\Models\CustomerAddress;
use Webkul\BagistoApi\Models\CustomerDownloadableProduct;
use Webkul\BagistoApi\Models\CustomerInvoice;
use Webkul\BagistoApi\Models\CustomerOrderShipment;
use Webkul\BagistoApi\Models\CustomerOrderShipmentItem;
use Webkul\BagistoApi\Models\CustomerReview;
use Webkul\BagistoApi\Models\Page;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\BagistoApi\Service\GenericIdNormalizer;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;

class BaseQueryItemResolver implements QueryItemResolverInterface
{
    /**
     * The GraphQL return type name of the resource being resolved.
     */
    private ?string $currentResourceClass = null;

    /**
     * Resolve a single item from a numeric or IRI identifier, scoping customer-owned models to their owner.
     */
    public function __invoke(?object $item, array $context): object
    {
        $args = $context['args'] ?? [];

        $id = $args['id'] ?? null;

        $this->currentResourceClass = $this->extractResourceClassName($context);

        if (
            $id === null
            || $id === ''
        ) {
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

        $resource = $this->publicQuery($modelClass)->find($numericId);

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
     * Determine whether the model holds a single customer's data and must be scoped to its owner.
     */
    protected function isCustomerOwned(string $modelClass): bool
    {
        return in_array($modelClass, [
            CustomerInvoice::class,
            CustomerOrderShipment::class,
            CustomerOrderShipmentItem::class,
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
        $customer = CurrentCustomer::user();

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

        if ($modelClass === CustomerOrderShipmentItem::class) {
            return $query->whereHas('shipment.order', function (Builder $order) use ($customerId) {
                $order->where('customer_id', $customerId)
                    ->where('customer_type', Customer::class);
            });
        }

        return $query->where('customer_id', $customerId);
    }

    /**
     * Build a query limited to the rows the storefront shows: active categories, current-channel pages, approved reviews, reachable products.
     */
    protected function publicQuery(string $modelClass): Builder
    {
        if (is_a($modelClass, Category::class, true)) {
            return $modelClass::query()->where('status', 1);
        }

        if (is_a($modelClass, Page::class, true)) {
            return $modelClass::query()->inCurrentChannel();
        }

        if (is_a($modelClass, ProductReview::class, true)) {
            return $modelClass::query()->where('status', ProductReview::STATUS_APPROVED);
        }

        if (is_a($modelClass, Product::class, true)) {
            return $modelClass::query()->reachableInStorefront(request()->attributes->get('bagisto_channel'));
        }

        return $modelClass::query();
    }

    /**
     * Get the not-found message for a customer-owned resource.
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
     * Extract the resource class name from the GraphQL return type in the resolver context.
     */
    protected function extractResourceClassName(array $context): ?string
    {
        $info = $context['info'] ?? null;

        if (! $info) {
            return null;
        }

        return $info->returnType->name ?? null;
    }

    /**
     * Map the GraphQL return type to its model class in the Webkul\BagistoApi\Models namespace.
     *
     * @throws BadRequestHttpException
     */
    protected function getModel(array $context): string
    {
        $info = $context['info'] ?? null;

        if (! $info) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.resolver.no-resolve-info')
            );
        }

        $resourceClass = $info->returnType->name ?? null;

        if (! $resourceClass) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.resolver.unknown-resource-class')
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
     * Get the translation key prefix for error messages, built from the kebab-cased GraphQL type name.
     */
    protected function getErrorMessagePrefix(): string
    {
        if ($this->currentResourceClass) {
            return 'bagistoapi::app.graphql.'.$this->classToKebabCase($this->currentResourceClass);
        }

        return 'bagistoapi::app.graphql.unknown-resource';
    }

    /**
     * Convert a PascalCase class name to kebab-case for translation keys.
     */
    private function classToKebabCase(string $class): string
    {
        $class = class_basename($class);

        $kebab = preg_replace('/([A-Z])/', '-$1', $class);

        return strtolower(ltrim($kebab, '-'));
    }
}
