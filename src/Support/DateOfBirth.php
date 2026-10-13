<?php

namespace Webkul\BagistoApi\Support;

use Carbon\Carbon;

/**
 * Keeps a customer's date of birth as a plain Y-m-d string.
 *
 * Core 2.5.0 casts the column to a date object, older cores return the raw string; the API
 * contract is the plain string on every supported core.
 */
class DateOfBirth
{
    /**
     * Format a date of birth value as Y-m-d, or null when it is empty.
     */
    public static function format(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d');
    }
}
