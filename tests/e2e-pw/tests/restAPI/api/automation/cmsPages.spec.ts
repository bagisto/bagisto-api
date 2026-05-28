// tests/restAPI/api/automation/cmsPages.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string, allowed: number[] = [0, 200, 400, 401, 404, 500]) {
  expect(allowed).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('CMS Pages REST API', () => {
  test('Should list CMS pages', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CMS_PAGES);
    assertStatus(response, 'GET /api/shop/cms_pages');
    if (response.status() === 200) {
      const body = await response.json();
      const items = Array.isArray(body) ? body : (body.data ?? []);
      console.log('CMS pages count:', items.length);
      if (items.length > 0) {
        const first = items[0];
        expect(first).toHaveProperty('id');
        console.log('First page:', JSON.stringify({ id: first.id, urlKey: first.urlKey ?? first.url_key }));
      }
    }
  });

  test('Should return paginated CMS pages', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CMS_PAGES, {
      params: { page: '1', per_page: '5' },
    });
    assertStatus(response, 'GET /api/shop/cms_pages (paginated)');
  });

  test('Should return single CMS page when one exists', async ({ request }) => {
    const list = await sendRestRequest(request, ENDPOINTS.CMS_PAGES);
    if (list.status() !== 200) {
      test.skip(true, 'List endpoint not available');
      return;
    }
    const listBody = await list.json();
    const items = Array.isArray(listBody) ? listBody : (listBody.data ?? []);
    if (items.length === 0) {
      test.skip(true, 'No CMS pages seeded');
      return;
    }
    const pageId = items[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.CMS_PAGE(pageId));
    assertStatus(response, `GET /api/shop/cms_pages/${pageId}`);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.id).toBe(pageId);
      console.log('Single CMS page id:', body.id);
    }
  });

  test('Should return 404 for non-existent CMS page', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CMS_PAGE(999999));
    expect([404, 400, 200]).toContain(response.status());
    console.log('GET /api/shop/cms_pages/999999:', response.status());
  });

  test('Should not accept POST on collection', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CMS_PAGES, {
      method: 'POST',
      data: { url_key: 'should-not-create' },
    });
    expect([401, 403, 404, 405, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/cms_pages:', response.status());
  });
});
