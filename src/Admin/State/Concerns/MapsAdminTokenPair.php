<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Models\AdminAuthTokenResponse;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenPair;
use Webkul\BagistoApi\Admin\State\AdminPermissionsProvider;

trait MapsAdminTokenPair
{
    /**
     * Fill a login or refresh response from an issued token pair.
     */
    protected function mapTokenPair(AdminApiTokenPair $pair, AdminAuthTokenResponse $response): AdminAuthTokenResponse
    {
        $session = $pair->session;

        $admin = $session->admin;

        $response->token_type = 'Bearer';
        $response->access_token = $pair->accessToken;
        $response->expires_in = $pair->expiresIn();
        $response->access_token_expires_at = $pair->accessTokenExpiresAt->toIso8601String();
        $response->refresh_token = $pair->refreshToken;
        $response->refresh_token_expires_at = $pair->refreshTokenExpiresAt->toIso8601String();

        $response->session = [
            'id' => (int) $session->id,
            'name' => $session->name,
            'clientType' => $session->client_type,
            'expiresAt' => $session->expires_at?->toIso8601String(),
        ];

        $response->admin = [
            'id' => (int) $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,

            'role' => $admin->role
                ? [
                    'id' => (int) $admin->role->id,
                    'name' => $admin->role->name,
                    'permissionType' => $admin->role->permission_type,
                ]
                : null,
        ];

        $response->permissions = AdminPermissionsProvider::buildPayload($admin)['permissions'];

        return $response;
    }
}
