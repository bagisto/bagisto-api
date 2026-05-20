<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\User\Models\Admin;

/**
 * Admin profile update — REST POST /api/admin/update and GraphQL mutation.
 *
 * Mirrors Bagisto core AccountController: `currentPassword` is required for any
 * update; a password change additionally requires a matching `confirmPassword`.
 * Email must stay unique across the `admins` table.
 *
 * Field values are read from the GraphQL args / REST request body directly
 * (camelCase) rather than the denormalized DTO — API Platform's snake_case
 * name converter does not map multi-word camelCase JSON keys onto the DTO.
 */
class AdminProfileProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $input = $this->input($context);

        $currentPassword = $input['currentPassword'] ?? null;

        if (empty($currentPassword) || ! Hash::check($currentPassword, $admin->password)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.profile.current-password-incorrect'));
        }

        $update = [];

        if (! empty($input['name'])) {
            $update['name'] = $input['name'];
        }

        if (! empty($input['email'])) {
            $email = $input['email'];

            if ($email !== $admin->email
                && Admin::where('email', $email)->where('id', '!=', $admin->id)->exists()
            ) {
                throw new InvalidInputException(__('bagistoapi::app.admin.profile.email-taken'));
            }

            $update['email'] = $email;
        }

        $passwordChanged = false;

        if (! empty($input['password'])) {
            if (($input['confirmPassword'] ?? null) !== $input['password']) {
                throw new InvalidInputException(__('bagistoapi::app.admin.profile.password-mismatch'));
            }

            $update['password'] = Hash::make($input['password']);
            $passwordChanged = true;
        }

        if (! empty($update)) {
            $admin->update($update);

            if ($passwordChanged) {
                Event::dispatch('admin.password.update.after', $admin);
            }
        }

        $admin->refresh();

        return (object) [
            'id'      => (string) $admin->id,
            'name'    => $admin->name,
            'email'   => $admin->email,
            'success' => true,
            'message' => __('bagistoapi::app.admin.profile.updated'),
        ];
    }

    /**
     * Normalized camelCase input — GraphQL args take precedence over the
     * REST request body.
     */
    protected function input(array $context): array
    {
        if (isset($context['args']['input']) && \is_array($context['args']['input'])) {
            return $context['args']['input'];
        }

        return request()->all();
    }
}
