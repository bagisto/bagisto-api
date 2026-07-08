<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleRestDto;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/cart-rules + adminMarketingCartRules GraphQL query.
 *
 * Branches: GraphQL → forceFilled AdminMarketingCartRule Eloquent rows (channels /
 * customerGroups connections set empty — detail-only, no per-row N+1); REST → the
 * flat AdminMarketingCartRuleRestDto.
 *
 * Listing rows omit conditions, channels, customerGroups (detail-only) — keeps the
 * listing query cheap. The primary coupon code IS surfaced via a subquery.
 */
class AdminMarketingCartRuleCollectionProvider extends AbstractAdminCollectionProvider
{
    protected bool $listingIsGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'name', 'sort_order'];
    }

    protected function buildQuery(array $args)
    {
        $p = DB::getTablePrefix();

        return DB::table('cart_rules')->select(
            'id', 'name', 'description', 'starts_from', 'ends_till',
            'status', 'coupon_type', 'use_auto_generation',
            'usage_per_customer', 'uses_per_coupon', 'times_used',
            'condition_type', 'action_type', 'discount_amount',
            'discount_quantity', 'discount_step', 'apply_to_shipping',
            'free_shipping', 'end_other_rules', 'uses_attribute_conditions',
            'sort_order', 'created_at', 'updated_at',
        )->selectRaw('(SELECT code FROM '.$p.'cart_rule_coupons WHERE '.$p.'cart_rule_coupons.cart_rule_id = '.$p.'cart_rules.id AND '.$p.'cart_rule_coupons.is_primary = 1 LIMIT 1) as coupon_code');
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $ids = is_array($args['id'])
                ? $args['id']
                : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if ($ids) {
                $query->whereIn('cart_rules.id', $ids);
            }
        }

        if (! empty($args['name'])) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['coupon_code'])) {
            $code = $args['coupon_code'];
            $query->whereExists(function ($sub) use ($code) {
                $sub->select(DB::raw(1))
                    ->from('cart_rule_coupons')
                    ->whereColumn('cart_rule_coupons.cart_rule_id', 'cart_rules.id')
                    ->where('cart_rule_coupons.code', 'like', '%'.$code.'%');
            });
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('status', (int) $args['status']);
        }

        if (isset($args['coupon_type']) && $args['coupon_type'] !== '' && $args['coupon_type'] !== null) {
            $query->where('coupon_type', (int) $args['coupon_type']);
        }

        if (isset($args['sort_order']) && $args['sort_order'] !== '' && $args['sort_order'] !== null) {
            $query->where('sort_order', (int) $args['sort_order']);
        }

        if (! empty($args['starts_from_from'])) {
            $query->where('starts_from', '>=', $args['starts_from_from']);
        }
        if (! empty($args['starts_from_to'])) {
            $query->where('starts_from', '<=', $args['starts_from_to']);
        }
        if (! empty($args['ends_till_from'])) {
            $query->where('ends_till', '>=', $args['ends_till_from']);
        }
        if (! empty($args['ends_till_to'])) {
            $query->where('ends_till', '<=', $args['ends_till_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);
        $columnMap = ['id' => 'id', 'name' => 'name', 'sort_order' => 'sort_order'];
        $query->orderBy($columnMap[$column] ?? 'id', $direction);
    }

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminMarketingCartRuleRestDto;
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
        $dto->couponCode = $row->coupon_code ?? null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        // channels / customerGroups / conditions are detail-only — null on list rows.

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminMarketingCartRule. The channels /
     * customerGroups connections are set empty (detail-only — no per-row N+1).
     * The primary coupon_code is forceFilled (the accessor reads it from the
     * attribute bag as a no-N+1 fast-path).
     */
    protected function mapRowToEloquent(object $row): AdminMarketingCartRule
    {
        $model = (new AdminMarketingCartRule)->forceFill([
            'id' => (int) $row->id,
            'name' => $row->name,
            'description' => $row->description,
            'starts_from' => $row->starts_from,
            'ends_till' => $row->ends_till,
            'status' => $row->status,
            'coupon_type' => $row->coupon_type,
            'use_auto_generation' => $row->use_auto_generation,
            'usage_per_customer' => $row->usage_per_customer,
            'uses_per_coupon' => $row->uses_per_coupon,
            'times_used' => $row->times_used,
            'condition_type' => $row->condition_type,
            'action_type' => $row->action_type,
            'discount_amount' => $row->discount_amount,
            'discount_quantity' => $row->discount_quantity,
            'discount_step' => $row->discount_step,
            'apply_to_shipping' => $row->apply_to_shipping,
            'free_shipping' => $row->free_shipping,
            'end_other_rules' => $row->end_other_rules,
            'uses_attribute_conditions' => $row->uses_attribute_conditions,
            'sort_order' => $row->sort_order,
            'coupon_code' => $row->coupon_code ?? null,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $model->setRelation('channels', collect());
        $model->setRelation('customer_groups', collect());

        return $model;
    }
}
