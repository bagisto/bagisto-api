<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminLogout;
use Webkul\BagistoApi\Admin\Models\AdminLogoutAll;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\BagistoApi\Admin\State\Concerns\RequiresAdminSession;

class AdminLogoutProcessor implements ProcessorInterface
{
    use RequiresAdminSession;

    /**
     * Create a new logout processor.
     */
    public function __construct(protected AdminApiAuthService $authService) {}

    /**
     * Log out of the current session, or of every session for the logout-all operation.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminLogout|AdminLogoutAll
    {
        $credential = $this->sessionCredential();

        if ($operation->getClass() === AdminLogoutAll::class) {
            $result = new AdminLogoutAll;
            $result->revoked = $this->authService->logoutAll($credential->admin, $credential->session, request());
            $result->success = true;
            $result->message = __('bagistoapi::app.admin.auth.sessions.logged-out-all');

            return $result;
        }

        $this->authService->logout($credential->admin, $credential->session, request());

        $result = new AdminLogout;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.auth.sessions.logged-out');

        return $result;
    }
}
