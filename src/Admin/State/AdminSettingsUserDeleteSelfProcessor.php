<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsUserDeleteSelfInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsUserDeleteSelf;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\User\Models\Admin;

/**
 * POST /api/admin/settings/users/delete-self + createAdminSettingsUserDeleteSelf.
 *
 * Mirrors Webkul\Admin UserController::destroySelf — deletes the authenticated
 * admin's OWN account after re-confirming their password. No ACL permission gate
 * (parity with core: the password IS the gate). Refuses the last remaining admin.
 */
class AdminSettingsUserDeleteSelfProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $password = $this->resolvePassword($data, $context);

        if ($password === null || $password === '') {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.self-delete-password-required'), 422);
        }

        if (! Hash::check($password, $admin->password)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.self-delete-incorrect-password'), 422);
        }

        if (Admin::count() <= 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.cannot-delete-last-admin'), 400);
        }

        $id = $admin->id;

        try {
            Event::dispatch('user.admin.delete.before', $id);

            Admin::find($id)?->delete();

            Event::dispatch('user.admin.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.delete-failed'), 500);
        }

        $result = new AdminSettingsUserDeleteSelf;
        $result->id = $id;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.settings.user.self-deleted');

        return $result;
    }

    protected function resolvePassword(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsUserDeleteSelfInput && $data->password !== null) {
            return $data->password;
        }

        $fromArgs = $context['args']['input']['password'] ?? $context['args']['password'] ?? null;
        if ($fromArgs !== null) {
            return (string) $fromArgs;
        }

        $fromBody = request()->input('password');

        return $fromBody !== null ? (string) $fromBody : null;
    }
}
