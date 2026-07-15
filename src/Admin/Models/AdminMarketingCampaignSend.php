<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignSendInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignSendProcessor;

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
                tags: ['Admin Marketing: Communications'],
                summary: 'Send a marketing campaign',
                description: 'Queues the campaign email for every subscriber in its customer_group. Refuses inactive campaigns with HTTP 422.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
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
                                    'campaignId' => 5,
                                    'queued' => 124,
                                    'message' => 'Campaign queued for 124 recipient(s).',
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
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $campaign_id = null;

    #[ApiProperty(writable: false, description: 'Number of recipients queued for delivery.')]
    public ?int $queued = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
