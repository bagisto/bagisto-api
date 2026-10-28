<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\GuestEuWithdrawalConfirmation;
use Webkul\BagistoApi\State\GuestEuWithdrawalConfirmationProvider;

class GuestEuWithdrawalConfirmationQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(protected GuestEuWithdrawalConfirmationProvider $provider) {}

    /**
     * Resolve a guest withdrawal confirmation from its uuid and order email.
     */
    public function __invoke(?object $item, array $context): GuestEuWithdrawalConfirmation
    {
        return $this->provider->provideForProof(
            isset($context['args']['uuid']) ? (string) $context['args']['uuid'] : null,
            isset($context['args']['email']) ? (string) $context['args']['email'] : null,
        );
    }
}
