<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsApiTokenRevokeInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsApiTokenCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsApiTokenItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsApiTokenProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminApiToken',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/settings/api-tokens',
            provider: AdminSettingsApiTokenCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: API Tokens'],
                summary: 'List API tokens',
                description: 'The API tokens server integrations use, newest first, revoked and expired ones included. Requires `settings.api_tokens`; without `settings.api_tokens.manage_others` only the caller\'s own tokens are listed. The token string itself is never returned, only its last characters (`tokenPreview`). API tokens are created in the admin panel or with `php artisan bagisto-api:api-token:create`.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page (1-based).', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: [
                        'type' => 'integer',
                        'example' => 10,
                    ]),
                    new Model\Parameter('admin_id', 'query', 'Filter by the admin the token acts as.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('status', 'query', 'Filter by status.', false, schema: [
                        'type' => 'string',
                        'enum' => AdminApiToken::STATUSES,
                    ]),
                    new Model\Parameter('domain', 'query', 'Filter by bound domain.', false, schema: [
                        'type' => 'string',
                        'example' => 'erp.example.com',
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in { data, meta } envelope.'),
                    '403' => new Model\Response(description: 'The role lacks `settings.api_tokens`.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/api-tokens/{id}',
            provider: AdminSettingsApiTokenItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: API Tokens'],
                summary: 'API token detail',
                description: 'One API token, with where it was last used from and the addresses its domain last resolved to. Requires `settings.api_tokens`, and `settings.api_tokens.manage_others` for a token of another admin.',
                parameters: [
                    new Model\Parameter('id', 'path', 'API token ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 4,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single API token.'),
                    '403' => new Model\Response(description: 'The role lacks `settings.api_tokens`.'),
                    '404' => new Model\Response(description: 'No API token the caller may see has this id.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/api-tokens/{id}',
            processor: AdminSettingsApiTokenProcessor::class,
            requirements: ['id' => '\d+'],
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: API Tokens'],
                summary: 'Revoke an API token',
                description: 'Revokes the API token; its next request is answered with `401` and `api_token_revoked`. The token stays in the list for the audit trail. Requires `settings.api_tokens.delete`, and `settings.api_tokens.manage_others` for a token of another admin.',
                parameters: [
                    new Model\Parameter('id', 'path', 'API token ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 4,
                    ]),
                ],
                responses: [
                    '204' => new Model\Response(description: 'API token revoked.'),
                    '403' => new Model\Response(description: 'The role lacks `settings.api_tokens.delete`.'),
                    '404' => new Model\Response(description: 'No API token the caller may manage has this id.'),
                    '409' => new Model\Response(description: 'The API token is already revoked.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsApiTokenCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'admin_id' => ['type' => 'Int'],
                'status' => ['type' => 'String'],
                'domain' => ['type' => 'String'],
            ],
            description: 'API tokens (cursor pagination). Requires settings.api_tokens; only your own without settings.api_tokens.manage_others.',
        ),
        new Query(
            provider: AdminSettingsApiTokenItemProvider::class,
            description: 'API token detail by id. Requires settings.api_tokens.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsApiTokenRevokeInput::class,
            processor: AdminSettingsApiTokenProcessor::class,
            read: false,
            description: 'Revoke an API token. Becomes deleteAdminApiToken; requires settings.api_tokens.delete.',
        ),
    ],
)]
class AdminSettingsApiToken
{
    use AcceptsCamelCaseWrites;

    /**
     * The API token id.
     */
    #[ApiProperty(identifier: true, writable: false, example: 4)]
    public ?int $id = null;

    /**
     * The id of the admin the token acts as.
     */
    #[ApiProperty(writable: false, example: 3)]
    public ?int $admin_id = null;

    /**
     * The name of the admin the token acts as.
     */
    #[ApiProperty(writable: false, example: 'ERP Integration')]
    public ?string $admin_name = null;

    /**
     * The id of the admin who created the token, or null when it was created with the console command.
     */
    #[ApiProperty(writable: false, example: 1)]
    public ?int $created_by_admin_id = null;

    /**
     * The name of the admin who created the token.
     */
    #[ApiProperty(writable: false, example: 'Example Admin')]
    public ?string $created_by_name = null;

    /**
     * The token name.
     */
    #[ApiProperty(writable: false, example: 'ERP sync')]
    public ?string $name = null;

    /**
     * The last characters of the token string, to recognise it.
     */
    #[ApiProperty(writable: false, example: 'x2Qk9fLw')]
    public ?string $token_preview = null;

    /**
     * The domain the token is bound to.
     */
    #[ApiProperty(writable: false, example: 'erp.example.com')]
    public ?string $domain = null;

    /**
     * The addresses the domain last resolved to.
     */
    #[ApiProperty(writable: false, example: ['203.0.113.20'])]
    public ?array $resolved_ips = null;

    /**
     * When the domain was last resolved.
     */
    #[ApiProperty(writable: false, example: '2026-10-21T10:05:00+05:30')]
    public ?string $resolved_at = null;

    /**
     * How many requests were refused because they came from another domain.
     */
    #[ApiProperty(writable: false, example: 0)]
    public ?int $domain_mismatch_count = null;

    /**
     * When a request was last refused because it came from another domain.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $last_domain_mismatch_at = null;

    /**
     * When the token expires, or null when it never expires.
     */
    #[ApiProperty(writable: false, example: '2027-10-21T23:59:59+05:30')]
    public ?string $expires_at = null;

    /**
     * When the token was last used, recorded at most once a minute.
     */
    #[ApiProperty(writable: false, example: '2026-10-21T10:05:00+05:30')]
    public ?string $last_used_at = null;

    /**
     * The client address of the latest request.
     */
    #[ApiProperty(writable: false, example: '203.0.113.20')]
    public ?string $last_ip_address = null;

    /**
     * The user agent of the latest request.
     */
    #[ApiProperty(writable: false, example: 'ExampleErp/2.1')]
    public ?string $last_user_agent = null;

    /**
     * The channel of the latest request: rest or graphql.
     */
    #[ApiProperty(writable: false, example: 'rest')]
    public ?string $last_channel = null;

    /**
     * When the token was revoked, or null while it is not.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $revoked_at = null;

    /**
     * Why the token was revoked.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $revoked_reason = null;

    /**
     * The id of the admin who revoked the token.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?int $revoked_by_admin_id = null;

    /**
     * The token status: active, expired or revoked.
     */
    #[ApiProperty(writable: false, example: 'active')]
    public ?string $status = null;

    /**
     * Whether the request was made with this API token.
     */
    #[ApiProperty(writable: false, example: false)]
    public ?bool $is_current = null;

    /**
     * When the token was created.
     */
    #[ApiProperty(writable: false, example: '2026-10-21T10:00:00+05:30')]
    public ?string $created_at = null;

    /**
     * The confirmation message of a revoke.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $message = null;
}
