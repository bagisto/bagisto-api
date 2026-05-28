<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRule;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\CatalogRule\Models\CatalogRule;

class AdminMarketingCatalogRuleItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.catalog-rule.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return CatalogRule::with(['channels', 'customer_groups'])->find($id);
    }

    protected function mapToDto(object $rule): AdminMarketingCatalogRule
    {
        /** @var CatalogRule $rule */
        $dto = new AdminMarketingCatalogRule;

        $dto->id = (int) $rule->id;
        $dto->name = $rule->name;
        $dto->description = $rule->description;
        $dto->startsFrom = $rule->starts_from ? (string) $rule->starts_from : null;
        $dto->endsTill = $rule->ends_till ? (string) $rule->ends_till : null;
        $dto->status = $rule->status !== null ? (int) $rule->status : null;
        $dto->sortOrder = $rule->sort_order !== null ? (int) $rule->sort_order : null;
        $dto->conditionType = $rule->condition_type !== null ? (int) $rule->condition_type : null;
        $dto->endOtherRules = $rule->end_other_rules !== null ? (int) $rule->end_other_rules : null;
        $dto->actionType = $rule->action_type;
        $dto->discountAmount = $rule->discount_amount !== null ? (float) $rule->discount_amount : null;
        $dto->conditions = is_array($rule->conditions) ? $rule->conditions : [];
        $dto->channels = $rule->channels->pluck('id')->map(fn ($v) => (int) $v)->all();
        $dto->customerGroups = $rule->customer_groups->pluck('id')->map(fn ($v) => (int) $v)->all();
        $dto->createdAt = $rule->created_at?->toIso8601String();
        $dto->updatedAt = $rule->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * Public alias used by the processor to reuse the mapping logic.
     */
    public function mapToDtoPublic(object $rule): AdminMarketingCatalogRule
    {
        return $this->mapToDto($rule);
    }
}
