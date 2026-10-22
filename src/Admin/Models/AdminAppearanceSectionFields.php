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
                    new Model\Parameter('locale', 'query', 'Locale the options are read in', false, schema: [
                        'type' => 'string',
                        'example' => 'en',
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Schema and current options.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'sectionId' => 3, 'type' => 'category_carousel', 'locale' => 'en',
                                    'schema' => [[
                                        'name' => 'title',
                                        'type' => 'text',
                                        'label' => 'Title',
                                    ]],
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
            description: 'The editor field schema for the type of section `sectionId` and the options it holds in `locale`: staged edits when there are any, otherwise the published values. Needs the `appearance.sections` permission.',
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

    /**
     * The section id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $section_id = null;

    /**
     * The type.
     */
    public ?string $type = null;

    /**
     * The locale.
     */
    public ?string $locale = null;

    /**
     * The field schema the section editor renders for the section.
     *
     * @var array<string, mixed>
     */
    public array $schema = [];

    /**
     * The draft or published option values of the section fields.
     *
     * @var array<string, mixed>
     */
    public array $options = [];
}
