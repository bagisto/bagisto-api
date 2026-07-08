<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditContext;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder;

/**
 * Populates AdminApiAuditContext for admin-API WRITE requests (POST/PUT/PATCH/
 * DELETE on /api/admin/*) so AdminApiAuditRecorder records the changes against
 * the calling admin + integration token. GET requests are never given a
 * context, so reads are not audited.
 *
 * On terminate, writes a single "envelope" row if the request was a successful
 * write that produced no Eloquent model events (the rare raw-DB/pivot-only case).
 */
class SetAdminApiAuditContext
{
    protected const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(
        protected AdminApiAuditContext $context,
        protected AdminApiAuditRecorder $recorder,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldAudit($request)) {
            $admin = Auth::guard('admin-api')->user();
            $token = $admin?->current_access_token ?? null;

            $tokenId = $token->id ?? $this->tokenIdFromBearer($request);

            $this->context->activate([
                'history_id' => (string) Str::uuid(),
                'admin_id' => $admin?->id,
                'admin_name' => $admin?->name,
                'token_id' => $tokenId,
                'token_name' => $token->name ?? null,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'tags' => $this->tagsFromPath($request),
            ]);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (
            ! $this->context->isActive()
            || $this->context->rowCount() > 0
            || $response->getStatusCode() < 200
            || $response->getStatusCode() >= 300
            // GraphQL queries and mutations are both POST to one endpoint, so a
            // "no model events" request may well be a read — never synthesise an
            // envelope row for it. Mutations are still recorded via model events.
            || str_ends_with($request->path(), 'graphql')
        ) {
            return;
        }

        $this->recorder->writeEnvelope(
            null,
            $this->idFromPath($request),
            $this->eventFromMethod($request->method()),
            $request->except(['password', 'password_confirmation']) ?: null,
        );
    }

    protected function shouldAudit(Request $request): bool
    {
        return config('bagistoapi.audit.enabled', true)
            && $request->is('api/admin/*')
            && in_array($request->method(), self::WRITE_METHODS, true)
            && Auth::guard('admin-api')->check();
    }

    protected function tokenIdFromBearer(Request $request): ?int
    {
        $bearer = (string) $request->bearerToken();
        $id = explode('|', $bearer)[0] ?? null;

        return ctype_digit((string) $id) ? (int) $id : null;
    }

    protected function tagsFromPath(Request $request): ?string
    {
        $path = trim(Str::after($request->path(), 'api/admin'), '/');

        $segments = array_values(array_filter(
            explode('/', $path),
            fn ($s) => $s !== '' && ! ctype_digit($s),
        ));

        return implode('.', array_slice($segments, 0, 2)) ?: null;
    }

    protected function idFromPath(Request $request): ?int
    {
        $numeric = array_filter(explode('/', $request->path()), 'ctype_digit');

        return $numeric ? (int) end($numeric) : null;
    }

    protected function eventFromMethod(string $method): string
    {
        return match (strtoupper($method)) {
            'POST' => 'created',
            'DELETE' => 'deleted',
            default => 'updated',
        };
    }
}
