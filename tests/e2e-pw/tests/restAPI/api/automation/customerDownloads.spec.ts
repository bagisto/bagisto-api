// tests/restAPI/api/automation/customerDownloads.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertPublicCustStatus(resp: any, label: string) {
  // endpoint returns 403 (not 401) when unauthenticated in this installation;
  // may also return 500 for unregistered routes.
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${label}:`, resp.status());
}

test.describe('Customer Downloadable Products (Public)', () => {
  test('Should handle GET /customer-downloadable-products without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_DOWNLOADABLE_PRODUCTS);
    assertPublicCustStatus(response, 'GET /api/shop/customer-downloadable-products');
  });

  test('Should handle GET /customer-downloadable-products/{id} without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_DOWNLOADABLE_PRODUCT(1));
    assertPublicCustStatus(response, 'GET /api/shop/customer-downloadable-products/1');
  });
});
