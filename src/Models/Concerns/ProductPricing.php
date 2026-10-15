<?php

namespace Webkul\BagistoApi\Models\Concerns;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Annotation\Groups;

trait ProductPricing
{
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getProductPrices()
    {
        return $this->product_prices;
    }

    /**
     * Current channel + customer group, resolved once per request.
     *
     * @return array{0: ?object, 1: ?object}
     */
    private function priceScope(): array
    {
        // Keyed on the request OBJECT, not spl_object_id() — PHP reuses object ids
        // after GC, so an id-keyed memo could hand a freed request's channel and
        // customer group to a new one and silently price against the wrong scope.
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
            // If any error occurs, return base price
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
     * Laravel accessor for maximum_price attribute.
     * Get product maximum price based on price index.
     * Falls back to base price if no price index is available.
     */
    public function getMaximumPriceAttribute(): float
    {
        return $this->priceFromIndex('max_price');
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
     * Falls back to base price if no price index is available.
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
     * Falls back to base price if no price index is available.
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

    public function getFormattedPriceAttribute(): ?string
    {
        $price = $this->getPriceAttribute();

        return $price !== null ? core()->formatPrice($price) : null;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_price(): ?string
    {
        return $this->getFormattedPriceAttribute();
    }

    public function getFormattedSpecialPriceAttribute(): ?string
    {
        $specialPrice = $this->getSpecialPriceAttribute();

        return $specialPrice ? core()->formatPrice($specialPrice) : null;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_special_price(): ?string
    {
        return $this->getFormattedSpecialPriceAttribute();
    }

    public function getFormattedMinimumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getMinimumPriceAttribute());
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_minimum_price(): ?string
    {
        return $this->getFormattedMinimumPriceAttribute();
    }

    public function getFormattedMaximumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getMaximumPriceAttribute());
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    #[Groups(['product:list'])]
    public function getFormatted_maximum_price(): ?string
    {
        return $this->getFormattedMaximumPriceAttribute();
    }

    public function getFormattedRegularMinimumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getRegularMinimumPriceAttribute());
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getFormatted_regular_minimum_price(): ?string
    {
        return $this->getFormattedRegularMinimumPriceAttribute();
    }

    public function getFormattedRegularMaximumPriceAttribute(): ?string
    {
        return core()->formatPrice($this->getRegularMaximumPriceAttribute());
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getFormatted_regular_maximum_price(): ?string
    {
        return $this->getFormattedRegularMaximumPriceAttribute();
    }
}
