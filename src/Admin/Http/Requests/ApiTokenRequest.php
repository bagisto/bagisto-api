<?php

namespace Webkul\BagistoApi\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Admin\Services\DomainBinding;
use Webkul\User\Repositories\AdminRepository;

class ApiTokenRequest extends FormRequest
{
    /**
     * The expiry mode of a token that expires at the end of a chosen day.
     */
    public const EXPIRES_ON_DATE = 'date';

    /**
     * The expiry mode of a token that never expires.
     */
    public const EXPIRES_NEVER = 'never';

    /**
     * Determine if the user is authorized to make this request; the ACL and the escalation rule decide it.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The rules for creating a token, or for renaming one on an update.
     */
    public function rules(): array
    {
        $nameRules = ['required', 'string', 'max:'.AdminApiTokenIssuer::NAME_MAX_LENGTH];

        if ($this->isMethod('put')) {
            return ['name' => $nameRules];
        }

        $expiryModes = app(AdminApiAuthSettings::class)->allowNonExpiringApiTokens()
            ? [self::EXPIRES_ON_DATE, self::EXPIRES_NEVER]
            : [self::EXPIRES_ON_DATE];

        return [
            'name' => $nameRules,

            'admin_id' => [
                'required',
                'integer',
                'exists:admins,id',
                function ($attribute, $value, $fail) {
                    $owner = app(AdminRepository::class)->with('role')->find($value);

                    if (
                        $owner
                        && ! app(AdminApiTokenIssuer::class)->canCreateFor(auth()->guard('admin')->user(), $owner)
                    ) {
                        $fail(trans('bagistoapi::app.admin.api-tokens.errors.cannot-create-for-admin'));
                    }
                },
            ],

            'domain' => [
                'required',
                'string',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if (app(DomainBinding::class)->normalize((string) $value) === null) {
                        $fail(trans('bagistoapi::app.admin.api-tokens.errors.invalid-domain'));
                    }
                },
            ],

            'expires_mode' => ['required', Rule::in($expiryModes)],
            'expires_at' => ['nullable', 'required_if:expires_mode,'.self::EXPIRES_ON_DATE, 'date_format:Y-m-d', 'after:today'],
            'no_expiry_confirmed' => ['accepted_if:expires_mode,'.self::EXPIRES_NEVER],
        ];
    }

    /**
     * The translated field names used in the validation messages.
     */
    public function attributes(): array
    {
        return [
            'name' => trans('bagistoapi::app.admin.api-tokens.fields.name'),
            'admin_id' => trans('bagistoapi::app.admin.api-tokens.fields.admin'),
            'domain' => trans('bagistoapi::app.admin.api-tokens.fields.domain'),
            'expires_mode' => trans('bagistoapi::app.admin.api-tokens.fields.expiry'),
            'expires_at' => trans('bagistoapi::app.admin.api-tokens.fields.expires-at'),
            'no_expiry_confirmed' => trans('bagistoapi::app.admin.api-tokens.fields.no-expiry-confirmation'),
        ];
    }

    /**
     * The chosen expiry, the end of the chosen day, or null for a token that never expires.
     */
    public function expiresAt(): ?Carbon
    {
        if ($this->input('expires_mode') !== self::EXPIRES_ON_DATE) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', (string) $this->input('expires_at'))->endOfDay();
    }
}
