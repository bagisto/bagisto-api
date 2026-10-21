<?php

namespace Webkul\BagistoApi\Admin\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;

class DomainBinding
{
    /**
     * Create a new domain binding.
     */
    public function __construct(
        protected DomainResolver $resolver,
        protected AdminApiTokenRepository $apiTokenRepository,
        protected AdminApiAuthSettings $settings,
    ) {}

    /**
     * The lower-case ASCII host of a domain or URL, or null for an IP address, a wildcard or no host.
     */
    public function normalize(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $host = parse_url(str_contains($value, '://') ? $value : '//'.$value, PHP_URL_HOST);

        if (
            ! is_string($host)
            || $host === ''
        ) {
            return null;
        }

        $host = rtrim(strtolower($host), '.');

        if (
            str_contains($host, '*')
            || filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) !== false
        ) {
            return null;
        }

        $host = $this->toAscii($host);

        if (
            $host === null
            || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
        ) {
            return null;
        }

        return $host;
    }

    /**
     * Whether the request comes from the domain the API token is bound to, in the configured mode.
     */
    public function matches(AdminApiToken $token, Request $request): bool
    {
        if ($this->settings->domainBindingMode() === AdminApiAuthSettings::BINDING_ORIGIN) {
            return $this->matchesOrigin($token, $request);
        }

        return $this->matchesAddress($token, (string) $request->ip());
    }

    /**
     * Count a request made from the wrong domain against the token.
     */
    public function recordMismatch(AdminApiToken $token): AdminApiToken
    {
        return $this->apiTokenRepository->recordDomainMismatch($token);
    }

    /**
     * The addresses the token's domain resolves to, from the cache while it is fresh.
     *
     * @return array<int, string>
     */
    public function resolvedAddresses(AdminApiToken $token): array
    {
        $cacheSeconds = $this->settings->domainDnsCacheSeconds();

        if (
            is_array($token->resolved_ips)
            && $token->resolved_at !== null
            && $token->resolved_at->greaterThan(Carbon::now()->subSeconds($cacheSeconds))
        ) {
            return $token->resolved_ips;
        }

        $addresses = $this->resolver->resolve((string) $token->domain);

        $this->apiTokenRepository->storeResolvedIps($token, $addresses);

        return $addresses;
    }

    /**
     * Whether the Origin header, or the Referer when there is no Origin, names the token's domain.
     */
    protected function matchesOrigin(AdminApiToken $token, Request $request): bool
    {
        $source = $request->headers->get('Origin') ?: $request->headers->get('Referer');

        if (
            ! is_string($source)
            || $source === 'null'
        ) {
            return false;
        }

        return $this->normalize($source) === $token->domain;
    }

    /**
     * Whether the client address is one the token's domain resolves to.
     */
    protected function matchesAddress(AdminApiToken $token, string $clientIp): bool
    {
        $client = @inet_pton($clientIp);

        if ($client === false) {
            return false;
        }

        foreach ($this->resolvedAddresses($token) as $address) {
            if (@inet_pton((string) $address) === $client) {
                return true;
            }
        }

        return false;
    }

    /**
     * The punycode form of an internationalised host, or null when it cannot be converted.
     */
    protected function toAscii(string $host): ?string
    {
        if (
            preg_match('/[^\x20-\x7e]/', $host) !== 1
            || ! function_exists('idn_to_ascii')
        ) {
            return $host;
        }

        $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        return $ascii === false
            ? null
            : strtolower($ascii);
    }
}
