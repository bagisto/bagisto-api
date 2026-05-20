<?php

namespace Webkul\BagistoApi\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
