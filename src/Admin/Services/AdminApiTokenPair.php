<?php

namespace Webkul\BagistoApi\Admin\Services;

use Carbon\CarbonInterface;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;

class AdminApiTokenPair
{
    /**
     * Create a token pair issued for a session.
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly CarbonInterface $accessTokenExpiresAt,
        public readonly string $refreshToken,
        public readonly int $refreshTokenId,
        public readonly CarbonInterface $refreshTokenExpiresAt,
        public readonly AdminApiSession $session,
        public readonly CarbonInterface $issuedAt,
    ) {}

    /**
     * Seconds until the access token expires.
     */
    public function expiresIn(): int
    {
        return max(0, $this->accessTokenExpiresAt->getTimestamp() - $this->issuedAt->getTimestamp());
    }
}
