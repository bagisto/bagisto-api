<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Webkul\BagistoApi\Console\Commands\InstallApiPlatformCommand;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class InstallProviderRegistrationTest extends BagistoApiTest
{
    private const PROVIDER = 'Webkul\\BagistoApi\\Providers\\BagistoApiServiceProvider::class';

    /**
     * Build the command under test.
     */
    private function command(): InstallApiPlatformCommand
    {
        return new InstallApiPlatformCommand(new Filesystem);
    }

    public function test_registers_provider_in_a_modern_providers_file(): void
    {
        $content = "<?php\n\nreturn [\n    App\\Providers\\AppServiceProvider::class,\n];\n";

        $updated = $this->command()->injectProvider($content, self::PROVIDER);

        expect($updated)->toContain('    '.self::PROVIDER.",\n];");
        expect(substr_count($updated, self::PROVIDER))->toBe(1);
    }

    public function test_registers_provider_in_a_legacy_array_helper_file(): void
    {
        $content = "<?php\n\nreturn array(\n    A::class,\n]);\n";

        $updated = $this->command()->injectProvider($content, self::PROVIDER);

        expect($updated)->toContain(self::PROVIDER);
    }

    public function test_returns_null_when_the_array_end_cannot_be_found(): void
    {
        expect($this->command()->injectProvider("<?php\n\nreturn config('providers');\n", self::PROVIDER))->toBeNull();
    }
}
