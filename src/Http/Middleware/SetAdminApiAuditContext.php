<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditContext;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder;

class SetAdminApiAuditContext
{
    /**
     * The write methods constant.
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
     * Handle the request.
     */
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

    /**
     * Terminate.
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
            $request->except(['password', 'password_confirmation']) ?: null,
        );
    }

    /**
     * Should audit.
     */
    protected function shouldAudit(Request $request): bool
    {
        return config('bagistoapi.audit.enabled', true)
            && $request->is('api/admin/*')
            && in_array($request->method(), self::WRITE_METHODS, true)
            && Auth::guard('admin-api')->check();
    }

    /**
     * Token id from bearer.
     */
    protected function tokenIdFromBearer(Request $request): ?int
    {
        $bearer = (string) $request->bearerToken();
        $id = explode('|', $bearer)[0] ?? null;

        return ctype_digit((string) $id) ? (int) $id : null;
    }

    /**
     * Tags from path.
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
     * Id from path.
     */
    protected function idFromPath(Request $request): ?int
    {
        $numeric = array_filter(explode('/', $request->path()), 'ctype_digit');

        return $numeric ? (int) end($numeric) : null;
    }

    /**
     * Event from method.
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
