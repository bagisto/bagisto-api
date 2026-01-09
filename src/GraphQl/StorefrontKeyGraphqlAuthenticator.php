<?php

namespace Webkul\BagistoApi\GraphQl;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Webkul\BagistoApi\Services\StorefrontKeyService;

/**
 * Authenticates GraphQL operations using X-STOREFRONT-KEY header
 */
class StorefrontKeyGraphqlAuthenticator
{
    public function __construct(
        protected StorefrontKeyService $storefrontKeyService,
        protected Request $request
    ) {}

    public function authenticate(): void
    {
        if ($this->isIntrospectionQuery($this->request)) {
            return;
        }

        $key = $this->request->header('X-STOREFRONT-KEY');

        if (! $key) {
            throw new BadRequestException('X-STOREFRONT-KEY header is required');
        }

        $ipAddress = $this->request->ip();
        $validation = $this->storefrontKeyService->validate($key, $ipAddress);

        if (! $validation['valid']) {
            throw new BadRequestException('Invalid storefront key');
        }

        $storefront = $validation['storefront'];
        $rateLimit = $this->storefrontKeyService->checkRateLimit($storefront);

        if (! $rateLimit['allowed']) {
            throw new BadRequestException('Rate limit exceeded. Please retry after '.$rateLimit['reset_at'].' seconds');
        }

        $this->request->attributes->set('storefront_key', $storefront);
        $this->request->attributes->set('rate_limit', $rateLimit);
    }

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
        } catch (\Exception $e) {
            return false;
        }
    }
}
