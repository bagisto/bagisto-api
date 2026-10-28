import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { SHOP_CMS } from '../../../rest/endpoints/coverage/shopCms.endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 90_000 });

const MISSING_KEY_MESSAGE = 'X-STOREFRONT-KEY header is required';

const UNKNOWN_ID = 999999999;

interface CreatedPage {
  id: number;
  urlKey: string;
  pageTitle: string;
  htmlContent: string;
  metaTitle: string;
  metaKeywords: string;
  metaDescription: string;
}

async function safeJson(response: APIResponse): Promise<any> {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

function uniqueSuffix(): string {
  return `${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`;
}

function numericIdOf(value: any): number {
  if (typeof value === 'number') {
    return value;
  }

  return Number(String(value ?? '').split('/').pop());
}

async function createCmsPage(request: APIRequestContext): Promise<CreatedPage> {
  const suffix = uniqueSuffix();
  const page = {
    urlKey: `e2e-shop-cms-${suffix}`,
    pageTitle: `E2E Shop Cms ${suffix}`,
    htmlContent: `<p>e2e shop cms body ${suffix}</p>`,
    metaTitle: `e2e meta ${suffix}`,
    metaKeywords: `e2e,${suffix}`,
    metaDescription: `e2e description ${suffix}`,
  };

  const response = await sendAdminRequest(request, SHOP_CMS.ADMIN_CMS_PAGES, {
    method: 'POST',
    data: {
      url_key: page.urlKey,
      page_title: page.pageTitle,
      html_content: page.htmlContent,
      meta_title: page.metaTitle,
      meta_keywords: page.metaKeywords,
      meta_description: page.metaDescription,
      channels: [1],
    },
  });
  expect(response.status()).toBe(201);

  const body = await safeJson(response);
  expect(typeof body?.id).toBe('number');

  return { id: body.id, ...page };
}

async function deleteCmsPage(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, SHOP_CMS.ADMIN_CMS_PAGE(id), { method: 'DELETE' });
}

async function getWithoutStorefrontKey(request: APIRequestContext, endpoint: string): Promise<APIResponse> {
  return request.get(`${env.baseUrl}${endpoint}`, {
    headers: { Accept: 'application/json' },
  });
}

async function allPageTranslations(request: APIRequestContext): Promise<any[]> {
  const rows: any[] = [];

  for (let page = 1; page <= 200; page++) {
    const response = await sendRestRequest(request, SHOP_CMS.PAGE_TRANSLATIONS, {
      params: { page: String(page), per_page: '50' },
    });
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(Array.isArray(body)).toBe(true);
    rows.push(...body);

    if (body.length < 50) {
      break;
    }
  }

  return rows;
}

function expectTranslationOfPage(translation: any, page: CreatedPage): void {
  expect(translation.pageTitle).toBe(page.pageTitle);
  expect(translation.urlKey).toBe(page.urlKey);
  expect(translation.htmlContent).toBe(page.htmlContent);
  expect(translation.metaTitle).toBe(page.metaTitle);
  expect(translation.metaKeywords).toBe(page.metaKeywords);
  expect(translation.metaDescription).toBe(page.metaDescription);
  expect(typeof translation.locale).toBe('string');
}

