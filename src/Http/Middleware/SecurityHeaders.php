<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds essential HTTP security headers to API responses
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $this->isApiRequest($request)) {
            return $response;
        }

        $this->addSecurityHeaders($response);

        return $response;
    }

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->path(), 'api/');
    }

    private function addSecurityHeaders($response): void
    {
        $headers = [
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'DENY',
            'X-XSS-Protection'        => '1; mode=block',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Permissions-Policy'      => 'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()',
            'Content-Security-Policy' => $this->getCSPHeader(),
        ];

        if ($this->shouldUseHSTS()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }
    }

    private function shouldUseHSTS(): bool
    {
        return app()->environment('production') || config('api-platform.force_https', false);
    }

    private function getCSPHeader(): string
    {
        $defaultCSP = "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
            ."style-src 'self' 'unsafe-inline'; "
            ."img-src 'self' data: https:; "
            ."font-src 'self'; "
            ."connect-src 'self'; "
            ."frame-ancestors 'none'; "
            ."base-uri 'self'; "
            ."form-action 'self'";

        return config('api-platform.csp_header', $defaultCSP);
    }
}
