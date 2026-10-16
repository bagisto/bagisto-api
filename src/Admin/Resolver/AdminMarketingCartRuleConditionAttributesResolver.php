<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleConditionAttributes;
use Webkul\BagistoApi\Admin\State\AdminMarketingRuleConditionAttributesProvider;

class AdminMarketingCartRuleConditionAttributesResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminMarketingRuleConditionAttributesProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminMarketingCartRuleConditionAttributes
    {
        return $this->provider->build(AdminMarketingCartRuleConditionAttributes::class);
    }
}
