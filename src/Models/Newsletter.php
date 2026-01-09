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
