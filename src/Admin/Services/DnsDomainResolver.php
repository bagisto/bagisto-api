<?php

namespace Webkul\BagistoApi\Admin\Services;

class DnsDomainResolver implements DomainResolver
{
    /**
     * The IPv4 and IPv6 addresses a host's A and AAAA records, or the hosts file, point to.
     *
     * @return array<int, string>
     */
    public function resolve(string $host): array
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA);

        $addresses = @gethostbynamel($host) ?: [];

        foreach (is_array($records) ? $records : [] as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;

            if (is_string($address)) {
                $addresses[] = $address;
            }
        }

        return array_values(array_unique($addresses));
    }
}
