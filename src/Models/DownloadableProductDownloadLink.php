<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\DownloadableProductDownloadLinkOutput;
use Webkul\BagistoApi\Dto\GenerateDownloadLinkInput;
use Webkul\BagistoApi\State\DownloadableProductProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'DownloadableProductDownloadLink',
    uriTemplate: '/downloadable-product-download-links',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            processor: DownloadableProductProcessor::class,
            input: GenerateDownloadLinkInput::class,
            output: DownloadableProductDownloadLinkOutput::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Check the customer may download the purchased link and return the URL that downloads it.',
        ),
    ]
)]
class DownloadableProductDownloadLink {}
