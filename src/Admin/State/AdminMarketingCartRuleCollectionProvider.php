<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/cart-rules + adminMarketingCartRules GraphQL query.
 *
 * Listing rows are slim (no conditions, no channels/customer_groups pivots) —
 * those load only on detail.
 */
class AdminMarketingCartRuleCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name', 'sort_order'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('cart_rules')->select(
            'id', 'name', 'description', 'starts_from', 'ends_till',
            'status', 'coupon_type', 'use_auto_generation',
            'usage_per_customer', 'uses_per_coupon', 'times_used',
            'condition_type', 'action_type', 'discount_amount',
            'discount_quantity', 'discount_step', 'apply_to_shipping',
            'free_shipping', 'end_other_rules', 'uses_attribute_conditions',
            'sort_order', 'created_at', 'updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }
        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('status', (int) $args['status']);
        }
        if (isset($args['coupon_type']) && $args['coupon_type'] !== '' && $args['coupon_type'] !== null) {
            $query->where('coupon_type', (int) $args['coupon_type']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);
        $columnMap = ['id' => 'id', 'name' => 'name', 'sort_order' => 'sort_order'];
        $query->orderBy($columnMap[$column] ?? 'id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingCartRule
    {
        $dto = new AdminMarketingCartRule;
        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->startsFrom = $row->starts_from ? Carbon::parse($row->starts_from)->toIso8601String() : null;
        $dto->endsTill = $row->ends_till ? Carbon::parse($row->ends_till)->toIso8601String() : null;
        $dto->status = (int) $row->status;
        $dto->couponType = (int) $row->coupon_type;
        $dto->useAutoGeneration = (int) $row->use_auto_generation;
        $dto->usagePerCustomer = (int) $row->usage_per_customer;
        $dto->usesPerCoupon = (int) $row->uses_per_coupon;
        $dto->timesUsed = (int) $row->times_used;
        $dto->conditionType = (int) $row->condition_type;
        $dto->actionType = $row->action_type;
        $dto->discountAmount = (float) $row->discount_amount;
        $dto->discountQuantity = (int) $row->discount_quantity;
        $dto->discountStep = (string) $row->discount_step;
        $dto->applyToShipping = (int) $row->apply_to_shipping;
        $dto->freeShipping = (int) $row->free_shipping;
        $dto->endOtherRules = (int) $row->end_other_rules;
        $dto->usesAttributeConditions = (int) $row->uses_attribute_conditions;
        $dto->sortOrder = (int) $row->sort_order;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
