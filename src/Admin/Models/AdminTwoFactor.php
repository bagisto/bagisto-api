<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminTwoFactorCodeInput;
use Webkul\BagistoApi\Admin\Resolver\AdminTwoFactorSetupResolver;
use Webkul\BagistoApi\Admin\State\AdminTwoFactorProcessor;
use Webkul\BagistoApi\Admin\State\AdminTwoFactorProvider;

/**
 * Two-factor authentication for the token's own admin: set up, enable and disable,
 * mirroring the admin "My Account" two-factor panel.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminTwoFactor',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/account/two-factor',
            provider: AdminTwoFactorProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Two-factor setup',
                description: 'Returns the QR code (SVG and otpauth URL) to scan into an authenticator app. The secret is generated on the first call and reused until two-factor authentication is disabled. Refused once two-factor authentication is enabled.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Setup data.',
                        content: new \ArrayObject(['application/json' => ['example' => [[
                            'id' => 1,
                            'enabled' => false,
                            'qrCodeSvg' => '<svg xmlns="http://www.w3.org/2000/svg" ...></svg>',
                            'qrCodeUrl' => 'otpauth://totp/Bagisto:admin%40example.com?secret=JBSWY3DPEHPK3PXP&issuer=Bagisto',
                            'backupCodes' => null,
                            'message' => null,
                        ]]]]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '422' => new Model\Response(description: 'Two-factor authentication is already enabled.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/account/two-factor/enable',
            input: AdminTwoFactorCodeInput::class,
            processor: AdminTwoFactorProcessor::class,
            status: 200,
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Enable two-factor authentication',
                description: 'Verifies a six-digit code from the authenticator app against the setup secret and turns two-factor authentication on. Returns eight one-time backup codes, shown only here and emailed to the admin.',
                requestBody: new Model\RequestBody(required: true, content: new \ArrayObject([
                    'application/json' => [
                        'schema' => ['type' => 'object', 'required' => ['code'], 'properties' => ['code' => ['type' => 'string', 'pattern' => '^\d{6}$']]],
                        'example' => ['code' => '123456'],
                    ],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'Enabled.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'enabled' => true,
                            'backupCodes' => ['204871', '553019', '760342', '118265', '931577', '402968', '687120', '015443'],
                            'message' => 'Two-Factor Authentication enabled successfully.',
                        ]]]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '422' => new Model\Response(description: 'Invalid code, or setup was not run first.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/account/two-factor/disable',
            input: AdminTwoFactorCodeInput::class,
            processor: AdminTwoFactorProcessor::class,
            status: 200,
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Disable two-factor authentication',
                description: 'Turns two-factor authentication off and clears the secret and backup codes. While it is enabled, `code` must carry a current authenticator code or an unused backup code.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => [
                        'schema' => ['type' => 'object', 'properties' => ['code' => ['type' => 'string']]],
                        'example' => ['code' => '123456'],
                    ],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'Disabled.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'enabled' => false,
                            'backupCodes' => null,
                            'message' => 'Two-Factor Authentication has been disabled.',
                        ]]]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '422' => new Model\Response(description: 'Missing or invalid code.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'setup',
            resolver: AdminTwoFactorSetupResolver::class,
            args: [],
            normalizationContext: ['groups' => ['query']],
            description: 'Two-factor setup QR code for the authenticated admin.',
        ),
        new Mutation(
            name: 'enable',
            input: AdminTwoFactorCodeInput::class,
            processor: AdminTwoFactorProcessor::class,
            read: false,
            normalizationContext: ['groups' => ['query']],
            description: 'Enable two-factor authentication with a code from the authenticator app. The `id` input is ignored.',
        ),
        new Mutation(
            name: 'disable',
            input: AdminTwoFactorCodeInput::class,
            processor: AdminTwoFactorProcessor::class,
            read: false,
            normalizationContext: ['groups' => ['query']],
            description: 'Disable two-factor authentication. While enabled, pass a current code or a backup code. The `id` input is ignored.',
        ),
    ],
)]
class AdminTwoFactor
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?int $id = null;

    #[ApiProperty(writable: false, description: 'Whether two-factor authentication is on.')]
    #[Groups(['query'])]
    public ?bool $enabled = null;

    #[ApiProperty(writable: false, description: 'QR code as an inline SVG (setup only).')]
    #[Groups(['query'])]
    public ?string $qr_code_svg = null;

    #[ApiProperty(writable: false, description: 'otpauth:// URL encoded in the QR code (setup only).')]
    #[Groups(['query'])]
    public ?string $qr_code_url = null;

    #[ApiProperty(writable: false, description: 'One-time backup codes, returned only when two-factor authentication is enabled.')]
    #[Groups(['query'])]
    public ?array $backup_codes = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $message = null;
}
