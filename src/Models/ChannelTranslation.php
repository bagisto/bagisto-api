<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Channel'],
                summary: 'Get a channel translation by ID',
                description: 'Returns the locale-specific translation row for a channel (homeSeo, name, etc.). Referenced from /api/shop/channels/{id} responses via the `translation` and `translations` IRI fields.',
            ),
        ),
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Channel'],
                summary: 'List channel translations',
                description: 'Lists all channel translation rows.',
            ),
        ),
    ],
    graphQlOperations: []
)]
class ChannelTranslation extends \Webkul\Core\Models\ChannelTranslation {}
