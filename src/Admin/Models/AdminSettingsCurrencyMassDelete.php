<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsCurrencyMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsCurrencyMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/currencies/mass-delete',
            input: AdminSettingsCurrencyMassDeleteInput::class,
            processor: AdminSettingsCurrencyMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Currencies'],
                summary: 'Mass delete currencies',
                description: 'Deletes a batch of currencies. If any ID is a channel base_currency_id, OR the batch would empty the currencies table, the entire batch is rejected with HTTP 400. Non-existent IDs are silently skipped.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices'],

                                'properties' => [
                                    'indices' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'example' => [2, 3],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Currencies deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [2, 3],
                                    'message' => 'Currencies deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Batch rejected — last currency or channel base currency.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminSettingsCurrencyMassDeleteInput::class,
            processor: AdminSettingsCurrencyMassDeleteProcessor::class,
            description: 'Mass-delete a batch of currencies. Becomes createAdminSettingsCurrencyMassDelete.',
        ),
    ],
)]
class AdminSettingsCurrencyMassDelete
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
