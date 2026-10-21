<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminForgotPassword;
use Webkul\BagistoApi\Admin\Models\AdminResetPassword;
use Webkul\BagistoApi\Admin\Services\AdminApiPasswordService;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;

class AdminPasswordProcessor implements ProcessorInterface
{
    use ReadsAdminAuthInput;

    /**
     * Create a new admin password processor.
     */
    public function __construct(protected AdminApiPasswordService $passwordService) {}

    /**
     * Send a reset link for forgot-password, or reset the password for reset-password, over REST or GraphQL.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminForgotPassword|AdminResetPassword
    {
        $input = $this->authInput($operation, $context);

        if ($operation->getClass() === AdminResetPassword::class) {
            return $this->reset($input);
        }

        return $this->forgot($input);
    }

    /**
     * Send the reset link and answer the same way whether or not the email belongs to an admin.
     */
    protected function forgot(array $input): AdminForgotPassword
    {
        $input = $this->validateAuthInput($input, [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $this->passwordService->sendResetLink($input['email'], request());

        $result = new AdminForgotPassword;
        $result->success = true;
        $result->message = trans('admin::app.users.forget-password.create.reset-link-sent');

        return $result;
    }

    /**
     * Reset the password with the emailed token, with core's rules.
     */
    protected function reset(array $input): AdminResetPassword
    {
        $input = $this->validateAuthInput($input, [
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', 'min:6'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $this->passwordService->reset($input, request());

        $result = new AdminResetPassword;
        $result->success = true;
        $result->message = trans('bagistoapi::app.admin.auth.password.reset');

        return $result;
    }
}
