<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use GraphQL\Error\UserError;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

/**
 * Simple Attribute model for GraphQL without TranslatableModel
 * This is just for input/output, not for actual database operations
 */
#[ApiResource(
    shortName: 'Attribute',
    description: 'Product attribute resource',
    routePrefix: '/api/shop',
    graphQlOperations: [
        new QueryCollection(),
        new Query(resolver: BaseQueryItemResolver::class),
    ],
    operations: [
        new GetCollection(
            uriTemplate: '/attributes'
        ),
        new Get(
            uriTemplate: '/attributes/{id}'
        ),
    ]
)]
class Attribute extends \Webkul\Attribute\Models\Attribute
{
    protected $hidden = ['translation'];

    public static function boot()
    {
        parent::boot();

        static::creating(function (EloquentModel $model) {
            if (static::where('code', $model->code)->exists()) {
                throw new UserError(__('bagistoapi::app.graphql.attribute.code-already-exists'));
            }
        });
    }

    #[ApiProperty(readableLink: true, writable: false, readable: true)]
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }

    /**
     * Get the attribute options with support for GraphQL args
     * Returns a Closure so API Platform's GraphQL ResourceFieldResolver
     * can invoke it with the GraphQL args: ($source, $args, $context).
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getOptions()
    {
        return function ($source, array $args = [], $context = null) {
            if (isset($args['first']) && is_numeric($args['first'])) {
                return $this->options()->limit((int) $args['first'])->get();
            }

            if (empty($args) && $this->relationLoaded('options')) {
                return $this->options;
            }

            return $this->options()->get();
        };
    }

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}
