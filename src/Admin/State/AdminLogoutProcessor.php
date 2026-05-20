<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Admin\Dto\AdminLogoutInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;

/**
 * Admin logout — revokes the current admin token, or every token when
 * the request sets `all = true`.
 */
class AdminLogoutProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::admin.logout.unauthenticated'),
            ];
        }

        $all = false;

        if ($data instanceof AdminLogoutInput) {
            $all = (bool) $data->all;
        } elseif (isset($context['args']['input']['all'])) {
            $all = (bool) $context['args']['input']['all'];
        } elseif (request()->has('all')) {
            $all = (bool) request()->input('all');
        }

        if ($all) {
            $admin->tokens()->delete();

            return (object) [
                'success' => true,
                'message' => __('bagistoapi::admin.logout.all-success'),
            ];
        }

        $token = $admin->currentAccessToken();

        if (! $token) {
            $token = PersonalAccessToken::findToken((string) AdminAuthHelper::bearerToken());
        }

        if (! $token) {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::admin.logout.token-not-found'),
            ];
        }

        $token->delete();

        return (object) [
            'success' => true,
            'message' => __('bagistoapi::admin.logout.success'),
        ];
    }
}
