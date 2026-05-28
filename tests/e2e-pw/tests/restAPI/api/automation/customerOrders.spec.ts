// tests/restAPI/api/automation/customerOrders.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Customer Orders (Public)', () => {
  test('Should return 403 when unauthenticated — no token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDERS);
    expect(response.status()).toBe(403);
    const body = await response.json();
    expect(body).toHaveProperty('detail');
    expect(body.detail).toBe('Unauthenticated. Please login to perform this action');
    console.log('Customer orders (no auth):', response.status());
  });

  test('Should return 403 when fetching order /id without token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(1));
    expect(response.status()).toBe(403);
    console.log('Customer order /1 (no auth):', response.status());
  });
});
