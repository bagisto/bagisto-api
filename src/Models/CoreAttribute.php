<?php

namespace Webkul\BagistoApi\Models;

use Webkul\Attribute\Contracts\Attribute as AttributeContract;
use Webkul\Attribute\Models\Attribute as BaseAttribute;
use Webkul\Attribute\Models\AttributeTranslation;

/**
 * Concord substitution for the core Attribute model, adding only a memoised
 * locale(). Attribute declares $translatedAttributes = ['name'], so every read
 * of an attribute name calls TranslatableModel::locale(), which re-resolves the
 * locale through the container.
 *
 * The $table / getForeignKey() / $translationModel overrides exist because
 * Eloquent and Astrotomic derive all three from the class name, which would
 * yield core_attributes, core_attribute_id and CoreAttributeTranslation.
 */
class CoreAttribute extends BaseAttribute implements AttributeContract
{
    protected $table = 'attributes';

    public $translationModel = AttributeTranslation::class;

    protected ?string $resolvedLocale = null;

    protected ?string $resolvedLocaleFor = null;

    public function getForeignKey()
    {
        return 'attribute_id';
    }

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
