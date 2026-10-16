<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webkul\Admin\Helpers\Dashboard;
use Webkul\BagistoApi\Admin\Models\AdminDashboard;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;

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
        'top-selling-products' => 'getTopSellingProducts',
        'top-customers' => 'getTopCustomers',
    ];

    /**
     * The stats type used when the request names none.
     */
    public const DEFAULT_TYPE = 'over-all';

    /**
     * Provide the dashboard stats payload for the requested `?type=`, mirroring `DashboardController::stats()`.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize($operation, AdminDashboard::class);

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

        $helper = app(Dashboard::class);

        $stats = $helper->{self::TYPE_FUNCTIONS[$type]}();

        return [
            'type' => $type,
            'dateRange' => $helper->getDateRange(),
            'statistics' => self::toArray($stats),
        ];
    }

    /**
     * Normalise the mixed shapes the Dashboard helpers return into a plain array for JSON serialisation.
     *
     * @param  mixed  $stats
     */
    private static function toArray($stats): array
    {
        return self::normalize($stats);
    }

    /**
     * Flatten Eloquent models and collections to plain arrays so the Symfony Serializer never reflects
     * on an Eloquent property, which fails on MySQL.
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
