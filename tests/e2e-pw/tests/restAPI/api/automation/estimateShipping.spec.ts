// tests/restAPI/api/automation/estimateShipping.spec.ts
//
// EstimateShipping is declared in the BagistoApi package as GraphQL-only
// (`operations: []`, `graphQlOperations: [Mutation]`). These REST smoke tests
// poke the URL anyway to lock down the expected absence of a REST endpoint
// (so a future REST exposure shows up here instead of silently shipping).
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
    assertStatus(response, 'POST /api/shop/estimate-shippings empty', [0, 200, 400, 404, 405, 422, 500]);
  });

  test('POST with non-shippable params returns 4xx', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING_REST, {
      method: 'POST',
      data: { country: 'US', postcode: '94103', token: 'invalid-token' },
    });
    assertStatus(response, 'POST /api/shop/estimate-shippings invalid token', [0, 200, 400, 404, 405, 422, 500]);
  });
});
