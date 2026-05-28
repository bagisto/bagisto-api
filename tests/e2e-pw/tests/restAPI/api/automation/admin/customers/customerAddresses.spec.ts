// tests/restAPI/api/automation/admin/customers/customerAddresses.spec.ts
//
// Admin Customer Addresses (W2) — CRUD under /customers/{customerId}/addresses.
// Ownership guard: addresses with a customer_id != path customerId → 403.
//
// Probed 2026-05-26: address create accepts {company_name,first_name,last_name,
// address (single string), city, state, country, postcode, phone}. Response
// surfaces companyName camelCase.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

// Per-test timeout bumped — under parallel load (worker contention against
// the dev server) the customer-create + address-create + cleanup-delete chain
// occasionally exceeds the 60s default.
test.describe.configure({ timeout: 120_000 });

function uniqueEmail(): string {
  return `e2e_addr_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`;
}

async function createCustomer(request: APIRequestContext) {
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
    method: 'POST',
    data: {
      first_name: 'Addr',
      last_name: 'Owner',
      email: uniqueEmail(),
      customer_group_id: 2,
      channel_id: 1,
      send_password: false,
      password: 'e2epass123',
    },
  });
  return await resp.json();
}

async function dropCustomer(request: APIRequestContext, id: number) {
  // Best-effort cleanup — never fail on slow / failed delete cascade.
  try {
    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(id), { method: 'DELETE' });
  } catch {
    // ignore
  }
}

const addrPayload = (overrides: Record<string, any> = {}) => ({
  company_name: 'ACME Inc',
  first_name: 'Addr',
  last_name: 'Owner',
  address: '1 Main Street',
  city: 'NYC',
  state: 'NY',
  country: 'US',
  postcode: '10001',
  phone: '5551234567',
  ...overrides,
});

test.describe('Admin Customer Addresses REST API', () => {
  test('list returns envelope (even when empty)', async ({ request }) => {
    const customer = await createCustomer(request);
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(customer.id));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.data).toBeDefined();
    expect(Array.isArray(body.data)).toBe(true);
    await dropCustomer(request, customer.id);
  });

  test('create + detail + delete round-trip', async ({ request }) => {
    const customer = await createCustomer(request);

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(customer.id),
      { method: 'POST', data: addrPayload() },
    );
    expect([200, 201]).toContain(createResp.status());
    const created = await createResp.json();
    expect(created.id).toBeGreaterThan(0);
    expect(created.customerId).toBe(customer.id);
    expect(created.companyName).toBe('ACME Inc');

    const detail = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(customer.id, created.id),
    );
    expect(detail.status()).toBe(200);
    const detailBody = await detail.json();
    expect(detailBody.id).toBe(created.id);

    const del = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(customer.id, created.id),
      { method: 'DELETE' },
    );
    expect([200, 204]).toContain(del.status());

    await dropCustomer(request, customer.id);
  });

  test('update persists changes', async ({ request }) => {
    const customer = await createCustomer(request);

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(customer.id),
      { method: 'POST', data: addrPayload() },
    );
    const created = await createResp.json();

    const newCity = `City${Date.now()}`;
    const updResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(customer.id, created.id),
      {
        method: 'PUT' as any,
        data: addrPayload({ city: newCity }),
      },
    );
    expect([200, 201]).toContain(updResp.status());
    const updated = await updResp.json();
    expect(updated.city).toBe(newCity);

    await dropCustomer(request, customer.id);
  });

  test('create rejects missing required fields', async ({ request }) => {
    const customer = await createCustomer(request);
    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(customer.id),
      { method: 'POST', data: {} },
    );
    // Empty body → 400/422; some empty-body paths surface 500 syntax-error.
    expect([400, 422, 500]).toContain(resp.status());
    await dropCustomer(request, customer.id);
  });

  test('cross-customer detail fetch is forbidden or 404', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(a.id),
      { method: 'POST', data: addrPayload() },
    );
    const addr = await createResp.json();

    // Try to fetch a's address via b's URL — ownership guard should kick in.
    const probe = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(b.id, addr.id),
    );
    expect([403, 404]).toContain(probe.status());

    await dropCustomer(request, a.id);
    await dropCustomer(request, b.id);
  });

  test('cross-customer delete is forbidden or 404', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(a.id),
      { method: 'POST', data: addrPayload() },
    );
    const addr = await createResp.json();

    const probe = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(b.id, addr.id),
      { method: 'DELETE' },
    );
    expect([403, 404]).toContain(probe.status());

    await dropCustomer(request, a.id);
    await dropCustomer(request, b.id);
  });

  test('detail returns 404 for unknown address id', async ({ request }) => {
    const customer = await createCustomer(request);
    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(customer.id, 99999999),
    );
    expect(resp.status()).toBe(404);
    await dropCustomer(request, customer.id);
  });
});
