<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSessionRevokeInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerApiSessionCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerApiSessionProcessor;
use Webkul\BagistoApi\Models\CustomerApiSession;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerApiSession',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customers/api-sessions',
            provider: AdminCustomerApiSessionCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers: API Sessions'],
                summary: 'List every active storefront customer session',
                description: 'Every live storefront login session of every customer, newest first, with where each was last used from. Requires `customers.api_sessions`.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page (1-based).', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: [
                        'type' => 'integer',
                        'example' => 10,
                    ]),
                    new Model\Parameter('customer_id', 'query', 'Filter by customer ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('client_type', 'query', 'Filter by client type.', false, schema: [
                        'type' => 'string',
                        'enum' => CustomerApiSession::CLIENT_TYPES,
                    ]),
                    new Model\Parameter('channel_id', 'query', 'Filter by the channel the session was created on.', false, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in { data, meta } envelope.'),
                    '403' => new Model\Response(description: 'The role lacks `customers.api_sessions`.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/customers/{customerId}/api-sessions',
            provider: AdminCustomerApiSessionCollectionProvider::class,
            paginationEnabled: false,
            requirements: ['customerId' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customers: API Sessions'],
                summary: "List a customer's active storefront sessions",
                description: 'The live storefront login sessions of one customer, newest first. Requires `customers.api_sessions`.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 1529,
                    ]),
                    new Model\Parameter('page', 'query', 'Page (1-based).', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: [
                        'type' => 'integer',
                        'example' => 10,
                    ]),
                    new Model\Parameter('client_type', 'query', 'Filter by client type.', false, schema: [
                        'type' => 'string',
                        'enum' => CustomerApiSession::CLIENT_TYPES,
                    ]),
                    new Model\Parameter('channel_id', 'query', 'Filter by the channel the session was created on.', false, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in { data, meta } envelope.'),
                    '403' => new Model\Response(description: 'The role lacks `customers.api_sessions`.'),
                    '404' => new Model\Response(description: 'No customer has this id.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customers/api-sessions/{id}',
            processor: AdminCustomerApiSessionProcessor::class,
            requirements: ['id' => '\d+'],
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers: API Sessions'],
                summary: 'Revoke a customer session',
                description: 'Ends one live storefront session of a customer; its access and refresh tokens stop working at once. Requires `customers.api_sessions.delete`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Session ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 41,
                    ]),
                ],
                responses: [
                    '204' => new Model\Response(description: 'Session ended.'),
                    '403' => new Model\Response(description: 'The role lacks `customers.api_sessions.delete`.'),
                    '404' => new Model\Response(description: 'No live customer session has this id.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerApiSessionCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'customer_id' => ['type' => 'Int'],
                'client_type' => ['type' => 'String'],
                'channel_id' => ['type' => 'Int'],
            ],
            description: 'Every active storefront customer session, or one customer\'s with customer_id (cursor pagination). Requires customers.api_sessions.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSessionRevokeInput::class,
            processor: AdminCustomerApiSessionProcessor::class,
            read: false,
            description: 'End one storefront customer session. Becomes deleteAdminCustomerApiSession; requires customers.api_sessions.delete.',
        ),
    ],
)]
class AdminCustomerApiSession
{
    use AcceptsCamelCaseWrites;

    /**
     * The session id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The customer the session belongs to.
     */
    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    /**
     * The customer's full name.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    /**
     * The customer's email address.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    /**
     * The channel the session was created on.
     */
    #[ApiProperty(writable: false)]
    public ?int $channel_id = null;

    /**
     * The device name given at login.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The kind of client: web, mobile, pos, desktop or other.
     */
    #[ApiProperty(writable: false)]
    public ?string $client_type = null;

    /**
     * The address the session was started from.
     */
    #[ApiProperty(writable: false)]
    public ?string $ip_address = null;

    /**
     * The user agent the session was started with.
     */
    #[ApiProperty(writable: false)]
    public ?string $user_agent = null;

    /**
     * The address the session was last used from.
     */
    #[ApiProperty(writable: false)]
    public ?string $last_ip_address = null;

    /**
     * The user agent the session was last used with.
     */
    #[ApiProperty(writable: false)]
    public ?string $last_user_agent = null;

    /**
     * When the session was last used, recorded at most once a minute.
     */
    #[ApiProperty(writable: false)]
    public ?string $last_used_at = null;

    /**
     * The transport the session was last used over: rest or graphql.
     */
    #[ApiProperty(writable: false)]
    public ?string $last_channel = null;

    /**
     * When the refresh token expires unless the session is refreshed.
     */
    #[ApiProperty(writable: false)]
    public ?string $refresh_expires_at = null;

    /**
     * When the session reaches its absolute lifetime.
     */
    #[ApiProperty(writable: false)]
    public ?string $expires_at = null;

    /**
     * When the session was ended, if it was.
     */
    #[ApiProperty(writable: false)]
    public ?string $revoked_at = null;

    /**
     * Why the session was ended.
     */
    #[ApiProperty(writable: false)]
    public ?string $revoked_reason = null;

    /**
     * When the session was started.
     */
    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    /**
     * The message of a revoke.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;

    /**
     * Build the resource of a stored customer session.
     */
    public static function fromSession(CustomerApiSession $session): self
    {
        $resource = new self;

        $customer = $session->customer;

        $resource->id = (int) $session->id;
        $resource->customer_id = $session->customer_id;
        $resource->customer_name = $customer ? trim($customer->first_name.' '.$customer->last_name) : null;
        $resource->customer_email = $customer?->email;
        $resource->channel_id = $session->channel_id;
        $resource->name = $session->name;
        $resource->client_type = $session->client_type;
        $resource->ip_address = $session->ip_address;
        $resource->user_agent = $session->user_agent;
        $resource->last_ip_address = $session->last_ip_address;
        $resource->last_user_agent = $session->last_user_agent;
        $resource->last_used_at = $session->last_used_at?->toIso8601String();
        $resource->last_channel = $session->last_channel;
        $resource->refresh_expires_at = $session->refresh_expires_at?->toIso8601String();
        $resource->expires_at = $session->expires_at?->toIso8601String();
        $resource->revoked_at = $session->revoked_at?->toIso8601String();
        $resource->revoked_reason = $session->revoked_reason;
        $resource->created_at = $session->created_at?->toIso8601String();

        return $resource;
    }
}
