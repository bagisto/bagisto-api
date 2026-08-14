<?php

namespace Webkul\BagistoApi\Traits;

use Illuminate\Database\Eloquent\Model;

trait ServesLoadedTranslation
{
    public function getRelationValue($key)
    {
        if (
            $key === 'translation'
            && ! $this->relationLoaded('translation')
            && $this->relationLoaded('translations')
        ) {
            $this->setRelation('translation', $this->translationFromLoaded());
        }

        return parent::getRelationValue($key);
    }

    protected function translationFromLoaded(): ?Model
    {
        return $this->translations->firstWhere('locale', app()->getLocale())
            ?? $this->translations->firstWhere('locale', config('translatable.fallback_locale'))
            ?? $this->translations->first();
    }
}
