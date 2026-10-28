<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminSettingsUser;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\User\Repositories\AdminRepository;

class AdminSettingsUserRevokeSessionsProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use ReadsAdminAuthInput;

    /**
     * Create a new revoke-sessions processor.
     */
    public function __construct(
        protected AdminRepository $adminRepository,
        protected AdminApiAuthService $authService,
    ) {}

    /**
     * Revoke every live session of an admin user and return the user with how many were revoked.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsUser
    {
        $actor = $this->authorizedAdmin('settings.api_sessions.delete', 'bagistoapi::app.admin.auth.errors.no-permission');

        $id = $this->idFromRequest($uriVariables, $context);

        $target = $id > 0
            ? $this->adminRepository->with('role')->find($id)
            : null;

        if (! $target) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.user.not-found'));
        }

        $revoked = $this->authService->revokeAdminSessions($actor, $target, request());

        $user = new AdminSettingsUser;
        $user->id = (int) $target->id;
        $user->name = $target->name;
        $user->email = $target->email;
        $user->role_id = $target->role_id !== null ? (int) $target->role_id : null;
        $user->role_name = $target->role?->name;
        $user->status = (int) $target->status;
        $user->image = $target->image;
        $user->image_url = $target->image ? Storage::url($target->image) : null;
        $user->created_at = $target->created_at?->toIso8601String();
        $user->updated_at = $target->updated_at?->toIso8601String();
        $user->message = trans('bagistoapi::app.admin.auth.sessions.revoked-for-admin', ['count' => $revoked]);

        return $user;
    }
}
