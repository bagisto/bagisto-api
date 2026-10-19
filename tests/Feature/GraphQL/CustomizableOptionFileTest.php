<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class CustomizableOptionFileTest extends GraphQLTestCase
{
    /**
     * The upload url.
     */
    private string $uploadUrl = '/api/shop/customizable-option-files';

    /**
     * Test add to cart over graphql resolves the upload token.
     */
    public function test_add_to_cart_over_graphql_resolves_the_upload_token(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $token = $this->uploadToken($customer, $product->id, $optionId);

        $response = $this->authenticatedGraphQL($customer, $this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 1,
            'customizableOptions' => [(string) $optionId => [$token]],
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors while adding file option to cart: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        expect((bool) ($data['success'] ?? false))->toBeTrue();
        expect((int) ($data['itemsCount'] ?? 0))->toBeGreaterThan(0);
    }

    /**
     * Test add to cart over graphql rejects a missing required file option.
     */
    public function test_add_to_cart_over_graphql_rejects_a_missing_required_file_option(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $this->addFileOption($product);

        $response = $this->authenticatedGraphQL($customer, $this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSuccessful();
        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test add to cart over graphql rejects an invalid token.
     */
    public function test_add_to_cart_over_graphql_rejects_an_invalid_token(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->authenticatedGraphQL($customer, $this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 1,
            'customizableOptions' => [(string) $optionId => ['not-a-real-token']],
        ]);

        $response->assertSuccessful();
        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test upload over graphql stages a file and returns a token.
     */
    public function test_upload_over_graphql_stages_a_file_and_returns_a_token(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->authenticatedGraphQL($customer, $this->uploadMutation(), [
            'input' => [
                'productId' => $product->id,
                'optionId' => $optionId,
                'file' => base64_encode('%PDF-1.4 staged over graphql'),
                'fileName' => 'spec.pdf',
            ],
        ]);

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.createCustomizableOptionFile.customizableOptionFile');

        expect($data['token'])->not->toBeEmpty()
            ->and($data['fileName'])->toBe('spec.pdf')
            ->and((int) $data['optionId'])->toBe($optionId);

        $added = $this->authenticatedGraphQL($customer, $this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 1,
            'customizableOptions' => [(string) $optionId => [$data['token']]],
        ]);

        $added->assertSuccessful();
        expect($added->json('errors'))->toBeNull()
            ->and((bool) $added->json('data.createAddProductInCart.addProductInCart.success'))->toBeTrue();
    }

    /**
     * Test upload over graphql rejects an unsupported extension.
     */
    public function test_upload_over_graphql_rejects_an_unsupported_extension(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();
        $optionId = $this->addFileOption($product);

        $response = $this->authenticatedGraphQL($customer, $this->uploadMutation(), [
            'input' => [
                'productId' => $product->id,
                'optionId' => $optionId,
                'file' => base64_encode('not allowed'),
                'fileName' => 'notes.txt',
            ],
        ]);

        $response->assertSuccessful();
        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake();
    }

    /**
     * Create simple product.
     */
    private function createSimpleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'GQL-FILE-'.uniqid(),
        ]);

        $this->upsertProductAttributeValue($product->id, 'price', 17.0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'weight', 1.0, null, 'default');
        $this->ensureInventory($product, 50);

        return $product;
    }

    /**
     * Add file option.
     */
    private function addFileOption(Product $product): int
    {
        $optionId = (int) DB::table('product_customizable_options')->insertGetId([
            'product_id' => $product->id,
            'type' => 'file',
            'is_required' => 1,
            'supported_file_extensions' => 'pdf,jpg,png',
            'sort_order' => 0,
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

    /**
     * Upload token.
     */
    private function uploadToken(Customer $customer, int $productId, int $optionId): string
    {
        $response = $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->post($this->uploadUrl, [
                'product_id' => $productId,
                'option_id' => $optionId,
                'file' => UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json']);

        expect($response->getStatusCode())->toBe(201);

        return (string) $response->json('token');
    }

    /**
     * Add to cart mutation.
     */
    private function addToCartMutation(): string
    {
        return <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $customizableOptions: Iterable
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  customizableOptions: $customizableOptions
                }
              ) {
                addProductInCart {
                  success
                  itemsCount
                }
              }
            }
        GQL;
    }

    /**
     * Upload mutation.
     */
    private function uploadMutation(): string
    {
        return <<<'GQL'
            mutation createCustomizableOptionFile($input: createCustomizableOptionFileInput!) {
              createCustomizableOptionFile(input: $input) {
                customizableOptionFile {
                  token
                  fileName
                  optionId
                }
              }
            }
        GQL;
    }
}
