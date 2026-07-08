<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\BookingProduct\Helpers\Booking as BookingHelper;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\BookingProduct\Models\BookingProductAppointmentSlot;
use Webkul\BookingProduct\Models\BookingProductDefaultSlot;
use Webkul\BookingProduct\Models\BookingProductEventTicket;
use Webkul\BookingProduct\Models\BookingProductRentalSlot;
use Webkul\BookingProduct\Models\BookingProductTableSlot;
use Webkul\BookingProduct\Repositories\BookingProductRepository;

class AddToCartBookingProductTest extends GraphQLTestCase
{
    private function loginCustomerAndGetToken(): string
    {
        $customerData = $this->createTestCustomer();

        return $customerData['token'];
    }

    private function customerHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    private function getGuestCartToken(): string
    {
        $mutation = <<<'GQL'
            mutation createCart {
              createCartToken(input: {}) {
                cartToken {
                  cartToken
                  success
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);
        $response->assertSuccessful();

        $data = $response->json('data.createCartToken.cartToken');

        $this->assertNotNull($data, 'cartToken response is null');
        $this->assertTrue((bool) ($data['success'] ?? false));

        $token = $data['cartToken'] ?? null;
        $this->assertNotEmpty($token, 'guest cart token is missing');

        return $token;
    }

    private function guestHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    private function createBookingProductFixture(string $bookingType): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-BOOKING-'.$bookingType.'-'.uniqid(),
        ]);

