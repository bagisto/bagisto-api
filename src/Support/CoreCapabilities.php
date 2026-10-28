<?php

namespace Webkul\BagistoApi\Support;

use Illuminate\Support\Facades\Schema;
use Throwable;
use Webkul\Attribute\Models\AttributeFamily;
use Webkul\Core\Rules\Regex;
use Webkul\Marketing\Models\Template;
use Webkul\Product\Models\ProductImageTranslation;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Repositories\SectionRepository;

/**
 * What the installed Bagisto core can do. Every version-dependent branch reads this.
 *
 * BACKWARD COMPATIBILITY: remove this class, and its callers, when the minimum
 * supported core is 2.4.10.
 */
class CoreCapabilities
{
    /** @var array<string, bool> */
    protected array $resolved = [];

    /** Theme customizations became theme sections in 2.4.10. */
    public function hasAppearanceSections(): bool
    {
        return $this->remember(
            'appearance-sections',
            fn () => class_exists(Section::class)
                && class_exists(SectionRepository::class)
        );
    }

    /** Product images carry a translated `alt_text` from 2.4.10. */
    public function hasProductImageAltText(): bool
    {
        return $this->remember(
            'product-image-alt-text',
            fn () => class_exists(ProductImageTranslation::class)
        );
    }

    /** `product_flat` gained derived columns in 2.4.10. */
    public function hasProductFlatDerivedColumns(): bool
    {
        return $this->remember(
            'product-flat-derived-columns',
            fn () => $this->hasColumn('product_flat', 'images_count')
        );
    }

    /** An attribute's `regex` is validated by a core rule from 2.4.10. */
    public function hasAttributeRegexRule(): bool
    {
        return $this->remember(
            'attribute-regex-rule',
            fn () => class_exists(Regex::class)
        );
    }

    /** From 2.4.10 an attribute family is protected by its code, not by being the last one. */
    public function hasDefaultAttributeFamilyCode(): bool
    {
        return $this->remember(
            'default-attribute-family-code',
            fn () => defined(AttributeFamily::class.'::DEFAULT_CODE')
        );
    }

    /** Email templates and marketing events expose their campaigns from 2.4.10. */
    public function hasCampaignRelations(): bool
    {
        return $this->remember(
            'campaign-relations',
            fn () => method_exists(Template::class, 'campaigns')
        );
    }

    /** Reporting only — prefer a capability above. */
    public function version(): string
    {
        return (string) core()->version();
    }

    /**
     * @param  callable(): bool  $probe
     */
    protected function remember(string $key, callable $probe): bool
    {
        return $this->resolved[$key] ??= (bool) $probe();
    }

    /** A schema read is unavailable during install and while migrations run. */
    protected function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }
}
