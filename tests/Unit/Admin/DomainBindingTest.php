<?php

namespace Webkul\BagistoApi\Tests\Unit\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Mockery;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Admin\Services\DomainBinding;
use Webkul\BagistoApi\Admin\Services\DomainResolver;
use Webkul\BagistoApi\Tests\BagistoApiTest;
use Webkul\BagistoApi\Tests\Fakes\FakeDomainResolver;

class DomainBindingTest extends BagistoApiTest
{
    // ========================================================================
    // Normalisation
    // ========================================================================

    /**
     * Test a domain or URL is reduced to its lower-case host.
     */
    public function test_a_domain_or_url_is_reduced_to_its_host(): void
    {
        $cases = [
            'erp.example.com' => 'erp.example.com',
            'ERP.Example.COM' => 'erp.example.com',
            '  erp.example.com  ' => 'erp.example.com',
            'https://erp.example.com/sync?full=1' => 'erp.example.com',
            'http://erp.example.com:8443' => 'erp.example.com',
            'erp.example.com:8443/path' => 'erp.example.com',
            'erp.example.com.' => 'erp.example.com',
            'https://user:secret@erp.example.com/' => 'erp.example.com',
            'localhost' => 'localhost',
        ];

        foreach ($cases as $input => $expected) {
            $this->assertSame($expected, $this->binding()->normalize($input), "'{$input}' was not normalised");
        }
    }

    /**
     * Test an internationalised domain is stored in punycode.
     */
    public function test_an_internationalised_domain_becomes_punycode(): void
    {
        if (! function_exists('idn_to_ascii')) {
            $this->markTestSkipped('The intl extension is not loaded.');
        }

        $this->assertSame('xn--bcher-kva.de', $this->binding()->normalize('bücher.de'));
        $this->assertSame('xn--bcher-kva.de', $this->binding()->normalize('https://BÜCHER.de/shop'));
    }

    /**
     * Test addresses, wildcards and empty values are refused.
     */
    public function test_addresses_wildcards_and_empty_values_are_refused(): void
    {
        foreach ([
            '',
            '   ',
            null,
            '203.0.113.10',
            'https://203.0.113.10/sync',
            '[2001:db8::1]',
            'https://[2001:db8::1]:8443/',
            '*.example.com',
            'erp.*.com',
            'https://',
            'erp_example!.com',
        ] as $input) {
            $this->assertNull($this->binding()->normalize($input), var_export($input, true).' was accepted');
        }
    }

    // ========================================================================
    // Matching
    // ========================================================================

    /**
     * Test dns mode matches the client address against the cached addresses, IPv4 and IPv6 alike.
     */
    public function test_dns_mode_matches_cached_addresses(): void
    {
        $token = $this->token('erp.example.com', ['203.0.113.10', '2001:db8::1']);

        $this->assertTrue($this->binding()->matches($token, $this->request('203.0.113.10')));
        $this->assertTrue($this->binding()->matches($token, $this->request('2001:db8:0:0:0:0:0:1')));
        $this->assertFalse($this->binding()->matches($token, $this->request('203.0.113.11')));
        $this->assertFalse($this->binding()->matches($token, $this->request('2001:db8::2')));
    }

    /**
     * Test dns mode matches an IPv4-mapped IPv6 client address against the IPv4 record.
     */
    public function test_dns_mode_matches_an_ipv4_mapped_client_address(): void
    {
        $token = $this->token('erp.example.com', ['203.0.113.10']);

        $this->assertTrue($this->binding()->matches($token, $this->request('::ffff:203.0.113.10')));
        $this->assertFalse($this->binding()->matches($token, $this->request('::ffff:203.0.113.11')));
    }

    /**
     * Test an empty lookup is not cached, so the next request resolves the domain again.
     */
    public function test_an_empty_lookup_is_not_cached(): void
    {
        $resolver = new FakeDomainResolver;

        $this->app->instance(DomainResolver::class, $resolver);

        $token = new AdminApiToken;
        $token->domain = 'erp.example.com';

        $this->assertFalse($this->binding()->matches($token, $this->request('203.0.113.10')));
        $this->assertNull($token->resolved_at);

        $this->assertFalse($this->binding()->matches($token, $this->request('203.0.113.10')));
        $this->assertSame(2, $resolver->lookupsOf('erp.example.com'));
    }

    /**
     * Test dns mode ignores the Origin header, which any server can send.
     */
    public function test_dns_mode_ignores_the_origin_header(): void
    {
        $token = $this->token('erp.example.com', ['203.0.113.10']);

        $request = $this->request('198.51.100.5', ['HTTP_ORIGIN' => 'https://erp.example.com']);

        $this->assertFalse($this->binding()->matches($token, $request));
    }

    /**
     * Test origin mode matches the Origin host, then the Referer host, and ignores the client address.
     */
    public function test_origin_mode_matches_the_origin_or_referer_host(): void
    {
        $this->useBindingMode(AdminApiAuthSettings::BINDING_ORIGIN);

        $token = $this->token('erp.example.com', ['203.0.113.10']);

        $this->assertTrue($this->binding()->matches($token, $this->request('198.51.100.5', ['HTTP_ORIGIN' => 'https://ERP.example.com'])));
        $this->assertTrue($this->binding()->matches($token, $this->request('198.51.100.5', ['HTTP_REFERER' => 'https://erp.example.com/page'])));
        $this->assertFalse($this->binding()->matches($token, $this->request('203.0.113.10')));
        $this->assertFalse($this->binding()->matches($token, $this->request('203.0.113.10', ['HTTP_ORIGIN' => 'null'])));
        $this->assertFalse($this->binding()->matches($token, $this->request('203.0.113.10', ['HTTP_ORIGIN' => 'https://erp.example.com.evil.test'])));
        $this->assertFalse($this->binding()->matches($token, $this->request('203.0.113.10', [
            'HTTP_ORIGIN' => 'https://evil.test',
            'HTTP_REFERER' => 'https://erp.example.com/page',
        ])));
    }

    /**
     * Resolve domains through a fake resolver.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(DomainResolver::class, new FakeDomainResolver);
    }

    /**
     * The domain binding service.
     */
    protected function binding(): DomainBinding
    {
        return app(DomainBinding::class);
    }

    /**
     * An unsaved API token bound to a domain whose addresses were resolved a moment ago.
     */
    protected function token(string $domain, array $addresses): AdminApiToken
    {
        $token = new AdminApiToken;

        $token->domain = $domain;
        $token->resolved_ips = $addresses;
        $token->resolved_at = Carbon::now();

        return $token;
    }

    /**
     * A request from a client address with extra server variables.
     */
    protected function request(string $ip, array $server = []): Request
    {
        return Request::create('/api/admin/get', 'GET', server: array_merge(['REMOTE_ADDR' => $ip], $server));
    }

    /**
     * Make the settings report the given domain binding mode.
     */
    protected function useBindingMode(string $mode): void
    {
        $settings = Mockery::mock(AdminApiAuthSettings::class)->makePartial();

        $settings->shouldReceive('domainBindingMode')->andReturn($mode);

        $this->app->instance(AdminApiAuthSettings::class, $settings);
    }
}
