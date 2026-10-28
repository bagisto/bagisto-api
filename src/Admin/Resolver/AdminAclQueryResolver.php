<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminAcl;
use Webkul\BagistoApi\Admin\State\AdminAclProvider;

class AdminAclQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminAclProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminAcl
    {
        return $this->provider->build();
    }
}
