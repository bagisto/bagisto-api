<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new Operation(
                tags: ['Channel'],
                summary: 'Get a channel translation by ID',
                description: 'Returns the locale-specific translation row for a channel (homeSeo, name, etc.). Referenced from /api/shop/channels/{id} responses via the `translation` and `translations` IRI fields. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Translation found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'channelId' => 1,
                                    'locale' => 'en',
                                    'name' => 'E2E ch 1781678253319',
                                    'description' => '',
                                    'maintenanceModeText' => 'Maintenance Mode',
                                    'homeSeo' => [
                                        'meta_title' => 'Demo store',
                                        'meta_keywords' => 'Demo store meta keyword',
                                        'meta_description' => 'Demo store meta description',
                                    ],
                                    'createdAt' => null,
                                    'updatedAt' => '2026-06-17T12:07:35+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Translation not found.',
                    ),
                ],
            ),
        ),
        new GetCollection(
            openapi: new Operation(
                tags: ['Channel'],
                summary: 'List channel translations',
                description: 'Lists all channel translation rows. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Translations listed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'channelId' => 1,
                                        'locale' => 'en',
                                        'name' => 'E2E ch 1781678253319',
                                        'description' => '',
                                        'maintenanceModeText' => 'Maintenance Mode',
                                        'homeSeo' => [
                                            'meta_title' => 'Demo store',
                                            'meta_keywords' => 'Demo store meta keyword',
                                            'meta_description' => 'Demo store meta description',
                                        ],
                                        'createdAt' => null,
                                        'updatedAt' => '2026-06-17T12:07:35+05:30',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: []
)]
class ChannelTranslation extends \Webkul\Core\Models\ChannelTranslation {}
