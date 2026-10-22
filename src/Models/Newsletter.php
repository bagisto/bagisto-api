<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Attribute\Groups;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterInput;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterOutput;
use Webkul\BagistoApi\Dto\UnsubscribeFromNewsletterInput;
use Webkul\BagistoApi\State\Processor\NewsletterSubscriptionProcessor;
use Webkul\BagistoApi\State\Processor\NewsletterUnsubscriptionProcessor;

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
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Subscribe to newsletter',
            openapi: new Operation(
                tags: ['Newsletter'],
                summary: 'Subscribe the authenticated customer to the newsletter',
                description: 'Requires Bearer token. Creates a newsletter subscription for the authenticated customer on the current channel.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['customerEmail'],

                                'properties' => [
                                    'customerEmail' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'example' => 'jane@example.com',
                                        'description' => 'Email to subscribe (must be unique in subscribers_list).',
                                    ],
                                ],
                            ],

                            'example' => [
                                'customerEmail' => 'jane@example.com',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Subscribed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'You have successfully subscribed to our newsletter.',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Response(description: 'Missing customerEmail, or email already subscribed.'),
                ],
            ),
        ),
        new Post(
            name: 'unsubscribeNewsletter',
            uriTemplate: '/newsletters/unsubscribe',
            input: UnsubscribeFromNewsletterInput::class,
            output: SubscribeToNewsletterOutput::class,
            processor: NewsletterUnsubscriptionProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Newsletter'],
                summary: 'Unsubscribe using a newsletter token',
                description: 'Deletes the newsletter subscription associated with the opaque token. Returns a generic success for unknown tokens.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['token'],

                                'properties' => [
                                    'token' => [
                                        'type' => 'string',
                                        'example' => 'unsubscribe-token',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    201 => new Response(description: 'The subscription was removed, or no subscription matched.'),
                    400 => new Response(description: 'The token is missing or invalid.'),
                ],
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
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Subscribe to newsletter',
        ),
    ]
)]
class Newsletter
{
    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    /**
     * The customer email.
     */
    #[ApiProperty(readable: false, writable: true)]
    #[Groups(['query', 'mutation'])]
    public ?string $customerEmail;

    /**
     * The success.
     */
    #[ApiProperty(readable: true, writable: false)]
    public bool $success;

    /**
     * The message.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $message;
}
