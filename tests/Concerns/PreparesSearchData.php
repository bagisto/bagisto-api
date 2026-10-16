<?php

namespace Webkul\BagistoApi\Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\Product;

/**
 * Seeds a uniquely named product, a search synonym, a search-term redirect and the Elasticsearch engine config.
 */
trait PreparesSearchData
{
    protected function productNamed(string $name): Product
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'SEARCH-'.uniqid()]);

        $this->upsertProductAttributeValue($product->id, 'name', $name, 'en', 'default');

        return $product;
    }

    protected function seedSynonyms(string $terms): void
    {
        DB::table('search_synonyms')->insert(['name' => 'test-'.uniqid(), 'terms' => $terms, 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function seedSearchTermRedirect(string $term, string $url): void
    {
        DB::table('search_terms')->insert([
            'term' => $term,
            'results' => 0,
            'uses' => 0,
            'redirect_url' => $url,
            'channel_id' => core()->getCurrentChannel()->id,
            'locale' => app()->getLocale(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function useElasticSearchEngine(): void
    {
        foreach (['engine', 'storefront_mode'] as $field) {
            DB::table('core_config')->where('code', 'catalog.products.search.'.$field)->delete();

            DB::table('core_config')->insert([
                'code' => 'catalog.products.search.'.$field,
                'value' => 'elastic',
                'channel_code' => null,
                'locale_code' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }
}
