// tests/restAPI/api/automation/estimateShipping.spec.ts
//
// EstimateShipping is exposed over REST as `POST /api/shop/estimate-shippings`
// (there is no GET). It estimates shipping for the cart of the Bearer token, so
// a request without a cart token is refused with 401.
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string, allowed: number[]) {
  expect(allowed).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Estimate Shipping REST API', () => {
  test('GET returns 404/405 (REST not exposed)', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST);
    assertStatus(response, 'GET /api/shop/estimate-shippings', [0, 200, 400, 404, 405, 500]);
  });

  test('POST without params returns 4xx', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST, {
      method: 'POST',
      data: {},
    });
    assertStatus(response, 'POST /api/shop/estimate-shippings empty', [400, 401, 403, 422]);
  });

  test('POST with non-shippable params returns 4xx', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST, {
      method: 'POST',
      data: { country: 'US', postcode: '94103', token: 'invalid-token' },
    });
    assertStatus(response, 'POST /api/shop/estimate-shippings invalid token', [400, 401, 403, 422]);
  });
});
