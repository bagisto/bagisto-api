<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsExchangeRateUpdateRatesInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateUpdateRatesProcessor;

/**
 * Auto-sync admin settings exchange rates from the configured external provider
 * (the admin "Update Rates" button — Webkul\Admin ExchangeRateController::updateRates).
 *
 * REST:    POST /api/admin/settings/exchange-rates/update-rates
 * GraphQL: createAdminSettingsExchangeRateUpdateRates
 *
 * Resolves the provider via services.exchange_api.<default>.class and runs its
 * updateRates(). Provider/network failures surface as HTTP 422 with the
 * provider's own message.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsExchangeRateUpdateRates',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/exchange-rates/update-rates',
            input: AdminSettingsExchangeRateUpdateRatesInput::class,
            processor: AdminSettingsExchangeRateUpdateRatesProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Exchange Rates'],
                summary: 'Update (auto-sync) exchange rates from the external provider',
                description: 'Refreshes every non-base currency exchange rate from the configured external rate provider (the admin "Update Rates" action). Takes no body. Provider/network errors return 422 with the provider message.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object', 'properties' => []],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Rates refreshed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'updated' => 3,
                                    'message' => 'Exchange rates updated successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'External provider failure (e.g. missing/invalid API key).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminSettingsExchangeRateUpdateRatesInput::class,
            processor: AdminSettingsExchangeRateUpdateRatesProcessor::class,
            description: 'Auto-sync exchange rates from the external provider. Becomes createAdminSettingsExchangeRateUpdateRates.',
        ),
    ],
)]
class AdminSettingsExchangeRateUpdateRates
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?int $updated = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
