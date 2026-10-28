<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceSectionFieldsQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionFieldsProvider;

/**
 * The field set a section of this type is built from, with the values currently held for
 * the locale — everything a client needs to render an editor for it.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionFields',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/appearance/sections/{id}/fields',
            provider: AdminAppearanceSectionFieldsProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Get the editable fields of a section',
                description: 'Returns the schema for the section\'s type and the options it currently holds — staged edits when there are any, otherwise the published values.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('locale', 'query', 'Locale the options are read in', false, schema: ['type' => 'string', 'example' => 'en']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Schema and current options.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'sectionId' => 3, 'type' => 'category_carousel', 'locale' => 'en',
                                    'schema' => [['name' => 'title', 'type' => 'text', 'label' => 'Title']],
                                    'options' => ['title' => 'Shop by category'],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Section not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: AdminAppearanceSectionFieldsQueryResolver::class,
            args: [
                'sectionId' => ['type' => 'Int!'],
                'locale' => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminAppearanceSectionFields
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $section_id = null;

    public ?string $type = null;

    public ?string $locale = null;

    /**
     * @var array<string, mixed>
     */
    public array $schema = [];

    /**
     * @var array<string, mixed>
     */
    public array $options = [];
}
