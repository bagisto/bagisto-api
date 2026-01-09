<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use GraphQL\Error\UserError;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\AttributeCollectionProvider;

/**
 * Simple Attribute model for BagistoApi without TranslatableModel
 * This is just for input/output, not for actual database operations
 */
#[ApiResource(
    shortName: 'Attribute',
    description: 'Product attribute resource',
    routePrefix: '/api/shop',
    security: "is_granted('ROLE_ADMIN')",
    operations: [
        new Get,
        new GetCollection(provider: AttributeCollectionProvider::class),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(
            provider: AttributeCollectionProvider::class,
            args: [
                'first' => [
                    'type'        => 'Int',
                    'description' => 'Limit the number of attributes returned (forward pagination)',
                ],
                'last' => [
                    'type'        => 'Int',
                    'description' => 'Limit the number of attributes returned (backward pagination)',
                ],
                'after' => [
                    'type'        => 'String',
                    'description' => 'Cursor for forward pagination',
                ],
                'before' => [
                    'type'        => 'String',
                    'description' => 'Cursor for backward pagination',
                ],
            ],
            paginationType: 'cursor',
        ),
    ],
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

    /**
     * Subresource: Options for this attribute
     *
     * Access via: GET /api/shop/attributes/{id}/options
     * GraphQL: attribute(id: ID).options
     */
    #[ApiProperty(
        readableLink: true,
        description: 'Attribute options (colors, sizes, etc.)'
    )]
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get _id (alias for id)
     */
    #[ApiProperty]
    public function get_id(): int
    {
        return $this->id;
    }
}
