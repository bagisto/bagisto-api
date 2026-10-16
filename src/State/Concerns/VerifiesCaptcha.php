<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Customer\Facades\Captcha;

/**
 * The storefront's reCAPTCHA check for login, registration, forgot password and contact-us:
 * when the merchant turns captcha on, a valid `recaptcha_token` is required.
 */
trait VerifiesCaptcha
{
    protected function assertCaptcha(mixed $data = null, array $context = []): void
    {
        if (! Captcha::isActive()) {
            return;
        }

        $token = (is_object($data) ? ($data->recaptchaToken ?? null) : null)
            ?? $context['args']['input']['recaptchaToken']
            ?? request()->input('recaptcha_token')
            ?? request()->input('recaptchaToken')
            ?? request()->header('X-Recaptcha-Token');

        $validator = Validator::make(
            ['recaptcha_token' => $token],
            Captcha::getValidations(),
            Captcha::getValidationMessages(),
        );

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }
}
