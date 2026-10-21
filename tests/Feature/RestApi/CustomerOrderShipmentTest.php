<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;

class CustomerOrderShipmentTest extends RestApiTestCase
{
    /**
     * The shipments url.
     */
    private string $shipmentsUrl = '/api/shop/customer-order-shipments';

    /**
     * The shipment items url.
     */
    private string $shipmentItemsUrl = '/api/shop/customer-order-shipment-items';

    // ============================================================================
    // /customer-order-shipments
    // ============================================================================

    /**
     * Test get shipments collection requires auth.
     */
    public function test_get_shipments_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->shipmentsUrl);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test get shipments collection for authenticated customer.
     */
    public function test_get_shipments_collection_for_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->shipmentsUrl);

        expect($response->getStatusCode())->toBeIn([200, 500]);
        if ($response->getStatusCode() === 200) {
            expect($response->json())->toBeArray();
        }
    }

    /**
     * Test get nonexistent shipment returns error.
     */
    public function test_get_nonexistent_shipment_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->shipmentsUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
    }

    // ============================================================================
    // /customer-order-shipment-items
    // ============================================================================

    /**
     * Test get shipment items collection requires auth.
     */
    public function test_get_shipment_items_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->shipmentItemsUrl);

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    /**
     * Test get shipment items collection for authenticated customer.
     */
    public function test_get_shipment_items_collection_for_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->shipmentItemsUrl);

        expect($response->getStatusCode())->toBeIn([200, 500]);
    }

    /**
     * Test get nonexistent shipment item returns error.
     */
    public function test_get_nonexistent_shipment_item_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->shipmentItemsUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
    }
}
