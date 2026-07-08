<?php

namespace Webkul\BagistoApi\Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\InvoiceItem;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Sales\Models\OrderTransaction;
use Webkul\Sales\Models\Refund;
use Webkul\Sales\Models\Shipment;

/**
 * Inline fixture factories for Admin API tests.
 *
 * Replaces the "look up existing row, skip if missing" pattern with explicit
 * fixture creation so tests don't depend on seeded dev data. Used by the
 * Admin Cart / Checkout / OrderAction / OrderCancel suites.
 */
trait AdminFixtureFactory
{
    /**
     * Find or create a saleable simple product. Always returns a product.
     */
    protected function findOrCreateSimpleProduct(): Product
    {
        $existing = Product::query()
            ->where('type', 'simple')
            ->whereHas('attribute_values', function ($q) {
                $q->where('attribute_id', function ($sq) {
                    $sq->select('id')->from('attributes')->where('code', 'status')->limit(1);
                })->where('boolean_value', 1);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        // Fall back: create one via the test base's helper if available.
        if (method_exists($this, 'createBaseProduct')) {
            $product = $this->createBaseProduct('simple');
            if (method_exists($this, 'ensureInventory')) {
                $this->ensureInventory($product, 50);
            }

            return $product;
        }

        // Last resort.
        return Product::factory()->create(['type' => 'simple']);
    }

    /**
     * Find or create a customer.
     */
    protected function findOrCreateCustomer(): Customer
    {
        $existing = Customer::query()->orderBy('id')->first();
        if ($existing) {
            return $existing;
        }

        if (method_exists($this, 'createCustomer')) {
            return $this->createCustomer();
        }

        return Customer::factory()->create();
    }

    /**
     * Bootstrap a draft cart (is_active=0) with one simple product item.
     * Returns the cart id. Never returns null — creates fixtures if needed.
     */
    protected function bootstrapAdminDraftCart(): int
    {
        if (method_exists($this, 'seedRequiredData')) {
            $this->seedRequiredData();
        }

        $customer = $this->findOrCreateCustomer();
        $product = $this->findOrCreateSimpleProduct();

        try {
            $cart = CartFacade::createCart([
                'customer' => $customer,
                'is_active' => false,
            ]);
            CartFacade::setCart($cart);
            CartFacade::addProduct($product, ['product_id' => $product->id, 'quantity' => 1]);
            CartFacade::collectTotals();

            return $cart->id;
        } catch (\Throwable $e) {
            // If CartFacade::addProduct fails (out of stock, channel issues etc.),
            // build the bare cart row by hand so tests that don't need items
            // can at least exercise the auth + sequence guards.
            if (isset($cart) && $cart instanceof Cart) {
                return $cart->id;
            }

            return $this->createBareDraftCart($customer)->id;
        }
    }

    /**
     * Same as bootstrapAdminDraftCart() but with no item (empty cart).
     */
    protected function bootstrapEmptyAdminDraftCart(): int
    {
        if (method_exists($this, 'seedRequiredData')) {
            $this->seedRequiredData();
        }

        $customer = $this->findOrCreateCustomer();

        try {
            $cart = CartFacade::createCart([
                'customer' => $customer,
                'is_active' => false,
            ]);

            return $cart->id;
        } catch (\Throwable $e) {
            return $this->createBareDraftCart($customer)->id;
        }
    }

    /**
     * Last-resort builder for a draft cart row when CartFacade::createCart fails.
     */
    protected function createBareDraftCart(Customer $customer): Cart
    {
        $channel = core()->getCurrentChannel();
        $baseCurrencyCode = core()->getBaseCurrencyCode();

        $cart = new Cart;
        $cart->customer_id = $customer->id;
        $cart->customer_email = $customer->email;
        $cart->customer_first_name = $customer->first_name;
        $cart->customer_last_name = $customer->last_name;
        $cart->is_active = 0;
        $cart->is_guest = 0;
        $cart->channel_id = $channel->id;
        $cart->global_currency_code = $baseCurrencyCode;
        $cart->base_currency_code = $baseCurrencyCode;
        $cart->channel_currency_code = $channel->base_currency->code ?? $baseCurrencyCode;
        $cart->cart_currency_code = $baseCurrencyCode;
        $cart->save();

        return $cart;
    }

    /**
     * Build an Order + OrderItem + OrderPayment row tied to a fresh customer
     * and simple product. Returns the order id.
     *
     * @param  string  $status  pending|processing|completed|closed|fraud|canceled
     */
    protected function bootstrapAdminOrder(string $status = 'pending', bool $isGuest = false): Order
    {
        if (method_exists($this, 'seedRequiredData')) {
            $this->seedRequiredData();
        }

        $customer = $isGuest ? null : $this->findOrCreateCustomer();
        $product = $this->findOrCreateSimpleProduct();

        $order = Order::factory()->create([
            'customer_id' => $customer?->id,
            'customer_email' => $customer?->email ?? 'guest-'.uniqid().'@example.com',
            'customer_first_name' => $customer?->first_name ?? 'Guest',
            'customer_last_name' => $customer?->last_name ?? 'Buyer',
            'is_guest' => $isGuest ? 1 : 0,
            'status' => $status,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => 'Test '.$product->sku,
            'type' => 'simple',
            'qty_ordered' => 1,
            'qty_invoiced' => 0,
            'qty_canceled' => 0,
            'qty_shipped' => 0,
            'qty_refunded' => 0,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
            'method' => 'cashondelivery',
        ]);

        return $order;
    }

    /**
     * Find or create a cancellable order (status not closed/fraud/canceled/completed,
     * with at least one item where qty_ordered > qty_canceled + qty_invoiced).
     */
    protected function findOrCreateCancellableOrder(): Order
    {
        $existing = Order::query()
            ->whereNotIn('status', [
                Order::STATUS_CLOSED,
                Order::STATUS_FRAUD,
                Order::STATUS_CANCELED,
                Order::STATUS_COMPLETED,
            ])
            ->whereHas('items', function ($q) {
                $q->whereRaw('(qty_ordered - qty_canceled - qty_invoiced) > 0');
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->bootstrapAdminOrder('pending');
    }

    /**
     * Find or create a non-guest order for reorder testing.
     */
    protected function findOrCreateReorderableOrder(): Order
    {
        $existing = Order::query()->where('is_guest', 0)->first();
        if ($existing) {
            return $existing;
        }

        return $this->bootstrapAdminOrder('pending', false);
    }

    /**
     * Find or create a guest order.
     */
    protected function findOrCreateGuestOrder(): Order
    {
        $existing = Order::query()->where('is_guest', 1)->first();
        if ($existing) {
            return $existing;
        }

        return $this->bootstrapAdminOrder('pending', true);
    }

    /* ---------------------------------------------------------------------
     | Sales action fixtures — P2 additions
     | ---------------------------------------------------------------------
     | Helpers for bootstrapping orders + addresses + invoices / shipments /
     | refunds / transactions / comments. Used by the Sales test cluster.
     */

    /**
     * Build a draft cart ready to be placed as an order: items, billing +
     * shipping address, shipping method (`flatrate_flatrate`) and a payment
     * method (`cashondelivery`). Returns the fresh cart.
     *
     * Best-effort — if Bagisto's cart engine can't fully prepare the cart in
     * the current env (e.g. no shipping rates), the cart is returned partial
     * and the caller can detect via `$cart->shipping_method` / `payment->method`.
     */
    protected function bootstrapPlaceableCart(): Cart
    {
        if (method_exists($this, 'seedRequiredData')) {
            $this->seedRequiredData();
        }

        $customer = $this->findOrCreateCustomer();
        $product = $this->findOrCreateSimpleProduct();

        $cart = CartFacade::createCart(['customer' => $customer, 'is_active' => false]);
        CartFacade::setCart($cart);

        try {
            CartFacade::addProduct($product, ['product_id' => $product->id, 'quantity' => 1]);
        } catch (\Throwable) {
            return $cart->fresh();
        }

        $billing = [
            'first_name' => $customer->first_name ?? 'Jane',
            'last_name' => $customer->last_name ?? 'Doe',
            'email' => $customer->email,
            'address' => ['12 Main St'],
            'city' => 'Berlin',
            'country' => 'DE',
            'state' => 'BE',
            'postcode' => '10115',
            'phone' => '+4930123456',
            'use_for_shipping' => true,
        ];

        try {
            CartFacade::saveAddresses(['billing' => $billing]);
        } catch (\Throwable) {
            return $cart->fresh();
        }

        try {
            CartFacade::collectTotals();
            CartFacade::saveShippingMethod('flatrate_flatrate');
            CartFacade::collectTotals();
            CartFacade::savePaymentMethod(['method' => 'cashondelivery']);
            CartFacade::collectTotals();
        } catch (\Throwable) {
            // partial — caller handles
        }

        return $cart->fresh();
    }

    /**
     * Bootstrap an Order whose items have qty_to_invoice > 0. Returns the
     * Order with `items.product` and `billing_address` loaded so the
     * InvoiceRepository::create path can find addresses.
     */
    protected function bootstrapInvoiceableOrder(string $status = 'pending'): Order
    {
        $order = $this->bootstrapAdminOrder($status, false);

        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'address_type' => OrderAddress::ADDRESS_TYPE_BILLING,
        ]);
        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'address_type' => OrderAddress::ADDRESS_TYPE_SHIPPING,
        ]);

        return $order->fresh(['items.product', 'addresses', 'payment']);
    }

