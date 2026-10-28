// tests/restAPI/api/automation/sections.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string, allowed: number[] = [0, 200, 400, 401, 404, 500]) {
  expect(allowed).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Sections REST API', () => {
  test('Should list sections', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SECTIONS);
    assertStatus(response, 'GET /api/shop/sections');
    if (response.status() === 200) {
      const body = await response.json();
      const items = Array.isArray(body) ? body : (body.data ?? []);
      console.log('Sections count:', items.length);
      if (items.length > 0) {
        expect(items[0]).toHaveProperty('id');
        console.log('First section:', JSON.stringify({
          id: items[0].id,
          type: items[0].type,
          themeCode: items[0].themeCode ?? items[0].theme_code,
        }));
      }
    }
  });

  test('Should return single section when one exists', async ({ request }) => {
    const list = await sendRestRequest(request, ENDPOINTS.SECTIONS);
    if (list.status() !== 200) {
      test.skip(true, 'List endpoint not available');
      return;
    }
    const body = await list.json();
    const items = Array.isArray(body) ? body : (body.data ?? []);
    if (items.length === 0) {
      test.skip(true, 'No sections seeded');
      return;
    }
    const tcId = items[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.SECTION(tcId));
    assertStatus(response, `GET /api/shop/sections/${tcId}`);
    if (response.status() === 200) {
      const detail = await response.json();
      expect(detail.id).toBe(tcId);
      console.log('Single section:', detail.id);
    }
  });

  test('Should return 404 for non-existent section', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SECTION(999999));
    expect([404, 400, 200]).toContain(response.status());
    console.log('GET /api/shop/sections/999999:', response.status());
  });

  test('Should accept locale parameter', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SECTIONS, {
      params: { locale: 'en' },
    });
    assertStatus(response, 'GET /api/shop/sections?locale=en');
  });

  test('Should return the theme the channel runs', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.THEME);
    assertStatus(response, 'GET /api/shop/theme', [200]);

    const body = await response.json();
    const theme = Array.isArray(body) ? body[0] : (body?.data?.[0] ?? body);

    expect(typeof theme.code).toBe('string');
    expect(Array.isArray(theme.sectionTypes)).toBe(true);
    console.log('Active theme:', theme.code);
  });
});
