import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';
import { UNSAFE_HTML, assertSanitisedHtml } from '../../../../rest/assertions/adminSanitisedHtml.assertions';

test.describe.configure({ timeout: 60_000 });

const NOT_FOUND = 'CMS page not found.';

function uniqueSlug(): string {
  return `e2e-cms-${Date.now().toString(36)}-${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

async function createCmsPage(request: APIRequestContext): Promise<{ id: number; slug: string }> {
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
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(typeof body.id).toBe('number');

  return { id: body.id, slug };
}

async function deleteCmsPage(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), { method: 'DELETE' });
}

test.describe('admin cms pages rest api', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(typeof body.meta.total).toBe('number');
  });

  test('listing with url_key filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      params: { url_key: 'about' },
    });
    expect(resp.status()).toBe(200);
  });

  test('listing with channel filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      params: { channel: 'default' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail of a created page returns its translations and channels', async ({ request }) => {
    const { id, slug } = await createCmsPage(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id));
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body).toEqual(expect.objectContaining({
        id,
        urlKey: slug,
        pageTitle: `E2E ${slug}`,
        metaTitle: 'E2E Meta',
      }));
      expect(body.channels.map((channel: any) => channel.id)).toEqual([1]);
    } finally {
      await deleteCmsPage(request, id);
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('create cms page happy path', async ({ request }) => {
    const slug = uniqueSlug();
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      method: 'POST',
      data: {
        url_key: slug,
        page_title: `E2E ${slug}`,
        html_content: '<p>E2E generated</p>',
        channels: [1],
      },
    });
    expect(resp.status()).toBe(201);
    const body = await resp.json();

    try {
      expect(body).toEqual(expect.objectContaining({
        urlKey: slug,
        pageTitle: `E2E ${slug}`,
        htmlContent: '<p>E2E generated</p>',
      }));

      const list = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, { params: { url_key: slug } });
      expect(list.status()).toBe(200);
      expect((await list.json()).data.map((row: any) => row.id)).toEqual([body.id]);
    } finally {
      await deleteCmsPage(request, body.id);
    }
  });

  test('create missing url_key returns 422', async ({ request }) => {
    const title = `No slug ${uniqueSlug()}`;
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      method: 'POST',
      data: { page_title: title, html_content: '<p>x</p>', channels: [1] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The url key field is required.');

    const list = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, { params: { page_title: title } });
    expect((await list.json()).data).toEqual([]);
  });

  test('create missing all fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The url key field is required.');
  });

  test('update cms page locale-nested', async ({ request }) => {
    const { id } = await createCmsPage(request);
    const renamed = `e2e-renamed-${uniqueSlug()}`;

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), {
        method: 'PUT',
        data: {
          locale: 'en',
          en: {
            url_key: renamed,
            page_title: 'Renamed E2E Page',
            html_content: '<p>Updated</p>',
          },
          channels: [1],
        },
      });
      expect(resp.status()).toBe(200);
      expect(await resp.json()).toEqual(expect.objectContaining({ id, urlKey: renamed, pageTitle: 'Renamed E2E Page' }));

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id));
      expect(detail.status()).toBe(200);
      expect(await detail.json()).toEqual(expect.objectContaining({
        urlKey: renamed,
        pageTitle: 'Renamed E2E Page',
        htmlContent: '<p>Updated</p>',
      }));
    } finally {
      await deleteCmsPage(request, id);
    }
  });

  test('delete fresh cms page returns 204', async ({ request }) => {
    const { id } = await createCmsPage(request);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(204);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id));
    expect(detail.status()).toBe(404);
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const { id } = await createCmsPage(request);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [id] },
    });
    expect(resp.status()).toBe(200);
    expect(await resp.json()).toEqual(expect.objectContaining({
      deleted: [id],
      message: 'CMS pages deleted successfully.',
    }));

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id));
    expect(detail.status()).toBe(404);
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The indices field is required and must be a non-empty array.');
  });

  test('should strip scripts and template code from page html on create and update', async ({ request }) => {
    const slug = `e2e-cms-xss-${Date.now()}-${Math.floor(Math.random() * 100000)}`;
    const createResp = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGES, {
      method: 'POST',
      data: {
        url_key: slug,
        page_title: 'E2E Sanitised Page',
        html_content: UNSAFE_HTML,
        channels: [1],
      },
    });
    expect(createResp.status()).toBe(201);
    const id = (await createResp.json()).id;
    expect(typeof id).toBe('number');

    try {
      const created = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id));
      expect(created.status()).toBe(200);
      assertSanitisedHtml((await created.json()).htmlContent);

      const upd = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id), {
        method: 'PUT',
        data: {
          locale: 'en',
          channels: [1],
          en: { url_key: slug, page_title: 'E2E Sanitised Page Updated', html_content: `${UNSAFE_HTML}<p>Updated</p>` },
        },
      });
      expect(upd.status()).toBe(200);

      const updated = await sendAdminRequest(request, ADMIN_SETTINGS.CMS_PAGE(id));
      expect(updated.status()).toBe(200);
      const updatedBody = await updated.json();
      assertSanitisedHtml(updatedBody.htmlContent);
      expect(updatedBody.htmlContent).toContain('<p>Updated</p>');
    } finally {
      await deleteCmsPage(request, id);
    }
  });
});
