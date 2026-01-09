<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Handles user authentication with JWT Bearer tokens
 */
class AuthenticationController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        try {

            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid email or password',
                    'error'   => 'invalid_credentials',
                ], 401);
            }

            $user = JWTAuth::user();

            return response()->json([
                'message'    => 'Login successful',
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL() * 60, // in seconds
                'user'       => [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => $user->name,
                ],
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token creation failed',
                'error'   => 'token_creation_failed',
            ], 500);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message'    => 'Registration successful',
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user'       => [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => $user->name,
                ],
            ], 201);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error'   => 'registration_failed',
            ], 500);
        }
    }

    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $token = JWTAuth::parseToken()->refresh();

            return response()->json([
                'message'    => 'Token refreshed',
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token refresh failed',
                'error'   => 'token_refresh_failed',
            ], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::parseToken()->invalidate();

            return response()->json([
                'message' => 'Logout successful',
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error'   => 'logout_failed',
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     *
     * GET /api/shop/me
     *
     * Header:
     * Authorization: Bearer {token}
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'user' => $user,
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'User not found',
                'error'   => 'user_not_found',
            ], 404);
        }
    }
}
