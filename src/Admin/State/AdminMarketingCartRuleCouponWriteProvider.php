<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleCoupon;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Placeholder provider so API Platform can route DELETE through the processor
 * without trying to load a real Eloquent entity.
 */
class AdminMarketingCartRuleCouponWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminMarketingCartRuleCoupon
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminMarketingCartRuleCoupon;
        $placeholder->id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $placeholder->cartRuleId = isset($uriVariables['cartRuleId']) ? (int) $uriVariables['cartRuleId'] : null;

        return $placeholder;
    }
}
