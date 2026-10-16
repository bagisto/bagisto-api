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
use Webkul\BagistoApi\State\AttributeOptionCollectionProvider;
use Webkul\BagistoApi\Traits\ServesLoadedTranslation;

#[ApiResource(
    shortName: 'AttributeOption',
    description: 'Attribute option resource',
    operations: [
        new GetCollection(
            uriTemplate: '/attribute-options',
            routePrefix: '/api/shop',
            provider: AttributeOptionCollectionProvider::class,
            openapi: new Model\Operation(
                tags: ['Attribute'],
                summary: 'List attribute options',
                description: 'Returns attribute options with their translations, ordered by sort order. Pass `attribute_id` to list one attribute\'s options — a boolean attribute returns its No (id 0) and Yes (id 1) options — and `search` to match the option label in the current locale or its admin name. Public endpoint.',
                parameters: [
                    new Model\Parameter(
                        name: 'attribute_id',
                        in: 'query',
                        required: false,
                        description: 'Only the options of this attribute. Returns 404 when the attribute does not exist.',
                        schema: ['type' => 'integer', 'example' => 23],
                    ),
                    new Model\Parameter(
                        name: 'search',
                        in: 'query',
                        required: false,
                        description: 'Partial match on the option label (current locale) or admin name.',
                        schema: ['type' => 'string', 'example' => 'red'],
                    ),
                ],
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
                                            ],
                                            [
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
                                        ],
                                        [
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
            provider: AttributeOptionCollectionProvider::class,
            extraArgs: [
                'attributeId' => ['type' => 'Int', 'description' => 'Only the options of this attribute; a boolean attribute returns its No (0) and Yes (1) options'],
                'search' => ['type' => 'String', 'description' => 'Partial match on the option label (current locale) or admin name'],
            ],
        ),
        new Query(resolver: BaseQueryItemResolver::class),
    ],
)]
class AttributeOption extends \Webkul\Attribute\Models\AttributeOption
{
    use ServesLoadedTranslation;

    /**
     * @var list<string>
     */
    protected $with = ['translations'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(readableLink: true)]
    public function getTranslations()
    {
        return $this->translations;
    }

    #[ApiProperty(readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?EloquentModel
    {
        return $this->translation;
    }
}
