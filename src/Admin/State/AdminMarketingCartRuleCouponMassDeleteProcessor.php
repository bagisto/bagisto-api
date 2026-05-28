<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCouponMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleCouponMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Models\CartRule;
use Webkul\CartRule\Models\CartRuleCoupon;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;

/**
 * Mass-delete coupons for a cart rule. IDs not belonging to the named
 * cart_rule are silently skipped (cross-rule isolation).
 */
class AdminMarketingCartRuleCouponMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        protected CartRuleCouponRepository $couponRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin);

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        $cartRuleId = (int) (
            $uriVariables['cartRuleId']
            ?? request()->route('cartRuleId')
            ?? ($context['args']['input']['cartRuleId'] ?? null)
            ?? ($context['args']['input']['cart_rule_id'] ?? null)
            ?? 0
        );

        if ($isGraphQL) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            if ($cartRuleId <= 0 && isset($rawArgs['cartRuleId'])) {
                $cartRuleId = (int) $rawArgs['cartRuleId'];
            }
            $indices = $rawArgs['indices'] ?? ($data instanceof AdminMarketingCartRuleCouponMassDeleteInput ? ($data->indices ?? []) : []);
        } else {
            $indices = (array) (request()->input('indices') ?? []);
        }

        if (! is_array($indices) || empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.indices-required'), 422);
        }

        if ($cartRuleId <= 0 || ! CartRule::find($cartRuleId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.cart-rule-not-found'));
        }

        $indices = array_values(array_unique(array_map('intval', $indices)));

        $ownedIds = CartRuleCoupon::where('cart_rule_id', $cartRuleId)
            ->whereIn('id', $indices)
            ->pluck('id')
            ->all();

        $skipped = array_values(array_diff($indices, $ownedIds));

        foreach ($ownedIds as $id) {
            $coupon = CartRuleCoupon::find($id);
            if (! $coupon) {
                continue;
            }
            Event::dispatch('cart_rules.coupons.delete.before', $coupon);
            $this->couponRepository->delete($id);
            Event::dispatch('cart_rules.coupons.delete.after', $coupon);
        }

        $dto = new AdminMarketingCartRuleCouponMassDelete;
        $dto->id = $cartRuleId;
        $dto->cartRuleId = $cartRuleId;
        $dto->deleted = count($ownedIds);
        $dto->skipped = $skipped;
        $dto->success = true;
        $dto->message = __('bagistoapi::app.admin.marketing.cart-rule-coupon.mass-deleted', ['count' => $dto->deleted]);

        if ($isGraphQL) {
            return $dto;
        }

        return new JsonResponse(array_filter([
            'id'         => $dto->id,
            'cartRuleId' => $dto->cartRuleId,
            'deleted'    => $dto->deleted,
            'skipped'    => $dto->skipped,
            'success'    => $dto->success,
            'message'    => $dto->message,
        ], static fn ($v) => $v !== null), 200);
    }

    protected function assertPermission(object $admin): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.no-permission'));
        }
        if (($role->permission_type ?? null) === 'all') {
            return;
        }
        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }
        if (! in_array('marketing.promotions.cart_rules.delete', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.no-permission'));
        }
    }
}
