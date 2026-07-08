<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRule;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CatalogRule\Models\CatalogRule;

/**
 * Catalog rule detail — GET /api/admin/marketing/catalog-rules/{id} +
 * adminMarketingCatalogRule.
 *
 * Branches: GraphQL → the AdminMarketingCatalogRule Eloquent model (channels /
 * customerGroups connections resolve); REST → the flat
 * AdminMarketingCatalogRuleRestDto built from the core CatalogRule (channels /
 * customer_groups as object arrays).
 */
class AdminMarketingCatalogRuleItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminMarketingCatalogRule|AdminMarketingCatalogRuleRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.catalog-rule.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminMarketingCatalogRule::with(['channels', 'customer_groups'])->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.catalog-rule.not-found'));
            }

            return $model;
        }

        $rule = CatalogRule::with(['channels', 'customer_groups'])->find($id);

        if (! $rule) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.catalog-rule.not-found'));
        }

        return $this->buildRestDto($rule);
    }

    /**
     * Public alias used by the processor to reuse the REST mapping logic.
     */
    public function buildRestDtoPublic(object $rule): AdminMarketingCatalogRuleRestDto
    {
        return $this->buildRestDto($rule);
    }

    protected function buildRestDto(object $rule): AdminMarketingCatalogRuleRestDto
    {
        /** @var CatalogRule $rule */
        $dto = new AdminMarketingCatalogRuleRestDto;

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

        $dto->channels = $rule->channels->map(fn ($c) => [
            'id' => (int) $c->id,
            'code' => $c->code,
            'name' => $c->name,
        ])->values()->all();

        $dto->customerGroups = $rule->customer_groups->map(fn ($g) => [
            'id' => (int) $g->id,
            'code' => $g->code,
            'name' => $g->name,
        ])->values()->all();

        $dto->createdAt = $rule->created_at?->toIso8601String();
        $dto->updatedAt = $rule->updated_at?->toIso8601String();

        return $dto;
    }
}
