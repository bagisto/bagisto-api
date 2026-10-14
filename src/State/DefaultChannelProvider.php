<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\PartialPaginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Models\DefaultChannel;
use Webkul\Core\Models\Channel;

/**
 * Provides the default channel for the store.
 */
class DefaultChannelProvider implements ProviderInterface
{
    public function __construct() {}

    /**
     * Provide the default channel.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Get default channel
        /** @var Channel|null $defaultChannel */
        $defaultChannel = core()->getDefaultChannel();

        if (! $defaultChannel) {
            return new PartialPaginator(new LengthAwarePaginator([], 0, 15, 1));
        }

        // Eager load all relationships
        $defaultChannel->load([
            'default_locale',
            'locales',
            'base_currency',
            'currencies',
            'inventory_sources',
            'root_category',
            'translations',
        ]);

        $output = new DefaultChannel;
        $output->id = $defaultChannel->id;
        $output->code = $defaultChannel->code;
        $output->name = $defaultChannel->name;
        $output->description = $defaultChannel->description;
        $output->theme = $defaultChannel->theme;
        $output->hostname = $defaultChannel->hostname;
        $output->logoUrl = $defaultChannel->logo_url;
        $output->faviconUrl = $defaultChannel->favicon_url;
        $output->timezone = $defaultChannel->timezone;
        $output->isMaintenanceOn = (bool) $defaultChannel->is_maintenance_on;
        $output->rootCategoryId = $defaultChannel->root_category_id;
        $output->defaultLocaleId = $defaultChannel->default_locale_id;
        $output->baseCurrencyId = $defaultChannel->base_currency_id;
        $output->createdAt = $defaultChannel->created_at?->format('Y-m-d H:i:s');
        $output->updatedAt = $defaultChannel->updated_at?->format('Y-m-d H:i:s');
        $output->maintenanceModeText = $defaultChannel->maintenance_mode_text;

        // Set nested relationships
        if ($defaultChannel->default_locale) {
            $output->defaultLocale = $this->transformLocale($defaultChannel->default_locale);
        }

        if ($defaultChannel->base_currency) {
            $output->baseCurrency = $this->transformCurrency($defaultChannel->base_currency);
        }

        if ($defaultChannel->locales) {
            $output->locales = $defaultChannel->locales->map(fn ($locale) => $this->transformLocale($locale))->toArray();
        }

        if ($defaultChannel->currencies) {
            $output->currencies = $defaultChannel->currencies->map(fn ($currency) => $this->transformCurrency($currency))->toArray();
        }

        if ($defaultChannel->inventory_sources) {
            $output->inventorySources = $defaultChannel->inventory_sources->map(fn ($source) => $this->transformInventorySource($source))->toArray();
        }

        if ($defaultChannel->root_category) {
            $output->rootCategory = $this->transformCategory($defaultChannel->root_category);
        }

        if ($defaultChannel->translations) {
            $output->translations = $defaultChannel->translations->map(fn ($translation) => $this->transformChannelTranslation($translation))->toArray();
        }

        // Handle homeSeo
        if ($defaultChannel->home_seo) {
            $output->homeSeo = $this->transformHomeSeo($defaultChannel->home_seo);
        }

        return new PartialPaginator(new LengthAwarePaginator([$output], 1, 15, 1));
    }

    private function transformLocale($locale)
    {
        $transformed = new \stdClass;
        $transformed->id = $locale->id;
        $transformed->code = $locale->code;
        $transformed->name = $locale->name;
        $transformed->englishName = $locale->english_name;
        $transformed->direction = $locale->direction;
        $transformed->status = $locale->status;
        $transformed->createdAt = $locale->created_at?->format('Y-m-d H:i:s');
        $transformed->updatedAt = $locale->updated_at?->format('Y-m-d H:i:s');

        return $transformed;
    }

    private function transformCurrency($currency)
    {
        $transformed = new \stdClass;
        $transformed->id = $currency->id;
        $transformed->code = $currency->code;
        $transformed->name = $currency->name;
        $transformed->symbol = $currency->symbol;
        $transformed->decimal = $currency->decimal_places;
        $transformed->createdAt = $currency->created_at?->format('Y-m-d H:i:s');
        $transformed->updatedAt = $currency->updated_at?->format('Y-m-d H:i:s');

        if ($currency->exchange_rate) {
            $transformed->exchangeRate = new \stdClass;
            $transformed->exchangeRate->id = $currency->exchange_rate->id;
            $transformed->exchangeRate->targetCurrency = $currency->exchange_rate->target_currency;
            $transformed->exchangeRate->rate = (float) $currency->exchange_rate->rate;
            $transformed->exchangeRate->createdAt = $currency->exchange_rate->created_at?->format('Y-m-d H:i:s');
            $transformed->exchangeRate->updatedAt = $currency->exchange_rate->updated_at?->format('Y-m-d H:i:s');
        }

        return $transformed;
    }

    private function transformInventorySource($source)
    {
        $transformed = new \stdClass;
        $transformed->id = $source->id;
        $transformed->code = $source->code;
        $transformed->name = $source->name;
        $transformed->description = $source->description;
        $transformed->contactName = $source->contact_name;
        $transformed->contactEmail = $source->contact_email;
        $transformed->contactNumber = $source->contact_number;
        $transformed->contactFax = $source->contact_fax;
        $transformed->country = $source->country;
        $transformed->state = $source->state;
        $transformed->city = $source->city;
        $transformed->street = $source->street;
        $transformed->postcode = $source->postcode;
        $transformed->priority = $source->priority;
        $transformed->latitude = $source->latitude;
        $transformed->longitude = $source->longitude;
        $transformed->status = (bool) $source->status;

        return $transformed;
    }

    private function transformCategory($category)
    {
        $transformed = new \stdClass;
        $transformed->id = $category->id;
        $transformed->name = $category->name;
        $transformed->slug = $category->slug;
        $transformed->description = $category->description;
        $transformed->status = (bool) $category->status;
        $transformed->displayMode = $category->display_mode;
        $transformed->logoUrl = $category->logo_url;
        $transformed->bannerUrl = $category->banner_url;
        $transformed->metaTitle = $category->meta_title;
        $transformed->metaDescription = $category->meta_description;
        $transformed->metaKeywords = $category->meta_keywords;
        $transformed->createdAt = $category->created_at?->format('Y-m-d H:i:s');
        $transformed->updatedAt = $category->updated_at?->format('Y-m-d H:i:s');

        return $transformed;
    }

    private function transformChannelTranslation($translation)
    {
        $transformed = new \stdClass;
        $transformed->id = $translation->id;
        $transformed->locale = $translation->locale;
        $transformed->name = $translation->name;
        $transformed->description = $translation->description;

        return $transformed;
    }

    private function transformHomeSeo($homeSeo)
    {
        if (is_array($homeSeo)) {
            $transformed = new \stdClass;
            $transformed->metaTitle = $homeSeo['meta_title'] ?? null;
            $transformed->metaDescription = $homeSeo['meta_description'] ?? null;
            $transformed->metaKeywords = $homeSeo['meta_keywords'] ?? null;

            return $transformed;
        }

        return null;
    }
}
