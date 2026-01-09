<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Validates Bearer token (JWT) for user authentication on protected routes
 */
class VerifyBearerToken
{
    /**
     * Routes that require Bearer token authentication
     * (in addition to X-STOREFRONT-KEY)
     */
    protected array $protectedRoutes = [
        '/api/shop/orders',
        '/api/shop/cart',
        '/api/shop/checkout',
        '/api/shop/profile',
        '/api/shop/wishlist',
        '/api/admin',  // All admin routes
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        // Check if this route requires Bearer token
        if (! $this->requiresBearerToken($path)) {
            return $next($request);
        }

        // Get Bearer token from Authorization header
        $token = $this->getBearerToken($request);

        if (! $token) {
            return $this->sendAuthenticationError('Bearer token is required for this operation');
        }

        try {
            // Verify and decode JWT token
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return $this->sendAuthenticationError('Invalid token or user not found', 'invalid_token');
            }

            // Check if token is expired
            if ($this->isTokenExpired($token)) {
                return $this->sendAuthenticationError('Token has expired', 'token_expired');
            }

            // Store authenticated user in request
            $request->attributes->set('auth_user', $user);
            $request->setUserResolver(fn () => $user);

        } catch (JWTException $e) {
            return $this->sendAuthenticationError('Invalid token: '.$e->getMessage(), 'invalid_token');
        } catch (\Exception $e) {
            return $this->sendAuthenticationError('Authentication error: '.$e->getMessage());
        }

        return $next($request);
    }

    protected function requiresBearerToken(string $path): bool
    {
        foreach ($this->protectedRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }

        return false;
    }

    protected function getBearerToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7); // Remove 'Bearer ' prefix
    }

    protected function isTokenExpired(string $token): bool
    {
        try {
            $decoded = JWTAuth::parseToken()->getPayload();
            $exp = $decoded->get('exp');

            return $exp && $exp < now()->timestamp;
        } catch (\Exception $e) {
            return true; // Treat any error as expired
        }
    }

    /**
     * Send authentication error response
     */
    protected function sendAuthenticationError(string $message, string $error = 'missing_token'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error'   => $error,
            'errors'  => [
                [
                    'message'    => $message,
                    'extensions' => [
                        'code' => 'UNAUTHENTICATED',
                    ],
                ],
            ],
        ], 401);
    }
}
