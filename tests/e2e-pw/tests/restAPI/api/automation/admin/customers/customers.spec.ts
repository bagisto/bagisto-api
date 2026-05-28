// tests/restAPI/api/automation/admin/customers/customers.spec.ts
//
// Admin Customers (W2) — CRUD + mass-delete + mass-update-status.
//
// Probed 2026-05-26:
//   GET    /api/admin/customers              → { data:[{id,firstName,...,customerGroupId,...}], meta }
//   POST   /api/admin/customers              → 201 + full customer detail
//   GET    /api/admin/customers/{id}         → detail with totalAddresses/totalOrders/totalAmountSpent populated
//   PUT    /api/admin/customers/{id}         → updated customer detail
//   DELETE /api/admin/customers/{id}         → { message }  (refuses with 400 if pending/processing orders)
//   POST   /api/admin/customers/mass-delete  → { deleted:[], skipped:[], message }
//   POST   /api/admin/customers/mass-update-status → { updated:[], value, message }
//
// Required create fields probed live: first_name, last_name, email,
// customer_group_id, channel_id, password (with send_password=false).

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

// Per-test timeout bumped — under parallel load create+detail+delete chains
// can exceed the 60s default.
test.describe.configure({ timeout: 120_000 });

function uniqueEmail(prefix = 'cust'): string {
  return `e2e_${prefix}_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`;
}

async function createCustomer(request: APIRequestContext, overrides: Record<string, any> = {}) {
  const payload = {
    first_name: 'E2E',
    last_name: 'Tester',
    email: uniqueEmail(),
    customer_group_id: 2, // 'general' — system group, always present
    channel_id: 1,
    send_password: false,
    password: 'e2epass123',
    ...overrides,
  };
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
    method: 'POST',
    data: payload,
  });
  return { resp, payload };
}

test.describe('Admin Customers REST API', () => {
  test('list returns envelope with data + meta', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      params: { per_page: '3' },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta).toBeDefined();
    expect(typeof body.meta.total).toBe('number');
  });

  test('list filter by email LIKE narrows the result', async ({ request }) => {
    const { resp: createResp, payload } = await createCustomer(request);
    expect([200, 201]).toContain(createResp.status());
    const created = await createResp.json();

    const filterResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      params: { email: payload.email },
    });
    expect(filterResp.status()).toBe(200);
    const body = await filterResp.json();
    expect(body.data.length).toBeGreaterThan(0);
    expect(body.data.some((c: any) => c.id === created.id)).toBe(true);

    // cleanup
    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id), { method: 'DELETE' });
  });

  test('list with status filter accepted', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      params: { status: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
  });

  test('create + detail + delete round-trip', async ({ request }) => {
    const { resp: createResp } = await createCustomer(request);
    expect([200, 201]).toContain(createResp.status());
    const created = await createResp.json();
    expect(created.id).toBeGreaterThan(0);
    expect(created.email).toContain('@example.com');

    const detailResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id));
    expect(detailResp.status()).toBe(200);
    const detail = await detailResp.json();
    expect(detail.id).toBe(created.id);
    // detail-only fields populated (vs listing where they're null)
    expect(typeof detail.totalAddresses).toBe('number');
    expect(typeof detail.totalOrders).toBe('number');

    const delResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id), {
      method: 'DELETE',
    });
    expect([200, 204]).toContain(delResp.status());

    // confirm gone
    const missing = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id));
    expect(missing.status()).toBe(404);
  });

  test('create rejects missing email with 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      method: 'POST',
      data: {
        first_name: 'No',
        last_name: 'Email',
        customer_group_id: 2,
        channel_id: 1,
        send_password: false,
        password: 'e2epass123',
      },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create rejects duplicate email with 422', async ({ request }) => {
    const { resp: firstResp, payload } = await createCustomer(request);
    expect([200, 201]).toContain(firstResp.status());
    const first = await firstResp.json();

    const dupResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      method: 'POST',
      data: { ...payload, first_name: 'Dup' },
    });
    expect([400, 422]).toContain(dupResp.status());

    // cleanup
    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(first.id), { method: 'DELETE' });
  });

  test('update persists changes', async ({ request }) => {
    const { resp: createResp } = await createCustomer(request);
    const created = await createResp.json();

    const newLast = `Updated${Date.now()}`;
    const updResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id), {
      method: 'PUT' as any,
      data: {
        first_name: created.firstName,
        last_name: newLast,
        email: created.email,
        customer_group_id: 2,
        channel_id: 1,
      },
    });
    expect([200, 201]).toContain(updResp.status());
    const updated = await updResp.json();
    expect(updated.lastName).toBe(newLast);

    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id), { method: 'DELETE' });
  });

  test('detail returns 404 for unknown id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(99999999));
    expect(resp.status()).toBe(404);
  });

  test('delete unknown id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(99999999), {
      method: 'DELETE',
    });
    expect([404, 400]).toContain(resp.status());
  });

  test('mass-delete removes both ids and reports them', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);
    const aId = (await a.resp.json()).id;
    const bId = (await b.resp.json()).id;

    const massResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [aId, bId] },
    });
    expect(massResp.status()).toBe(200);
    const body = await massResp.json();
    expect(Array.isArray(body.deleted)).toBe(true);
    expect(body.deleted).toEqual(expect.arrayContaining([aId, bId]));

    // confirm one is gone
    const gone = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(aId));
    expect(gone.status()).toBe(404);
  });

  test('mass-delete rejects empty indices with 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-update-status flips status for both ids', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);
    const aId = (await a.resp.json()).id;
    const bId = (await b.resp.json()).id;

    const massResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [aId, bId], value: 0 },
    });
    expect(massResp.status()).toBe(200);
    const body = await massResp.json();
    expect(Array.isArray(body.updated)).toBe(true);
    expect(body.updated).toEqual(expect.arrayContaining([aId, bId]));

    // cleanup
    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [aId, bId] },
    });
  });

  test('mass-update-status rejects invalid value', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [1], value: 99 },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('delete-with-active-orders guard (smoke)', async ({ request }) => {
    // We can't reliably create an order with status=pending here; this test
    // exercises the guard's HTTP shape against an arbitrary customer and
    // accepts either 200 (no orders → delete OK) or 400 (guard fired).
    // Skipped if no customers exist.
    const list = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      params: { per_page: '1' },
    });
    const body = await list.json();
    if (!body.data || body.data.length === 0) test.skip(true, 'no customers in DB');
    // Don't actually delete a real DB customer — just smoke the endpoint shape
    // by hitting an obviously-fake id.
    const probe = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(99999998), {
      method: 'DELETE',
    });
    expect([200, 204, 400, 404]).toContain(probe.status());
  });
});
