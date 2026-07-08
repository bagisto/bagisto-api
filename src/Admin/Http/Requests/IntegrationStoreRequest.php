<?php

namespace Webkul\BagistoApi\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Admin\Rules\IpOrCidr;

class IntegrationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $busyAdminIds = AdminPersonalAccessToken::listed()->pluck('admin_id')->all();

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'admin_id' => [
                'required',
                'integer',
                'exists:admins,id',
                function ($attribute, $value, $fail) use ($busyAdminIds) {
                    if (in_array((int) $value, $busyAdminIds, true)) {
                        $fail(trans('bagistoapi::app.integration.errors.admin-has-token'));
                    }
                },
            ],
            'permission_type' => ['required', 'in:all,custom,same_as_web'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'allowed_ips' => ['nullable', 'array'],
            'allowed_ips.*' => ['string', new IpOrCidr],
        ];
    }

    /**
     * Normalise the incoming "one entry per line" textarea (from the Blade form)
     * into a clean array before validation runs. JSON/array clients can pass
     * `allowed_ips` directly as an array — it's left alone.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->input('allowed_ips_text');

        if ($raw !== null && ! $this->has('allowed_ips')) {
            $entries = preg_split('/[\r\n,]+/', (string) $raw) ?: [];
            $entries = array_values(array_unique(array_filter(array_map('trim', $entries))));

            $this->merge(['allowed_ips' => $entries]);
        }
    }
}
