<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Product\Models\ProductCustomizableOptionPrice as BaseProductCustomizableOptionPrice;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new Operation(
                tags: ['Product'],
                summary: 'Get a customizable option price by ID',
                description: 'Returns a single price-bearing value for a customizable option. Referenced from `/api/shop/products/{id}/customizable-options` responses via the `customizableOptionPrices` IRI list.',
                responses: [
                    '200' => new Response(
                        description: 'Customizable option price',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'label' => 'Extra',
                                    'price' => 5,
                                    'formattedPrice' => '$5.00',
                                    'sortOrder' => 0,
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Customizable option price not found.',
                    ),
                ],
            ),
        ),
        new GetCollection(
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List customizable option prices',
                description: 'Lists all customizable option price rows. Use the parent product\'s `customizable-options` sub-resource to scope to one option.',
                responses: [
                    '200' => new Response(
                        description: 'Customizable option price collection',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'label' => 'Extra',
                                        'price' => 5,
                                        'formattedPrice' => '$5.00',
                                        'sortOrder' => 0,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class ProductCustomizableOptionPrice extends BaseProductCustomizableOptionPrice
{
    /**
     * Get the customizable option that owns the price.
     */
    public function customizable_option(): BelongsTo
    {
        return $this->belongsTo(ProductCustomizableOption::class, 'product_customizable_option_id');
    }

    /**
     * Get id
     */
    #[ApiProperty(
        identifier: true,
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get label
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getPriceAttribute($value)
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    /**
     * Get price
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getPrice(): ?float
    {
        return $this->price ? (float) $this->price : null;
    }

    public function getFormattedPriceAttribute(): ?string
    {
        return $this->price !== null ? core()->formatPrice($this->price) : null;
    }

    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_price(): ?string
    {
        return $this->getFormattedPriceAttribute();
    }

    /**
     * Get sort_order
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getSort_order(): ?int
    {
        return $this->sort_order;
    }
}