    /**
     * Bootstrap an Order whose items have qty_to_ship > 0.
     */
    protected function bootstrapShippableOrder(string $status = 'processing'): Order
    {
        return $this->bootstrapInvoiceableOrder($status);
    }

    /**
     * Bootstrap an Order whose items have qty_to_refund > 0 (i.e. invoiced
     * but not yet refunded).
     */
    protected function bootstrapRefundableOrder(string $status = 'processing'): Order
    {
        $order = $this->bootstrapInvoiceableOrder($status);
        $order->items->each(function ($item) {
            $item->qty_invoiced = $item->qty_ordered;
            $item->save();
        });

        return $order->fresh(['items.product', 'addresses', 'payment']);
    }

    /**
     * Create an Order + a paid Invoice + InvoiceItem row. Returns the Order
     * with `invoices.items` loaded. Used by tests that need an existing
     * Invoice id without going through the InvoiceRepository pipeline.
     */
    protected function bootstrapOrderWithInvoice(string $orderStatus = 'pending'): Order
    {
        $order = $this->bootstrapInvoiceableOrder($orderStatus);
        $item = $order->items->first();

        $invoice = Invoice::factory()->create([
            'order_id' => $order->id,
            'state' => 'paid',
            'total_qty' => (int) $item->qty_ordered,
            'sub_total' => 100,
            'base_sub_total' => 100,
            'grand_total' => 100,
            'base_grand_total' => 100,
            'base_currency_code' => 'USD',
            'order_currency_code' => 'USD',
            'channel_currency_code' => 'USD',
            'increment_id' => 'INV-FX-'.uniqid(),
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'order_item_id' => $item->id,
            'name' => $item->name,
            'sku' => $item->sku,
            'qty' => (int) $item->qty_ordered,
            'price' => 100,
            'base_price' => 100,
            'total' => 100,
            'base_total' => 100,
        ]);

        return $order->fresh(['invoices.items', 'items.product', 'addresses', 'payment']);
    }

