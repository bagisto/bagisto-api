<?php

namespace Webkul\BagistoApi\Admin\Services;

use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\User\Models\Admin;

class AdminApiCredentialRevoker
{
    /**
     * The session kept while the password changes, the one the admin changed it from.
     */
    protected ?int $preservedSessionId = null;

    /**
     * What the last automatic revocation ended: sessions and API tokens.
     *
     * @var array{sessions: int, api_tokens: int}
     */
    protected array $lastRevocation = ['sessions' => 0, 'api_tokens' => 0];

    /**
     * Create a new credential revoker.
     */
    public function __construct(
        protected AdminApiTokenService $tokenService,
        protected AdminApiTokenRepository $apiTokenRepository,
        protected AdminApiTwoFactorService $twoFactorService,
        protected AdminApiAuthSettings $settings,
        protected AdminApiAuthAuditor $auditor,
    ) {}

    /**
     * Run a change that may update the password while keeping one session signed in.
     */
    public function preserving(?int $sessionId, callable $callback): mixed
    {
        $previous = $this->preservedSessionId;

        $this->preservedSessionId = $sessionId;

        $this->lastRevocation = ['sessions' => 0, 'api_tokens' => 0];

        try {
            return $callback();
        } finally {
            $this->preservedSessionId = $previous;
        }
    }

    /**
     * End the admin's other sessions, and API tokens when configured, after a password change.
     */
    public function passwordChanged(Admin $admin): void
    {
        $this->twoFactorService->revokePendingForAdmin($admin->id);

        $sessions = $this->settings->revokeSessionsOnPasswordChange()
            ? $this->tokenService->revokeAllForAdmin($admin->id, AdminApiSession::REVOKED_PASSWORD_CHANGE, $this->preservedSessionId)
            : 0;

        $apiTokens = $this->settings->revokeApiTokensOnPasswordChange()
            ? $this->revokeApiTokens($admin, AdminApiSession::REVOKED_PASSWORD_CHANGE)
            : 0;

        $this->recorded(AdminApiSession::REVOKED_PASSWORD_CHANGE, $admin, $sessions, $apiTokens);
    }

    /**
     * End every session of a deactivated admin; API tokens are only refused while the admin is inactive.
     */
    public function deactivated(Admin $admin): void
    {
        $this->twoFactorService->revokePendingForAdmin($admin->id);

        $sessions = $this->tokenService->revokeAllForAdmin($admin->id, AdminApiSession::REVOKED_DEACTIVATED);

        $this->recorded(AdminApiSession::REVOKED_DEACTIVATED, $admin, $sessions, 0);
    }

    /**
     * End every session after a password reset, whatever the configuration, and API tokens when configured.
     */
    public function passwordReset(Admin $admin): void
    {
        $this->twoFactorService->revokePendingForAdmin($admin->id);

        $sessions = $this->tokenService->revokeAllForAdmin($admin->id, AdminApiSession::REVOKED_PASSWORD_RESET);

        $apiTokens = $this->settings->revokeApiTokensOnPasswordChange()
            ? $this->revokeApiTokens($admin, AdminApiSession::REVOKED_PASSWORD_RESET)
            : 0;

        $this->recorded(AdminApiSession::REVOKED_PASSWORD_RESET, $admin, $sessions, $apiTokens);
    }

    /**
     * What the last automatic revocation ended inside the current preserving() call.
     *
     * @return array{sessions: int, api_tokens: int}
     */
    public function lastRevocation(): array
    {
        return $this->lastRevocation;
    }

    /**
     * Revoke every API token of an admin, firing the before and after events.
     */
    protected function revokeApiTokens(Admin $admin, string $reason): int
    {
        Event::dispatch('bagistoapi.admin.api-token.revoke-all.before', $admin->id);

        $revoked = $this->apiTokenRepository->revokeAllForAdmin($admin->id, $reason);

        Event::dispatch('bagistoapi.admin.api-token.revoke-all.after', $admin->id);

        return $revoked;
    }

    /**
     * Remember what a revocation ended and audit it when it ended anything.
     */
    protected function recorded(string $reason, Admin $admin, int $sessions, int $apiTokens): void
    {
        $this->lastRevocation = [
            'sessions' => $this->lastRevocation['sessions'] + $sessions,
            'api_tokens' => $this->lastRevocation['api_tokens'] + $apiTokens,
        ];

        if ($sessions + $apiTokens === 0) {
            return;
        }

        $this->auditor->record(AdminApiAuthAuditor::EVENT_CREDENTIALS_AUTO_REVOKED, $admin, null, request(), [
            'reason' => $reason,
            'admin_id' => (int) $admin->id,
            'sessions' => $sessions,
            'api_tokens' => $apiTokens,
        ]);
    }
}
