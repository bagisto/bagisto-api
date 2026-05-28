<?php

namespace Webkul\BagistoApi\Admin\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates an IPv4 or IPv6 address — with or without a CIDR prefix.
 *
 * Examples:
 *   192.168.1.1        ✔ IPv4
 *   2001:db8::1        ✔ IPv6
 *   10.0.0.0/24        ✔ IPv4 + CIDR
 *   2001:db8::/32      ✔ IPv6 + CIDR
 *   10.0.0.0/40        ✘ invalid prefix for IPv4
 *   not-an-ip          ✘ invalid
 */
class IpOrCidr implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
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

        if ($prefix < 0 || $prefix > $maxPrefix) {
            $fail(trans('bagistoapi::app.admin.integration.validation.cidr-prefix-invalid'));
        }
    }
}
