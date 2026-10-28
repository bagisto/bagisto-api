import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function createSynonym(request: APIRequestContext, name: string, terms: string): Promise<number> {
  const r = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYMS, {
    method: 'POST',
    data: { name, terms },
  });
  expect(r.status()).toBe(201);

  return (await r.json()).id;
}

test.describe('admin marketing search synonyms', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYMS);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYMS, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYM(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Search synonym not found.');
  });

  test('create with empty body returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYMS, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYMS, {
      method: 'POST',
      data: { name: `e2e-syn-${ts}`, terms: 'shirt,tshirt,tee' },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({ name: `e2e-syn-${ts}`, terms: 'shirt,tshirt,tee' }));
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYM(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).name).toBe(`e2e-syn-${ts}`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYM(id), {
      method: 'PUT' as any,
      data: { name: `e2e-syn-${ts}-upd`, terms: 'pant,trousers' },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, name: `e2e-syn-${ts}-upd`, terms: 'pant,trousers' }));

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYM(id));
    expect((await reread.json()).terms).toBe('pant,trousers');

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYM(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Search synonym deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYM(id));
    expect(gone.status()).toBe(404);
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYMS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The indices field is required and must be a non-empty array of search synonym IDs.');
  });

  test('mass-delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const ids = [
      await createSynonym(request, `e2e-syn-mass-${ts}-0`, 'a,b,c'),
      await createSynonym(request, `e2e-syn-mass-${ts}-1`, 'a,b,c'),
    ];

    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYMS_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(resp.status()).toBe(200);
    expect(await resp.json()).toEqual(expect.objectContaining({
      deleted: ids,
      message: 'Search synonyms deleted successfully.',
    }));

    for (const id of ids) {
      expect((await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_SYNONYM(id))).status()).toBe(404);
    }
  });
});
