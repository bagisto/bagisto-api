import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../../rest/helpers/restClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';
import { ENDPOINTS } from '../../../../rest/endpoints/endpoints';

test.describe.configure({ timeout: 60_000 });

const NOT_FOUND = 'Newsletter subscriber not found.';

function uniqueEmail(): string {
  return `e2e_sub_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}@example.com`;
}

async function subscribe(request: APIRequestContext): Promise<{ id: number; email: string }> {
  const email = uniqueEmail();
  const resp = await sendRestRequest(request, ENDPOINTS.NEWSLETTER_SUBSCRIBE, {
    method: 'POST',
    data: { customerEmail: email },
  });
  expect(resp.status()).toBe(201);
  expect((await resp.json()).success).toBe(true);

  const list = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS, { params: { email } });
  expect(list.status()).toBe(200);
  const rows = (await list.json()).data;
  expect(rows).toHaveLength(1);
  expect(rows[0]).toEqual(expect.objectContaining({ email, isSubscribed: true }));

  return { id: rows[0].id, email };
}

test.describe('admin marketing newsletter subscribers', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('listing with is_subscribed filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS, {
      params: { is_subscribed: '1' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('toggle a created subscriber and delete it', async ({ request }) => {
    const { id, email } = await subscribe(request);

    const toggleResp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id), {
      method: 'PUT' as any,
      data: { is_subscribed: false },
    });
    expect(toggleResp.status()).toBe(200);
    expect(await toggleResp.json()).toEqual(expect.objectContaining({ id, email, isSubscribed: false }));

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).isSubscribed).toBe(false);

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id), { method: 'DELETE' });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Subscription deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id));
    expect(gone.status()).toBe(404);
  });

  test('toggle missing is_subscribed returns 422', async ({ request }) => {
    const { id } = await subscribe(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id), {
        method: 'PUT' as any,
        data: {},
      });
      expect(resp.status()).toBe(422);
      expect((await resp.json()).detail).toBe('The is_subscribed field is required.');

      const detail = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id));
      expect((await detail.json()).isSubscribed).toBe(true);
    } finally {
      await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id), { method: 'DELETE' });
    }
  });

  test('delete non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(99999999), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });
});
