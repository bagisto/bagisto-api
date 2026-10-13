<?php

namespace Webkul\BagistoApi\State\Concerns;

trait ClampsPageSize
{
    /**
     * Clamp a requested page size between one and the configured maximum.
     */
    protected function clampPageSize(int $requested): int
    {
        $maximum = max(1, (int) config('api-platform.defaults.pagination_maximum_items_per_page', 50));

        return min(max($requested, 1), $maximum);
    }
}
