<?php

namespace Webkul\BagistoApi\Models\Concerns;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Models\Attribute;
use Webkul\BagistoApi\Models\AttributeFamily;
use Webkul\BagistoApi\Models\AttributeValue;
use Webkul\BagistoApi\Models\BookingProduct;
use Webkul\BagistoApi\Models\Category;
use Webkul\BagistoApi\Models\Channel;
use Webkul\BagistoApi\Models\ProductBundleOption;
use Webkul\BagistoApi\Models\ProductCustomizableOption;
use Webkul\BagistoApi\Models\ProductDownloadableLink;
use Webkul\BagistoApi\Models\ProductDownloadableSample;
use Webkul\BagistoApi\Models\ProductGroupedProduct;
use Webkul\BagistoApi\Models\ProductImage;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\BagistoApi\Models\ProductVideo;

trait ProductRelations
{
    /**
     * Attribute values.
     */
    public function attribute_values(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'product_id');
    }

    /**
     * Get super attributes relationship (many-to-many).
     */
    public function super_attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_super_attributes');
    }

    /**
     * Get only enabled variants (status attribute_id=8, boolean_value=1).
     */
    public function variants(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')
            ->whereHas('attribute_values', function ($query) {
                $query->where('attribute_id', 8)
                    ->where('boolean_value', 1);
            });
    }

    /**
     * Get super attributes.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getSuper_attributes()
    {
        return $this->super_attributes;
    }

    /**
     * Set super attributes.
     */
    public function setSuper_attributes($value): void
    {
        $this->super_attributes = $value;
    }

    /**
     * Get product categories relationship.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Get product categories.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set product categories.
     */
    public function setCategories($value): void
    {
        $this->categories = $value;
    }

    /**
     * The images that belong to the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')
            ->orderBy('position');
    }

    /**
     * The images of the product.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false,
        readableLink: true
    )]
    #[Groups(['mutation'])]
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set images.
     */
    public function setImages($value): void
    {
        $this->images = $value;
    }

    /**
     * The videos that belong to the product.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class, 'product_id')
            ->orderBy('position');
    }

    /**
     * The videos of the product.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false,
        readableLink: true
    )]
    #[Groups(['mutation'])]
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set videos.
     */
    public function setVideos($value): void
    {
        $this->videos = $value;
    }

    /**
     * The images that belong to the product.
     */
    public function base_image_url(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')
            ->orderBy('position');
    }

    /**
     * The base image of the product.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: true,
        readableLink: true
    )]
    #[Groups(['product:list'])]
    public function getBase_image_url(): ProductImage
    {
        return $this->base_image_url;
    }

    /**
     * Channels.
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'product_channels', 'product_id', 'channel_id');
    }

    /**
     * Limit the query to products the storefront may show on a channel: assigned to it,
     * enabled on it and visible individually, as core's storefront listing and product page do.
     */
    public function scopeVisibleInStorefront(Builder $query, ?string $channelCode = null): Builder
    {
        $channel = $channelCode
            ? core()->getAllChannels()->firstWhere('code', $channelCode)
            : core()->getCurrentChannel();

        if (! $channel) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereHas('channels', fn ($channels) => $channels->whereKey($channel->id))
            ->whereHas('attribute_values', fn ($values) => $values
                ->where('attribute_id', static::$systemAttributes['status']['id'])
                ->where('boolean_value', 1)
                ->where(fn ($scope) => $scope->where('channel', $channel->code)->orWhereNull('channel')))
            ->whereHas('attribute_values', fn ($values) => $values
                ->where('attribute_id', static::$systemAttributes['visible_individually']['id'])
                ->where('boolean_value', 1));
    }

    /**
     * Limit the query to products a client may open on their own: everything the storefront shows,
     * plus the enabled variants of such a product, which core exposes through their parent.
     */
    public function scopeReachableInStorefront(Builder $query, ?string $channelCode = null): Builder
    {
        return $query->where(fn ($scope) => $scope
            ->visibleInStorefront($channelCode)
            ->orWhere(fn ($variant) => $variant
                ->whereNotNull('parent_id')
                ->whereHas('attribute_values', fn ($values) => $values
                    ->where('attribute_id', static::$systemAttributes['status']['id'])
                    ->where('boolean_value', 1))
                ->whereIn('parent_id', static::query()->visibleInStorefront($channelCode)->select('products.id'))));
    }

    /**
     * Get channels.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Set channels.
     */
    public function setChannels($value): void
    {
        $this->channels = $value;
    }

    /**
     * Attribute family.
     */
    public function attribute_family(): BelongsTo
    {
        return $this->belongsTo(AttributeFamily::class, 'attribute_family_id');
    }

    /**
     * Get attribute family.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    #[Groups(['mutation'])]
    public function getAttribute_family(): ?AttributeFamily
    {
        return $this->attribute_family;
    }

    /**
     * Set attribute family.
     */
    public function setAttribute_family(?AttributeFamily $value): void
    {
        $this->attribute_family = $value;
    }

    /**
     * Get booking products attributes.
     */
    #[ApiProperty(
        writable: true,
        readable: true,
        required: true
    )]
    public function getBookingProductsAttributes()
    {
        return $this->getBookingProducts();
    }

    /**
     * Get booking products.
     */
    public function getBookingProducts()
    {
        return $this->booking_products;
    }

    /**
     * Set booking products.
     */
    public function setBookingProducts($value): void
    {
        $this->booking_products = $value;
    }

    /**
     * Get booking type attribute.
     */
    public function getBookingTypeAttribute(): ?string
    {
        if ($this->type !== 'booking') {
            return null;
        }

        return $this->booking_products->first()?->type;
    }

    /**
     * Get booking type.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false,
    )]
    #[Groups(['product:list'])]
    public function getBooking_type(): ?string
    {
        return $this->booking_type;
    }

    /**
     * Get the booking products relationship.
     */
    public function booking_products(): HasMany
    {
        return $this->hasMany(BookingProduct::class, 'product_id');
    }

    /**
     * Get bundle options.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getBundleOptions()
    {
        return $this->bundle_options;
    }

    /**
     * Set bundle options.
     */
    public function setBundleOptions($value): void
    {
        $this->bundle_options = $value;
    }

    /**
     * Get the bundle options relationship.
     */
    public function bundle_options(): HasMany
    {
        return $this->hasMany(ProductBundleOption::class);
    }

    /**
     * Grouped products.
     */
    public function grouped_products(): HasMany
    {
        return $this->hasMany(ProductGroupedProduct::class, 'product_id');
    }

    /**
     * Downloadable links.
     */
    public function downloadable_links(): HasMany
    {
        return $this->hasMany(ProductDownloadableLink::class, 'product_id');
    }

    /**
     * Downloadable samples.
     */
    public function downloadable_samples(): HasMany
    {
        return $this->hasMany(ProductDownloadableSample::class, 'product_id');
    }

    /**
     * Get downloadable samples.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getDownloadableSamples()
    {
        return $this->downloadable_samples;
    }

    /**
     * Set downloadable samples.
     */
    public function setDownloadableSamples($value): void
    {
        $this->downloadable_samples = $value;
    }

    /**
     * The customizable options that belong to the product.
     */
    public function customizable_options(): HasMany
    {
        return $this->hasMany(ProductCustomizableOption::class, 'product_id')
            ->orderBy('sort_order');
    }

    /**
     * Get customizable options.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false
    )]
    #[Groups(['mutation'])]
    public function getCustomizable_options()
    {
        return $this->customizable_options()
            ->with('customizable_option_prices')
            ->get();
    }

    /**
     * Set customizable options.
     */
    public function setCustomizable_options($value): void
    {
        $this->customizable_options = $value;
    }

    /**
     * Approved reviews.
     */
    public function approved_reviews(): HasMany
    {
        return $this->approvedReviews();
    }

    /**
     * Get reviews.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getReviews()
    {
        return function ($source, array $args = [], $context = null) {
            $relation = $this->reviews();

            $relation = $relation->where('status', $args['status'] ?? 'approved');

            if (
                isset($args['first'])
                && is_numeric($args['first'])
            ) {
                $relation = $relation->limit((int) $args['first']);
            }

            if (
                empty($args)
                && $this->relationLoaded('reviews')
            ) {
                return $this->reviews->where('status', 'approved')->values();
            }

            return $relation->get();
        };
    }

    /**
     * Get the product reviews that owns the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Related products.
     */
    public function related_products(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'product_relations', 'parent_id', 'child_id');
    }

    /**
     * Get related products.
     */
    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getRelatedProducts()
    {
        return function ($source, array $args = [], $context = null) {

            $relation = $source->related_products();

            $total = $relation->count();

            $limit = $args['first'] ?? $args['last'] ?? 30;

            $items = $relation->limit($limit)->get();

            return new LengthAwarePaginator(
                $items,
                $total,
                $limit,
                1,
                ['path' => '/']
            );
        };
    }

    /**
     * Up sells.
     */
    public function up_sells(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'product_up_sells', 'parent_id', 'child_id');
    }

    /**
     * Get up sells.
     */
    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getUpSells()
    {
        return function ($source, array $args = [], $context = null) {
            $relation = $source->up_sells();

            $total = $relation->count();

            $limit = $args['first'] ?? $args['last'] ?? 30;
            $items = $relation->limit($limit)->get();

            return new LengthAwarePaginator(
                $items,
                $total,
                $limit,
                1,
                ['path' => '/']
            );
        };
    }

    /**
     * Cross sells.
     */
    public function cross_sells(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'product_cross_sells', 'parent_id', 'child_id');
    }

    /**
     * Get cross sells.
     */
    #[ApiProperty(writable: true, readable: true, required: false)]
    public function getCrossSells()
    {
        return function ($source, array $args = [], $context = null) {
            $relation = $source->cross_sells();

            $total = $relation->count();

            $limit = $args['first'] ?? $args['last'] ?? 30;
            $items = $relation->limit($limit)->get();

            return new LengthAwarePaginator(
                $items,
                $total,
                $limit,
                1,
                ['path' => '/']
            );
        };
    }
}
