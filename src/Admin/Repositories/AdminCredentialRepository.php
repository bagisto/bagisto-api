<?php

namespace Webkul\BagistoApi\Admin\Repositories;

use Closure;
use Webkul\Core\Eloquent\Repository;
use Webkul\User\Contracts\Admin as AdminContract;
use Webkul\User\Models\Admin;

class AdminCredentialRepository extends Repository
{
    /**
     * Specify the model contract.
     */
    public function model(): string
    {
        return AdminContract::class;
    }

    /**
     * Run a callback in a transaction with the admin row locked and freshly read, so its second factor is used once.
     */
    public function withLockedAdmin(int $adminId, Closure $callback): mixed
    {
        return $this->model->getConnection()->transaction(fn () => $callback(
            $this->model->newQuery()
                ->lockForUpdate()
                ->find($adminId)
        ));
    }

    /**
     * Store a new password hash with a fresh remember token, which the admin model does not mass-assign.
     */
    public function resetPassword(int $adminId, string $passwordHash, string $rememberToken): Admin
    {
        $admin = $this->model->newQuery()->findOrFail($adminId);

        $admin->forceFill([
            'password' => $passwordHash,
            $admin->getRememberTokenName() => $rememberToken,
        ])->save();

        return $admin;
    }
}
