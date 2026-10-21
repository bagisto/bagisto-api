<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditContext;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;

class SetAdminApiAuditContext
{
    /**
     * The HTTP methods of the requests that are audited.
     */
    protected const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Create a new instance.
     */
    public function __construct(
        protected AdminApiAuditContext $context,
        protected AdminApiAuditRecorder $recorder,
    ) {}

    /**
     * Start auditing an authenticated admin API write, recording which session or API token made it and how.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldAudit($request)) {
            $credential = AdminAuthHelper::credential();

            $this->context->activate([
                'history_id' => (string) Str::uuid(),
                'admin_id' => $credential?->admin->id,
                'admin_name' => $credential?->admin->name,
                'token_id' => $credential?->id(),
                'token_name' => $credential?->name(),
                'token_kind' => $credential?->kind(),
                'channel' => AdminAuthHelper::channel($request),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'tags' => $this->tagsFromPath($request),
            ]);
        }

        return $next($request);
    }

    /**
     * Write the envelope row for an audited write that changed no audited model.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (
            ! $this->context->isActive()
            || $this->context->rowCount() > 0
            || $response->getStatusCode() < 200
            || $response->getStatusCode() >= 300
            || str_ends_with($request->path(), 'graphql')
        ) {
            return;
        }

        $this->recorder->writeEnvelope(
            null,
            $this->idFromPath($request),
            $this->eventFromMethod($request->method()),
            $request->except(['password', 'password_confirmation', 'current_password', 'refresh_token']) ?: null,
        );
    }

    /**
     * Whether the request is an authenticated admin API write while auditing is enabled.
     */
    protected function shouldAudit(Request $request): bool
    {
        return config('bagistoapi.audit.enabled', true)
            && $request->is('api/admin/*')
            && in_array($request->method(), self::WRITE_METHODS, true)
            && Auth::guard('admin-api')->check();
    }

    /**
     * The first two path segments after the admin prefix, as dotted tags.
     */
    protected function tagsFromPath(Request $request): ?string
    {
        $path = trim(Str::after($request->path(), 'api/admin'), '/');

        $segments = array_values(array_filter(
            explode('/', $path),
            fn ($segment) => $segment !== '' && ! ctype_digit($segment),
        ));

        return implode('.', array_slice($segments, 0, 2)) ?: null;
    }

    /**
     * The last numeric path segment, taken as the audited record's id.
     */
    protected function idFromPath(Request $request): ?int
    {
        $numeric = array_filter(explode('/', $request->path()), 'ctype_digit');

        return $numeric ? (int) end($numeric) : null;
    }

    /**
     * The audit event an HTTP method stands for.
     */
    protected function eventFromMethod(string $method): string
    {
        return match (strtoupper($method)) {
            'POST' => 'created',
            'DELETE' => 'deleted',
            default => 'updated',
        };
    }
}
