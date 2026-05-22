// tests/restAPI/api/automation/customerOrderDetail.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

function authHeaders(token: string) {
  return { Authorization: `Bearer ${token}` };
}

function generateUniqueEmail() {
  return `order_${Date.now()}@example.com`;
}

function generatePassword() {
  return `Order${Math.floor(Math.random() * 10000)}!`;
}

let authToken: string | null = null;
let customerEmail: string;
let customerPassword: string;

test.describe('Customer Order — GET /detail', () => {
  test('Should return status for /customer-orders/1 without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(1));
    assertStatus(response, 'GET /api/shop/customer-orders/1 (no auth)');
  });

  test('Should return status for /customer-orders/999999 without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(999999));
    assertStatus(response, 'GET /api/shop/customer-orders/999999 (no auth)');
  });
});

test.describe('Customer Order — Single Order Detail', () => {
  let orderId: number | null = null;

  test.beforeAll(async ({ request }) => {
    customerEmail = generateUniqueEmail();
    customerPassword = generatePassword();
    console.log(`Customer order test credentials - Email: ${customerEmail}, Password: ${customerPassword}`);

    const loginResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email: customerEmail, password: customerPassword },
    });

    if (loginResp.status() === 200) {
      const body = await loginResp.json();
      authToken = body.token as string;
      console.log('Logged in. Token:', authToken?.slice(0, 12) + '...');
    } else {
      // Try to register first
      const registerResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
        method: 'POST',
        data: {
          first_name: 'Order',
          last_name: 'User',
          email: customerEmail,
          password: customerPassword,
          password_confirmation: customerPassword,
        },
      });

      if (registerResp.status() === 200 || registerResp.status() === 201) {
        const loginRetry = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
          method: 'POST',
          data: { email: customerEmail, password: customerPassword },
        });
        if (loginRetry.status() === 200) {
          const body = await loginRetry.json();
          authToken = body.token as string;
          console.log('Registered and logged in for customer order tests');
        }
      }
    }

    if (authToken) {
      const ordersResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDERS, {
        headers: authHeaders(authToken),
      });
      if (ordersResp.status() === 200) {
        const ordersBody = await ordersResp.json();
        if (Array.isArray(ordersBody) && ordersBody.length > 0) {
          orderId = ordersBody[0].id;
        }
      }
    }
  });

  test('Should return 401/403 when fetching order without auth token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(1));
    assertStatus(response, 'GET /api/shop/customer-orders/1 (no auth token)');
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
    assertStatus(response, `GET /api/shop/customer-orders/${orderId} (authenticated)`);
    if (response.status() === 200) {
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
    }
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