<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webkul\Admin\Helpers\Reporting as ReportingHelper;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;

class AdminReportingProvider implements ProviderInterface
{
    /**
     * Overview stat types and their helper methods; an empty `type` falls back to the first entry.
     */
    public const TYPE_OVERVIEW_FUNCTIONS = [
        'total-sales' => 'getTotalSalesStats',
        'total-orders' => 'getTotalOrdersStats',
        'total-customers' => 'getTotalCustomersStats',
        'top-selling-products-by-revenue' => 'getTopSellingProductsByRevenue',
    ];

    /**
     * Sales stat types and their helper methods.
     */
    public const TYPE_SALES_FUNCTIONS = [
        'total-sales' => 'getTotalSalesStats',
        'average-sales' => 'getAverageSalesStats',
        'total-orders' => 'getTotalOrdersStats',
        'purchase-funnel' => 'getPurchaseFunnelStats',
        'abandoned-carts' => 'getAbandonedCartsStats',
        'refunds' => 'getRefundsStats',
        'tax-collected' => 'getTaxCollectedStats',
        'shipping-collected' => 'getShippingCollectedStats',
        'top-payment-methods' => 'getTopPaymentMethods',
        'sales-by-coupon' => 'getSalesByCouponStats',
    ];

    /**
     * Customers stat types and their helper methods.
     */
    public const TYPE_CUSTOMERS_FUNCTIONS = [
        'total-customers' => 'getTotalCustomersStats',
        'customers-with-most-sales' => 'getCustomersWithMostSales',
        'customers-with-most-orders' => 'getCustomersWithMostOrders',
        'customers-with-most-reviews' => 'getCustomersWithMostReviews',
        'top-customer-groups' => 'getTopCustomerGroups',
    ];

    /**
     * Products stat types and their helper methods.
     */
    public const TYPE_PRODUCTS_FUNCTIONS = [
        'total-sold-quantities' => 'getTotalSoldQuantitiesStats',
        'total-products-added-to-wishlist' => 'getTotalProductsAddedToWishlistStats',
        'top-selling-products-by-revenue' => 'getTopSellingProductsByRevenue',
        'top-selling-products-by-quantity' => 'getTopSellingProductsByQuantity',
        'products-with-most-reviews' => 'getProductsWithMostReviews',
        'last-search-terms' => 'getLastSearchTerms',
        'top-search-terms' => 'getTopSearchTerms',
    ];

    /**
     * Reporting entity and its type to helper-method map.
     */
    public const ENTITY_MAPS = [
        'overview' => self::TYPE_OVERVIEW_FUNCTIONS,
        'sales' => self::TYPE_SALES_FUNCTIONS,
        'customers' => self::TYPE_CUSTOMERS_FUNCTIONS,
        'products' => self::TYPE_PRODUCTS_FUNCTIONS,
    ];

    /**
     * Reporting entity whose map is consulted; subclasses override it.
     */
    protected string $entity = 'overview';

    /**
     * Stat shape: 'graph' for the panel summary, 'table' for the detailed View Details and export shape.
     */
    protected string $mode = 'graph';

    /**
     * Provide the reporting payload for the entity and the requested `?type=`, mirroring `Reporting/Controller::stats()`.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize($operation, null, $this->entity === 'overview' ? 'reporting' : 'reporting.'.$this->entity);

        return [self::buildPayload($this->entity, request()->query('type'), $this->mode)];
    }

    /**
     * Resolve the entity's `type` to its Reporting helper method and return the normalised payload.
     *
     * @param  string  $mode  'graph' (panel summary) | 'table' (View Details / export)
     * @return array{entity:string,type:string,dateRange:?array,statistics:array}
     */
    public static function buildPayload(string $entity, ?string $type = null, string $mode = 'graph'): array
    {
        if (! array_key_exists($entity, self::ENTITY_MAPS)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.reporting.invalid-entity'));
        }

        $map = self::ENTITY_MAPS[$entity];
        $type = $type ?: array_key_first($map);

        if (! array_key_exists($type, $map)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.reporting.invalid-type'));
        }

        $helper = app(ReportingHelper::class);

        $stats = $mode === 'table'
            ? $helper->{$map[$type]}('table')
            : $helper->{$map[$type]}();

        return [
            'entity' => $entity,
            'type' => $type,
            'dateRange' => $helper->getDateRange(),
            'statistics' => self::toArray($stats),
        ];
    }

    /**
     * Normalise the mixed shapes the Reporting helpers return into a plain array for JSON serialisation.
     *
     * @param  mixed  $stats
     */
    private static function toArray($stats): array
    {
        return self::normalize($stats);
    }

    /**
     * Flatten Eloquent models and collections to plain arrays so the Symfony Serializer never reflects
     * on an Eloquent property, which fails on MySQL; keep in sync with `AdminDashboardProvider::normalize`.
     *
     * @param  mixed  $value
     */
    private static function normalize($value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof Model) {
            return self::normalizeArray($value->toArray());
        }

        if ($value instanceof Collection) {
            return self::normalizeArray($value->toArray());
        }

        if (is_array($value)) {
            return self::normalizeArray($value);
        }

        if (
            is_object($value)
            && method_exists($value, 'toArray')
        ) {
            return self::normalizeArray($value->toArray());
        }

        return (array) $value;
    }

    /**
     * Recursively normalise each nested model, collection, arrayable or stringable item of the array.
     */
    private static function normalizeArray(array $value): array
    {
        foreach ($value as $key => $item) {
            if (
                $item instanceof Model
                || $item instanceof Collection
            ) {
                $value[$key] = self::normalize($item);
            } elseif (is_array($item)) {
                $value[$key] = self::normalizeArray($item);
            } elseif (
                is_object($item)
                && method_exists($item, 'toArray')
            ) {
                $value[$key] = self::normalizeArray($item->toArray());
            } elseif (
                is_object($item)
                && $item instanceof \Stringable
            ) {
                $value[$key] = (string) $item;
            } elseif (is_object($item)) {
                $value[$key] = (array) $item;
            }
        }

        return $value;
    }
}
