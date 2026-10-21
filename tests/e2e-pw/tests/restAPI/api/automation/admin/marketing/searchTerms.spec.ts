import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

async function createTerm(request: APIRequestContext, term: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS, {
    method: 'POST',
    data: { term, channel_id: 1, locale: 'en' },
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(body.term).toBe(term);

  return body.id;
}

async function deleteTerm(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id), { method: 'DELETE' });
}

test.describe('admin marketing search terms', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('listing with term filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS, {
      params: { term: 'nonexistent-zzz' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Search term not found.');
  });

  test('update non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(99999999), {
      method: 'PUT' as any,
      data: { term: 'new-term' },
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Search term not found.');
  });

  test('update and delete a created term round trip', async ({ request }) => {
    const original = `e2e-term-${uniqueStamp()}`;
    const id = await createTerm(request, original);
    const renamed = `${original}-renamed`;

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id), {
      method: 'PUT' as any,
      data: { term: renamed },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, term: renamed }));

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).term).toBe(renamed);

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id), { method: 'DELETE' });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Search term deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id));
    expect(gone.status()).toBe(404);
  });

  test('update with empty term returns 422', async ({ request }) => {
    const original = `e2e-term-${uniqueStamp()}`;
    const id = await createTerm(request, original);

    try {
      const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id), {
        method: 'PUT' as any,
        data: { term: '' },
      });
      expect(resp.status()).toBe(422);
      expect((await resp.json()).detail).toBe('The term field is required.');

      const detail = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id));
      expect((await detail.json()).term).toBe(original);
    } finally {
      await deleteTerm(request, id);
    }
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('At least one ID must be provided in indices.');
  });

  test('mass-delete non-existent ids deletes nothing', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [99999998, 99999999] },
    });
    expect(resp.status()).toBe(200);
    expect(await resp.json()).toEqual(expect.objectContaining({
      deleted: [],
      message: 'Search terms deleted successfully.',
    }));
  });

  test('mass-delete removes created terms', async ({ request }) => {
    const stamp = uniqueStamp();
    const ids = [
      await createTerm(request, `e2e-term-mass-${stamp}-0`),
      await createTerm(request, `e2e-term-mass-${stamp}-1`),
    ];

    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS_MASS_DELETE, {
      method: 'POST',
      data: { indices: ids },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).deleted).toEqual(ids);

    for (const id of ids) {
      expect((await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id))).status()).toBe(404);
    }
  });
});
