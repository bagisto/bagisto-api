<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webkul\Admin\Helpers\Dashboard;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Provides the admin Dashboard stats payload for both REST and the GraphQL
 * resolver. Mirrors `DashboardController::stats()` — picks the helper method
 * keyed by `?type=` (default: over-all), serialises it to a normalised array
 * the API resource can consume.
 */
class AdminDashboardProvider implements ProviderInterface
{
    /**
     * Allowed `type` values + their Dashboard helper method name.
     */
    public const TYPE_FUNCTIONS = [
        'over-all' => 'getOverAllStats',
        'today' => 'getTodayStats',
        'stock-threshold-products' => 'getStockThresholdProducts',
        'total-sales' => 'getSalesStats',
        'total-visitors' => 'getVisitorStats',
        'top-selling-products' => 'getTopSellingProducts',
        'top-customers' => 'getTopCustomers',
    ];

    public const DEFAULT_TYPE = 'over-all';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return [self::buildPayload(request()->query('type', self::DEFAULT_TYPE))];
    }

    /**
     * Resolve `?type=` → Dashboard helper method → normalised payload.
     *
     * @return array{type:string,dateRange:?string,statistics:array}
     */
    public static function buildPayload(?string $type = null): array
    {
        $type = $type ?: self::DEFAULT_TYPE;

        if (! array_key_exists($type, self::TYPE_FUNCTIONS)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.dashboard.invalid-type'));
        }

        /** @var Dashboard $helper */
        $helper = app(Dashboard::class);

        $stats = $helper->{self::TYPE_FUNCTIONS[$type]}();

        return [
            'type' => $type,
            'dateRange' => $helper->getDateRange(),
            'statistics' => self::toArray($stats),
        ];
    }

    /**
     * Helpers return mixed shapes (arrays, Eloquent Collections, Support
     * Collections). Normalise to plain arrays for JSON serialisation —
     * blade-rendered snippets that don't survive JSON encoding are silently
     * dropped.
     *
     * @param  mixed  $stats
     */
    private static function toArray($stats): array
    {
        return self::normalize($stats);
    }

    /**
     * Recursively flatten Eloquent models / collections to plain arrays so
     * Symfony Serializer never reflects on an Eloquent property (which would
     * trigger `Schema\Builder::getTypes()` and HTTP 500 "This database driver
     * does not support user-defined types" on MySQL).
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

        if (is_object($value) && method_exists($value, 'toArray')) {
            return self::normalizeArray($value->toArray());
        }

        return (array) $value;
    }

    private static function normalizeArray(array $value): array
    {
        foreach ($value as $k => $v) {
            if ($v instanceof Model
                || $v instanceof Collection) {
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
