<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Webkul\BagistoApi\Dto\GdprDataPdfOutput;
use Webkul\BagistoApi\Resolver\GdprDataPdfQueryResolver;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GdprDataPdf',
    operations: [],
    graphQlOperations: [
        new Query(
            resolver: GdprDataPdfQueryResolver::class,
            output: GdprDataPdfOutput::class,
            normalizationContext: [
                'groups' => ['query'],
            ],
            args: [],
            description: 'Return the authenticated customer\'s GDPR export as a base64-encoded PDF when GDPR is enabled.',
        ),
    ],
)]
class GdprDataPdf {}
