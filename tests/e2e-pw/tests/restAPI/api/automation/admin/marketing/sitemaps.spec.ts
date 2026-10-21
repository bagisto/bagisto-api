import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

function uniqueStamp(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

test.describe('admin marketing sitemaps', () => {
  test('listing returns 200 and envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS);
    expect(resp.status()).toBe(200);
    expect(Array.isArray((await resp.json()).data)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      params: { page: '1', per_page: '5' },
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).meta.perPage).toBe(5);
  });

  test('detail non-existent returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Sitemap not found.');
  });

  test('create with empty body returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The file name field is required.');
  });

  test('create detail update and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: { file_name: `e2e-sitemap-${ts}.xml`, path: '/', channels: [1] },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(created).toEqual(expect.objectContaining({ fileName: `e2e-sitemap-${ts}.xml`, path: '/', channels: [1] }));
    const id = created.id;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id));
    expect(detail.status()).toBe(200);
    expect((await detail.json()).fileName).toBe(`e2e-sitemap-${ts}.xml`);

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id), {
      method: 'PUT' as any,
      data: { file_name: `e2e-sitemap-${ts}-upd.xml`, path: '/' },
    });
    expect(updateResp.status()).toBe(200);
    expect(await updateResp.json()).toEqual(expect.objectContaining({ id, fileName: `e2e-sitemap-${ts}-upd.xml`, channels: [1] }));

    const reread = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id));
    expect((await reread.json()).fileName).toBe(`e2e-sitemap-${ts}-upd.xml`);

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id), {
      method: 'DELETE',
    });
    expect(delResp.status()).toBe(200);
    expect((await delResp.json()).message).toBe('Sitemap deleted successfully.');

    const gone = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id));
    expect(gone.status()).toBe(404);
  });

  test('create without channels returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: { file_name: `e2e-sitemap-${uniqueStamp()}.xml`, path: '/' },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The channels field is required.');
  });

  test('create with invalid file_name without xml extension returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: { file_name: `e2e-${uniqueStamp()}.txt`, path: '/', channels: [1] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The file name field must end with one of the following: .xml.');
  });

  test('create with invalid path without leading slash returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: { file_name: `e2e-${uniqueStamp()}.xml`, path: 'invalid', channels: [1] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The path field must start with one of the following: /.');
  });
});
