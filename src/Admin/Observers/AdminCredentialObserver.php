<?php

namespace Webkul\BagistoApi\Admin\Observers;

use Webkul\BagistoApi\Admin\Services\AdminApiCredentialRevoker;
use Webkul\User\Models\Admin;

class AdminCredentialObserver
{
    /**
     * Create a new admin credential observer.
     */
    public function __construct(protected AdminApiCredentialRevoker $revoker) {}

    /**
     * Revoke API credentials when an admin's password changes or the admin is deactivated, from any screen.
     */
    public function updated(Admin $admin): void
    {
        if ($admin->wasChanged('password')) {
            $this->revoker->passwordChanged($admin);
        }

        if (
            $admin->wasChanged('status')
            && ! $admin->status
        ) {
            $this->revoker->deactivated($admin);
        }
    }
}
