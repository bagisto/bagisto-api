<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Attribute\Groups;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterInput;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterOutput;
use Webkul\BagistoApi\State\Processor\NewsletterSubscriptionProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Post(
            name: 'createNewsletterSubscription',
            uriTemplate: '/newsletters',
            input: SubscribeToNewsletterInput::class,
            output: SubscribeToNewsletterOutput::class,
            processor: NewsletterSubscriptionProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Subscribe to newsletter',
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Newsletter'],
                summary: 'Subscribe the authenticated customer to the newsletter',
                description: 'Requires Bearer token. Creates a newsletter subscription for the authenticated customer on the current channel.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['customerEmail'],
                                'properties' => [
                                    'customerEmail' => [
                                        'type'        => 'string',
                                        'format'      => 'email',
                                        'example'     => 'jane@example.com',
                                        'description' => 'Email to subscribe (must be unique in subscribers_list).',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: SubscribeToNewsletterInput::class,
            output: SubscribeToNewsletterOutput::class,
            processor: NewsletterSubscriptionProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Subscribe to newsletter',
        ),
    ]
)]
class Newsletter
{
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(readable: false, writable: true)]
    #[Groups(['query', 'mutation'])]
    public ?string $customerEmail;

    #[ApiProperty(readable: true, writable: false)]
    public bool $success;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message;
}
