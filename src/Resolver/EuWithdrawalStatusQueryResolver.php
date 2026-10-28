<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\EuWithdrawalStatus;
use Webkul\BagistoApi\State\FeatureStatusProvider;

class EuWithdrawalStatusQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): EuWithdrawalStatus
    {
        return FeatureStatusProvider::build(EuWithdrawalStatus::class);
    }
}
