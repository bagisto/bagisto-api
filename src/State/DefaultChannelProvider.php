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
        $output->allowedIps = $defaultChannel->allowed_ips;
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
            $output->translations = $defaultChannel->translations->map(fn ($trans) => $this->transformChannelTranslation($trans))->toArray();
        }

        // Handle homeSeo
        if ($defaultChannel->home_seo) {
            $output->homeSeo = $this->transformHomeSeo($defaultChannel->home_seo);
        }

        return new PartialPaginator(new LengthAwarePaginator([$output], 1, 15, 1));
    }

    private function transformLocale($locale)
    {
        $obj = new \stdClass;
        $obj->id = $locale->id;
        $obj->code = $locale->code;
        $obj->name = $locale->name;
        $obj->englishName = $locale->english_name;
        $obj->direction = $locale->direction;
        $obj->status = $locale->status;
        $obj->createdAt = $locale->created_at?->format('Y-m-d H:i:s');
        $obj->updatedAt = $locale->updated_at?->format('Y-m-d H:i:s');

        return $obj;
    }

    private function transformCurrency($currency)
    {
        $obj = new \stdClass;
        $obj->id = $currency->id;
        $obj->code = $currency->code;
        $obj->name = $currency->name;
        $obj->symbol = $currency->symbol;
        $obj->decimal = $currency->decimal_places;
        $obj->createdAt = $currency->created_at?->format('Y-m-d H:i:s');
        $obj->updatedAt = $currency->updated_at?->format('Y-m-d H:i:s');

        if ($currency->exchange_rate) {
            $obj->exchangeRate = new \stdClass;
            $obj->exchangeRate->id = $currency->exchange_rate->id;
            $obj->exchangeRate->targetCurrency = $currency->exchange_rate->target_currency;
            $obj->exchangeRate->rate = (float) $currency->exchange_rate->rate;
            $obj->exchangeRate->createdAt = $currency->exchange_rate->created_at?->format('Y-m-d H:i:s');
            $obj->exchangeRate->updatedAt = $currency->exchange_rate->updated_at?->format('Y-m-d H:i:s');
        }

        return $obj;
    }

    private function transformInventorySource($source)
    {
        $obj = new \stdClass;
        $obj->id = $source->id;
        $obj->code = $source->code;
        $obj->name = $source->name;
        $obj->description = $source->description;
        $obj->contactName = $source->contact_name;
        $obj->contactEmail = $source->contact_email;
        $obj->contactNumber = $source->contact_number;
        $obj->contactFax = $source->contact_fax;
        $obj->country = $source->country;
        $obj->state = $source->state;
        $obj->city = $source->city;
        $obj->street = $source->street;
        $obj->postcode = $source->postcode;
        $obj->priority = $source->priority;
        $obj->latitude = $source->latitude;
        $obj->longitude = $source->longitude;
        $obj->status = (bool) $source->status;

        return $obj;
    }

    private function transformCategory($category)
    {
        $obj = new \stdClass;
        $obj->id = $category->id;
        $obj->name = $category->name;
        $obj->slug = $category->slug;
        $obj->description = $category->description;
        $obj->status = (bool) $category->status;
        $obj->displayMode = $category->display_mode;
        $obj->logoUrl = $category->logo_url;
        $obj->bannerUrl = $category->banner_url;
        $obj->metaTitle = $category->meta_title;
        $obj->metaDescription = $category->meta_description;
        $obj->metaKeywords = $category->meta_keywords;
        $obj->createdAt = $category->created_at?->format('Y-m-d H:i:s');
        $obj->updatedAt = $category->updated_at?->format('Y-m-d H:i:s');

        return $obj;
    }

    private function transformChannelTranslation($translation)
    {
        $obj = new \stdClass;
        $obj->id = $translation->id;
        $obj->locale = $translation->locale;
        $obj->name = $translation->name;
        $obj->description = $translation->description;

        return $obj;
    }

    private function transformHomeSeo($homeSeo)
    {
        if (is_array($homeSeo)) {
            $obj = new \stdClass;
            $obj->metaTitle = $homeSeo['meta_title'] ?? null;
            $obj->metaDescription = $homeSeo['meta_description'] ?? null;
            $obj->metaKeywords = $homeSeo['meta_keywords'] ?? null;

            return $obj;
        }

        return null;
    }
}