    /**
     * Create an Order + Shipment row tied to it.
     */
    protected function bootstrapOrderWithShipment(string $orderStatus = 'processing'): Order
    {
        $order = $this->bootstrapShippableOrder($orderStatus);

        Shipment::factory()->create([
            'order_id' => $order->id,
            'order_address_id' => $order->addresses->firstWhere('address_type', OrderAddress::ADDRESS_TYPE_SHIPPING)?->id,
            'customer_id' => $order->customer_id,
            'customer_type' => Customer::class,
            'total_qty' => 1,
        ]);

        return $order->fresh(['shipments']);
    }

    /**
     * Create an Order + Refund row tied to it.
     */
    protected function bootstrapOrderWithRefund(string $orderStatus = 'processing'): Order
    {
        $order = $this->bootstrapRefundableOrder($orderStatus);

        Refund::factory()->create([
            'order_id' => $order->id,
            'state' => 'refunded',
            'total_qty' => 1,
            'grand_total' => 50,
            'base_grand_total' => 50,
            'sub_total' => 50,
            'base_sub_total' => 50,
            'base_currency_code' => 'USD',
            'order_currency_code' => 'USD',
            'channel_currency_code' => 'USD',
            'increment_id' => 'REF-FX-'.uniqid(),
        ]);

        return $order->fresh(['refunds']);
    }

