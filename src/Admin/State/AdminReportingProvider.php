<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\Admin\Helpers\Reporting as ReportingHelper;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Provides reporting payloads for the 4 sub-pages — Overview / Sales /
 * Customers / Products. Mirrors `Reporting/Controller::stats()` and its
 * 3 subclasses; the `?type=` query param routes to the appropriate
 * `Webkul\Admin\Helpers\Reporting` method.
 *
 * Each sub-page declares its own type → helper-method map in TYPE_*_FUNCTIONS;
 * the resource sets `$entity` on the operation so the provider knows which
 * map to consult.
 */
class AdminReportingProvider implements ProviderInterface
{
    /** Empty `type` falls back to the first entry in the map (sub-page overview). */
    public const TYPE_OVERVIEW_FUNCTIONS = [
        'total-sales'                     => 'getTotalSalesStats',
        'total-orders'                    => 'getTotalOrdersStats',
        'total-customers'                 => 'getTotalCustomersStats',
        'top-selling-products-by-revenue' => 'getTopSellingProductsByRevenue',
    ];

    public const TYPE_SALES_FUNCTIONS = [
        'total-sales'         => 'getTotalSalesStats',
        'average-sales'       => 'getAverageSalesStats',
        'total-orders'        => 'getTotalOrdersStats',
        'purchase-funnel'     => 'getPurchaseFunnelStats',
        'abandoned-carts'     => 'getAbandonedCartsStats',
        'refunds'             => 'getRefundsStats',
        'tax-collected'       => 'getTaxCollectedStats',
        'shipping-collected'  => 'getShippingCollectedStats',
        'top-payment-methods' => 'getTopPaymentMethods',
    ];

    public const TYPE_CUSTOMERS_FUNCTIONS = [
        'total-customers'             => 'getTotalCustomersStats',
        'customers-traffic'           => 'getCustomersTrafficStats',
        'customers-with-most-sales'   => 'getCustomersWithMostSales',
        'customers-with-most-orders'  => 'getCustomersWithMostOrders',
        'customers-with-most-reviews' => 'getCustomersWithMostReviews',
        'top-customer-groups'         => 'getTopCustomerGroups',
    ];

    public const TYPE_PRODUCTS_FUNCTIONS = [
        'total-sold-quantities'            => 'getTotalSoldQuantitiesStats',
        'total-products-added-to-wishlist' => 'getTotalProductsAddedToWishlistStats',
        'top-selling-products-by-revenue'  => 'getTopSellingProductsByRevenue',
        'top-selling-products-by-quantity' => 'getTopSellingProductsByQuantity',
        'products-with-most-reviews'       => 'getProductsWithMostReviews',
        'products-with-most-visits'        => 'getProductsWithMostVisits',
        'last-search-terms'                => 'getLastSearchTerms',
        'top-search-terms'                 => 'getTopSearchTerms',
    ];

    public const ENTITY_MAPS = [
        'overview'  => self::TYPE_OVERVIEW_FUNCTIONS,
        'sales'     => self::TYPE_SALES_FUNCTIONS,
        'customers' => self::TYPE_CUSTOMERS_FUNCTIONS,
        'products'  => self::TYPE_PRODUCTS_FUNCTIONS,
    ];

    /** Subclasses override to pick the right map. */
    protected string $entity = 'overview';

    /**
     * Stat shape: 'graph' = the panel summary (charts/headline), 'table' = the
     * detailed { columns, records } shape behind the admin "View Details" page
     * and the Export button. Subclasses override for the view endpoints.
     */
    protected string $mode = 'graph';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return [self::buildPayload($this->entity, request()->query('type'), $this->mode)];
    }

    /**
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

        /** @var ReportingHelper $helper */
        $helper = app(ReportingHelper::class);

        $stats = $mode === 'table'
            ? $helper->{$map[$type]}('table')
            : $helper->{$map[$type]}();

        return [
            'entity'     => $entity,
            'type'       => $type,
            'dateRange'  => $helper->getDateRange(),
            'statistics' => self::toArray($stats),
        ];
    }

    /** @param mixed $stats */
    private static function toArray($stats): array
    {
        return self::normalize($stats);
    }

    /**
     * Recursively flatten Eloquent models / collections to plain arrays so
     * Symfony Serializer never reflects on an Eloquent property (which would
     * trigger `Schema\Builder::getTypes()` and HTTP 500 "This database driver
     * does not support user-defined types" on MySQL). Same fix as
     * `AdminDashboardProvider::normalize` — keep them in sync.
     *
     * @param  mixed  $value
     */
    private static function normalize($value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return self::normalizeArray($value->toArray());
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return self::normalizeArray($value->toArray());
        }

        if (is_array($value)) {
            return self::normalizeArray($value);
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return self::normalizeArray($value->toArray());
        }

        return (array) $value;
    }

    private static function normalizeArray(array $value): array
    {
        foreach ($value as $k => $v) {
            if ($v instanceof \Illuminate\Database\Eloquent\Model
                || $v instanceof \Illuminate\Support\Collection) {
                $value[$k] = self::normalize($v);
            } elseif (is_array($v)) {
                $value[$k] = self::normalizeArray($v);
            } elseif (is_object($v) && method_exists($v, 'toArray')) {
                $value[$k] = self::normalizeArray($v->toArray());
            } elseif (is_object($v) && $v instanceof \Stringable) {
                $value[$k] = (string) $v;
            } elseif (is_object($v)) {
                $value[$k] = (array) $v;
            }
        }

        return $value;
    }
}
