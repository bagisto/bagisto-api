<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\NewsletterUnsubscribeInput;
use Webkul\BagistoApi\State\NewsletterUnsubscribeProcessor;

/**
 * Removes a newsletter subscription with the token from the newsletter email.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'NewsletterUnsubscribe',
    operations: [
        new Post(
            uriTemplate: '/newsletters/unsubscribe',
            input: NewsletterUnsubscribeInput::class,
            output: self::class,
            processor: NewsletterUnsubscribeProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Newsletter'],
                summary: 'Unsubscribe from the newsletter',
                description: 'Removes the newsletter subscription with the token from the unsubscribe link in the newsletter email (`/subscription/{token}`), as the storefront does. The answer is the same whether or not the token matched. No login is needed.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['token'],
                                'properties' => [
                                    'token' => ['type' => 'string', 'example' => '6a1f0c9e2b7d4'],
                                ],
                            ],
                            'example' => ['token' => '6a1f0c9e2b7d4'],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Unsubscribed, or no subscription used that token.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'You have been unsubscribed from the newsletter.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'The token is missing.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: NewsletterUnsubscribeInput::class,
            output: self::class,
            processor: NewsletterUnsubscribeProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ],
)]
class NewsletterUnsubscribe
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
