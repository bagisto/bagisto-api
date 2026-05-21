// tests/restAPI/api/automation/customerAddress.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertCustomerAddressFields } from '../../rest/assertions/customerAddress.assertions';

test.describe('Customer Addresses (Public)', () => {
  test('Should return 401/404 when unauthenticated on GET /customer-addresses', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESSES);
    expect([401, 404]).toContain(response.status());
    console.log('Customer addresses (no auth):', response.status());
  });

  test('Should return 401/404 when creating address without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS_CREATE, {
      method: 'POST',
      data: {
        firstName: 'Test', lastName: 'User',
        address1: '123 Main St', city: 'Los Angeles',
        state: 'CA', country: 'US', postcode: '90210',
        phone: '555-0101',
      },
    });
    expect([401, 404]).toContain(response.status());
    console.log('POST /customer-addresses (no auth):', response.status());
  });

  test('Should return 401/404 when fetching address /id without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(1));
    expect([401, 404]).toContain(response.status());
    console.log('GET /customer-addresses/1 (no auth):', response.status());
  });
});

test.describe('Customer Addresses (PUT / DELETE)', () => {
  test('Should return 404 when updating non-existent address without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(999999), {
      method: 'PUT',
      data: { city: 'San Francisco' },
    });
    expect(response.status()).toBe(404);
    console.log('PUT /customer-addresses/999999: 404 (route not registered)');
  });

  test('Should return 404 when deleting non-existent address without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ADDRESS(999999), {
      method: 'DELETE',
    });
    expect(response.status()).toBe(404);
    console.log('DELETE /customer-addresses/999999: 404 (route not registered)');
  });
});
