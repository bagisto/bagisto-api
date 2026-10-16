<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Models\GdprData;
use Webkul\BagistoApi\State\Concerns\BuildsGdprData;
use Webkul\BagistoApi\State\Concerns\GdprFeatureGate;

/**
 * Mirrors the storefront GDPRController::htmlView for the signed-in customer.
 */
class GdprDataProvider implements ProviderInterface
{
    use BuildsGdprData;
    use GdprFeatureGate;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->build()];
    }

    public function build(): GdprData
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $this->assertGdprEnabled();

        $data = $this->gdprData($customer);

        $result = new GdprData;
        $result->id = $customer->id;
        $result->customer = $data['customer'];
        $result->addresses = $data['addresses'];
        $result->orders = $data['orders'];

        return $result;
    }
}
