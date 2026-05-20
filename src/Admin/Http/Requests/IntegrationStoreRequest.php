<?php

namespace Webkul\BagistoApi\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;

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
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'admin_id'        => [
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
            'permissions'     => ['nullable', 'array'],
            'permissions.*'   => ['string'],
        ];
    }
}
