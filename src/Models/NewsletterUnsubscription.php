<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterOutput;
use Webkul\BagistoApi\Dto\UnsubscribeFromNewsletterInput;
use Webkul\BagistoApi\State\Processor\NewsletterUnsubscriptionProcessor;

#[ApiResource(
    shortName: 'NewsletterUnsubscription',
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'create',
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
            description: 'Unsubscribe using the opaque newsletter token. Unknown tokens return generic success.',
        ),
    ],
)]
class NewsletterUnsubscription {}
