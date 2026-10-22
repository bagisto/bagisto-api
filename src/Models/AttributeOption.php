<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\BagistoApi\Traits\ServesLoadedTranslation;

#[ApiResource(
    shortName: 'AttributeOption',
    description: 'Attribute option resource',
    operations: [
        new GetCollection(
            uriTemplate: '/attribute-options',
            routePrefix: '/api/shop',
            openapi: new Model\Operation(
                tags: ['Attribute'],
                summary: 'List all attribute options',
                description: 'Returns all attribute options with their translations. Public endpoint.',
                responses: [
                    '200' => new Model\Response(
                        description: 'List of attribute options.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'adminName' => 'Red',
                                        'sortOrder' => 0,

                                        'translation' => [
                                            'id' => 1,
                                            'attributeOptionId' => 1,
                                            'locale' => 'en',
                                            'label' => 'Red',
                                        ],

                                        'translations' => [
                                            [
                                                'id' => 86,
                                                'attributeOptionId' => 1,
                                                'locale' => 'de',
                                                'label' => '',
                                            ], [
                                                'id' => 1,
                                                'attributeOptionId' => 1,
                                                'locale' => 'en',
                                                'label' => 'Red',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/attribute-options/{id}',
            routePrefix: '/api/shop',
            openapi: new Model\Operation(
                tags: ['Attribute'],
                summary: 'Get a single attribute option by ID',
                description: 'Returns one attribute option with its translations. Public endpoint.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Attribute option detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'adminName' => 'Red',
                                    'sortOrder' => 0,

                                    'translation' => [
                                        'id' => 1,
                                        'attributeOptionId' => 1,
                                        'locale' => 'en',
                                        'label' => 'Red',
                                    ],

                                    'translations' => [
                                        [
                                            'id' => 86,
                                            'attributeOptionId' => 1,
                                            'locale' => 'de',
                                            'label' => '',
                                        ], [
                                            'id' => 1,
                                            'attributeOptionId' => 1,
                                            'locale' => 'en',
                                            'label' => 'Red',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Attribute option not found.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CursorAwareCollectionProvider::class,
            description: 'Every attribute option of every attribute, ordered by ID; read one attribute\'s options through its `options` field instead. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header.',
        ),
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One attribute option. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header.',
        ),
    ],
)]
class AttributeOption extends \Webkul\Attribute\Models\AttributeOption
{
    use ServesLoadedTranslation;

    /**
     * The relations always eager loaded with the attribute option.
     *
     * @var list<string>
     */
    protected $with = ['translations'];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get translations.
     */
    #[ApiProperty(readableLink: true)]
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get translation.
     */
    #[ApiProperty(readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?EloquentModel
    {
        return $this->translation;
    }
}
