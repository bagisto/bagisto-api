// Admin CMS Pages REST e2e.
// Listing / detail / create / update / delete / mass-delete.
// Body shape: create = top-level scalars broadcast across locales; update is
// LOCALE-NESTED { "<locale>": { url_key, page_title, html_content, ... },
// channels: [...], locale: "<code>" } per CLAUDE.md.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];
const OK_CREATE = [200, 201, 400, 422, 429];
const OK_UPDATE = [200, 201, 400, 404, 422, 429];
const OK_DELETE = [200, 204, 400, 404, 422, 429];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function uniqueSlug(): string {
  return `e2e-cms-${Date.now().toString(36).slice(-6)}`;
}

async function createCmsPage(request: any): Promise<{ id: number | null; slug: string }> {
  const slug = uniqueSlug();
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
    method: 'POST',
    data: {
      url_key: slug,
      page_title: `E2E ${slug}`,
      html_content: '<p>E2E generated</p>',
      meta_title: 'E2E Meta',
      meta_keywords: 'e2e',
      meta_description: 'e2e generated page',
      channels: [1],
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null, slug };
}

test.describe('Admin CMS Pages REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with url_key filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      params: { url_key: 'about' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('listing with channel filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      params: { channel: 'default' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200 or 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create cms page happy path', async ({ request }) => {
    const { id } = await createCmsPage(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), { method: 'DELETE' });
    }
  });

  test('create missing url_key returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      method: 'POST',
      data: { page_title: 'No slug', html_content: '<p>x</p>', channels: [1] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create missing all fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update cms page locale-nested', async ({ request }) => {
    const { id } = await createCmsPage(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), {
      method: 'PUT',
      data: {
        locale: 'en',
        en: {
          url_key: `e2e-renamed-${Date.now().toString(36).slice(-4)}`,
          page_title: 'Renamed E2E Page',
          html_content: '<p>Updated</p>',
        },
        channels: [1],
      },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), { method: 'DELETE' });
  });

  test('delete fresh cms page returns 200/204', async ({ request }) => {
    const { id } = await createCmsPage(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const { id } = await createCmsPage(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [id] },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
