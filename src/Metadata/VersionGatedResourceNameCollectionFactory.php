<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use Webkul\BagistoApi\Support\CoreCapabilities;

/**
 * Keeps only the theme resources the installed core can serve.
 *
 * Discovery drops a resource whose parent class is missing, but the admin resources
 * extend plain Eloquent and name their table directly, so they need dropping here.
 *
 * BACKWARD COMPATIBILITY: remove this class and its extend() call when the minimum
 * supported core is 2.4.10.
 */
final class VersionGatedResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface
{
    /**
     * Resources that need core 2.4.10 or newer.
     *
     * @var list<string>
     */
    private const APPEARANCE_PREFIXES = [
        'Webkul\BagistoApi\Admin\Models\AdminAppearance',
        'Webkul\BagistoApi\Admin\Dto\AdminAppearance',
        'Webkul\BagistoApi\Models\Section',
        'Webkul\BagistoApi\Models\Theme',
    ];

    /**
     * Resources that need a core older than 2.4.10. Matched first, so ThemeCustomization
     * is not caught by the Theme prefix above.
     *
     * @var list<string>
     */
    private const LEGACY_PREFIXES = [
        'Webkul\BagistoApi\Admin\Models\AdminSettingsTheme',
        'Webkul\BagistoApi\Admin\Dto\AdminSettingsTheme',
        'Webkul\BagistoApi\Models\ThemeCustomization',
    ];

    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $inner,
        private readonly CoreCapabilities $capabilities,
    ) {}

    public function create(): ResourceNameCollection
    {
        $appearance = $this->capabilities->hasAppearanceSections();

        $classes = [];

        foreach ($this->inner->create() as $resourceClass) {
            if ($this->matches($resourceClass, self::LEGACY_PREFIXES)) {
                if (! $appearance) {
                    $classes[] = $resourceClass;
                }

                continue;
            }

            if (! $appearance && $this->matches($resourceClass, self::APPEARANCE_PREFIXES)) {
                continue;
            }

            $classes[] = $resourceClass;
        }

        return new ResourceNameCollection($classes);
    }

    /**
     * @param  list<string>  $prefixes
     */
    private function matches(string $resourceClass, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($resourceClass, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
