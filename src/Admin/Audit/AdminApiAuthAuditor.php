<?php

namespace Webkul\BagistoApi\Admin\Audit;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\Customer\Models\Customer;
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
     * The audit event of a password accepted for an admin who must still pass a second factor.
     */
    public const EVENT_TWO_FACTOR_CHALLENGE = 'two_factor_challenge';

    /**
     * The audit event of a wrong two-factor or backup code.
     */
    public const EVENT_TWO_FACTOR_FAILED = 'two_factor_failed';

    /**
     * The audit event of a forgot password request.
     */
    public const EVENT_PASSWORD_RESET_REQUESTED = 'password_reset_requested';

    /**
     * The audit event of a password reset with a reset token.
     */
    public const EVENT_PASSWORD_RESET = 'password_reset';

    /**
     * The audit event of an admin updating their own account.
     */
    public const EVENT_ACCOUNT_UPDATED = 'account_updated';

    /**
     * The audit event of sessions or API tokens revoked by a password change, deactivation or reset.
     */
    public const EVENT_CREDENTIALS_AUTO_REVOKED = 'credentials_auto_revoked';

    /**
     * The event of an API token created in the panel or with the console command.
     */
    public const EVENT_API_TOKEN_CREATED = 'api_token_created';

    /**
     * The event of an API token given a new name.
     */
    public const EVENT_API_TOKEN_RENAMED = 'api_token_renamed';

    /**
     * The event of an API token revoked by an admin.
     */
    public const EVENT_API_TOKEN_REVOKED = 'api_token_revoked';

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
            'channel' => $this->channel($request),
            'method' => $request->method(),
            'url' => $request->url(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'tags' => self::TAG,
        ]);
    }

    /**
     * Write an audit row for an authentication event about a storefront customer session.
     */
    public function recordForCustomer(
        string $event,
        ?Customer $customer,
        ?CustomerApiSession $session,
        Request $request,
        array $details = [],
        ?Admin $actor = null,
    ): void {
        if (! config('bagistoapi.audit.enabled', true)) {
            return;
        }

        $this->recorder->writeEvent([
            'history_id' => $this->context->historyId ?? (string) Str::uuid(),
            'event' => $event,
            'auditable_type' => $session ? CustomerApiSession::class : null,
            'auditable_id' => $session?->getKey(),
            'old_values' => null,
            'new_values' => ($details + array_filter(['customer_id' => $customer?->id, 'admin_id' => $actor?->id])) ?: null,
            'user_type' => $customer ? Customer::class : null,
            'user_id' => $customer?->id,
            'admin_name' => $actor?->name,
            'token_id' => $session?->getKey(),
            'token_name' => $session?->name,
            'token_kind' => AdminApiAudit::TOKEN_KIND_CUSTOMER_SESSION,
            'channel' => $this->channel($request),
            'method' => $request->method(),
            'url' => $request->url(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'tags' => self::TAG,
        ]);
    }

    /**
     * The channel of the request: rest or graphql for the API, panel for the admin panel, cli without a route.
     */
    protected function channel(Request $request): string
    {
        if ($request->route() === null) {
            return AdminApiAudit::CHANNEL_CLI;
        }

        return $request->is('api/*')
            ? AdminAuthHelper::channel($request)
            : AdminApiAudit::CHANNEL_PANEL;
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
