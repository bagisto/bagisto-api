<?php

namespace Webkul\BagistoApi\Support;

use Carbon\Carbon;

class DateOfBirth
{
    /**
     * Format a date of birth value as Y-m-d, or null when it is empty.
     */
    public static function format(mixed $value): ?string
    {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d');
    }
}
