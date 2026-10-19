<?php

namespace Webkul\BagistoApi\Models;

use Webkul\Attribute\Contracts\Attribute as AttributeContract;
use Webkul\Attribute\Models\Attribute as BaseAttribute;
use Webkul\Attribute\Models\AttributeTranslation;

class CoreAttribute extends BaseAttribute implements AttributeContract
{
    /**
     * The table.
     */
    protected $table = 'attributes';

    /**
     * The translation model.
     */
    public $translationModel = AttributeTranslation::class;

    /**
     * The resolved locale.
     */
    protected ?string $resolvedLocale = null;

    /**
     * The resolved locale for.
     */
    protected ?string $resolvedLocaleFor = null;

    /**
     * Get foreign key.
     */
    public function getForeignKey()
    {
        return 'attribute_id';
    }

    /**
     * Locale.
     */
    protected function locale()
    {
        $for = (string) ($this->defaultLocale ?? '');

        if (
            $this->resolvedLocale !== null
            && $this->resolvedLocaleFor === $for
        ) {
            return $this->resolvedLocale;
        }

        $this->resolvedLocaleFor = $for;

        return $this->resolvedLocale = parent::locale();
    }
}
