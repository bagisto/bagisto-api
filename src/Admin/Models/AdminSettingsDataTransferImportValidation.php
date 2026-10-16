<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportActionInput;
use Webkul\BagistoApi\Admin\Resolver\AdminSettingsDataTransferImportValidationResolver;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportValidationProcessor;

/**
 * Validation of a file too large for one request: either window by window, or as a
 * queued background batch whose progress is polled until it reports the verdict.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImportValidation',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/data-transfer/imports/{id}/validate-chunk',
            requirements: ['id' => '\d+'],
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportValidationProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Validate the next window of an import file',
                description: 'Validates the next window of rows of the uploaded file and returns progress, resuming where the previous call left off. Call it until `done` is true; the last call carries `isValid` and the finalised `import`. An importer without windowed validation validates the whole file in one call.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'Validation progress.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => 7,
                            'state' => 'pending',
                            'total' => 5000,
                            'processed' => 1000,
                            'invalid' => 3,
                            'progress' => 20,
                            'done' => false,
                            'isValid' => true,
                            'import' => null,
                        ]]]),
                    ),
                    '400' => new Model\Response(description: 'The file could not be validated.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.import.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/settings/data-transfer/imports/{id}/validate/queue',
            requirements: ['id' => '\d+'],
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportValidationProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Queue the validation of an import',
                description: 'Dispatches the validation as a background queue batch and returns the row `total`. Poll `GET .../validate/status` until `done`. A `total` of 0 means nothing was fanned out (an empty file or a bad header) and the import was validated straight away.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'Validation dispatched.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => 7,
                            'state' => 'validating',
                            'total' => 5000,
                            'processed' => 0,
                            'invalid' => null,
                            'progress' => 0,
                            'done' => false,
                            'isValid' => null,
                            'import' => null,
                        ]]]),
                    ),
                    '400' => new Model\Response(description: 'The validation could not be queued.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.import.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/data-transfer/imports/{id}/validate/status',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsDataTransferImportValidationProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Queued validation progress',
                description: 'Progress of a queued validation. Once `done`, it carries the verdict `isValid` and the finalised `import`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Import ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Validation progress.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => 7,
                            'state' => 'validated',
                            'total' => 5000,
                            'processed' => 5000,
                            'invalid' => null,
                            'progress' => 100,
                            'done' => true,
                            'isValid' => true,
                            'import' => [
                                'id' => 7,
                                'state' => 'validated',
                                'type' => 'products',
                                'action' => 'append',
                                'processed_rows_count' => 5000,
                                'invalid_rows_count' => 0,
                                'errors_count' => 0,
                            ],
                        ]]]),
                    ),
                    '400' => new Model\Response(description: 'The progress could not be read.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.import.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'chunk',
            input: AdminSettingsDataTransferImportActionInput::class,
            read: false,
            processor: AdminSettingsDataTransferImportValidationProcessor::class,
            description: 'Validate the next window of an import file.',
        ),
        new Mutation(
            name: 'queue',
            input: AdminSettingsDataTransferImportActionInput::class,
            read: false,
            processor: AdminSettingsDataTransferImportValidationProcessor::class,
            description: 'Queue the validation of an import.',
        ),
        new Query(
            resolver: AdminSettingsDataTransferImportValidationResolver::class,
            args: ['importId' => ['type' => 'Int!']],
            normalizationContext: ['groups' => ['query']],
            description: 'Queued validation progress.',
        ),
    ],
)]
class AdminSettingsDataTransferImportValidation
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $state = null;

    #[ApiProperty(writable: false, description: 'Number of data rows in the file.')]
    #[Groups(['query'])]
    public ?int $total = null;

    #[ApiProperty(writable: false, description: 'Rows validated so far.')]
    #[Groups(['query'])]
    public ?int $processed = null;

    #[ApiProperty(writable: false, description: 'Invalid rows found so far; windowed validation only.')]
    #[Groups(['query'])]
    public ?int $invalid = null;

    #[ApiProperty(writable: false, description: 'Percentage done, 0 to 100.')]
    #[Groups(['query'])]
    public ?int $progress = null;

    #[ApiProperty(writable: false, description: 'True once validation is finished.')]
    #[Groups(['query'])]
    public ?bool $done = null;

    #[ApiProperty(writable: false, description: 'Whether the import can be started; set once done.')]
    #[Groups(['query'])]
    public ?bool $is_valid = null;

    #[ApiProperty(writable: false, description: 'The finalised import record; set once done.')]
    #[Groups(['query'])]
    public ?array $import = null;
}
