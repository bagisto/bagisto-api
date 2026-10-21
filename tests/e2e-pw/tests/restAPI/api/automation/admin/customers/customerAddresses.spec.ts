import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

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
  expect(resp.status()).toBe(201);
  return await resp.json();
}

async function dropCustomer(request: APIRequestContext, id: number) {
  await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(id), { method: 'DELETE' });
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

test.describe('admin customer addresses rest api', () => {
  test('list returns envelope even when empty', async ({ request }) => {
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
    expect(createResp.status()).toBe(201);
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
    expect(del.status()).toBe(200);
    expect((await del.json()).message).toBe('Address deleted successfully.');

    const gone = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(customer.id, created.id),
    );
    expect(gone.status()).toBe(404);

    await dropCustomer(request, customer.id);
  });

  test('update persists changes', async ({ request }) => {
    const customer = await createCustomer(request);

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(customer.id),
      { method: 'POST', data: addrPayload() },
    );
    expect(createResp.status()).toBe(201);
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
    expect(updResp.status()).toBe(200);
    const updated = await updResp.json();
    expect(updated.city).toBe(newCity);

    const detail = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(customer.id, created.id),
    );
    expect((await detail.json()).city).toBe(newCity);

    await dropCustomer(request, customer.id);
  });

  test('create rejects missing required fields', async ({ request }) => {
    const customer = await createCustomer(request);
    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(customer.id),
      { method: 'POST', data: {} },
    );
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The first name field is required.');

    const list = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(customer.id));
    expect((await list.json()).data).toEqual([]);
    await dropCustomer(request, customer.id);
  });

  test('cross-customer detail fetch returns 404', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(a.id),
      { method: 'POST', data: addrPayload() },
    );
    expect(createResp.status()).toBe(201);
    const addr = await createResp.json();

    const probe = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(b.id, addr.id),
    );
    expect(probe.status()).toBe(404);
    expect((await probe.json()).detail).toBe('Address not found.');

    await dropCustomer(request, a.id);
    await dropCustomer(request, b.id);
  });

  test('cross-customer delete returns 403', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESSES(a.id),
      { method: 'POST', data: addrPayload() },
    );
    expect(createResp.status()).toBe(201);
    const addr = await createResp.json();

    const probe = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(b.id, addr.id),
      { method: 'DELETE' },
    );
    expect(probe.status()).toBe(403);
    expect((await probe.json()).detail).toBe('This address does not belong to the specified customer.');

    const stillThere = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER_ADDRESS(a.id, addr.id));
    expect(stillThere.status()).toBe(200);

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
