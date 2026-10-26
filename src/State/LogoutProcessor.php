<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Webkul\BagistoApi\Models\CustomerLogout;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\BagistoApi\Support\CurrentCustomer;

class LogoutProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(protected CustomerApiAuthService $authService) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $customer = CurrentCustomer::user();

        $session = CurrentCustomer::session();

        if (
            ! $customer
            || ! $session
        ) {
            return $this->output([
                'success' => false,
                'message' => __('bagistoapi::app.graphql.logout.unauthenticated'),
            ]);
        }

        try {
            $deviceToken = $data->deviceToken ?? null;
            if ($deviceToken) {
                Event::dispatch('bagistoapi.customer.device-token.delete', [
                    'customerId' => $customer->id,
                    'deviceToken' => $deviceToken,
                ]);
            }

            if (Schema::hasColumn('customers', 'device_token')) {
                $customer->forceFill(['device_token' => null]);
                $customer->save();
            }

            $this->authService->logout($session);

            return $this->output([
                'success' => true,
                'message' => __('bagistoapi::app.graphql.logout.logged-out-successfully'),
            ]);

        } catch (\Exception $exception) {
            return $this->output([
                'success' => false,
                'message' => __('bagistoapi::app.graphql.logout.error-during-logout'),
            ]);
        }
    }

    /**
     * Output.
     */
    private function output(array $data): CustomerLogout
    {
        $output = new CustomerLogout;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
