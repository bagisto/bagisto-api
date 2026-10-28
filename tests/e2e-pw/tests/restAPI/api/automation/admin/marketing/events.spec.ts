import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

test.describe('admin marketing events', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.EVENTS);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.EVENTS, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.EVENT(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Marketing event not found.');
  });

  test('create with empty body returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.EVENTS, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.EVENTS, {
      method: 'POST',
      data: {
        name: `E2E Event ${ts}`,
        description: 'e2e event',
        date: '2027-12-31',
      },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({ name: `E2E Event ${ts}`, description: 'e2e event' }));
    expect(created.date).toContain('2027-12-31');
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.EVENT(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).name).toBe(`E2E Event ${ts}`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.EVENT(id), {
      method: 'PUT' as any,
      data: {
        name: `E2E Event ${ts} updated`,
        description: 'updated',
        date: '2028-01-01',
      },
    });
    expect(updateResp.status()).toBe(200);
    const updated = await updateResp.json();
    expect(updated).toEqual(expect.objectContaining({ id, name: `E2E Event ${ts} updated`, description: 'updated' }));
    expect(updated.date).toContain('2028-01-01');

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.EVENT(id));
    expect((await reread.json()).description).toBe('updated');

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.EVENT(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Marketing event deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.EVENT(id));
    expect(gone.status()).toBe(404);
  });

  test('create missing date returns 422', async ({ request }) => {
    const name = `E2E Event ${uniqueStamp()}`;
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.EVENTS, {
      method: 'POST',
      data: { name, description: 'no date' },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The date field is required.');
  });
});
