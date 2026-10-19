<?php

namespace Webkul\BagistoApi\Tests\Concerns;

trait AssertsSanitisedHtml
{
    /**
     * Rich text carrying a script tag, Blade echoes and directives, and raw PHP.
     */
    protected function unsafeHtml(): string
    {
        return '<p>Safe text</p><script>alert(1)</script>{{ $secret }}{!! $raw !!}@php echo 1; @endphp<?php echo 2; ?>';
    }

    /**
     * Assert the stored value is what core's `clean_content()` makes of the sent one.
     */
    protected function assertSanitisedHtml(?string $stored, string $sent): void
    {
        expect($stored)->toBe(clean_content($sent));
        expect($stored)->toContain('Safe text');
        expect($stored)->not->toContain('<script');
        expect($stored)->not->toContain('{{');
        expect($stored)->not->toContain('{!!');
        expect($stored)->not->toContain('@php');
        expect($stored)->not->toContain('<?php');
    }
}