        $booking = BookingProduct::query()->create([
            'product_id' => $product->id,
            'type' => $bookingType,
            'qty' => 100,
            'available_every_week' => 1,
            'available_from' => $bookingType === 'event' ? Carbon::now()->addDay()->format('Y-m-d H:i:s') : null,
            'available_to' => $bookingType === 'event' ? Carbon::now()->addMonth()->format('Y-m-d H:i:s') : null,
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
        $weekday = (int) Carbon::parse($tomorrow)->format('w');

        if ($bookingType === 'default') {
            BookingProductDefaultSlot::query()->create([
                'booking_product_id' => $booking->id,
                'booking_type' => 'many',
                'duration' => 30,
                'break_time' => 0,
                'slots' => [
                    (string) $weekday => [
                        ['from' => '09:00', 'to' => '10:00', 'qty' => 10, 'status' => 1],
                    ],
                ],
            ]);
        } elseif ($bookingType === 'appointment') {
            BookingProductAppointmentSlot::query()->create([
                'booking_product_id' => $booking->id,
                'duration' => 30,
                'break_time' => 0,
                'same_slot_all_days' => 1,
                'slots' => [
                    ['from' => '09:00', 'to' => '10:00', 'qty' => 10, 'status' => 1],
                ],
            ]);
        } elseif ($bookingType === 'table') {
            BookingProductTableSlot::query()->create([
                'booking_product_id' => $booking->id,
                'price_type' => 'table',
                'guest_limit' => 1,
                'duration' => 30,
                'break_time' => 0,
                'prevent_scheduling_before' => 0,
                'same_slot_all_days' => 1,
                'slots' => [
                    ['from' => '09:00', 'to' => '10:00', 'qty' => 10, 'status' => 1],
                ],
            ]);
        } elseif ($bookingType === 'rental') {
            BookingProductRentalSlot::query()->create([
                'booking_product_id' => $booking->id,
                'renting_type' => 'daily',
                'daily_price' => 10,
                'hourly_price' => 0,
                'same_slot_all_days' => 1,
                'slots' => [],
            ]);
        } elseif ($bookingType === 'event') {
            /** @var BookingProductEventTicket $ticket */
            $ticket = BookingProductEventTicket::query()->create([
                'booking_product_id' => $booking->id,
                'price' => 10,
                'qty' => 100,
                'special_price_from' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                'special_price_to' => Carbon::now()->addMonth()->format('Y-m-d H:i:s'),
            ]);

            DB::table('booking_product_event_ticket_translations')->insert([
                'booking_product_event_ticket_id' => $ticket->id,
                'locale' => 'en',
                'name' => 'Test Ticket',
                'description' => 'Test Ticket Description',
            ]);
        }

        return [
            'product' => $product,
            'booking' => $booking,
            'tomorrowDate' => $tomorrow,
        ];
    }

    /**
     * Get a valid slot timestamp for a booking product on a given date.
     */
    private function getSlotTimestamp(int $productId, string $date): ?string
    {
        try {
            /** @var BookingProductRepository $bookingProductRepository */
            $bookingProductRepository = app(BookingProductRepository::class);
            $bookingProduct = $bookingProductRepository->findOneByField('product_id', $productId);

            if (! $bookingProduct) {
                return null;
            }

            /** @var BookingHelper $bookingHelper */
            $bookingHelper = app(BookingHelper::class);
            $slots = $bookingHelper->getSlotsByDate($bookingProduct, $date);

            $timestamp = $slots[0]['timestamp'] ?? null;

            return is_string($timestamp) ? $timestamp : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get a valid rental hourly slot (from/to timestamps) for a booking product on a given date.
     *
     * @return array{from:int,to:int}|null
     */
    private function getRentalHourlySlot(int $productId, string $date): ?array
    {
        try {
            /** @var BookingProductRepository $bookingProductRepository */
            $bookingProductRepository = app(BookingProductRepository::class);
            $bookingProduct = $bookingProductRepository->findOneByField('product_id', $productId);

            if (! $bookingProduct) {
                return null;
            }

            /** @var BookingHelper $bookingHelper */
            $bookingHelper = app(BookingHelper::class);
            $slots = $bookingHelper->getSlotsByDate($bookingProduct, $date);

            $innerSlot = $slots[0]['slots'][0] ?? null;
            if (! $innerSlot) {
                return null;
            }

            $from = $innerSlot['from_timestamp'] ?? null;
            $to = $innerSlot['to_timestamp'] ?? null;

            return ($from && $to) ? ['from' => (int) $from, 'to' => (int) $to] : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function assertCartSuccess(array $json): void
    {
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding booking product to cart: '.json_encode($json['errors']));
        }

        $data = $json['data']['createAddProductInCart']['addProductInCart'] ?? null;
        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
    }

    private function assertCustomerCartSuccess(array $json): void
    {
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding booking product to cart as customer: '.json_encode($json['errors']));
        }

        $data = $json['data']['createAddProductInCart']['addProductInCart'] ?? null;
        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertFalse((bool) ($data['isGuest'] ?? true));
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
    }

    public function test_create_add_default_booking_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('default');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for default booking product.');
        }

        $booking = json_encode([
            'type' => 'default',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking }) {
                addProductInCart {
                  id
                  cartToken
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCartSuccess($response->json());
    }

    public function test_create_add_default_booking_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('default');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for default booking product.');
        }

        $booking = json_encode([
            'type' => 'default',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking }) {
                addProductInCart {
                  id
                  customerId
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCustomerCartSuccess($response->json());
    }

    public function test_create_add_rental_booking_product_daily_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('rental');
        $dateFrom = $fixture['tomorrowDate'];

        $dateTo = Carbon::parse($dateFrom)->addDay()->format('Y-m-d');

        $booking = json_encode([
            'type' => 'rental',
            'renting_type' => 'daily',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking }) {
                addProductInCart {
                  id
                  cartToken
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCartSuccess($response->json());
    }

    public function test_create_add_rental_booking_product_daily_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('rental');
        $dateFrom = $fixture['tomorrowDate'];

        $dateTo = Carbon::parse($dateFrom)->addDay()->format('Y-m-d');

        $booking = json_encode([
            'type' => 'rental',
            'renting_type' => 'daily',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking }) {
                addProductInCart {
                  id
                  customerId
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCustomerCartSuccess($response->json());
    }

    public function test_create_add_event_booking_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('event');

        $ticketIds = DB::table('booking_product_event_tickets')
            ->where('booking_product_id', (int) $fixture['booking']->id)
            ->orderBy('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $qty = [];
        foreach ($ticketIds as $ticketId) {
            $qty[(string) $ticketId] = 1;
        }

        $booking = json_encode([
            'type' => 'event',
            'qty' => $qty,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking }) {
                addProductInCart {
                  id
                  cartToken
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCartSuccess($response->json());
    }

    public function test_create_add_event_booking_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('event');

        $ticketIds = DB::table('booking_product_event_tickets')
            ->where('booking_product_id', (int) $fixture['booking']->id)
            ->orderBy('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $qty = [];
        foreach ($ticketIds as $ticketId) {
            $qty[(string) $ticketId] = 1;
        }

        $booking = json_encode([
            'type' => 'event',
            'qty' => $qty,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking }) {
                addProductInCart {
                  id
                  customerId
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCustomerCartSuccess($response->json());
    }

    public function test_create_add_table_booking_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('table');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for table booking product.');
        }

        $booking = json_encode([
            'type' => 'table',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int, $specialNote: String) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking, bookingNote: $specialNote }) {
                addProductInCart {
                  id
                  cartToken
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
            'specialNote' => 'This is a special note',
        ], $headers);

        $response->assertSuccessful();
        $this->assertCartSuccess($response->json());
    }

    public function test_create_add_table_booking_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('table');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for table booking product.');
        }

        $booking = json_encode([
            'type' => 'table',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!, $quantity: Int, $specialNote: String) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity, booking: $booking, bookingNote: $specialNote }) {
                addProductInCart {
                  id
                  customerId
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
            'specialNote' => 'This is a special note',
        ], $headers);

        $response->assertSuccessful();
        $this->assertCustomerCartSuccess($response->json());
    }

    public function test_create_add_appointment_booking_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('appointment');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for appointment booking product.');
        }

        $booking = json_encode([
            'type' => 'appointment',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!) {
              createAddProductInCart(input: { productId: $productId, booking: $booking }) {
                addProductInCart {
                  id
                  cartToken
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCartSuccess($response->json());
    }

    public function test_create_add_appointment_booking_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('appointment');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for appointment booking product.');
        }

        $booking = json_encode([
            'type' => 'appointment',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $booking: String!) {
              createAddProductInCart(input: { productId: $productId, booking: $booking }) {
                addProductInCart {
                  id
                  customerId
                  success
                  isGuest
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => (int) $fixture['product']->id,
            'booking' => $booking,
        ], $headers);

        $response->assertSuccessful();
        $this->assertCustomerCartSuccess($response->json());
    }

    /**
     * Create a rental booking product fixture with hourly slots.
     */
    private function createHourlyRentalFixture(): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-BOOKING-rental-hourly-'.uniqid(),
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

        $booking = BookingProduct::query()->create([
            'product_id' => $product->id,
            'type' => 'rental',
            'qty' => 100,
            'available_every_week' => 1,
            'available_from' => null,
            'available_to' => null,
        ]);

        BookingProductRentalSlot::query()->create([
            'booking_product_id' => $booking->id,
            'renting_type' => 'hourly',
            'daily_price' => 0,
            'hourly_price' => 5,
            'same_slot_all_days' => 1,
            'slots' => [
                ['from' => '09:00', 'to' => '17:00'],
            ],
        ]);

        return [
            'product' => $product,
            'booking' => $booking,
            'tomorrowDate' => $tomorrow,
        ];
    }

    /**
     * Full add-to-cart mutation matching the complete API spec for booking products.
     */
    private function fullBookingMutation(): string
    {
        return <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $booking: String!
              $quantity: Int
              $specialNote: String
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  booking: $booking
                  bookingNote: $specialNote
                }
              ) {
                addProductInCart {
                  id
                  _id
                  cartToken
                  customerId
                  channelId
                  subtotal
                  baseSubtotal
                  discountAmount
                  baseDiscountAmount
                  taxAmount
                  baseTaxAmount
                  shippingAmount
                  baseShippingAmount
                  grandTotal
                  baseGrandTotal
                  formattedSubtotal
                  formattedDiscountAmount
                  formattedTaxAmount
                  formattedShippingAmount
                  formattedGrandTotal
                  couponCode
                  items {
                    totalCount
                    pageInfo {
                      startCursor
                      endCursor
                      hasNextPage
                      hasPreviousPage
                    }
                    edges {
                      cursor
                      node {
                        id
                        cartId
                        productId
                        name
                        sku
                        quantity
                        price
                        basePrice
                        total
                        baseTotal
                        discountAmount
                        baseDiscountAmount
                        taxAmount
                        baseTaxAmount
                        type
                        formattedPrice
                        formattedTotal
                        priceInclTax
                        basePriceInclTax
                        formattedPriceInclTax
                        totalInclTax
                        baseTotalInclTax
                        formattedTotalInclTax
                        productUrlKey
                        canChangeQty
                        options
                      }
                    }
                  }
                  success
                  message
                  sessionToken
                  isGuest
                  itemsQty
                  itemsCount
                  haveStockableItems
                  paymentMethod
                  paymentMethodTitle
                  subTotalInclTax
                  baseSubTotalInclTax
                  formattedSubTotalInclTax
                  taxTotal
                  formattedTaxTotal
                  shippingAmountInclTax
                  baseShippingAmountInclTax
                  formattedShippingAmountInclTax
                }
              }
            }
        GQL;
    }

    /**
     * Assert full cart response structure and values for booking products.
     */
    private function assertFullBookingCartResponse(array $data, int $productId, bool $isGuest = true): void
    {
        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));

