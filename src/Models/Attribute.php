<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model as OpenApiModel;
use GraphQL\Error\UserError;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;

/**
 * Simple Attribute model for GraphQL without TranslatableModel
 * This is just for input/output, not for actual database operations
 */
#[ApiResource(
    shortName: 'Attribute',
    description: 'Product attribute resource',
    routePrefix: '/api/shop',
    graphQlOperations: [
        new QueryCollection(provider: CursorAwareCollectionProvider::class),
        new Query(resolver: BaseQueryItemResolver::class),
    ],
    operations: [
        new GetCollection(
            uriTemplate: '/attributes',
            openapi: new OpenApiModel\Operation(
                tags: ['Attribute'],
                summary: 'List all attributes',
                description: 'Returns all product attributes with their translations and options. Public endpoint.',
                responses: [
                    '200' => new OpenApiModel\Response(
                        description: 'List of attributes.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'               => 1,
                                        'code'             => 'sku',
                                        'adminName'        => 'SKU',
                                        'type'             => 'text',
                                        'position'         => 1,
                                        'isRequired'       => 1,
                                        'isUnique'         => 1,
                                        'isFilterable'     => 0,
                                        'isComparable'     => 0,
                                        'isConfigurable'   => 0,
                                        'isUserDefined'    => 0,
                                        'isVisibleOnFront' => 0,
                                        'valuePerLocale'   => 0,
                                        'valuePerChannel'  => 0,
                                        'enableWysiwyg'    => 0,
                                        'createdAt'        => '2024-04-16T21:44:17+05:30',
                                        'updatedAt'        => '2024-04-16T21:44:17+05:30',
                                        'columnName'       => 'text_value',
                                        'validations'      => '{ required: true }',
                                        'options'          => [],
                                        'translation'      => [
                                            'id'          => 1,
                                            'attributeId' => 1,
                                            'locale'      => 'en',
                                            'name'        => 'SKU',
                                        ],
                                        'translations' => ['/api/shop/attribute_translations/1'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/attributes/{id}',
            openapi: new OpenApiModel\Operation(
                tags: ['Attribute'],
                summary: 'Get a single attribute by ID',
                description: 'Returns one product attribute with its translations and options. Public endpoint.',
                responses: [
                    '200' => new OpenApiModel\Response(
                        description: 'Attribute detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'               => 1,
                                    'code'             => 'sku',
                                    'adminName'        => 'SKU',
                                    'type'             => 'text',
                                    'position'         => 1,
                                    'isRequired'       => 1,
                                    'isUnique'         => 1,
                                    'isFilterable'     => 0,
                                    'isComparable'     => 0,
                                    'isConfigurable'   => 0,
                                    'isUserDefined'    => 0,
                                    'isVisibleOnFront' => 0,
                                    'valuePerLocale'   => 0,
                                    'valuePerChannel'  => 0,
                                    'enableWysiwyg'    => 0,
                                    'createdAt'        => '2024-04-16T21:44:17+05:30',
                                    'updatedAt'        => '2024-04-16T21:44:17+05:30',
                                    'columnName'       => 'text_value',
                                    'validations'      => '{ required: true }',
                                    'options'          => [],
                                    'translation'      => [
                                        'id'          => 1,
                                        'attributeId' => 1,
                                        'locale'      => 'en',
                                        'name'        => 'SKU',
                                    ],
                                    'translations' => ['/api/shop/attribute_translations/1'],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new OpenApiModel\Response(
                        description: 'Attribute not found.',
                    ),
                ],
            ),
        ),
    ]
)]
class Attribute extends \Webkul\Attribute\Models\Attribute
{
    #[ApiProperty(readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model
    {
        return $this->translation;
    }

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
