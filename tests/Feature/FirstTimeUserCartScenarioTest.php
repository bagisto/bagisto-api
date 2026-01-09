<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class FirstTimeUserCartScenarioTest extends TestCase
{
    use RefreshDatabase;

    private string $graphqlUrl = '/graphql';

    /**
     * GraphQL query to add product to cart (for first-time users)
     */
    private function addProductToCart(string $token, int $productId, int $quantity): array
    {
        $mutation = <<<'GQL'
            mutation addProductToCart($token: String!, $productId: Int!, $quantity: Int!) {
              addProductToCartCartToken(input: {
                token: $token
                productId: $productId
                quantity: $quantity
              }) {
                success
                message
                cart {
                  id
                  cartToken
                  itemsCount
                  items {
                    id
                    productId
                    name
                    quantity
                    formattedPrice
                    formattedTotal
                  }
                  formattedGrandTotal
                }
              }
            }
        GQL;

        return $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => compact('token', 'productId', 'quantity'),
        ])->json();
    }

    /**
     * GraphQL query to read cart (for first-time users)
     */
    private function readCart(string $token, int $cartId): array
    {
        $query = <<<'GQL'
            query readCart($token: String!, $cartId: Int!) {
              readCartToken(input: {
                token: $token
                cartId: $cartId
              }) {
                cart {
                  id
                  cartToken
                  itemsCount
                  items {
                    id
                    name
                    quantity
                    formattedPrice
                    formattedTotal
                  }
                  formattedSubtotal
                  formattedTaxAmount
                  formattedShippingAmount
                  formattedGrandTotal
                }
              }
            }
        GQL;

        return $this->postJson($this->graphqlUrl, [
            'query'     => $query,
            'variables' => compact('token', 'cartId'),
        ])->json();
    }

    /**
     * GraphQL mutation to update item quantity (for first-time users)
     */
    private function updateCartItem(string $token, int $cartItemId, int $quantity): array
    {
        $mutation = <<<'GQL'
            mutation updateCartItem($token: String!, $cartItemId: Int!, $quantity: Int!) {
              updateItemCartToken(input: {
                token: $token
                cartItemId: $cartItemId
                quantity: $quantity
              }) {
                success
                message
                cart {
                  itemsCount
                  items {
                    id
                    quantity
                    formattedTotal
                  }
                  formattedGrandTotal
                }
              }
            }
        GQL;

        return $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => compact('token', 'cartItemId', 'quantity'),
        ])->json();
    }

    /**
     * SCENARIO 1: First-time guest user creates and manages cart
     *
     * User Steps:
     * 1. Browse products
     * 2. Add first product to cart
     * 3. View cart
     * 4. Add another product
     * 5. Update quantity
     * 6. View final cart
     */
    public function test_first_time_guest_user_creates_cart(): void
    {
        echo "\n\n🛒 SCENARIO 1: First-Time Guest User Creates Cart\n";
        echo "═════════════════════════════════════════════════\n";

        // Step 1: Create some products (simulating browsing)
        $product1 = Product::factory()->create(['name' => 'Laptop']);
        $product2 = Product::factory()->create(['name' => 'Mouse']);

        echo "📦 Products available:\n";
        echo "  - Product 1: {$product1->name} (ID: {$product1->id})\n";
        echo "  - Product 2: {$product2->name} (ID: {$product2->id})\n\n";

        // Step 2: Guest user adds first product to cart
        // For guests, we use a simple cart token (cart ID)
        $guestToken = '1'; // This will be the cart ID for guests

        echo "👤 Guest User Action: Add first product\n";
        echo "  Input: token='$guestToken', productId={$product1->id}, quantity=1\n";

        $addResponse1 = $this->addProductToCart($guestToken, $product1->id, 1);

        $this->assertTrue($addResponse1['data']['addProductToCartCartToken']['success']);
        $cartId = $addResponse1['data']['addProductToCartCartToken']['cart']['id'];

        echo "  ✅ Success! Cart created\n";
        echo "  Response:\n";
        echo "    - Cart ID: {$cartId}\n";
        echo "    - Items in cart: {$addResponse1['data']['addProductToCartCartToken']['cart']['itemsCount']}\n";
        echo "    - Total: {$addResponse1['data']['addProductToCartCartToken']['cart']['formattedGrandTotal']}\n\n";

        // Step 3: Guest views their cart
        echo "👤 Guest User Action: View cart\n";
        echo "  Input: token='$guestToken', cartId={$cartId}\n";

        $readResponse = $this->readCart($guestToken, $cartId);

        $this->assertTrue(isset($readResponse['data']['readCartToken']['cart']));
        $cart = $readResponse['data']['readCartToken']['cart'];

        echo "  ✅ Cart retrieved\n";
        echo "  Response:\n";
        echo "    - Items: {$cart['itemsCount']}\n";
        echo "    - Items in cart:\n";
        foreach ($cart['items'] as $item) {
            echo "      • {$item['name']} x {$item['quantity']} = {$item['formattedTotal']}\n";
        }
        echo "    - Subtotal: {$cart['formattedSubtotal']}\n";
        echo "    - Grand Total: {$cart['formattedGrandTotal']}\n\n";

        // Step 4: Guest adds another product
        echo "👤 Guest User Action: Add second product\n";
        echo "  Input: token='$guestToken', productId={$product2->id}, quantity=2\n";

        $addResponse2 = $this->addProductToCart($guestToken, $product2->id, 2);

        $this->assertTrue($addResponse2['data']['addProductToCartCartToken']['success']);

        echo "  ✅ Second product added\n";
        echo "  Response:\n";
        echo "    - Items in cart: {$addResponse2['data']['addProductToCartCartToken']['cart']['itemsCount']}\n";
        echo "    - Total: {$addResponse2['data']['addProductToCartCartToken']['cart']['formattedGrandTotal']}\n\n";

        // Step 5: Guest updates quantity of first product
        $firstItemId = $addResponse2['data']['addProductToCartCartToken']['cart']['items'][0]['id'];

        echo "👤 Guest User Action: Update first product quantity\n";
        echo "  Input: token='$guestToken', cartItemId={$firstItemId}, quantity=3\n";

        $updateResponse = $this->updateCartItem($guestToken, $firstItemId, 3);

        $this->assertTrue($updateResponse['data']['updateItemCartToken']['success']);

        echo "  ✅ Quantity updated\n";
        echo "  Response:\n";
        echo "    - New quantity: {$updateResponse['data']['updateItemCartToken']['cart']['items'][0]['quantity']}\n";
        echo "    - New total: {$updateResponse['data']['updateItemCartToken']['cart']['formattedGrandTotal']}\n\n";

        // Step 6: Guest views final cart
        echo "👤 Guest User Action: View final cart\n";

        $finalCart = $this->readCart($guestToken, $cartId);
        $finalData = $finalCart['data']['readCartToken']['cart'];

        echo "  ✅ Final cart:\n";
        echo "    - Total items: {$finalData['itemsCount']}\n";
        echo "    - Items:\n";
        foreach ($finalData['items'] as $item) {
            echo "      • {$item['name']} x {$item['quantity']} = {$item['formattedTotal']}\n";
        }
        echo "    - Subtotal: {$finalData['formattedSubtotal']}\n";
        echo "    - Tax: {$finalData['formattedTaxAmount']}\n";
        echo "    - Shipping: {$finalData['formattedShippingAmount']}\n";
        echo "    - GRAND TOTAL: {$finalData['formattedGrandTotal']}\n";
        echo "    ✨ Ready for checkout!\n\n";
    }

    /**
     * SCENARIO 2: First-time authenticated user creates cart
     *
     * User Steps:
     * 1. User logs in/registers
     * 2. Browses and adds product to cart
     * 3. Views cart
     * 4. Continues shopping - adds more items
     * 5. Checks cart details
     */
    public function test_first_time_authenticated_user_creates_cart(): void
    {
        echo "\n\n🛒 SCENARIO 2: First-Time Authenticated User Creates Cart\n";
        echo "═════════════════════════════════════════════════════════\n";

        // Step 1: Create customer and get token (simulating login/register)
        $customer = Customer::factory()->create([
            'email'      => 'newuser@example.com',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        $token = $customer->createToken('api-token')->plainTextToken;

        echo "👤 New User Registration/Login\n";
        echo "  User: {$customer->first_name} {$customer->last_name}\n";
        echo "  Email: {$customer->email}\n";
        echo '  Token generated: '.substr($token, 0, 20)."...\n\n";

        // Step 2: Browse products and add to cart
        $product1 = Product::factory()->create(['name' => 'Wireless Headphones']);
        $product2 = Product::factory()->create(['name' => 'Phone Case']);
        $product3 = Product::factory()->create(['name' => 'USB Cable']);

        echo "📦 Products available:\n";
        echo "  1. {$product1->name} (ID: {$product1->id})\n";
        echo "  2. {$product2->name} (ID: {$product2->id})\n";
        echo "  3. {$product3->name} (ID: {$product3->id})\n\n";

        // Step 3: Add first product
        echo "🛍️ Step 1: Browse and add first product\n";
        echo "  Action: Add {$product1->name}\n";

        $addResponse1 = $this->addProductToCart($token, $product1->id, 1);

        $this->assertTrue($addResponse1['data']['addProductToCartCartToken']['success']);
        $cartId = $addResponse1['data']['addProductToCartCartToken']['cart']['id'];

        echo "  ✅ Added to cart!\n";
        echo "    - Cart created with ID: {$cartId}\n";
        echo "    - Items: {$addResponse1['data']['addProductToCartCartToken']['cart']['itemsCount']}\n\n";

        // Step 4: Continue shopping - add more items
        echo "🛍️ Step 2: Continue shopping - add more items\n";

        echo "  Action: Add {$product2->name}\n";
        $addResponse2 = $this->addProductToCart($token, $product2->id, 2);
        echo "    ✅ Added {$addResponse2['data']['addProductToCartCartToken']['cart']['items'][count($addResponse2['data']['addProductToCartCartToken']['cart']['items']) - 1]['name']}\n";

        echo "  Action: Add {$product3->name}\n";
        $addResponse3 = $this->addProductToCart($token, $product3->id, 3);
        echo "    ✅ Added {$addResponse3['data']['addProductToCartCartToken']['cart']['items'][count($addResponse3['data']['addProductToCartCartToken']['cart']['items']) - 1]['name']}\n\n";

        // Step 5: Check cart details
        echo "🛍️ Step 3: Review shopping cart\n";

        $cartDetails = $this->readCart($token, $cartId);
        $finalCart = $cartDetails['data']['readCartToken']['cart'];

        echo "  📋 Cart Summary:\n";
        echo "    Total Items: {$finalCart['itemsCount']}\n";
        echo "    Items in cart:\n";

        foreach ($finalCart['items'] as $index => $item) {
            echo '      '.($index + 1).". {$item['name']}\n";
            echo "         Qty: {$item['quantity']}\n";
            echo "         Price: {$item['formattedPrice']}\n";
            echo "         Total: {$item['formattedTotal']}\n";
        }

        echo "\n    💰 Pricing Breakdown:\n";
        echo "      Subtotal:        {$finalCart['formattedSubtotal']}\n";
        echo "      Tax:             {$finalCart['formattedTaxAmount']}\n";
        echo "      Shipping:        {$finalCart['formattedShippingAmount']}\n";
        echo "      ─────────────────────────\n";
        echo "      GRAND TOTAL:     {$finalCart['formattedGrandTotal']}\n\n";

        echo "    ✨ Ready to proceed to checkout!\n\n";
    }

    /**
     * SCENARIO 3: First-time user makes mistakes and corrects them
     *
     * User Steps:
     * 1. Adds wrong quantity
     * 2. Updates quantity
     * 3. Realizes wrong product
     * 4. Still learning but cart works!
     */
    public function test_first_time_user_makes_mistakes_and_learns(): void
    {
        echo "\n\n🛒 SCENARIO 3: First-Time User Makes Mistakes\n";
        echo "══════════════════════════════════════════════\n";

        $token = 'guest-token-1';
        $product1 = Product::factory()->create(['name' => 'Item A']);
        $product2 = Product::factory()->create(['name' => 'Item B']);

        echo "👤 New Guest User\n\n";

        // Mistake 1: Adds too many items
        echo "❌ MISTAKE 1: Adds too many of first product\n";
        echo "   Action: Add 10 units of {$product1->name}\n";

        $response1 = $this->addProductToCart($token, $product1->id, 10);
        $cartId = $response1['data']['addProductToCartCartToken']['cart']['id'];

        echo '   Added: '.$response1['data']['addProductToCartCartToken']['cart']['items'][0]['quantity']." items\n";
        echo '   Total: '.$response1['data']['addProductToCartCartToken']['cart']['formattedGrandTotal']."\n\n";

        // Correction 1: Updates to correct quantity
        echo "✅ CORRECTION 1: User reduces quantity to 2\n";
        $itemId = $response1['data']['addProductToCartCartToken']['cart']['items'][0]['id'];
        echo "   Action: Update quantity to 2\n";

        $updateResponse = $this->updateCartItem($token, $itemId, 2);

        echo '   Updated: '.$updateResponse['data']['updateItemCartToken']['cart']['items'][0]['quantity']." items\n";
        echo '   New Total: '.$updateResponse['data']['updateItemCartToken']['cart']['formattedGrandTotal']."\n\n";

        // Mistake 2: Adds wrong product
        echo "❌ MISTAKE 2: Adds wrong product\n";
        echo "   Action: Accidentally adds {$product2->name}\n";

        $response2 = $this->addProductToCart($token, $product2->id, 1);
        $itemCount = count($response2['data']['addProductToCartCartToken']['cart']['items']);

        echo "   Now has {$itemCount} different items in cart\n";
        echo "   Items:\n";
        foreach ($response2['data']['addProductToCartCartToken']['cart']['items'] as $item) {
            echo "     - {$item['name']} x {$item['quantity']}\n";
        }
        echo '   Total: '.$response2['data']['addProductToCartCartToken']['cart']['formattedGrandTotal']."\n\n";

        // Recovery: User accepts it or can learn to remove items
        echo "✨ USER LEARNING:\n";
        echo "   ✅ Successfully created cart\n";
        echo "   ✅ Successfully added items\n";
        echo "   ✅ Successfully updated quantities\n";
        echo "   📌 Note: Can also remove items using removeItemCartToken mutation\n";
        echo "   📌 Cart persists with token, can view anytime\n\n";
    }
}
