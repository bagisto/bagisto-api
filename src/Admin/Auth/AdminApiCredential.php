<?php

namespace Webkul\BagistoApi\Admin\Auth;

use Carbon\CarbonInterface;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\User\Models\Admin;

class AdminApiCredential
{
    /**
     * Create the credential a request authenticated with: a login session or an API token.
     */
    public function __construct(
        public readonly Admin $admin,
        public readonly ?AdminApiSession $session = null,
        public readonly ?AdminApiToken $apiToken = null,
        public readonly ?CarbonInterface $expiresAt = null,
    ) {}

    /**
     * The credential of a request made with a session's access token.
     */
    public static function forSession(Admin $admin, AdminApiSession $session, ?CarbonInterface $expiresAt): self
    {
        return new self($admin, session: $session, expiresAt: $expiresAt);
    }

    /**
     * The credential of a request made with an API token.
     */
    public static function forApiToken(Admin $admin, AdminApiToken $apiToken): self
    {
        return new self($admin, apiToken: $apiToken, expiresAt: $apiToken->expires_at);
    }

    /**
     * Whether the request carries a login session's access token.
     */
    public function isSession(): bool
    {
        return $this->session !== null;
    }

    /**
     * Whether the request carries an API token.
     */
    public function isApiToken(): bool
    {
        return $this->apiToken !== null;
    }

    /**
     * The token kind recorded in the audit trail: "session" or "api_token".
     */
    public function kind(): string
    {
        return $this->isSession()
            ? AdminApiAudit::TOKEN_KIND_SESSION
            : AdminApiAudit::TOKEN_KIND_API_TOKEN;
    }

    /**
     * The id of the session or API token.
     */
    public function id(): int
    {
        return (int) ($this->session?->id ?? $this->apiToken?->id);
    }

    /**
     * The device name of the session, or the name of the API token.
     */
    public function name(): ?string
    {
        return $this->session?->name ?? $this->apiToken?->name;
    }
}
