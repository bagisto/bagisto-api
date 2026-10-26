<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Models\CustomerLogoutAll;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\BagistoApi\Support\CurrentCustomer;

class CustomerLogoutAllProcessor implements ProcessorInterface
{
    /**
     * Create a new logout-all processor.
     */
    public function __construct(protected CustomerApiAuthService $authService) {}

    /**
     * End every session of the signed-in customer.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CustomerLogoutAll
    {
        $session = CurrentCustomer::requireSession();

        $output = new CustomerLogoutAll;

        $output->revoked = $this->authService->logoutAll($session->customer);
        $output->success = true;
        $output->message = __('bagistoapi::app.graphql.auth.logged-out-all');

        return $output;
    }
}
