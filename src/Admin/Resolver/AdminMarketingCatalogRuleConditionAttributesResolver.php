<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRuleConditionAttributes;
use Webkul\BagistoApi\Admin\State\AdminMarketingRuleConditionAttributesProvider;

class AdminMarketingCatalogRuleConditionAttributesResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminMarketingRuleConditionAttributesProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminMarketingCatalogRuleConditionAttributes
    {
        return $this->provider->build(AdminMarketingCatalogRuleConditionAttributes::class);
    }
}
