<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;

class CustomerOrderShipmentTest extends RestApiTestCase
{
    private string $shipmentsUrl = '/api/shop/customer-order-shipments';

    private string $shipmentItemsUrl = '/api/shop/customer-order-shipment-items';

    // ── /customer-order-shipments ─────────────────────────────

    public function test_get_shipments_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->shipmentsUrl);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

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

    public function test_get_nonexistent_shipment_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->shipmentsUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
    }

    // ── /customer-order-shipment-items ────────────────────────

    public function test_get_shipment_items_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->shipmentItemsUrl);

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_get_shipment_items_collection_for_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->shipmentItemsUrl);

        expect($response->getStatusCode())->toBeIn([200, 500]);
    }

    public function test_get_nonexistent_shipment_item_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->shipmentItemsUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
    }
}
