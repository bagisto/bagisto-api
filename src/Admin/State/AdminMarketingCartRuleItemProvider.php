<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\CartRule\Models\CartRule;

/**
 * Detail provider. Eager-loads channels + customer_groups + primary coupon
 * so the response carries everything the admin Edit screen would show.
 */
class AdminMarketingCartRuleItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.cart-rule.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return CartRule::with(['cart_rule_channels', 'cart_rule_customer_groups', 'coupon_code'])->find($id);
    }

    protected function mapToDto(object $rule): AdminMarketingCartRule
    {
        /** @var CartRule $rule */
        $dto = new AdminMarketingCartRule;
        $dto->id = (int) $rule->id;
        $dto->name = $rule->name;
        $dto->description = $rule->description;
        $dto->startsFrom = $rule->starts_from ? \Carbon\Carbon::parse($rule->starts_from)->toIso8601String() : null;
        $dto->endsTill = $rule->ends_till ? \Carbon\Carbon::parse($rule->ends_till)->toIso8601String() : null;
        $dto->status = (int) $rule->status;
        $dto->couponType = (int) $rule->coupon_type;
        $dto->useAutoGeneration = (int) $rule->use_auto_generation;
        $dto->usagePerCustomer = (int) $rule->usage_per_customer;
        $dto->usesPerCoupon = (int) $rule->uses_per_coupon;
        $dto->timesUsed = (int) $rule->times_used;
        $dto->conditionType = (int) $rule->condition_type;
        $dto->conditions = is_array($rule->conditions) ? $rule->conditions : [];
        $dto->actionType = $rule->action_type;
        $dto->discountAmount = (float) $rule->discount_amount;
        $dto->discountQuantity = (int) $rule->discount_quantity;
        $dto->discountStep = (string) $rule->discount_step;
        $dto->applyToShipping = (int) $rule->apply_to_shipping;
        $dto->freeShipping = (int) $rule->free_shipping;
        $dto->endOtherRules = (int) $rule->end_other_rules;
        $dto->usesAttributeConditions = (int) $rule->uses_attribute_conditions;
        $dto->sortOrder = (int) $rule->sort_order;
        $dto->couponCode = optional($rule->coupon_code)->code;
        $dto->channels = $rule->cart_rule_channels->pluck('id')->map(fn ($v) => (int) $v)->all();
        $dto->customerGroups = $rule->cart_rule_customer_groups->pluck('id')->map(fn ($v) => (int) $v)->all();
        $dto->createdAt = $rule->created_at?->toIso8601String();
        $dto->updatedAt = $rule->updated_at?->toIso8601String();

        return $dto;
    }
}
