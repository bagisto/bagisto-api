// tests/restAPI/api/automation/admin/customers/customerImpersonate.spec.ts
//
// Admin Customer Impersonate (W2) — POST /customers/{customerId}/impersonate
// returns a 1-hour customer Sanctum token. We assert the token shape but do
// NOT exercise it against any storefront endpoint (out of scope here).

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

test.describe.configure({ timeout: 120_000 });

async function createCustomer(request: APIRequestContext) {
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
    method: 'POST',
    data: {
      first_name: 'Imp',
      last_name: 'ersonate',
      email: `e2e_imp_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
      customer_group_id: 2,
      channel_id: 1,
      send_password: false,
      password: 'e2epass123',
    },
  });
  return await resp.json();
}

async function dropCustomer(request: APIRequestContext, id: number) {
  try {
    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(id), { method: 'DELETE' });
  } catch {
    // ignore — best-effort cleanup
  }
}

test.describe('Admin Customer Impersonate REST API', () => {
  test('impersonate returns a token bound to the customer', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_IMPERSONATE(customer.id),
      { method: 'POST', data: {} },
    );
    expect([200, 201]).toContain(resp.status());
    const body = await resp.json();
    expect(typeof body.token).toBe('string');
    expect(body.token.length).toBeGreaterThan(20);
    expect(body.customerId).toBe(customer.id);
    expect(body.customerEmail).toBe(customer.email);
    expect(typeof body.expiresAt).toBe('string');
    expect(typeof body.impersonatedByAdminId).toBe('number');

    await dropCustomer(request, customer.id);
  });

  test('impersonate of unknown customer returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_IMPERSONATE(99999999),
      { method: 'POST', data: {} },
    );
    expect([400, 404]).toContain(resp.status());
  });
});
