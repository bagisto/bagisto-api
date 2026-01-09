<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Dto\CreateProductReviewInput;
use Webkul\BagistoApi\Dto\UpdateProductReviewInput;
use Webkul\BagistoApi\State\ProductReviewProcessor;
use Webkul\BagistoApi\State\ProductReviewProvider;
use Webkul\BagistoApi\State\ProductReviewUpdateProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductReview',
    uriTemplate: '/reviews',
    operations: [
        new \ApiPlatform\Metadata\GetCollection(
            uriTemplate: '/reviews'
        ),
        new \ApiPlatform\Metadata\Get(
            uriTemplate: '/reviews/{id}'
        ),
        new \ApiPlatform\Metadata\Post(
            uriTemplate: '/reviews',
            processor: ProductReviewProcessor::class,
            denormalizationContext: [
                'groups'                 => ['mutation'],
                'allow_extra_attributes' => true,
            ]
        ),
        new \ApiPlatform\Metadata\Patch(
            uriTemplate: '/reviews/{id}',
            processor: ProductReviewProcessor::class,
            denormalizationContext: [
                'groups'                 => ['mutation'],
                'allow_extra_attributes' => true,
            ]
        ),
        new \ApiPlatform\Metadata\Delete(
            uriTemplate: '/reviews/{id}'
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: ProductReviewProvider::class,
            args: [
                'product_id' => ['type' => 'Int', 'description' => 'Filter reviews by product ID'],
                'status'     => ['type' => 'Int', 'description' => 'Filter reviews by status (0=pending, 1=approved, 2=rejected)'],
                'rating'     => ['type' => 'Int', 'description' => 'Filter reviews by rating (1-5 stars)'],
                'first'      => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'       => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'      => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before'     => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ]
        ),
        new Query,
        new Mutation(
            name: 'create',
            input: CreateProductReviewInput::class,
            output: ProductReview::class,
            processor: ProductReviewProcessor::class,
        ),
        new Mutation(
            name: 'update',
            input: UpdateProductReviewInput::class,
            output: ProductReview::class,
            provider: ProductReviewUpdateProvider::class,
            processor: ProductReviewProcessor::class,
            description: 'Update an existing product review'
        ),
        new DeleteMutation(name: 'delete'),
    ]
)]
class ProductReview extends \Webkul\Product\Models\ProductReview
{
    protected $fillable = [
        'comment',
        'title',
        'rating',
        'status',
        'product_id',
        'customer_id',
        'name',
    ];

    protected $casts = [
        'id'          => 'int',
        'product_id'  => 'int',
        'customer_id' => 'int',
        'title'       => 'string',
        'comment'     => 'string',
        'name'        => 'string',
        'rating'      => 'int',
        'status'      => 'int',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Override __get to expose Eloquent attributes to Serializer
     * Removing public property declarations ensures this method is called
     * instead of property reflection accessing empty declared values.
     */
    public function __get($key)
    {
        if ($this->hasAttribute($key)) {
            return $this->getAttribute($key);
        }

        return parent::__get($key);
    }

    /**
     * Override __isset to ensure isset() works correctly with __get()
     * This is critical for Symfony PropertyAccessor which checks isset() before reading.
     */
    public function __isset($key)
    {
        if ($this->hasAttribute($key)) {
            return true;
        }

        return parent::__isset($key);
    }

    /**
     * Override __set to handle attribute setting properly
     */
    public function __set($key, $value)
    {
        if (in_array($key, ['id', 'product_id', 'customer_id', 'title', 'comment', 'rating', 'name', 'email', 'status', 'created_at', 'updated_at'])) {
            $this->setAttribute($key, $value);
        } else {
            parent::__set($key, $value);
        }
    }

    public function getAttachmentsAttribute()
    {
        return $this->getAttachmentUrls();
    }

    public function getAttachmentUrls()
    {
        return $this->images->first() ? $this->images->map(function ($item) {
            return [
                'type' => $item->type,
                'url'  => $item->url(),
            ];
        })->toJson() : null;
    }
}
