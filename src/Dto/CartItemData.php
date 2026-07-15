<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Checkout\Models\CartItem;
use Webkul\Product\Repositories\ProductBundleOptionRepository;

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
    public static function fromModel(CartItem $item): self
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
        $data->price = (float) core()->convertPrice($item->base_price ?? 0);
        $data->basePrice = (float) ($item->base_price ?? 0);
        $data->formattedPrice = core()->currency($item->base_price ?? 0);

        // Prices including tax
        $data->priceInclTax = (float) core()->convertPrice($item->base_price_incl_tax ?? $item->base_price ?? 0);
        $data->basePriceInclTax = (float) ($item->base_price_incl_tax ?? $item->base_price ?? 0);
        $data->formattedPriceInclTax = core()->currency($item->base_price_incl_tax ?? $item->base_price ?? 0);

        // Line totals
        $data->total = (float) core()->convertPrice($item->base_total ?? 0);
        $data->baseTotal = (float) ($item->base_total ?? 0);
        $data->formattedTotal = core()->currency($item->base_total ?? 0);

        // Line totals including tax
        $data->totalInclTax = (float) core()->convertPrice($item->base_total_incl_tax ?? $item->base_total ?? 0);
        $data->baseTotalInclTax = (float) ($item->base_total_incl_tax ?? $item->base_total ?? 0);
        $data->formattedTotalInclTax = core()->currency($item->base_total_incl_tax ?? $item->base_total ?? 0);

        // Discounts
        $data->discountAmount = (float) core()->convertPrice($item->base_discount_amount ?? 0);
        $data->baseDiscountAmount = (float) ($item->base_discount_amount ?? 0);

        // Tax
        $data->taxAmount = (float) core()->convertPrice($item->base_tax_amount ?? 0);
        $data->baseTaxAmount = (float) ($item->base_tax_amount ?? 0);

        // Product info - extract formatted attributes (bundle options, configurable options, etc.)
        $additional = $item->additional ?
            (is_string($item->additional) ? json_decode($item->additional, true) : $item->additional) : [];

        $attributes = ! empty($additional['attributes'])
            ? array_values($additional['attributes'])
            : null;

        // For bundle products, enrich options with can_change_qty and is_required from DB
        if ($attributes && $item->type === 'bundle') {
            $attributes = self::enrichBundleOptions($attributes, $additional);
        }

        $data->options = $attributes;

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

    /**
     * Enrich bundle option attributes with can_change_qty and is_required from DB.
     *
     * Checkbox/Multiselect options: qty is fixed by admin (can_change_qty = false)
     * Radio/Select options: qty can be changed by customer (can_change_qty = true)
     */
    private static function enrichBundleOptions(array $attributes, array $additional): array
    {
        $optionRepo = app(ProductBundleOptionRepository::class);

        foreach ($attributes as &$attribute) {
            $optionId = $attribute['option_id'] ?? null;

            if (! $optionId) {
                continue;
            }

            $bundleOption = $optionRepo->find($optionId);

            $attribute['is_required'] = (bool) ($bundleOption?->is_required ?? false);
            $attribute['can_change_qty'] = in_array($bundleOption?->type, ['radio', 'select']);
        }

        return $attributes;
    }
}
