<?php

namespace Webkul\BagistoApi\Admin\Audit;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\User\Models\Admin;

class AdminApiAuthAuditor
{
    /**
     * The audit event of a successful login.
     */
    public const EVENT_LOGIN = 'login';

    /**
     * The audit event of a login with wrong credentials or by an inactive admin.
     */
    public const EVENT_LOGIN_FAILED = 'login_failed';

    /**
     * The audit event of a login refused after too many failed attempts.
     */
    public const EVENT_LOCKOUT = 'lockout';

    /**
     * The audit event of a refresh token exchanged for a new pair.
     */
    public const EVENT_REFRESH = 'refresh';

    /**
     * The audit event of an already used refresh token presented again, which revokes its session.
     */
    public const EVENT_REFRESH_REUSE = 'refresh_reuse';

    /**
     * The audit event of an admin logging out of one session.
     */
    public const EVENT_LOGOUT = 'logout';

    /**
     * The audit event of an admin logging out of every session.
     */
    public const EVENT_LOGOUT_ALL = 'logout_all';

    /**
     * The audit event of one session revoked from the sessions list.
     */
    public const EVENT_SESSION_REVOKED = 'session_revoked';

    /**
     * The audit event of every session of an admin revoked by another admin.
     */
    public const EVENT_SESSIONS_REVOKED = 'sessions_revoked';

    /**
     * The audit event of an API token used from a domain it is not bound to.
     */
    public const EVENT_DOMAIN_MISMATCH = 'domain_mismatch';

    /**
     * The tag every authentication audit row carries.
     */
    public const TAG = 'auth';

    /**
     * Create a new authentication auditor.
     */
    public function __construct(
        protected AdminApiAuditRecorder $recorder,
        protected AdminApiAuditContext $context,
    ) {}

    /**
     * Write an audit row for an authentication event about a session or an API token.
     */
    public function record(
        string $event,
        ?Admin $admin,
        AdminApiSession|AdminApiToken|null $subject,
        Request $request,
        array $details = [],
    ): void {
        if (! config('bagistoapi.audit.enabled', true)) {
            return;
        }

        $this->recorder->writeEvent([
            'history_id' => $this->context->historyId ?? (string) Str::uuid(),
            'event' => $event,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'old_values' => null,
            'new_values' => $details ?: null,
            'user_type' => $admin ? Admin::class : null,
            'user_id' => $admin?->id,
            'admin_name' => $admin?->name,
            'token_id' => $subject?->getKey(),
            'token_name' => $subject?->name,
            'token_kind' => $this->tokenKind($subject),
            'channel' => AdminAuthHelper::channel($request),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'tags' => self::TAG,
        ]);
    }

    /**
     * The token kind of the audited subject.
     */
    protected function tokenKind(AdminApiSession|AdminApiToken|null $subject): ?string
    {
        return match (true) {
            $subject instanceof AdminApiSession => AdminApiAudit::TOKEN_KIND_SESSION,
            $subject instanceof AdminApiToken => AdminApiAudit::TOKEN_KIND_API_TOKEN,
            default => null,
        };
    }
}
