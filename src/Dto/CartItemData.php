<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Checkout\Models\CartItem;
use Webkul\Product\Repositories\ProductBundleOptionRepository;

#[ApiResource(
    shortName: 'CartItem',
    graphQlOperations: [
        new Query(name: 'item_query', output: false),
    ]
)]
class CartItemData
{
    /**
     * The id.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    /**
     * The cart id.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $cartId = null;

    /**
     * The product id.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $productId = null;

    /**
     * The name.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $name = null;

    /**
     * The sku.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $sku = null;

    /**
     * The quantity.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $quantity = null;

    /**
     * The price.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $price = null;

    /**
     * The base price.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $basePrice = null;

    /**
     * The total.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $total = null;

    /**
     * The base total.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseTotal = null;

    /**
     * The discount amount.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;

    /**
     * The base discount amount.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseDiscountAmount = null;

    /**
     * The tax amount.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $taxAmount = null;

    /**
     * The base tax amount.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseTaxAmount = null;

    /**
     * The options.
     */
    #[Groups(['query', 'mutation'])]
    public ?array $options = null;

    /**
     * The type.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $type = null;

    /**
     * The formatted price.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $formattedPrice = null;

    /**
     * The formatted total.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $formattedTotal = null;

    /**
     * The price incl tax.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $priceInclTax = null;

    /**
     * The base price incl tax.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $basePriceInclTax = null;

    /**
     * The formatted price incl tax.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $formattedPriceInclTax = null;

    /**
     * The total incl tax.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $totalInclTax = null;

    /**
     * The base total incl tax.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseTotalInclTax = null;

    /**
     * The formatted total incl tax.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $formattedTotalInclTax = null;

    /**
     * The base image.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $baseImage = null;

    /**
     * The product url key.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $productUrlKey = null;

    /**
     * The can change qty.
     */
    #[Groups(['query', 'mutation'])]
    public ?bool $canChangeQty = null;

    /**
     * Create CartItemData from CartItem model.
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

        $data->price = (float) core()->convertPrice($item->base_price ?? 0);
        $data->basePrice = (float) ($item->base_price ?? 0);
        $data->formattedPrice = core()->currency($item->base_price ?? 0);

        $data->priceInclTax = (float) core()->convertPrice($item->base_price_incl_tax ?? $item->base_price ?? 0);
        $data->basePriceInclTax = (float) ($item->base_price_incl_tax ?? $item->base_price ?? 0);
        $data->formattedPriceInclTax = core()->currency($item->base_price_incl_tax ?? $item->base_price ?? 0);

        $data->total = (float) core()->convertPrice($item->base_total ?? 0);
        $data->baseTotal = (float) ($item->base_total ?? 0);
        $data->formattedTotal = core()->currency($item->base_total ?? 0);

        $data->totalInclTax = (float) core()->convertPrice($item->base_total_incl_tax ?? $item->base_total ?? 0);
        $data->baseTotalInclTax = (float) ($item->base_total_incl_tax ?? $item->base_total ?? 0);
        $data->formattedTotalInclTax = core()->currency($item->base_total_incl_tax ?? $item->base_total ?? 0);

        $data->discountAmount = (float) core()->convertPrice($item->base_discount_amount ?? 0);
        $data->baseDiscountAmount = (float) ($item->base_discount_amount ?? 0);

        $data->taxAmount = (float) core()->convertPrice($item->base_tax_amount ?? 0);
        $data->baseTaxAmount = (float) ($item->base_tax_amount ?? 0);

        $additional = $item->additional ?
            (is_string($item->additional) ? json_decode($item->additional, true) : $item->additional) : [];

        $attributes = ! empty($additional['attributes'])
            ? array_values($additional['attributes'])
            : null;

        if (
            $attributes
            && $item->type === 'bundle'
        ) {
            $attributes = self::enrichBundleOptions($attributes, $additional);
        }

        $data->options = $attributes;

        if ($item->product) {
            try {
                $data->baseImage = json_encode($item->product->getTypeInstance()->getBaseImage($item));
            } catch (\Exception $exception) {
                $data->baseImage = null;
            }
            $data->productUrlKey = $item->product->url_key;
            $data->canChangeQty = $item->product->getTypeInstance()->showQuantityBox();
        }

        return $data;
    }

    /**
     * Enrich bundle option attributes with can_change_qty and is_required from DB.
     * Checkbox/Multiselect options: qty is fixed by admin (can_change_qty = false).
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
