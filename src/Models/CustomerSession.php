<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\CustomerSessionRevokeInput;
use Webkul\BagistoApi\OpenApi\CustomerAuthResponses;
use Webkul\BagistoApi\State\CustomerSessionCollectionProvider;
use Webkul\BagistoApi\State\CustomerSessionProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerSession',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customer/sessions',
            provider: CustomerSessionCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Customer Authentication'],
                summary: "List the signed-in customer's active sessions",
                description: 'Every live session of the signed-in customer, newest first, with where each was last used from. `isCurrent` marks the session of the access token sent. Needs a customer access token.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The live sessions, newest first.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'id' => 41,
                                    'name' => 'iPhone 15',
                                    'clientType' => 'mobile',
                                    'ipAddress' => '203.0.113.5',
                                    'userAgent' => 'Shop/1.0',
                                    'lastIpAddress' => '203.0.113.5',
                                    'lastUserAgent' => 'Shop/1.0',
                                    'lastUsedAt' => '2026-10-23T10:00:00+00:00',
                                    'lastChannel' => 'rest',
                                    'refreshExpiresAt' => '2026-11-22T10:00:00+00:00',
                                    'expiresAt' => '2027-01-21T10:00:00+00:00',
                                    'isCurrent' => true,
                                    'createdAt' => '2026-10-23T10:00:00+00:00',
                                    'message' => null,
                                ]],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(
                        description: 'The access token is missing, invalid or its session ended (`unauthenticated`), or it expired (`access_token_expired`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => CustomerAuthResponses::UNAUTHENTICATED,
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customer/sessions/{id}',
            processor: CustomerSessionProcessor::class,
            requirements: ['id' => '\d+'],
            read: false,
            openapi: new Model\Operation(
                tags: ['Customer Authentication'],
                summary: 'Revoke one of my sessions',
                description: 'Ends one live session of the signed-in customer, for example on a lost phone; its access and refresh tokens stop working at once. Revoking the current session logs it out.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Session ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 41,
                    ]),
                ],
                responses: [
                    '204' => new Model\Response(description: 'Session ended.'),
                    '401' => new Model\Response(description: 'The access token is missing, invalid or expired (`unauthenticated`, `access_token_expired`).'),
                    '404' => new Model\Response(description: 'No live session of the signed-in customer has this id.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CustomerSessionCollectionProvider::class,
            paginationEnabled: false,
            description: "The signed-in customer's active sessions, newest first.",
        ),
        new Mutation(
            name: 'delete',
            input: CustomerSessionRevokeInput::class,
            processor: CustomerSessionProcessor::class,
            read: false,
            description: "End one of the signed-in customer's sessions. Becomes deleteCustomerSession.",
        ),
    ],
)]
class CustomerSession implements SnakeCaseFieldsResource
{
    use AcceptsCamelCaseWrites;

    /**
     * The session id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

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
     * When the session's refresh token expires unless it is refreshed.
     */
    #[ApiProperty(writable: false)]
    public ?string $refresh_expires_at = null;

    /**
     * When the session reaches its absolute lifetime.
     */
    #[ApiProperty(writable: false)]
    public ?string $expires_at = null;

    /**
     * Whether this is the session of the access token sent.
     */
    #[ApiProperty(writable: false)]
    public ?bool $is_current = null;

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
     * Build the resource of a stored session.
     */
    public static function fromSession(CustomerApiSession $session, ?int $currentSessionId): self
    {
        $resource = new self;

        $resource->id = (int) $session->id;
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
        $resource->is_current = $currentSessionId === (int) $session->id;
        $resource->created_at = $session->created_at?->toIso8601String();

        return $resource;
    }
}
