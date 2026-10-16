<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminMarketingCatalogRuleConditionAttributesResolver;
use Webkul\BagistoApi\Admin\State\AdminMarketingRuleConditionAttributesProvider;

/**
 * The attributes and operators a catalog rule condition can use, as the admin catalog rule form offers them.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCatalogRuleConditionAttributes',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/marketing/catalog-rules/condition-attributes',
            provider: AdminMarketingRuleConditionAttributesProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Catalog rule condition attributes',
                description: 'Every attribute a condition can test, grouped as on the admin form, each with its `type` and selectable `options`, plus the operators each type accepts. Build `conditions` entries as `{ attribute: key, operator, value, attribute_type: type }`.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Condition attributes and operators.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'attributes' => [[
                                        'key' => 'product',
                                        'label' => 'Product Attribute',
                                        'children' => [[
                                            'key' => 'product|attribute_family_id',
                                            'type' => 'select',
                                            'label' => 'Attribute Family',
                                            'options' => [['id' => 1, 'admin_name' => 'Default']],
                                        ]],
                                    ]],
                                    'operators' => [
                                        'select' => [
                                            ['operator' => '==', 'label' => 'Is equal to'],
                                            ['operator' => '!=', 'label' => 'Is not equal to'],
                                        ],
                                    ],
                                ]],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                    '403' => new Model\Response(description: 'No permission.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: AdminMarketingCatalogRuleConditionAttributesResolver::class,
            args: [],
            normalizationContext: ['groups' => ['query']],
            description: 'The attributes and operators a catalog rule condition can use.',
        ),
    ],
)]
class AdminMarketingCatalogRuleConditionAttributes
{
    #[ApiProperty(identifier: true, readable: false, writable: false)]
    public string $id = 'catalog-rule-conditions';

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false, description: 'Attribute groups, each with children of { key, type, label, options }.')]
    #[Groups(['query'])]
    public ?array $attributes = null;

    /**
     * @var array<string, array<int, array{operator: string, label: string}>>|null
     */
    #[ApiProperty(writable: false, description: 'Operators allowed for each attribute type.')]
    #[Groups(['query'])]
    public ?array $operators = null;
}
