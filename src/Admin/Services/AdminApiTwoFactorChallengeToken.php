<?php

namespace Webkul\BagistoApi\Admin\Services;

use Webkul\BagistoApi\Admin\Models\AdminApiTwoFactorChallenge;

class AdminApiTwoFactorChallengeToken
{
    /**
     * Create a two-factor challenge with its plain `<id>|<secret>` token, shown to the client once.
     */
    public function __construct(
        public readonly AdminApiTwoFactorChallenge $challenge,
        public readonly string $token,
    ) {}
}
