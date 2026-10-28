import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

test.describe.configure({ timeout: 120_000 });

async function createCustomer(request: APIRequestContext) {
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
    method: 'POST',
    data: {
      first_name: 'Note',
      last_name: 'Subject',
      email: `e2e_note_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
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

async function listNotes(request: APIRequestContext, customerId: number): Promise<any[]> {
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER_NOTES(customerId));
  expect(resp.status()).toBe(200);
  return (await resp.json()).data;
}

test.describe('admin customer notes rest api', () => {
  test('create note with only note text', async ({ request }) => {
    const customer = await createCustomer(request);
    const text = `e2e note ${Date.now()}`;

    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(customer.id),
      { method: 'POST', data: { note: text } },
    );
    expect(resp.status()).toBe(201);
    const body = await resp.json();
    expect(body.id).toBeGreaterThan(0);
    expect(body.customerId).toBe(customer.id);
    expect(body.note).toBe(text);
    expect(body.customerNotified).toBe(false);

    expect((await listNotes(request, customer.id)).map((row: any) => row.id)).toEqual([body.id]);

    await dropCustomer(request, customer.id);
  });

  test('create note with customernotified true', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(customer.id),
      {
        method: 'POST',
        data: { note: `notify ${Date.now()}`, customerNotified: true },
      },
    );
    expect(resp.status()).toBe(201);
    const body = await resp.json();
    expect(body.customerNotified).toBe(true);

    await dropCustomer(request, customer.id);
  });

  test('create rejects empty note', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(customer.id),
      { method: 'POST', data: { note: '' } },
    );
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('Note text is required.');
    expect(await listNotes(request, customer.id)).toEqual([]);

    await dropCustomer(request, customer.id);
  });

  test('create for unknown customer returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(99999999),
      { method: 'POST', data: { note: 'orphan' } },
    );
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Customer not found.');
  });
});
