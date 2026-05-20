<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Hash;
use Webkul\BagistoApi\Admin\Dto\AdminLoginInput;
use Webkul\User\Models\Admin;

/**
 * Admin login — REST POST /api/admin/login and GraphQL createAdminLogin.
 *
 * Mirrors Bagisto core SessionController: validates email/password, rejects
 * inactive admins (`status = 0`). On success issues a Sanctum personal access
 * token on the Admin model (tokenable_type = Admin).
 */
class AdminLoginProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        if (! $data instanceof AdminLoginInput) {
            $data = new AdminLoginInput(
                (string) (request()->input('email') ?? ''),
                (string) (request()->input('password') ?? ''),
            );
        }

        $email = trim((string) $data->email);
        $password = (string) $data->password;

        if ($email === '' || $password === '') {
            return $this->fail(__('bagistoapi::admin.login.credentials-required'));
        }

        $admin = Admin::where('email', $email)->first();

        if (! $admin || ! Hash::check($password, $admin->password)) {
            return $this->fail(__('bagistoapi::admin.login.invalid-credentials'));
        }

        if (! $admin->status) {
            return $this->fail(__('bagistoapi::admin.login.account-inactive'));
        }

        $token = $admin->createToken('admin-api')->plainTextToken;

        return (object) [
            'id'      => $admin->id,
            '_id'     => $admin->id,
            'name'    => $admin->name,
            'email'   => $admin->email,
            'token'   => $token,
            'success' => true,
            'message' => __('bagistoapi::admin.login.successful'),
        ];
    }

    /**
     * Build a failed-login response object.
     */
    protected function fail(string $message): object
    {
        return (object) [
            'id'      => 0,
            '_id'     => 0,
            'name'    => null,
            'email'   => null,
            'token'   => '',
            'success' => false,
            'message' => $message,
        ];
    }
}
