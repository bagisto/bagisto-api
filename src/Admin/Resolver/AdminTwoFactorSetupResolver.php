<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminTwoFactor;
use Webkul\BagistoApi\Admin\State\AdminTwoFactorProvider;

class AdminTwoFactorSetupResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminTwoFactorProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminTwoFactor
    {
        return $this->provider->setup();
    }
}
