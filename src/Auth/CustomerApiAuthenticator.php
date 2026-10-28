<?php

namespace Webkul\BagistoApi\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Services\Jwt\InvalidJwtException;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtVerifier;
use Webkul\BagistoApi\Exception\CustomerAuthException;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Repositories\CustomerApiSessionRepository;
use Webkul\BagistoApi\Support\RequestChannel;

class CustomerApiAuthenticator
{
    /**
     * Seconds between two writes of a session's last-used details from the same address.
     */
    public const USAGE_WRITE_INTERVAL_SECONDS = 60;

    /**
     * Create a new customer authenticator.
     */
    public function __construct(
        protected JwtVerifier $verifier,
        protected CustomerApiSessionRepository $sessionRepository,
    ) {}

    /**
     * Authenticate a customer access token, its live session and channel, and record where it was used from.
     *
     * @throws CustomerAuthException
     */
    public function authenticate(string $bearer, Request $request): CustomerApiSession
    {
        try {
            $claims = $this->verifier->verifyCustomerAccessToken($bearer);
        } catch (InvalidJwtException $exception) {
            throw new CustomerAuthException($exception->getMessage(), $exception->errorCode(), $exception->getStatusCode());
        }

        $session = $this->sessionRepository->findWithCustomer($claims['sid']);

        if (
            ! $session
            || $session->customer_id !== $claims['sub']
            || ! $session->isActive()
            || ! $session->customer
            || ! $session->customer->status
            || $session->channel_id !== (int) RequestChannel::resolve($request)?->id
        ) {
            throw new CustomerAuthException(
                trans('bagistoapi::app.graphql.auth.invalid-or-expired-token'),
                CustomerAuthException::CODE_UNAUTHENTICATED,
            );
        }

        $this->recordUsage($session, $request);

        return $session;
    }

    /**
     * The transport of a request: GraphQL for the GraphQL endpoint, REST otherwise.
     */
    public static function channel(Request $request): string
    {
        return str_ends_with(rtrim($request->getPathInfo(), '/'), '/graphql')
            ? CustomerApiSession::CHANNEL_GRAPHQL
            : CustomerApiSession::CHANNEL_REST;
    }

    /**
     * Store the last-used details, at most once a minute unless the client address changed.
     */
    protected function recordUsage(CustomerApiSession $session, Request $request): void
    {
        $ip = $request->ip();

        if (
            $session->last_used_at !== null
            && $session->last_used_at->greaterThan(Carbon::now()->subSeconds(self::USAGE_WRITE_INTERVAL_SECONDS))
            && $session->last_ip_address === $ip
        ) {
            return;
        }

        $this->sessionRepository->touchUsage($session, [
            'last_used_at' => Carbon::now(),
            'last_ip_address' => $ip,
            'last_user_agent' => Str::limit((string) $request->userAgent(), 1023, '') ?: null,
            'last_channel' => static::channel($request),
        ]);
    }
}
