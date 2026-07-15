<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminConfigurationUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminConfigurationUpdateProcessor;

/**
 * Admin Configuration bulk-update.
 *
 * REST   : POST /api/admin/configuration (JSON or multipart for file fields)
 * GraphQL: createAdminConfigurationUpdate mutation (scalars only)
 *
 * Pipeline:
 *   1. Resolve admin / permission gate (system.configuration.edit).
 *   2. Load field defs for `slug`. 404 if no Item matches.
 *   3. Every key in `values` must start with `slug.` (anti-scope-escape).
 *   4. Server-side Validator built from each field's `validation` string.
 *   5. Decide scope columns from `channel_based` / `locale_based` per field.
 *   6. File fields: handled by CoreConfigRepository via request()->hasFile().
 *   7. Scalar upsert via repository.
 *   8. `core.configuration.save.before` + `.after` events.
 *   9. Return the freshly-resolved values (same shape as GET).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminConfigurationUpdate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/configuration',
            inputFormats: [
                'json' => ['application/json'],
                'multipart' => ['multipart/form-data'],
            ],
            processor: AdminConfigurationUpdateProcessor::class,
            status: 200,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Configuration'],
                summary: 'Bulk-update configuration values for one slug',
                description: 'Upserts every value in `values` under the given slug, validating server-side against each field\'s registered `validation` string. Send as `application/json` for scalars or as `multipart/form-data` (with `values[<dotted.code>]` as the file part) when uploading file/image fields.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['slug', 'values'],
                                'properties' => [
                                    'slug' => ['type' => 'string', 'example' => 'sales.order_settings'],
                                    'channel' => ['type' => 'string', 'example' => 'default'],
                                    'locale' => ['type' => 'string', 'example' => 'en'],
                                    'values' => [
                                        'type' => 'object',
                                        'example' => [
                                            'sales.order_settings.reorder.admin' => '1',
                                            'sales.order_settings.reorder.shop' => '0',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['slug'],
                                'properties' => [
                                    'slug' => ['type' => 'string'],
                                    'channel' => ['type' => 'string'],
                                    'locale' => ['type' => 'string'],
                                    'values[general.design.admin_logo.logo_image]' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Configuration updated. Returns the freshly-resolved values for the slug.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Configuration updated successfully.',
                                    'slug' => 'sales.order_settings',
                                    'channel' => 'default',
                                    'locale' => 'en',
                                    'values' => [
                                        'sales.order_settings.reorder.admin' => '1',
                                        'sales.order_settings.reorder.shop' => '0',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failed, scope-escape, missing slug/values, or custom-view field write attempt.'),
                    '404' => new Model\Response(description: 'Slug not registered.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                    '403' => new Model\Response(description: 'Missing system.configuration.edit permission.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminConfigurationUpdateInput::class,
            processor: AdminConfigurationUpdateProcessor::class,
            description: 'Bulk-update configuration. Becomes createAdminConfigurationUpdate. File/image-type fields are REST-only.',
        ),
    ],
)]
class AdminConfigurationUpdate
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $slug = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;

    #[ApiProperty(writable: false)]
    public ?string $channel = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    /**
     * @var array<string, string|null>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $values = null;
}
