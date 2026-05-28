<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Models\CartRule;
use Webkul\CartRule\Models\CartRuleCoupon;

/**
 * Lists all coupons for a given cart rule. Wraps results in the standard admin
 * `{ data, meta }` envelope through the framework's PaginatorInterface.
 */
class AdminMarketingCartRuleCouponCollectionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $cartRuleId = (int) (
            $uriVariables['cartRuleId']
            ?? $context['args']['cartRuleId']
            ?? $context['args']['cart_rule_id']
            ?? request()->route('cartRuleId')
            ?? 0
        );

        if ($cartRuleId <= 0 && ! empty($context['args']) && is_array($context['args'])) {
            foreach ($context['args'] as $v) {
                if (is_array($v) && ! empty($v['cartRuleId'])) {
                    $cartRuleId = (int) $v['cartRuleId'];
                    break;
                }
            }
        }

        if ($cartRuleId <= 0 || ! CartRule::find($cartRuleId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.cart-rule-not-found'));
        }

        $rows = CartRuleCoupon::where('cart_rule_id', $cartRuleId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($coupon) => AdminMarketingCartRuleCouponProcessor::toDto($coupon))
            ->all();

        $total = count($rows);
        $perPage = max($total, 1);

        return new Paginator(new LengthAwarePaginator($rows, $total, $perPage, 1));
    }
}
