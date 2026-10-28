<?php

namespace Webkul\BagistoApi\Admin\Services;

interface DomainResolver
{
    /**
     * The IPv4 and IPv6 addresses a host's A and AAAA records point to.
     *
     * @return array<int, string>
     */
    public function resolve(string $host): array;
}
