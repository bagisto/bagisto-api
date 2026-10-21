<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Models\AdminAccountUpdate;
use Webkul\BagistoApi\Admin\Services\AdminApiCredentialRevoker;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Admin\State\Concerns\RequiresAdminSession;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

class AdminAccountUpdateProcessor implements ProcessorInterface
{
    use ReadsAdminAuthInput;
    use RequiresAdminSession;

    /**
     * Create a new account update processor.
     */
    public function __construct(
        protected AdminRepository $adminRepository,
        protected AdminApiCredentialRevoker $revoker,
        protected AdminApiAuthAuditor $auditor,
    ) {}

    /**
     * Update the signed-in admin's name, email and password with core's rules, over REST or GraphQL.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminAccountUpdate
    {
        $credential = $this->sessionCredential();

        $admin = $credential->admin;

        $input = $this->validateAuthInput($this->authInput($operation, $context), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
            'current_password' => ['required', 'string', 'min:6'],
        ]);

        if (! Hash::check($input['current_password'], $admin->password)) {
            throw new AdminAuthException(
                trans('admin::app.account.edit.invalid-password'),
                AdminAuthException::CODE_INVALID_CURRENT_PASSWORD,
                422,
            );
        }

        $changes = array_filter([
            'name' => $input['name'],
            'email' => $input['email'] ?? null,
            'password' => empty($input['password']) ? null : Hash::make($input['password']),
        ], fn ($value) => $value !== null);

        $passwordChanged = isset($changes['password']);

        Event::dispatch('bagistoapi.admin.account.update.before', $admin);

        $updated = $this->revoker->preserving(
            $credential->session->id,
            fn () => $this->adminRepository->update($changes, $admin->id),
        );

        $revocation = $this->revoker->lastRevocation();

        if ($passwordChanged) {
            Event::dispatch('admin.password.update.after', $updated);
        }

        Event::dispatch('bagistoapi.admin.account.update.after', $updated);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_ACCOUNT_UPDATED, $updated, $credential->session, request(), [
            'fields' => array_keys($changes),
            'password_changed' => $passwordChanged,
            'revoked_sessions' => $revocation['sessions'],
            'revoked_api_tokens' => $revocation['api_tokens'],
        ]);

        return $this->toResponse($updated, $passwordChanged, $revocation);
    }

    /**
     * The account update answer: the stored account and what the password change revoked.
     *
     * @param  array{sessions: int, api_tokens: int}  $revocation
     */
    protected function toResponse(Admin $admin, bool $passwordChanged, array $revocation): AdminAccountUpdate
    {
        $response = new AdminAccountUpdate;

        $response->id = (int) $admin->id;
        $response->name = $admin->name;
        $response->email = $admin->email;
        $response->image = $admin->image;
        $response->role_id = $admin->role_id === null ? null : (int) $admin->role_id;
        $response->role_name = $admin->role?->name;
        $response->password_changed = $passwordChanged;
        $response->revoked_sessions = $revocation['sessions'];
        $response->revoked_api_tokens = $revocation['api_tokens'];
        $response->success = true;
        $response->message = trans('admin::app.account.edit.update-success');

        return $response;
    }
}
