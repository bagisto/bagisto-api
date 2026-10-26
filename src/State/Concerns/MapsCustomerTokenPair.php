<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Models\CustomerAuthTokenResponse;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiTokenPair;

trait MapsCustomerTokenPair
{
    /**
     * Fill a login, registration or refresh response from an issued token pair.
     */
    protected function mapTokenPair(CustomerApiTokenPair $pair, CustomerAuthTokenResponse $response): CustomerAuthTokenResponse
    {
        $session = $pair->session;

        $customer = $session->customer;

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

        $response->customer = $customer
            ? [
                'id' => (int) $customer->id,
                'firstName' => $customer->first_name,
                'lastName' => $customer->last_name,
                'email' => $customer->email,
            ]
            : null;

        return $response;
    }
}