test.describe('shop cms pages rest api', () => {
  let page: CreatedPage | null = null;

  test.beforeEach(async ({ request }) => {
    page = null;
    page = await createCmsPage(request);
  });

  test.afterEach(async ({ request }) => {
    if (page) {
      await deleteCmsPage(request, page.id);
    }
  });

  test('should list a newly created page with its current locale translation', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CMS.PAGES, {
      params: { url_key: page!.urlKey },
    });
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);

    const row = body[0];
    expect(row.id).toBe(`/api/shop/pages/${page!.id}`);
    expect(row._id).toBe(page!.id);
    expect(row.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    expect(row.updatedAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    expect(row.translation.id).toMatch(/^\/api\/shop\/page_translations\/\d+$/);
    expect(row.translation._id).toBe(numericIdOf(row.translation.id));
    expect(row.translation.cmsPageId).toBe(String(page!.id));
    expectTranslationOfPage(row.translation, page!);
  });

  test('should cap the page listing at the requested items per page', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CMS.PAGES, {
      params: { itemsPerPage: '1' },
    });
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(`/api/shop/pages/${body[0]._id}`);
  });

  test('should return an empty listing for a url key no page uses', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CMS.PAGES, {
      params: { url_key: `missing-${uniqueSuffix()}` },
    });
    expect(response.status()).toBe(200);
    expect(await safeJson(response)).toEqual([]);
  });

  test('should return a single page by id with its translation', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CMS.PAGE(page!.id));
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(body.id).toBe(`/api/shop/pages/${page!.id}`);
    expect(body._id).toBe(page!.id);
    expect(body).toHaveProperty('layout');
    expect(body.translation.cmsPageId).toBe(String(page!.id));
    expectTranslationOfPage(body.translation, page!);
  });

  test('should return 404 for a page that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CMS.PAGE(UNKNOWN_ID));
    expect(response.status()).toBe(404);

    const body = await safeJson(response);
    expect(body?._id).toBeUndefined();
    expect(body?.translation).toBeUndefined();
  });

  test('should return 404 for a page after it is deleted', async ({ request }) => {
    await deleteCmsPage(request, page!.id);
    const deletedId = page!.id;
    page = null;

    const response = await sendRestRequest(request, SHOP_CMS.PAGE(deletedId));
    expect(response.status()).toBe(404);
  });

  test('should list every locale translation of a newly created page', async ({ request }) => {
    const rows = (await allPageTranslations(request)).filter((row: any) => Number(row.cmsPageId) === page!.id);

    expect(rows.length).toBeGreaterThan(0);

    for (const row of rows) {
      expectTranslationOfPage(row, page!);
    }

    expect(new Set(rows.map((row: any) => row.locale)).size).toBe(rows.length);
  });

  test('should cap the page translation listing at the requested page size', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CMS.PAGE_TRANSLATIONS, {
      params: { per_page: '2' },
    });
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(2);
  });

  test('should return a single page translation by id', async ({ request }) => {
    const pageResponse = await sendRestRequest(request, SHOP_CMS.PAGE(page!.id));
    expect(pageResponse.status()).toBe(200);
    const translationId = (await safeJson(pageResponse)).translation._id;

    const response = await sendRestRequest(request, SHOP_CMS.PAGE_TRANSLATION(translationId));
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(numericIdOf(body.id)).toBe(translationId);
    expect(Number(body.cmsPageId)).toBe(page!.id);
    expectTranslationOfPage(body, page!);
  });

  test('should return 404 for a page translation that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CMS.PAGE_TRANSLATION(UNKNOWN_ID));
    expect(response.status()).toBe(404);

    const body = await safeJson(response);
    expect(body?.pageTitle).toBeUndefined();
    expect(body?.urlKey).toBeUndefined();
  });
});

test.describe('shop cms pages rest api without a storefront key', () => {
  const endpoints = [
    { label: 'the page listing', endpoint: SHOP_CMS.PAGES },
    { label: 'a single page', endpoint: SHOP_CMS.PAGE(1) },
    { label: 'the page translation listing', endpoint: SHOP_CMS.PAGE_TRANSLATIONS },
    { label: 'a single page translation', endpoint: SHOP_CMS.PAGE_TRANSLATION(1) },
  ];

  for (const { label, endpoint } of endpoints) {
    test(`should refuse ${label} without a storefront key`, async ({ request }) => {
      const response = await getWithoutStorefrontKey(request, endpoint);
      expect(response.status()).toBe(401);

      const body = await safeJson(response);
      expect(body.message).toBe(MISSING_KEY_MESSAGE);
      expect(body.error).toBe('missing_key');
    });
  }
});
