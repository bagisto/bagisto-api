<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminConfigurationValuesQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationValuesProvider;

/**
 * Admin Configuration values — returns the effective values map for a slug.
 *
 * REST   : GET /api/admin/configuration?slug=<section>.<group>
 * GraphQL: adminConfigurationValues(slug:, channel:, locale:)
 *
 * `slug` is required (anti-foot-gun — would otherwise dump the whole
 * core_config table). Channel/locale resolution falls back to the current
 * request's channel/locale per `core()->getConfigData()` semantics.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminConfigurationValues',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/configuration',
            provider: AdminConfigurationValuesProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Configuration'],
                summary: 'Configuration values for one slug',
                description: 'Returns the flat `key → string` map of effective values for every field under the given slug. Falls back to per-field defaults when no `core_config` row exists.',
                parameters: [
                    new Model\Parameter('slug', 'query', 'Required. `<section>.<group>` slug, e.g. `sales.order_settings`.', true, schema: ['type' => 'string']),
                    new Model\Parameter('channel', 'query', 'Channel code for resolution. Defaults to the requested channel.', false, schema: ['type' => 'string']),
                    new Model\Parameter('locale', 'query', 'Locale code for resolution. Defaults to the requested locale.', false, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Effective values.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'slug'    => 'sales.order_settings',
                                    'channel' => 'default',
                                    'locale'  => 'en',
                                    'values'  => [
                                        'sales.order_settings.reorder.admin'         => '1',
                                        'sales.order_settings.reorder.shop'          => '1',
                                        'sales.order_settings.minimum_order.enable'  => '0',
                                    ],
                                ]],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Slug not found.'),
                    '422' => new Model\Response(description: 'Slug query parameter missing.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'values',
            resolver: AdminConfigurationValuesQueryResolver::class,
            args: [
                'slug'    => ['type' => 'String!'],
                'channel' => ['type' => 'String'],
                'locale'  => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Returns the effective configuration values for the given slug.',
        ),
    ],
)]
class AdminConfigurationValues
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $slug = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $channel = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $locale = null;

    /**
     * @var array<string, string|null>|null
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $values = null;
}
