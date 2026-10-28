<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminConfigurationDownloadProvider;

/**
 * Downloads the file stored for a file or image configuration field. REST only, as
 * binary streams are not transportable over GraphQL.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminConfigurationDownload',
    operations: [
        new Get(
            uriTemplate: '/configuration/download',
            provider: AdminConfigurationDownloadProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Admin Configuration'],
                summary: 'Download a configuration file',
                description: 'Streams the file saved for a `file` or `image` configuration field as an attachment. Send `Accept: application/octet-stream`. `channel` and `locale` pick the scoped value for channel- or locale-based fields and default to the current ones.',
                parameters: [
                    new Model\Parameter('code', 'query', 'Full dotted field code.', true, schema: ['type' => 'string', 'example' => 'sales.invoice_settings.pdf_print_outs.logo']),
                    new Model\Parameter('channel', 'query', 'Channel code.', false, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('locale', 'query', 'Locale code.', false, schema: ['type' => 'string', 'example' => 'en']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'The stored file is downloaded.'),
                    '404' => new Model\Response(description: 'Unknown field, or no file is stored for it.'),
                    '422' => new Model\Response(description: 'Missing code, or the field is not a file or image field.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                    '403' => new Model\Response(description: 'Missing the configuration permission.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminConfigurationDownload
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $code = null;
}
