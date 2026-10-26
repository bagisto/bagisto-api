<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Helper\CustomerProfileHelper;
use Webkul\BagistoApi\Models\CustomerProfile as CustomerProfileModel;
use Webkul\BagistoApi\Support\CurrentCustomer;

class CustomerQueryResolver implements QueryItemResolverInterface
{
    /**
     * Handle the invoke magic method.
     */
    public function __invoke(?object $item, array $context): CustomerProfileModel
    {
        $customer = CurrentCustomer::user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (! CurrentCustomer::session()) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.token-not-found-or-expired'));
        }

        return CustomerProfileHelper::mapCustomerToProfile($customer);
    }
}
