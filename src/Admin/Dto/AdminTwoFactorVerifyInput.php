<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class AdminTwoFactorVerifyInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The challenge token the login returned.
     */
    #[ApiProperty(description: 'The challengeToken returned by the login.', example: '41|9b1e7c...')]
    public ?string $challenge_token = null;

    /**
     * The 6-digit code from the authenticator app.
     */
    #[ApiProperty(description: 'The 6-digit code from the authenticator app. Send this or backupCode.', example: '123456')]
    public ?string $code = null;

    /**
     * One of the admin's backup codes, which is used up.
     */
    #[ApiProperty(description: 'One of the backup codes given when two-factor authentication was enabled; each works once. Send this or code.', example: '804213')]
    public ?string $backup_code = null;
}
