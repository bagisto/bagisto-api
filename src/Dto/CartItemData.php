<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Data Transfer Object for Cart Items
 *
 * Represents individual items in a shopping cart with pricing and product information.
 * Used in API responses for cart operations.
 *
 * This class is registered as an ApiResource to enable GraphQL type generation,
 * but the Query operation with output: false means it won't be exposed as a standalone query.
 */
#[ApiResource(
    shortName: 'CartItem',
    graphQlOperations: [
        new Query(name: 'item_query', output: false),
    ]
)]
class CartItemData
{
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[Groups(['query', 'mutation'])]
    public ?int $cartId = null;

    #[Groups(['query', 'mutation'])]
    public ?int $productId = null;

    #[Groups(['query', 'mutation'])]
    public ?string $name = null;

    #[Groups(['query', 'mutation'])]
    public ?string $sku = null;

    #[Groups(['query', 'mutation'])]
    public ?int $quantity = null;

    #[Groups(['query', 'mutation'])]
    public ?float $price = null;

    #[Groups(['query', 'mutation'])]
    public ?float $basePrice = null;

    #[Groups(['query', 'mutation'])]
    public ?float $total = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseTotal = null;

    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseDiscountAmount = null;

    #[Groups(['query', 'mutation'])]
    public ?float $taxAmount = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseTaxAmount = null;

    #[Groups(['query', 'mutation'])]
    public ?array $options = null;

    #[Groups(['query', 'mutation'])]
    public ?string $type = null;

    #[Groups(['query', 'mutation'])]
    public ?string $formattedPrice = null;

    #[Groups(['query', 'mutation'])]
    public ?string $formattedTotal = null;

    #[Groups(['query', 'mutation'])]
    public ?float $priceInclTax = null;

    #[Groups(['query', 'mutation'])]
    public ?float $basePriceInclTax = null;

    #[Groups(['query', 'mutation'])]
    public ?string $formattedPriceInclTax = null;

    #[Groups(['query', 'mutation'])]
    public ?float $totalInclTax = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseTotalInclTax = null;

    #[Groups(['query', 'mutation'])]
    public ?string $formattedTotalInclTax = null;

    #[Groups(['query', 'mutation'])]
    public ?string $baseImage = null;

    #[Groups(['query', 'mutation'])]
    public ?string $productUrlKey = null;

    #[Groups(['query', 'mutation'])]
    public ?bool $canChangeQty = null;

    /**
     * Create CartItemData from CartItem model
     */
    public static function fromModel(\Webkul\Checkout\Models\CartItem $item): self
    {
        $data = new self;

        $data->id = $item->id;
        $data->cartId = $item->cart_id;
        $data->productId = $item->product_id;
        $data->name = $item->name ?? ($item->product?->name ?? '');
        $data->sku = $item->sku ?? ($item->product?->sku ?? '');
        $data->quantity = (int) $item->quantity;
        $data->type = $item->type;

        // Base prices
        $data->price = (float) ($item->price ?? 0);
        $data->basePrice = (float) ($item->base_price ?? 0);
        $data->formattedPrice = core()->formatPrice($item->price ?? 0);

        // Prices including tax
        $data->priceInclTax = (float) ($item->price_incl_tax ?? $item->price ?? 0);
        $data->basePriceInclTax = (float) ($item->base_price_incl_tax ?? $item->base_price ?? 0);
        $data->formattedPriceInclTax = core()->formatPrice($item->price_incl_tax ?? $item->price ?? 0);

        // Line totals
        $data->total = (float) ($item->total ?? 0);
        $data->baseTotal = (float) ($item->base_total ?? 0);
        $data->formattedTotal = core()->formatPrice($item->total ?? 0);

        // Line totals including tax
        $data->totalInclTax = (float) ($item->total_incl_tax ?? $item->total ?? 0);
        $data->baseTotalInclTax = (float) ($item->base_total_incl_tax ?? $item->base_total ?? 0);
        $data->formattedTotalInclTax = core()->formatPrice($item->total_incl_tax ?? $item->total ?? 0);

        // Discounts
        $data->discountAmount = (float) ($item->discount_amount ?? 0);
        $data->baseDiscountAmount = (float) ($item->base_discount_amount ?? 0);

        // Tax
        $data->taxAmount = (float) ($item->tax_amount ?? 0);
        $data->baseTaxAmount = (float) ($item->base_tax_amount ?? 0);

        // Product info
        $data->options = $item->additional ?
            (is_string($item->additional) ? json_decode($item->additional, true) : $item->additional) : null;

        // Base image
        if ($item->product) {
            try {
                $data->baseImage = json_encode($item->product->getTypeInstance()->getBaseImage($item));
            } catch (\Exception $e) {
                $data->baseImage = null;
            }
            $data->productUrlKey = $item->product->url_key;
            $data->canChangeQty = $item->product->getTypeInstance()->showQuantityBox();
        }

        return $data;
    }
}
