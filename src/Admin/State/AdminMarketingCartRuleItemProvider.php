<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Models\CartRule;

/**
 * Cart rule detail — GET /api/admin/marketing/cart-rules/{id} +
 * adminMarketingCartRule.
 *
 * Branches: GraphQL → the AdminMarketingCartRule Eloquent model (channels /
 * customerGroups connections resolve); REST → the flat
 * AdminMarketingCartRuleRestDto built from the core CartRule (channels /
 * customer_groups as object arrays).
 */
class AdminMarketingCartRuleItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminMarketingCartRule|AdminMarketingCartRuleRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminMarketingCartRule::with(['channels', 'customer_groups'])->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule.not-found'));
            }

            return $model;
        }

        $rule = CartRule::with(['cart_rule_channels', 'cart_rule_customer_groups', 'coupon_code'])->find($id);

        if (! $rule) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule.not-found'));
        }

        return $this->buildRestDto($rule);
    }

    /**
     * Public alias used by the processor / copy processor to reuse the REST
     * mapping logic.
     */
    public function buildRestDtoPublic(object $rule): AdminMarketingCartRuleRestDto
    {
        return $this->buildRestDto($rule);
    }

    /** Public alias of the Eloquent lookup for the copy processor. */
    public function findEntityPublic(int $id): ?object
    {
        return CartRule::with(['cart_rule_channels', 'cart_rule_customer_groups', 'coupon_code'])->find($id);
    }

    protected function buildRestDto(object $rule): AdminMarketingCartRuleRestDto
    {
        /** @var CartRule $rule */
        $dto = new AdminMarketingCartRuleRestDto;

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
        $dto->couponCode = DB::table('cart_rule_coupons')
            ->where('cart_rule_id', $rule->id)
            ->where('is_primary', 1)
            ->value('code');

        $dto->channels = $rule->cart_rule_channels->map(fn ($c) => [
            'id'   => (int) $c->id,
            'code' => $c->code,
            'name' => $c->name,
        ])->values()->all();

        $dto->customerGroups = $rule->cart_rule_customer_groups->map(fn ($g) => [
            'id'   => (int) $g->id,
            'code' => $g->code,
            'name' => $g->name,
        ])->values()->all();

        $dto->createdAt = $rule->created_at?->toIso8601String();
        $dto->updatedAt = $rule->updated_at?->toIso8601String();

        return $dto;
    }
}
