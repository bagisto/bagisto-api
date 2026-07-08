<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class CustomizableOptionFileTest extends RestApiTestCase
{
    private string $uploadUrl = '/api/shop/customizable-option-files';

    private string $addProductUrl = '/api/shop/add-product-in-cart';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake();
    }

    private function createSimpleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'REST-FILE-'.uniqid(),
        ]);

        $this->upsertProductAttributeValue($product->id, 'price', 17.0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'weight', 1.0, null, 'default');
        $this->ensureInventory($product, 50);

        return $product;
    }

    private function addFileOption(Product $product, array $attributes = []): int
    {
        $optionId = (int) DB::table('product_customizable_options')->insertGetId([
            'product_id' => $product->id,
            'type' => 'file',
            'is_required' => 1,
            'supported_file_extensions' => 'pdf,jpg,png',
            'sort_order' => 0,
            ...$attributes,
        ]);

        DB::table('product_customizable_option_translations')->insert([
            'product_customizable_option_id' => $optionId,
            'locale' => 'en',
            'label' => 'Upload your design',
        ]);

        DB::table('product_customizable_option_prices')->insert([
            'product_customizable_option_id' => $optionId,
            'label' => '',
            'price' => 0,
            'sort_order' => 0,
        ]);

        return $optionId;
    }

    private function upload(Customer $customer, int $productId, int $optionId, UploadedFile $file): TestResponse
    {
        return $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->post($this->uploadUrl, [
                'product_id' => $productId,
                'option_id' => $optionId,
                'file' => $file,
            ], ['Accept' => 'application/json']);
    }

    public function test_upload_returns_a_token(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->upload(
            $customer,
            $product->id,
            $optionId,
            UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf')
        );

        expect($response->getStatusCode())->toBe(201);
        expect($response->json('token'))->toBeString()->not->toBeEmpty();
        expect($response->json('fileName'))->toBe('spec.pdf');
        expect((int) $response->json('optionId'))->toBe($optionId);
    }

    public function test_upload_rejects_a_non_file_option(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();

        $textOption = (int) DB::table('product_customizable_options')->insertGetId([
            'product_id' => $product->id,
            'type' => 'textarea',
            'is_required' => 1,
            'sort_order' => 0,
        ]);

        $response = $this->upload(
            $customer,
            $product->id,
            $textOption,
            UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf')
        );

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_upload_rejects_a_wrong_extension(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->upload(
            $customer,
            $product->id,
            $optionId,
            UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream')
        );

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_upload_rejects_a_file_over_the_size_limit(): void
    {
        config(['storefront.cart.customizable_file.max_size_kb' => 100]);

        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->upload(
            $customer,
            $product->id,
            $optionId,
            UploadedFile::fake()->create('big.pdf', 200, 'application/pdf')
        );

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_upload_returns_404_for_unknown_product(): void
    {
        $customer = $this->createCustomer();

        $response = $this->upload(
            $customer,
            999999,
            1,
            UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf')
        );

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_upload_requires_authentication(): void
    {
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->post($this->uploadUrl, [
            'product_id' => $product->id,
            'option_id' => $optionId,
            'file' => UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf'),
        ], [...$this->storefrontHeaders(), 'Accept' => 'application/json']);

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_add_to_cart_resolves_the_token_and_consumes_it(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $token = $this->upload(
            $customer,
            $product->id,
            $optionId,
            UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf')
        )->json('token');

        $add = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
            'customizableOptions' => [(string) $optionId => [$token]],
        ]);

        expect($add->getStatusCode())->toBeIn([200, 201]);
        expect((bool) $add->json('success'))->toBeTrue();
        expect((int) $add->json('itemsCount'))->toBeGreaterThan(0);

        // Token is single-use: it is forgotten after a successful add, so re-using it fails.
        $reuse = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
            'customizableOptions' => [(string) $optionId => [$token]],
        ]);

        expect($reuse->getStatusCode())->toBe(400);
    }

    public function test_add_to_cart_rejects_a_missing_required_file_option(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $this->addFileOption($product);

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_add_to_cart_rejects_an_invalid_token(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
            'customizableOptions' => [(string) $optionId => ['not-a-real-token']],
        ]);

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_add_to_cart_rejects_another_customers_token(): void
    {
        $owner = $this->createCustomer();
        $attacker = $this->createCustomer(['email' => 'attacker-'.uniqid().'@example.com']);
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $token = $this->upload(
            $owner,
            $product->id,
            $optionId,
            UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf')
        )->json('token');

        $response = $this->authenticatedPost($attacker, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
            'customizableOptions' => [(string) $optionId => [$token]],
        ]);

        expect($response->getStatusCode())->toBe(403);
    }
}
