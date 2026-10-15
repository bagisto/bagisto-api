<?php

namespace Webkul\BagistoApi\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Admin\Rules\IpOrCidr;
use Webkul\User\Models\Admin;

class IntegrationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules, rejecting admins that already hold a token or whose role the signed-in admin cannot grant.
     */
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

                    if (! AdminAuthHelper::canGrantRole(
                        auth()->guard('admin')->user(),
                        Admin::with('role')->find($value)?->role
                    )) {
                        $fail(trans('bagistoapi::app.admin.settings.user.no-permission'));
                    }
                },
            ],
            'permission_type' => ['required', 'in:all,custom,same_as_web'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_column((array) config('acl', []), 'key'))],
            'allowed_ips' => ['nullable', 'array'],
            'allowed_ips.*' => ['string', new IpOrCidr],
        ];
    }

    /**
     * Split the one-entry-per-line `allowed_ips_text` textarea into `allowed_ips` unless that array was sent directly.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->input('allowed_ips_text');

        if (
            $raw !== null
            && ! $this->has('allowed_ips')
        ) {
            $entries = preg_split('/[\r\n,]+/', (string) $raw) ?: [];
            $entries = array_values(array_unique(array_filter(array_map('trim', $entries))));

            $this->merge(['allowed_ips' => $entries]);
        }
    }
}
