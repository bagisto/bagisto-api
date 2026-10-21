<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Illuminate\Support\Carbon;
use Webkul\DataGrid\Enums\DateRangeOptionEnum;

trait ResolvesAdminDateRange
{
    /**
     * Resolve the start and end dates of the requested date range.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function resolveAdminDateRange(array $args): array
    {
        $from = $this->dateArg($args, 'date_from') ?? $this->dateArg($args, 'created_at_from');
        $to = $this->dateArg($args, 'date_to') ?? $this->dateArg($args, 'created_at_to');

        if (
            $from
            || $to
        ) {
            return [
                $from ? Carbon::parse($from) : null,
                $to ? Carbon::parse($to) : null,
            ];
        }

        $preset = $this->dateArg($args, 'date_range');

        if (! $preset) {
            return [null, null];
        }

        foreach (DateRangeOptionEnum::options() as $option) {
            if ($option['name'] === $preset) {
                return [Carbon::parse($option['from']), Carbon::parse($option['to'])];
            }
        }

        return [null, null];
    }

    /**
     * Read a date filter value from GraphQL args, falling back to the REST query.
     */
    protected function dateArg(array $args, string $key): mixed
    {
        return $args[$key] ?? request()->query($key);
    }
}
