<?php

namespace Webkul\BagistoApi\Tests;

use Tests\TestCase;
use Webkul\Admin\Tests\Concerns\AdminTestBench;
use Webkul\BagistoApi\Support\CoreCapabilities;
use Webkul\Core\Tests\Concerns\CoreAssertions;

class BagistoApiTest extends TestCase
{
    use AdminTestBench, CoreAssertions;

    /**
     * BACKWARD COMPATIBILITY: a test covering behaviour only a newer core has skips on an
     * older one. Remove this and its callers when the minimum supported core is 2.4.10.
     */
    protected function core(): CoreCapabilities
    {
        return app(CoreCapabilities::class);
    }

    protected function skipUnlessCoreSupports(bool $supported, string $feature): void
    {
        if (! $supported) {
            $this->markTestSkipped($feature.' needs Bagisto 2.4.10 or newer (running '.$this->core()->version().').');
        }
    }
}