    /**
     * Create an OrderTransaction row tied to the order. Returns the id.
     */
    protected function bootstrapOrderTransaction(Order $order, array $overrides = []): int
    {
        $invoiceId = $order->invoices->first()?->id;
        if (! $invoiceId) {
            $order = $this->bootstrapOrderWithInvoice($order->status);
            $invoiceId = $order->invoices->first()->id;
        }

        return OrderTransaction::factory()->create(array_merge([
            'order_id' => $order->id,
            'invoice_id' => $invoiceId,
            'amount' => $order->base_grand_total ?? 100,
        ], $overrides))->id;
    }

    /**
     * Insert an order_comments row tied to the order. Returns the id.
     */
    protected function bootstrapOrderComment(Order $order, array $overrides = []): int
    {
        return DB::table('order_comments')->insertGetId(array_merge([
            'order_id' => $order->id,
            'comment' => 'Auto comment '.uniqid(),
            'customer_notified' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    /* ---------------------------------------------------------------------
     | P3 additions — Customer sidebar / settings / catalog
     | --------------------------------------------------------------------- */

    /**
     * Find or create a system customer group (`is_user_defined = 0`). Always
     * returns a group — seeds `general` if none exist.
     */
    protected function findOrCreateSystemCustomerGroup(): CustomerGroup
    {
        if (method_exists($this, 'seedRequiredData')) {
            $this->seedRequiredData();
        }

        $existing = CustomerGroup::where('is_user_defined', 0)->first()
            ?? CustomerGroup::where('code', 'general')->first();

        if ($existing) {
            return $existing;
        }

        return CustomerGroup::create([
            'code' => 'general',
            'name' => 'General',
            'is_user_defined' => 0,
        ]);
    }

    /**
     * Find or create a customer that has at least one address. Returns the
     * customer id. Always succeeds.
     */
    protected function bootstrapCustomerWithAddress(): int
    {
        $existing = Customer::whereHas('addresses')->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        $customer = $this->findOrCreateCustomer();
        CustomerAddress::factory()->create([
            'customer_id' => $customer->id,
            'address_type' => CustomerAddress::ADDRESS_TYPE,
        ]);

        return (int) $customer->id;
    }

    /**
     * Find or create a customer with an active storefront cart carrying at
     * least one top-level (non-child) item. Returns the customer id.
     */
    protected function bootstrapCustomerWithActiveCart(): int
    {
        $existing = Cart::query()
            ->whereNotNull('customer_id')
            ->where('is_active', 1)
            ->whereHas('items', fn ($q) => $q->whereNull('parent_id'))
            ->value('customer_id');

        if ($existing !== null) {
            return (int) $existing;
        }

        $customer = $this->findOrCreateCustomer();
        $product = $this->findOrCreateSimpleProduct();

        try {
            $cart = CartFacade::createCart([
                'customer' => $customer,
                'is_active' => true,
            ]);
            CartFacade::setCart($cart);
            CartFacade::addProduct($product, ['product_id' => $product->id, 'quantity' => 1]);
            CartFacade::collectTotals();
        } catch (\Throwable) {
            // Fall back: assemble cart + cart_items directly.
            $cart = $this->createBareDraftCart($customer);
            $cart->is_active = 1;
            $cart->save();

            DB::table('cart_items')->insert([
                'quantity' => 1,
                'sku' => $product->sku,
                'type' => $product->type,
                'name' => 'Test '.$product->sku,
                'price' => 0,
                'base_price' => 0,
                'total' => 0,
                'base_total' => 0,
                'weight' => 0,
                'total_weight' => 0,
                'base_total_weight' => 0,
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'parent_id' => null,
                'additional' => json_encode(['product_id' => $product->id, 'quantity' => 1]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $customer->id;
    }

    /**
     * Find or create a customer with at least one wishlist row. Returns the
     * customer id.
     */
    protected function bootstrapCustomerWithWishlist(): int
    {
        $existing = Customer::whereHas('wishlist_items')->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        if (method_exists($this, 'seedRequiredData')) {
            $this->seedRequiredData();
        }

        $customer = $this->findOrCreateCustomer();
        $product = $this->findOrCreateSimpleProduct();

        $channel = core()->getCurrentChannel();

        DB::table('wishlist_items')->insert([
            'channel_id' => $channel->id,
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'additional' => json_encode([]),
            'moved_to_cart' => 0,
            'shared' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) $customer->id;
    }

    /**
     * Find or create a customer that has at least one order. Returns the
     * customer id.
     */
    protected function bootstrapCustomerWithOrders(): int
    {
        $existing = Customer::whereHas('orders')->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        $order = $this->bootstrapAdminOrder('pending', false);

        return (int) $order->customer_id;
    }

    /**
     * Ensure the `color` + `size` configurable attributes and at least 2
     * options each exist (per installer seeder). Returns `[colorId, sizeId,
     * colorOptionIds[], sizeOptionIds[]]`. Best-effort — if the attribute
     * rows are missing entirely, seeds minimal rows.
     */
    protected function ensureColorSizeAttributesWithOptions(): array
    {
        if (method_exists($this, 'seedRequiredData')) {
            $this->seedRequiredData();
        }

        $colorId = DB::table('attributes')->where('code', 'color')->value('id');
        if (! $colorId) {
            $colorId = DB::table('attributes')->insertGetId([
                'code' => 'color',
                'admin_name' => 'Color',
                'type' => 'select',
                'is_required' => 0,
                'is_unique' => 0,
                'value_per_locale' => 0,
                'value_per_channel' => 0,
                'is_filterable' => 1,
                'is_configurable' => 1,
                'is_user_defined' => 0,
                'is_visible_on_front' => 1,
                'use_in_flat' => 1,
                'swatch_type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sizeId = DB::table('attributes')->where('code', 'size')->value('id');
        if (! $sizeId) {
            $sizeId = DB::table('attributes')->insertGetId([
                'code' => 'size',
                'admin_name' => 'Size',
                'type' => 'select',
                'is_required' => 0,
                'is_unique' => 0,
                'value_per_locale' => 0,
                'value_per_channel' => 0,
                'is_filterable' => 1,
                'is_configurable' => 1,
                'is_user_defined' => 0,
                'is_visible_on_front' => 1,
                'use_in_flat' => 1,
                'swatch_type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ([$colorId, $sizeId] as $attrId) {
            $count = DB::table('attribute_options')->where('attribute_id', $attrId)->count();
            for ($i = $count; $i < 2; $i++) {
                DB::table('attribute_options')->insert([
                    'admin_name' => 'opt-'.$attrId.'-'.$i.'-'.uniqid(),
                    'attribute_id' => $attrId,
                    'sort_order' => $i,
                ]);
            }
        }

        $colorOptions = DB::table('attribute_options')->where('attribute_id', $colorId)->limit(2)->pluck('id')->all();
        $sizeOptions = DB::table('attribute_options')->where('attribute_id', $sizeId)->limit(2)->pluck('id')->all();

        return [$colorId, $sizeId, $colorOptions, $sizeOptions];
    }

    /**
     * Ensure a product exists with a non-null SKU. Returns the product.
     * Used by ProductTest list/search/pagination shape checks.
     */
    protected function ensureProductWithSku(): Product
    {
        $existing = Product::query()->whereNotNull('sku')->orderBy('id')->first();
        if ($existing) {
            return $existing;
        }

        return $this->findOrCreateSimpleProduct();
    }
}
