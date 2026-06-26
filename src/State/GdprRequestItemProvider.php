<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\GdprRequest;
use Webkul\BagistoApi\State\Concerns\GdprFeatureGate;

class GdprRequestItemProvider implements ProviderInterface
{
    use GdprFeatureGate;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->assertGdprEnabled();

        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.gdpr.unauthenticated'));
        }

        $id = $uriVariables['id'] ?? null;

        if (is_string($id) && str_contains($id, '/')) {
            $id = basename($id);
        }

        if (! is_numeric($id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.not-found'), 404);
        }

        $gdprRequest = GdprRequest::where('id', (int) $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $gdprRequest) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.gdpr.not-found'), 404);
        }

        return $gdprRequest;
    }
}
