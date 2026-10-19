<?php

namespace Webkul\BagistoApi\Admin\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IpOrCidr implements ValidationRule
{
    /**
     * Validate.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            ! is_string($value)
            || $value === ''
        ) {
            $fail(trans('bagistoapi::app.integration.validation.ip-invalid'));

            return;
        }

        if (! str_contains($value, '/')) {
            if (filter_var($value, FILTER_VALIDATE_IP) === false) {
                $fail(trans('bagistoapi::app.integration.validation.ip-invalid'));
            }

            return;
        }

        [$ip, $prefix] = explode('/', $value, 2);

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $fail(trans('bagistoapi::app.integration.validation.ip-invalid'));

            return;
        }

        if (! ctype_digit($prefix)) {
            $fail(trans('bagistoapi::app.admin.integration.validation.cidr-prefix-invalid'));

            return;
        }

        $prefix = (int) $prefix;

        $maxPrefix = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false ? 32 : 128;

        if (
            $prefix < 0
            || $prefix > $maxPrefix
        ) {
            $fail(trans('bagistoapi::app.admin.integration.validation.cidr-prefix-invalid'));
        }
    }
}
