<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleConditionAttributes;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRuleConditionAttributes;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\CartRule\Repositories\CartRuleRepository;
use Webkul\CatalogRule\Repositories\CatalogRuleRepository;

/**
 * Condition metadata for cart and catalog rules: the attribute tree from the core repository's
 * getConditionAttributes() and the per-type operators the admin rule forms offer.
 */
class AdminMarketingRuleConditionAttributesProvider implements ProviderInterface
{
    /**
     * Operators per attribute type, as listed in the admin cart and catalog rule forms.
     */
    protected const OPERATORS = [
        'price' => ['==', '!=', '>=', '<=', '>', '<'],
        'decimal' => ['==', '!=', '>=', '<=', '>', '<'],
        'integer' => ['==', '!=', '>=', '<=', '>', '<'],
        'text' => ['==', '!=', '{}', '!{}'],
        'boolean' => ['==', '!='],
        'date' => ['==', '!=', '>=', '<=', '>', '<'],
        'datetime' => ['==', '!=', '>=', '<=', '>', '<'],
        'select' => ['==', '!='],
        'radio' => ['==', '!='],
        'multiselect' => ['{}', '!{}'],
        'checkbox' => ['{}', '!{}'],
    ];

    /**
     * Core lang key suffix for each operator; multiselect and checkbox use "contains" for {}.
     */
    protected const LABELS = [
        '==' => 'is-equal-to',
        '!=' => 'is-not-equal-to',
        '>=' => 'equals-or-greater-than',
        '<=' => 'equals-or-less-than',
        '>' => 'greater-than',
        '<' => 'less-than',
        '{}' => 'contain',
        '!{}' => 'does-not-contain',
    ];

    public function __construct(
        protected CartRuleRepository $cartRuleRepository,
        protected CatalogRuleRepository $catalogRuleRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->build($operation->getClass())];
    }

    public function build(string $resourceClass): AdminMarketingCartRuleConditionAttributes|AdminMarketingCatalogRuleConditionAttributes
    {
        AdminReadPermissions::authorize(null, $resourceClass);

        $isCartRule = $resourceClass === AdminMarketingCartRuleConditionAttributes::class;

        $attributes = $isCartRule
            ? $this->cartRuleRepository->getConditionAttributes()
            : $this->catalogRuleRepository->getConditionAttributes();

        $result = new $resourceClass;
        $result->attributes = json_decode(json_encode($attributes), true);
        $result->operators = $this->operators($isCartRule ? 'cart-rules' : 'catalog-rules');

        return $result;
    }

    /**
     * @return array<string, array<int, array{operator: string, label: string}>>
     */
    protected function operators(string $form): array
    {
        $operators = [];

        foreach (self::OPERATORS as $type => $codes) {
            foreach ($codes as $code) {
                $label = in_array($type, ['multiselect', 'checkbox']) && $code === '{}' ? 'contains' : self::LABELS[$code];

                $operators[$type][] = [
                    'operator' => $code,
                    'label' => trans("admin::app.marketing.promotions.{$form}.edit.{$label}"),
                ];
            }
        }

        return $operators;
    }
}
