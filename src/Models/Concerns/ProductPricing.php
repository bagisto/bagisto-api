<?php

namespace Webkul\BagistoApi\Models\Concerns;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Annotation\Groups;

trait ProductPricing
{
    /**
     * Get product prices.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getProductPrices()
    {
        return $this->product_prices;
    }

    /**
     * Get minimum price attribute.
     */
    public function getMinimumPriceAttribute(): float
    {
        try {
            [$currentChannel, $customerGroup] = $this->priceScope();

            if (
                ! $currentChannel
                || ! $customerGroup
            ) {
                return floatval($this->price ?? 0);
            }

            return $this->priceFromIndex('min_price');
        } catch (\Exception $exception) {
            return (float) core()->convertPrice(floatval($this->getSystemAttributeValue('price') ?? 0));
        }
    }

    /**
     * Get product minimum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getMinimum_price(): float
    {
        return $this->getMinimumPriceAttribute();
    }

    /**
     * Get the highest price the product sells at, never below its regular price.
     */
    public function getMaximumPriceAttribute(): float
    {
        return max($this->priceFromIndex('max_price'), $this->priceFromIndex('regular_max_price'));
    }

    /**
     * Get product maximum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getMaximum_price(): float
    {
        return $this->getMaximumPriceAttribute();
    }

    /**
     * Laravel accessor for regular_minimum_price attribute.
     * Get product regular minimum price based on price index.
     */
    public function getRegularMinimumPriceAttribute(): float
    {
        return $this->priceFromIndex('regular_min_price');
    }

    /**
     * Get product regular minimum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getRegular_minimum_price(): float
    {
        return $this->getRegularMinimumPriceAttribute();
    }

    /**
     * Laravel accessor for regular_maximum_price attribute.
     * Get product regular maximum price based on price index.
     */
    public function getRegularMaximumPriceAttribute(): float
    {
        return $this->priceFromIndex('regular_max_price');
    }

    /**
     * Get product regular maximum price for BagistoApi API.
     * Exposed to BagistoApi schema via ApiProperty attribute.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getRegular_maximum_price(): float
    {
        return $this->getRegularMaximumPriceAttribute();
    }

    /**
     * Get formatted price attribute.
     */
    public function getFormattedPriceAttribute(): ?string
    {
        $price = $this->getPriceAttribute();

        return $price !== null ? core()->formatPrice($price) : null;
    }

    /**
     * Get formatted price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_price(): ?string
    {
        return $this->getFormattedPriceAttribute();
    }

    /**
     * Get formatted special price attribute.
     */
    public function getFormattedSpecialPriceAttribute(): ?string
    {
        $specialPrice = $this->getSpecialPriceAttribute();

        return $specialPrice ? core()->formatPrice($specialPrice) : null;
    }

    /**
     * Get formatted special price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_special_price(): ?string
    {
        return $this->getFormattedSpecialPriceAttribute();
    }

    /**
     * Get formatted minimum price attribute.
     */
    public function getFormattedMinimumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getMinimumPriceAttribute());
    }

    /**
     * Get formatted minimum price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_minimum_price(): ?string
    {
        return $this->getFormattedMinimumPriceAttribute();
    }

    /**
     * Get formatted maximum price attribute.
     */
    public function getFormattedMaximumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getMaximumPriceAttribute());
    }

    /**
     * Get formatted maximum price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_maximum_price(): ?string
    {
        return $this->getFormattedMaximumPriceAttribute();
    }

    /**
     * Get formatted regular minimum price attribute.
     */
    public function getFormattedRegularMinimumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getRegularMinimumPriceAttribute());
    }

    /**
     * Get formatted regular minimum price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getFormatted_regular_minimum_price(): ?string
    {
        return $this->getFormattedRegularMinimumPriceAttribute();
    }

    /**
     * Get formatted regular maximum price attribute.
     */
    public function getFormattedRegularMaximumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getRegularMaximumPriceAttribute());
    }

    /**
     * Get formatted regular maximum price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getFormatted_regular_maximum_price(): ?string
    {
        return $this->getFormattedRegularMaximumPriceAttribute();
    }

    /**
     * Current channel + customer group, resolved once per request.
     *
     * @return array{0: ?object, 1: ?object}
     */
    private function priceScope(): array
    {
        $request = request();

        self::$priceScopeByRequest ??= new \WeakMap;

        if (! isset(self::$priceScopeByRequest[$request])) {
            self::$priceScopeByRequest[$request] = [
                core()->getCurrentChannel(),
                resolve('Webkul\Customer\Repositories\CustomerRepository')->getCurrentGroup(),
            ];
        }

        return self::$priceScopeByRequest[$request];
    }

    /**
     * Price index row for the current channel + customer group, memoised per model.
     */
    private function resolvedPriceIndex(): ?object
    {
        if ($this->priceIndexResolved) {
            return $this->priceIndexMemo;
        }

        $this->priceIndexResolved = true;

        [$channel, $group] = $this->priceScope();

        if (
            ! $channel
            || ! $group
        ) {
            return $this->priceIndexMemo = null;
        }

        if (! $this->relationLoaded('price_indices')) {
            $this->load('price_indices');
        }

        return $this->priceIndexMemo = $this->price_indices->first(
            fn ($index) => (int) $index->channel_id === (int) $channel->id
                && (int) $index->customer_group_id === (int) $group->id
        );
    }

    /**
     * Resolve a price-index column, falling back to the base price.
     */
    private function priceFromIndex(string $column): float
    {
        try {
            $priceIndex = $this->resolvedPriceIndex();

            if ($priceIndex) {
                return (float) core()->convertPrice(floatval($priceIndex->{$column}));
            }

            return (float) core()->convertPrice(floatval($this->getSystemAttributeValue('price') ?? 0));
        } catch (\Exception $exception) {
            return (float) core()->convertPrice(floatval($this->getSystemAttributeValue('price') ?? 0));
        }
    }
}