        if ($isGuest) {
            $this->assertTrue((bool) ($data['isGuest'] ?? false));
        } else {
            $this->assertFalse((bool) ($data['isGuest'] ?? true));
            $this->assertNotNull($data['customerId'] ?? null);
        }

        $this->assertNotNull($data['channelId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertGreaterThan(0, (int) ($data['itemsQty'] ?? 0));
        $this->assertArrayHasKey('haveStockableItems', $data);

        // Cart totals
        $this->assertGreaterThanOrEqual(0, (float) ($data['subtotal'] ?? -1));
        $this->assertSame((float) $data['subtotal'], (float) $data['baseSubtotal']);
        $this->assertGreaterThanOrEqual(0, (float) ($data['grandTotal'] ?? -1));
        $this->assertSame((float) $data['grandTotal'], (float) $data['baseGrandTotal']);
        $this->assertNotNull($data['formattedSubtotal']);
        $this->assertNotNull($data['formattedGrandTotal']);
        $this->assertNotNull($data['formattedDiscountAmount']);
        $this->assertNotNull($data['formattedTaxAmount']);
        $this->assertNotNull($data['formattedShippingAmount']);

        // Tax/shipping inclusive fields
        $this->assertArrayHasKey('subTotalInclTax', $data);
        $this->assertArrayHasKey('baseSubTotalInclTax', $data);
        $this->assertArrayHasKey('formattedSubTotalInclTax', $data);
        $this->assertArrayHasKey('taxTotal', $data);
        $this->assertArrayHasKey('formattedTaxTotal', $data);
        $this->assertArrayHasKey('shippingAmountInclTax', $data);
        $this->assertArrayHasKey('baseShippingAmountInclTax', $data);
        $this->assertArrayHasKey('formattedShippingAmountInclTax', $data);

        // Nullable fields
        $this->assertArrayHasKey('couponCode', $data);
        $this->assertArrayHasKey('paymentMethod', $data);
        $this->assertArrayHasKey('paymentMethodTitle', $data);
        $this->assertArrayHasKey('sessionToken', $data);

        // Cart item
        $this->assertGreaterThan(0, (int) ($data['items']['totalCount'] ?? 0));
        $edges = $data['items']['edges'] ?? [];
        $this->assertNotEmpty($edges);

        $item = $edges[0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');
        $this->assertSame($productId, (int) ($item['productId'] ?? 0));
        $this->assertSame('booking', $item['type'] ?? '');
        $this->assertGreaterThanOrEqual(0, (float) ($item['price'] ?? -1));
        $this->assertSame((float) $item['price'], (float) $item['basePrice']);
        $this->assertGreaterThanOrEqual(0, (float) ($item['total'] ?? -1));
        $this->assertSame((float) $item['total'], (float) $item['baseTotal']);
        $this->assertNotNull($item['name']);
        $this->assertNotNull($item['sku']);
        $this->assertNotNull($item['formattedPrice']);
        $this->assertNotNull($item['formattedTotal']);
        $this->assertNotNull($item['formattedPriceInclTax']);
        $this->assertNotNull($item['formattedTotalInclTax']);
        $this->assertArrayHasKey('priceInclTax', $item);
        $this->assertArrayHasKey('basePriceInclTax', $item);
        $this->assertArrayHasKey('totalInclTax', $item);
        $this->assertArrayHasKey('baseTotalInclTax', $item);
        $this->assertArrayHasKey('productUrlKey', $item);
        $this->assertArrayHasKey('canChangeQty', $item);
        $this->assertArrayHasKey('options', $item);

        // Pagination
        $pageInfo = $data['items']['pageInfo'] ?? null;
        $this->assertNotNull($pageInfo);
        $this->assertArrayHasKey('startCursor', $pageInfo);
        $this->assertArrayHasKey('endCursor', $pageInfo);
        $this->assertArrayHasKey('hasNextPage', $pageInfo);
        $this->assertArrayHasKey('hasPreviousPage', $pageInfo);

        // Cursor on edge
        $this->assertArrayHasKey('cursor', $edges[0]);
    }

    // ─── Full response tests ────────────────────────────────────────────

    /**
     * Default booking product — full response as guest.
     */
    public function test_default_booking_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('default');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for default booking product.');
        }

        $booking = json_encode([
            'type' => 'default',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, true);
    }

    /**
     * Default booking product — full response as customer.
     */
    public function test_default_booking_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('default');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for default booking product.');
        }

