import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';
import { placeOrder } from '../../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 120_000 });

function uniqueEmail(prefix = 'cust'): string {
  return `e2e_${prefix}_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`;
}

async function createCustomer(request: APIRequestContext, overrides: Record<string, any> = {}) {
  const payload = {
    first_name: 'E2E',
    last_name: 'Tester',
    email: uniqueEmail(),
    customer_group_id: 2,
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

test.describe('admin customers rest api', () => {
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

  test('list filter by email like narrows the result', async ({ request }) => {
    const { resp: createResp, payload } = await createCustomer(request);
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();

    const filterResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      params: { email: payload.email },
    });
    expect(filterResp.status()).toBe(200);
    const body = await filterResp.json();
    expect(body.data.length).toBeGreaterThan(0);
    expect(body.data.some((c: any) => c.id === created.id)).toBe(true);

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
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created.id).toBeGreaterThan(0);
    expect(created.email).toContain('@example.com');

    const detailResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id));
    expect(detailResp.status()).toBe(200);
    const detail = await detailResp.json();
    expect(detail.id).toBe(created.id);
    expect(typeof detail.totalAddresses).toBe('number');
    expect(typeof detail.totalOrders).toBe('number');

    const delResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Customer deleted successfully.');

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
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The email field is required.');
  });

  test('create rejects duplicate email with 422', async ({ request }) => {
    const { resp: firstResp, payload } = await createCustomer(request);
    expect(firstResp.status()).toBe(201);
    const first = await firstResp.json();

    const dupResp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
      method: 'POST',
      data: { ...payload, first_name: 'Dup' },
    });
    expect(dupResp.status()).toBe(422);
    expect((await dupResp.json()).detail).toBe('The email has already been taken.');

    const list = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, { params: { email: payload.email } });
    expect((await list.json()).data.map((c: any) => c.id)).toEqual([first.id]);

    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(first.id), { method: 'DELETE' });
  });

  test('update persists changes', async ({ request }) => {
    const { resp: createResp } = await createCustomer(request);
    expect(createResp.status()).toBe(201);
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
    expect(updResp.status()).toBe(200);
    const updated = await updResp.json();
    expect(updated.lastName).toBe(newLast);

    const detail = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id));
    expect((await detail.json()).lastName).toBe(newLast);

    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(created.id), { method: 'DELETE' });
  });

  test('detail returns 404 for unknown id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Customer not found.');
  });

  test('delete unknown id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(99999999), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Customer not found.');
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

    const gone = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(aId));
    expect(gone.status()).toBe(404);
  });

  test('mass-delete rejects empty indices with 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('At least one customer id is required.');
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

    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [aId, bId] },
    });
  });

  test('mass-update-status rejects invalid value', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [99999999], value: 99 },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('Status value must be 0 or 1.');
  });

  test('delete refuses a customer with a pending order', async ({ request }) => {
    test.setTimeout(180_000);
    const order = await placeOrder(request);

    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(order.customer.id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(400);
    expect((await resp.json()).detail).toBe('Customer has pending or processing orders and cannot be deleted.');

    const detail = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(order.customer.id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).id).toBe(order.customer.id);
  });
});
