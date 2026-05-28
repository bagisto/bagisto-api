<?php

namespace Webkul\BagistoApi\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\BagistoApi\Admin\Rules\IpOrCidr;

class IntegrationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'permission_type'       => ['required', 'in:all,custom,same_as_web'],
            'permissions'           => ['nullable', 'array'],
            'permissions.*'         => ['string'],

            'expires_mode'          => ['nullable', 'in:never,expires'],
            'expires_at'            => ['nullable', 'date', 'after:today', 'required_if:expires_mode,expires'],

            'rate_min_mode'         => ['nullable', 'in:unlimited,limited'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'required_if:rate_min_mode,limited'],

            'rate_day_mode'         => ['nullable', 'in:unlimited,limited'],
            'rate_limit_per_day'    => ['nullable', 'integer', 'min:1', 'required_if:rate_day_mode,limited'],

            'ip_mode'               => ['nullable', 'in:any,restricted'],
            'allowed_ips'           => ['nullable', 'array'],
            'allowed_ips.*'         => ['string', new IpOrCidr],
        ];
    }

    /**
     * Map the "one entry per line" textarea (from the Blade form) into the
     * validated `allowed_ips` array. When `ip_mode=any` (or the user clears
     * the list), normalise to an empty array so the service writes NULL.
     */
    protected function prepareForValidation(): void
    {
        $mode = $this->input('ip_mode');

        if ($mode === 'any') {
            $this->merge(['allowed_ips' => []]);

            return;
        }

        $raw = $this->input('allowed_ips_text');

        if ($raw !== null && ! $this->has('allowed_ips')) {
            $entries = preg_split('/[\r\n,]+/', (string) $raw) ?: [];
            $entries = array_values(array_unique(array_filter(array_map('trim', $entries))));

            $this->merge(['allowed_ips' => $entries]);
        }
    }
}
