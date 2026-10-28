import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function basePayload(ts: string) {
  return {
    entity_type: 'product',
    request_path: `e2e-old-${ts}`,
    target_path: `e2e-new-${ts}`,
    redirect_type: '301',
    locale: 'en',
  };
}

async function createRewrite(request: APIRequestContext, payload: Record<string, any>): Promise<number> {
  const r = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES, {
    method: 'POST',
    data: payload,
  });
  expect(r.status()).toBe(201);

  return (await r.json()).id;
}

test.describe('admin marketing url rewrites', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('listing with entity_type filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES, {
      params: { entity_type: 'product' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('URL rewrite not found.');
  });

  test('create with empty body returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The entity type field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES, {
      method: 'POST',
      data: basePayload(ts),
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({
      entityType: 'product',
      requestPath: `e2e-old-${ts}`,
      targetPath: `e2e-new-${ts}`,
      redirectType: '301',
      locale: 'en',
    }));
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITE(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).requestPath).toBe(`e2e-old-${ts}`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITE(id), {
      method: 'PUT' as any,
      data: { ...basePayload(ts), redirect_type: '302' },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, redirectType: '302' }));

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITE(id));
    expect((await reread.json()).redirectType).toBe('302');

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITE(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('URL rewrite deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITE(id));
    expect(gone.status()).toBe(404);
  });

  test('create with invalid entity_type returns 422', async ({ request }) => {
    const ts = uniqueStamp();
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES, {
      method: 'POST',
      data: { ...basePayload(ts), entity_type: 'banana' },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The selected entity type is invalid.');
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('At least one URL rewrite id is required.');
  });

  test('mass-delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const ids = [
      await createRewrite(request, { ...basePayload(`${ts}-0`), request_path: `e2e-mass-${ts}-0` }),
      await createRewrite(request, { ...basePayload(`${ts}-1`), request_path: `e2e-mass-${ts}-1` }),
    ];

    const resp = await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITES_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(resp.status()).toBe(200);
    expect(await resp.json()).toEqual(expect.objectContaining({
      deleted: ids,
      message: 'URL rewrites deleted successfully.',
    }));

    for (const id of ids) {
      expect((await sendAdminRequest(request, ADMIN_MARKETING.URL_REWRITE(id))).status()).toBe(404);
    }
  });
});
