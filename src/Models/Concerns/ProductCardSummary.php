<?php

namespace Webkul\BagistoApi\Models\Concerns;

use ApiPlatform\Metadata\ApiProperty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\Serializer\Annotation\Groups;

trait ProductCardSummary
{
    /**
     * Add the approved review aggregates a product card needs, so a page of products reads them in one query.
     */
    public static function withReviewSummary(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->withSum('approvedReviews', 'rating');
    }

    /**
     * Whether the product sells below its regular price, as the storefront card's sale badge decides.
     */
    #[ApiProperty(writable: false, readable: true, required: false, schema: ['type' => 'integer', 'enum' => [0, 1]])]
    public function getOnSaleAttribute(): int
    {
        return $this->getTypeInstance()->haveDiscount() ? 1 : 0;
    }

    /**
     * The average approved rating to one decimal, as the storefront shows it.
     */
    #[ApiProperty(writable: false, readable: true, required: false, schema: ['type' => 'number'])]
    public function getAverageRatingAttribute(): float
    {
        $average = $this->reviewAggregate('approved_reviews_avg_rating', fn () => $this->approvedReviews()->avg('rating'));

        return round((float) $average, 1);
    }

    /**
     * The sum of the approved ratings, which the storefront card calls the rating total.
     */
    #[ApiProperty(writable: false, readable: true, required: false, schema: ['type' => 'integer'])]
    public function getTotalRatingAttribute(): int
    {
        return (int) $this->reviewAggregate('approved_reviews_sum_rating', fn () => $this->approvedReviews()->sum('rating'));
    }

    /**
     * The number of approved reviews.
     */
    #[ApiProperty(writable: false, readable: true, required: false, schema: ['type' => 'integer'])]
    public function getReviewsCountAttribute(): int
    {
        return (int) $this->reviewAggregate('approved_reviews_count', fn () => $this->approvedReviews()->count());
    }

    /**
     * Placeholder so the REST detail can carry the specification table; the REST detail fills it.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getAdditionalAttributesAttribute(): ?string
    {
        return null;
    }

    /**
     * Placeholder so the REST detail can carry the customer group offers; the REST detail fills it.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getCustomerGroupOffersAttribute(): ?string
    {
        return null;
    }

    /**
     * Placeholder so the REST detail can carry the configurable option configuration; the REST detail fills it.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getConfigurableConfigAttribute(): ?string
    {
        return null;
    }

    /**
     * Whether the product can be bought, on the REST product card.
     */
    #[Groups(['product:list'])]
    public function getIs_saleable(): bool
    {
        return $this->isSaleable();
    }

    /**
     * Whether the product is on sale, on the REST product card.
     */
    #[Groups(['product:list'])]
    public function getOn_sale(): int
    {
        return $this->getOnSaleAttribute();
    }

    /**
     * The average rating, on the REST product card.
     */
    #[Groups(['product:list'])]
    public function getAverage_rating(): float
    {
        return $this->getAverageRatingAttribute();
    }

    /**
     * The rating total, on the REST product card.
     */
    #[Groups(['product:list'])]
    public function getTotal_rating(): int
    {
        return $this->getTotalRatingAttribute();
    }

    /**
     * The number of approved reviews, on the REST product card.
     */
    #[Groups(['product:list'])]
    public function getReviews_count(): int
    {
        return $this->getReviewsCountAttribute();
    }

    /**
     * A preloaded review aggregate, or the value computed on demand when the query did not load it.
     */
    protected function reviewAggregate(string $attribute, callable $compute): mixed
    {
        return array_key_exists($attribute, $this->attributes)
            ? $this->attributes[$attribute]
            : $compute();
    }
}
