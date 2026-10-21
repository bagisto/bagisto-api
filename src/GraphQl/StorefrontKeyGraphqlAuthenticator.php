<?php

namespace Webkul\BagistoApi\GraphQl;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Webkul\BagistoApi\Services\StorefrontKeyService;

class StorefrontKeyGraphqlAuthenticator
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected StorefrontKeyService $storefrontKeyService,
        protected Request $request
    ) {}

    /**
     * Authenticate.
     */
    public function authenticate(): void
    {
        if ($this->isIntrospectionQuery($this->request)) {
            return;
        }

        $key = $this->request->header('X-STOREFRONT-KEY');

        if (! $key) {
            throw new BadRequestException(__('bagistoapi::app.graphql.headers.storefront-key-required'));
        }

        $ipAddress = $this->request->ip();
        $validation = $this->storefrontKeyService->validate($key, $ipAddress);

        if (! $validation['valid']) {
            throw new BadRequestException(__('bagistoapi::app.graphql.headers.storefront-key-invalid'));
        }

        $storefront = $validation['storefront'];
        $rateLimit = $this->storefrontKeyService->checkRateLimit($storefront);

        if (! $rateLimit['allowed']) {
            throw new BadRequestException(__('bagistoapi::app.graphql.headers.storefront-key-rate-limited', ['seconds' => $rateLimit['reset_at']]));
        }

        $this->request->attributes->set('storefront_key', $storefront);
        $this->request->attributes->set('rate_limit', $rateLimit);
    }

    /**
     * Whether introspection query.
     */
    protected function isIntrospectionQuery(Request $request): bool
    {
        $body = $request->getContent();

        if (empty($body)) {
            return false;
        }

        try {
            $data = json_decode($body, true);
            $query = $data['query'] ?? '';

            return strpos($query, '__schema') !== false ||
                   strpos($query, '__type') !== false;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
