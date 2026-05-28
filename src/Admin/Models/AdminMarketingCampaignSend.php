<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignSendInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignSendProcessor;

/**
 * One-action resource: manually trigger a marketing campaign send.
 *
 * REST:
 *   POST /api/admin/marketing/campaigns/{id}/send
 *     200: { campaignId, queued, message }
 *
 * GraphQL:
 *   createAdminMarketingCampaignSend(input: { campaignId: Int! })
 *
 * Behaviour: queues a NewsletterMail for every recipient in the campaign's
 * customer_group (or the guest subscribers list when the group code is 'guest').
 * Mirrors Webkul\Marketing\Helpers\Campaign::process logic for a single
 * campaign — without the date-based event gate (manual triggers ignore the
 * date check so admin can do test sends).
 *
 * Refuses when campaign.status = 0 (inactive) — HTTP 422.
 *
 * Permission: marketing.communications.campaigns.edit.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCampaignSend',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/campaigns/{id}/send',
            input: AdminMarketingCampaignSendInput::class,
            processor: AdminMarketingCampaignSendProcessor::class,
            status: 200,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Send a marketing campaign',
                description: 'Queues the campaign email for every subscriber in its customer_group. Refuses inactive campaigns with HTTP 422.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Send queued.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'         => 12,
                                    'campaignId' => 12,
                                    'queued'     => 5,
                                    'message'    => 'Campaign queued for 5 recipient(s).',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks marketing.communications.campaigns.edit.'),
                    '404' => new Model\Response(description: 'Campaign not found.'),
                    '422' => new Model\Response(description: 'Campaign is inactive.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMarketingCampaignSendInput::class,
            processor: AdminMarketingCampaignSendProcessor::class,
            description: 'Manually trigger a marketing campaign send. Becomes createAdminMarketingCampaignSend.',
        ),
    ],
)]
class AdminMarketingCampaignSend
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $campaignId = null;

    #[ApiProperty(writable: false, description: 'Number of recipients queued for delivery.')]
    public ?int $queued = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