        $booking = json_encode([
            'type' => 'default',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, false);
    }

    /**
     * Appointment booking product — full response as guest.
     */
    public function test_appointment_booking_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('appointment');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for appointment booking product.');
        }

        $booking = json_encode([
            'type' => 'appointment',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'booking' => $booking,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, true);
    }

    /**
     * Appointment booking product — full response as customer.
     */
    public function test_appointment_booking_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('appointment');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for appointment booking product.');
        }

        $booking = json_encode([
            'type' => 'appointment',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'booking' => $booking,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, false);
    }

    /**
     * Rental booking product (daily) — full response as guest.
     */
    public function test_rental_daily_booking_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('rental');
        $dateFrom = $fixture['tomorrowDate'];
        $dateTo = Carbon::parse($dateFrom)->addDay()->format('Y-m-d');

        $booking = json_encode([
            'type' => 'rental',
            'renting_type' => 'daily',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, true);
    }

    /**
     * Rental booking product (daily) — full response as customer.
     */
    public function test_rental_daily_booking_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('rental');
        $dateFrom = $fixture['tomorrowDate'];
        $dateTo = Carbon::parse($dateFrom)->addDay()->format('Y-m-d');

        $booking = json_encode([
            'type' => 'rental',
            'renting_type' => 'daily',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, false);
    }

    /**
     * Rental booking product (hourly) — full response as guest.
     */
    public function test_rental_hourly_booking_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $this->seedRequiredData();

        $fixture = $this->createHourlyRentalFixture();
        $date = $fixture['tomorrowDate'];

        $slot = $this->getRentalHourlySlot((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for hourly rental booking product.');
        }

        $booking = json_encode([
            'type' => 'rental',
            'renting_type' => 'hourly',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, true);
    }

    /**
     * Rental booking product (hourly) — full response as customer.
     */
    public function test_rental_hourly_booking_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->seedRequiredData();

        $fixture = $this->createHourlyRentalFixture();
        $date = $fixture['tomorrowDate'];

        $slot = $this->getRentalHourlySlot((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for hourly rental booking product.');
        }

        $booking = json_encode([
            'type' => 'rental',
            'renting_type' => 'hourly',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, false);
    }

    /**
     * Event booking product — full response as guest.
     */
    public function test_event_booking_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('event');

        $ticketIds = DB::table('booking_product_event_tickets')
            ->where('booking_product_id', (int) $fixture['booking']->id)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $qty = [];
        foreach ($ticketIds as $ticketId) {
            $qty[(string) $ticketId] = 1;
        }

        $booking = json_encode([
            'type' => 'event',
            'qty' => $qty,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, true);
    }

    /**
     * Event booking product — full response as customer.
     */
    public function test_event_booking_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('event');

        $ticketIds = DB::table('booking_product_event_tickets')
            ->where('booking_product_id', (int) $fixture['booking']->id)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $qty = [];
        foreach ($ticketIds as $ticketId) {
            $qty[(string) $ticketId] = 1;
        }

        $booking = json_encode([
            'type' => 'event',
            'qty' => $qty,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, false);
    }

    /**
     * Table booking product — full response as guest with special note.
     */
    public function test_table_booking_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('table');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for table booking product.');
        }

        $booking = json_encode([
            'type' => 'table',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
            'specialNote' => 'This is a special note',
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, true);
    }

    /**
     * Table booking product — full response as customer with special note.
     */
    public function test_table_booking_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->seedRequiredData();

        $fixture = $this->createBookingProductFixture('table');
        $date = $fixture['tomorrowDate'];

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $date);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for table booking product.');
        }

        $booking = json_encode([
            'type' => 'table',
            'date' => $date,
            'slot' => $slot,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullBookingMutation(), [
            'productId' => (int) $fixture['product']->id,
            'quantity' => 1,
            'booking' => $booking,
            'specialNote' => 'This is a special note',
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $json['data']['createAddProductInCart']['addProductInCart'];
        $this->assertFullBookingCartResponse($data, (int) $fixture['product']->id, false);
    }
}
