<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\AttributeOptionCollectionProvider;

/**
 * AttributeOption - Subresource of Attribute with REST and GraphQL support
 *
 * Pattern: Like ProductCustomerGroupPrice with multiple #[ApiResource] annotations
 * - Subresource routes: /attributes/{id}/options
 * - Root routes: /attribute-options (for GraphQL queries and root REST access)
 */
#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/attributes/{attribute_id}/options',
    uriVariables: [
        'attribute_id' => new Link(
            fromClass: Attribute::class,
            fromProperty: 'options',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(provider: AttributeOptionCollectionProvider::class),
    ],
    graphQlOperations: []
)]
#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/attributes/{attribute_id}/options/{id}',
    uriVariables: [
        'attribute_id' => new Link(
            fromClass: Attribute::class,
            fromProperty: 'options',
            identifiers: ['id']
        ),
        'id' => new Link(fromClass: AttributeOption::class),
    ],
    operations: [
        new Get,
    ],
    graphQlOperations: []
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'AttributeOption',
    uriTemplate: '/attribute-options',
    operations: [

    ],
    graphQlOperations: [
        new QueryCollection(
            paginationType: 'cursor',
            args: [
                'attributeId' => [
                    'type'        => 'Int!',
                    'description' => 'Filter options by attribute ID (required)',
                ],
                'first'  => ['type' => 'Int', 'description' => 'Limit results (forward pagination)'],
                'last'   => ['type' => 'Int', 'description' => 'Limit results (backward pagination)'],
                'after'  => ['type' => 'String', 'description' => 'Cursor for forward pagination'],
                'before' => ['type' => 'String', 'description' => 'Cursor for backward pagination'],
            ]
        ),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'AttributeOption',
    uriTemplate: '/attribute-options/{id}',
    operations: [

    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
class AttributeOption extends \Webkul\Attribute\Models\AttributeOption
{
    protected $visible = [
        'id',
        'attribute_id',
        'admin_name',
        'sort_order',
        'swatch_value',
        'swatch_value_url',
        'translation',
        'translations',
    ];

    protected $appends = [
        'swatch_value_url',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get _id (alias for id)
     */
    #[ApiProperty]
    public function get_id(): ?int
    {
        return $this->id;
    }

    /**
     * Get attribute_id
     */
    #[ApiProperty]
    public function getAttributeId(): ?int
    {
        return $this->attribute_id;
    }

    /**
     * Get admin_name
     */
    #[ApiProperty]
    public function getAdminName(): ?string
    {
        return $this->admin_name;
    }

    /**
     * Get sort_order
     */
    #[ApiProperty]
    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    /**
     * Get swatch_value
     */
    #[ApiProperty]
    public function getSwatchValue(): ?string
    {
        return $this->swatch_value;
    }

    /**
     * Swatch value URL
     */
    #[ApiProperty]
    public function getSwatchValueUrl(): ?string
    {
        return $this->swatch_value_url;
    }

    /**
     * Translations for this attribute option
     * Expose the inherited translations relationship with readableLink for pagination
     */
    #[ApiProperty(readableLink: true, description: 'Translations for this attribute option')]
    public function getTranslations()
    {
        // Return the relationship collection
        return $this->getAttribute('translations') ?? parent::translations();
    }

    #[ApiProperty(readableLink: true)]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->translation;
    }
}
