<?php

namespace Webkul\BagistoApi\Tests\Fakes;

use Webkul\BagistoApi\Admin\Services\DomainResolver;

class FakeDomainResolver implements DomainResolver
{
    /**
     * The addresses each host resolves to.
     *
     * @var array<string, array<int, string>>
     */
    protected array $records = [];

    /**
     * The hosts looked up, in order.
     *
     * @var array<int, string>
     */
    protected array $lookups = [];

    /**
     * Create a resolver that knows the given hosts.
     *
     * @param  array<string, array<int, string>>  $records
     */
    public function __construct(array $records = [])
    {
        $this->records = $records;
    }

    /**
     * Point a host at the given addresses.
     *
     * @param  array<int, string>  $addresses
     */
    public function set(string $host, array $addresses): self
    {
        $this->records[$host] = $addresses;

        return $this;
    }

    /**
     * The addresses a host points to, recording the lookup.
     *
     * @return array<int, string>
     */
    public function resolve(string $host): array
    {
        $this->lookups[] = $host;

        return $this->records[$host] ?? [];
    }

    /**
     * How many times a host was looked up.
     */
    public function lookupsOf(string $host): int
    {
        return count(array_keys($this->lookups, $host, true));
    }
}
