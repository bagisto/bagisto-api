<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;

class ProductRestProvider extends ProductGraphQLProvider
{
    /**
     * The reserved keys constant.
     */
    private const RESERVED_KEYS = [
        'query', 'sort', 'order', 'page', 'per_page',
        'locale', 'channel', 'filter',
    ];

    /**
     * Mirrors the package-wide `pagination_maximum_items_per_page` cap from
     * config/api-platform.php. This provider overrides API Platform's.
     */
    private const MAX_PER_PAGE = 50;

    /**
     * The default per page constant.
     */
    private const DEFAULT_PER_PAGE = 30;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $query = request()->query();

        $args = [];

        if (
            isset($query['query'])
            && $query['query'] !== ''
        ) {
            $args['query'] = (string) $query['query'];
        }

        [$sortKey, $reverse] = $this->parseSort($query['sort'] ?? null, $query['order'] ?? null);
        if ($sortKey !== null) {
            $args['sortKey'] = $sortKey;
            $args['reverse'] = $reverse;
        }

        $perPage = isset($query['per_page'])
            ? min(self::MAX_PER_PAGE, max(1, (int) $query['per_page']))
            : self::DEFAULT_PER_PAGE;
        $page = isset($query['page']) ? max(1, (int) $query['page']) : 1;
        $args['first'] = $perPage;
        if ($page > 1) {
            $args['after'] = base64_encode((string) (($page - 1) * $perPage - 1));
        }

        if (! empty($query['locale'])) {
            $args['locale'] = (string) $query['locale'];
        }
        if (! empty($query['channel'])) {
            $args['channel'] = (string) $query['channel'];
        }

        $filter = $this->buildFilter($query);
        if (! empty($filter)) {
            $args['filter'] = $filter;
        }

        return parent::provide($operation, $uriVariables, ['args' => $args]);
    }

    /**
     * Parse sort into GraphQL (sortKey, reverse) tuple.
     * Accepts compound `name-asc` or separate `sort=name&order=asc`.
     */
    private function parseSort(?string $sort, ?string $order): array
    {
        if (! $sort) {
            return [null, false];
        }

        $direction = $order ? strtolower($order) : null;

        if (str_contains($sort, '-')) {
            $pos = strrpos($sort, '-');
            $suffix = strtolower(substr($sort, $pos + 1));
            if (in_array($suffix, ['asc', 'desc'], true)) {
                $direction = $suffix;
                $sort = substr($sort, 0, $pos);
            }
        }

        return [strtoupper($sort), $direction === 'desc'];
    }

    /**
     * Build the filter array from REST query params.
     * Everything outside the reserved key list is treated as a filter.
     */
    private function buildFilter(array $query): array
    {
        $filter = [];

        foreach ($query as $key => $value) {
            if (in_array($key, self::RESERVED_KEYS, true)) {
                continue;
            }
            if (
                $value === ''
                || $value === null
            ) {
                continue;
            }
            $filter[$key] = $value;
        }

        if (
            isset($filter['price'])
            && is_string($filter['price'])
            && str_contains($filter['price'], ',')
        ) {
            [$from, $to] = array_pad(array_map('trim', explode(',', $filter['price'], 2)), 2, null);
            if (
                $from !== ''
                && $from !== null
            ) {
                $filter['price_from'] = $from;
            }
            if (
                $to !== ''
                && $to !== null
            ) {
                $filter['price_to'] = $to;
            }
            unset($filter['price']);
        }

        if (! empty($query['filter'])) {
            if (is_array($query['filter'])) {
                $filter = array_merge($filter, $query['filter']);
            } elseif (is_string($query['filter'])) {
                $decoded = json_decode($query['filter'], true);
                if (
                    json_last_error() === JSON_ERROR_NONE
                    && is_array($decoded)
                ) {
                    $filter = array_merge($filter, $decoded);
                }
            }
        }

        return $filter;
    }
}
