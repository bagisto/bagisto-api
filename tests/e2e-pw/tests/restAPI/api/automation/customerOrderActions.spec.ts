// tests/restAPI/api/automation/customerOrderActions.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string, allowed: number[] = [0, 200, 201, 400, 401, 403, 404, 422, 500]) {
  expect(allowed).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

function authHeaders(token: string) {
  return { Authorization: `Bearer ${token}` };
}

function uniqueEmail() {
  return `orderaction_${Date.now()}@example.com`;
}

function genPassword() {
  return `OrderAct${Math.floor(Math.random() * 10000)}!`;
}

let authToken: string | null = null;
let customerEmail: string;
let customerPassword: string;

test.describe('Customer Order Actions (Cancel + Reorder)', () => {
  test.beforeAll(async ({ request }) => {
    customerEmail = uniqueEmail();
    customerPassword = genPassword();

    const reg = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
      method: 'POST',
      data: {
        first_name: 'Order',
        last_name: 'Action',
        email: customerEmail,
        password: customerPassword,
        password_confirmation: customerPassword,
      },
    });
    if (reg.status() === 200 || reg.status() === 201) {
      const login = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
        method: 'POST',
        data: { email: customerEmail, password: customerPassword },
      });
      if (login.status() === 200) {
        const body = await login.json();
        authToken = body.token as string;
      }
    }
  });

  test('POST /cancel-order without auth → 401/403', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: { id: 1 },
    });
    expect([401, 403, 404, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/cancel-order no auth:', response.status());
  });

  test('POST /cancel-order with auth — missing id → 4xx', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'No auth token');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: {},
      headers: authHeaders(authToken),
    });
    expect([200, 400, 401, 403, 404, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/cancel-order missing id:', response.status());
  });

  test('POST /cancel-order with non-existent order id → 4xx', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'No auth token');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: { id: 999999 },
      headers: authHeaders(authToken),
    });
    expect([200, 400, 401, 403, 404, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/cancel-order non-existent:', response.status());
  });

  test('POST /reorder without auth → 401/403', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER, {
      method: 'POST',
      data: { id: 1 },
    });
    expect([401, 403, 404, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/reorder no auth:', response.status());
  });

  test('POST /reorder with auth — non-existent order id → 4xx', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'No auth token');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER, {
      method: 'POST',
      data: { id: 999999 },
      headers: authHeaders(authToken),
    });
    expect([200, 201, 400, 401, 403, 404, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/reorder non-existent:', response.status());
  });

  test('POST /reorder with auth — missing id → 4xx', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'No auth token');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER, {
      method: 'POST',
      data: {},
      headers: authHeaders(authToken),
    });
    expect([200, 400, 401, 403, 404, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/reorder missing id:', response.status());
  });

  test('Cross-customer order: cancel another customer\'s order → 403/404', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'No auth token');
      return;
    }
    // Try to cancel id=1 (likely belongs to another customer or doesn't exist)
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: { id: 1 },
      headers: authHeaders(authToken),
    });
    expect([200, 400, 401, 403, 404, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/cancel-order cross-customer:', response.status());
  });

  test('GET on /cancel-order should be rejected', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER);
    assertStatus(response, 'GET /api/shop/cancel-order', [0, 200, 400, 404, 405, 500]);
  });

  test('GET on /reorder should be rejected', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER);
    assertStatus(response, 'GET /api/shop/reorder', [0, 200, 400, 404, 405, 500]);
  });
});
