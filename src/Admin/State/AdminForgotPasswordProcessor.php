<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Password;
use Webkul\BagistoApi\Admin\Dto\AdminForgotPasswordInput;

/**
 * Admin forgot password — sends a reset link via the `admins` password broker.
 *
 * Mirrors Bagisto core ForgetPasswordController, which uses
 * Password::broker('admins') (table `admin_password_resets`).
 */
class AdminForgotPasswordProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $email = '';

        if ($data instanceof AdminForgotPasswordInput) {
            $email = trim((string) $data->email);
        } elseif (isset($context['args']['input']['email'])) {
            $email = trim((string) $context['args']['input']['email']);
        } else {
            $email = trim((string) (request()->input('email') ?? ''));
        }

        if ($email === '') {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.admin.forgot-password.email-required'),
            ];
        }

        try {
            $response = Password::broker('admins')->sendResetLink(['email' => $email]);

            if ($response === Password::RESET_LINK_SENT) {
                return (object) [
                    'success' => true,
                    'message' => __('bagistoapi::app.admin.forgot-password.reset-link-sent'),
                ];
            }

            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.admin.forgot-password.email-not-found'),
            ];
        } catch (\Exception $e) {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.admin.forgot-password.error'),
            ];
        }
    }
}
