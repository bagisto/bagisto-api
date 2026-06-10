<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Illuminate\Support\Carbon;
use Webkul\DataGrid\Enums\DateRangeOptionEnum;

/**
 * Resolves an admin datagrid "date" filter to a [from, to] Carbon pair with
 * exact parity to the Bagisto admin datagrid date presets.
 *
 * A custom range (date_from / date_to, or the created_at_from / created_at_to
 * aliases) wins; otherwise a date_range preset is expanded straight from
 * Webkul\DataGrid\Enums\DateRangeOptionEnum so BOTH the accepted preset keys
 * (today, yesterday, this_week, this_month, last_month, last_three_months,
 * last_six_months, this_year) AND their resolved ranges match the admin grid
 * 1:1. An unknown preset resolves to [null, null] (no date filter).
 *
 * Values are read from the GraphQL $args first, then the REST query string,
 * so the same trait serves both transports.
 */
trait ResolvesAdminDateRange
{
    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function resolveAdminDateRange(array $args): array
    {
        $from = $this->dateArg($args, 'date_from') ?? $this->dateArg($args, 'created_at_from');
        $to = $this->dateArg($args, 'date_to') ?? $this->dateArg($args, 'created_at_to');

        if ($from || $to) {
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
