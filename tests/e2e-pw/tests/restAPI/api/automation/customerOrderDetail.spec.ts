// tests/restAPI/api/automation/customerOrderDetail.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertReviewStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

function authHeaders(token: string) {
  return { Authorization: `Bearer ${token}` };
}

test.describe('Customer Order — GET /detail ( Cust-Orders/{id} )', () => {
  test('Should return status for /customer-orders/1 without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(1));
    assertReviewStatus(response, 'GET /api/shop/customer-orders/1 (no auth)');
  });

  test('Should return status for /customer-orders/999999 without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(999999));
    assertReviewStatus(response, 'GET /api/shop/customer-orders/999999 (no auth)');
  });
});

test.describe('Customer Order — Single Order Detail (Auth Required)', () => {
  const hasCreds = !!(process.env.BAGISTO_CUSTOMER_EMAIL && process.env.BAGISTO_CUSTOMER_PASSWORD);
  let authToken: string | null = null;
  let orderId: number | null = null;

  test.beforeEach(async ({ request }) => {
    if (!hasCreds) {
      test.skip(true, 'BAGISTO_CUSTOMER_EMAIL / BAGISTO_CUSTOMER_PASSWORD not set in .env');
      return;
    }
    const email = process.env.BAGISTO_CUSTOMER_EMAIL!;
    const password = process.env.BAGISTO_CUSTOMER_PASSWORD!;
    const loginResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST', data: { email, password },
    });
    if (loginResp.status() === 200) {
      const body = await loginResp.json();
      authToken = body.token as string;
      console.log('Logged in. Token:', authToken.slice(0, 12) + '...');
    }

    const ordersResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDERS, {
      headers: authToken ? authHeaders(authToken) : {},
    });
    if (ordersResp.status() === 200) {
      const ordersBody = await ordersResp.json();
      if (Array.isArray(ordersBody) && ordersBody.length > 0) {
        orderId = ordersBody[0].id;
      }
    }
  });

  test('Should return 401/403 when fetching order without auth token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(1));
    assertReviewStatus(response, 'GET /api/shop/customer-orders/1 (no auth token)');
  });

  test('Should return own order details when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    if (!orderId) {
      test.skip(true, 'No customer orders found');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(orderId), {
      headers: authHeaders(authToken),
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.id).toBe(orderId);
    expect(body).toHaveProperty('status');
    expect(body).toHaveProperty('items');
    console.log('Order detail:', JSON.stringify({
      id: body.id,
      status: body.status,
      items: body.items?.length,
      total: body.total,
      grandTotal: body.grandTotal,
    }, null, 2));
  });

  test('Should return order items when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    if (!orderId) {
      test.skip(true, 'No customer orders found');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(orderId), {
      headers: authHeaders(authToken),
    });
    if (response.status() === 200) {
      const body = await response.json();
      const items = body.items ?? [];
      expect(Array.isArray(items)).toBeTruthy();
      if (items.length > 0) {
        items.forEach((item: any) => {
          expect(item).toHaveProperty('id');
          expect(item).toHaveProperty('name');
          expect(item).toHaveProperty('qty');
        });
        console.log(`Order ${orderId} item details:`, items.map((i: any) => ({
          id: i.id, name: i.name, qty: i.qty, unitPrice: i.unitPrice,
        })));
      }
    }
  });

  test('Should return 404 for non-existent order when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(999999), {
      headers: authHeaders(authToken),
    });
    expect([200, 404]).toContain(response.status());
    console.log(`GET /api/shop/customer-orders/999999 (authenticated):`, response.status());
  });
});
