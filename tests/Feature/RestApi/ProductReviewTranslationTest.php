<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Product\Models\ProductReview;

class ProductReviewTranslationTest extends RestApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
    }

    private function setTranslationEnabled(bool $enabled): void
    {
        foreach ([
            'magic_ai.general.settings.enabled' => $enabled ? '1' : '0',
            'magic_ai.storefront_features.review_translation.enabled' => $enabled ? '1' : '0',
        ] as $code => $value) {
            DB::table('core_config')->where('code', $code)->delete();
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    public function test_approved_review_is_translated_when_magic_ai_is_enabled(): void
    {
        $this->seedRequiredData();
        $this->setTranslationEnabled(true);
        $product = $this->createBaseProduct('simple');
        $review = ProductReview::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
            'comment' => 'This product is excellent.',
        ]);

        MagicAI::shouldReceive('translate')
            ->once()
            ->with('This product is excellent.', core()->getCurrentLocale()->name)
            ->andReturn('Ce produit est excellent.');

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews/'.$review->id.'/translation');

        $response->assertOk()->assertJsonPath('content', 'Ce produit est excellent.');
    }

    public function test_review_translation_is_forbidden_when_magic_ai_is_disabled(): void
    {
        $this->seedRequiredData();
        $this->setTranslationEnabled(false);
        $product = $this->createBaseProduct('simple');
        $review = ProductReview::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
        ]);

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews/'.$review->id.'/translation');

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_pending_review_translation_is_rejected(): void
    {
        $this->seedRequiredData();
        $this->setTranslationEnabled(true);
        $product = $this->createBaseProduct('simple');
        $review = ProductReview::factory()->create([
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews/'.$review->id.'/translation');

        expect($response->getStatusCode())->toBe(400);
    }
}
