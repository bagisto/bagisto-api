<?php

namespace Webkul\BagistoApi\Contracts;

use Illuminate\Support\Collection;

interface PreloadsCollection
{
    /**
     * Load the per-row values of a fetched page in bulk, so serializing it issues no query per row.
     */
    public static function preloadCollection(Collection $items): void;
}
