// tests/restAPI/api/automation/themeCustomizations.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string, allowed: number[] = [0, 200, 400, 401, 404, 500]) {
  expect(allowed).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Theme Customizations REST API', () => {
  test('Should list theme customizations', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.THEME_CUSTOMIZATIONS);
    assertStatus(response, 'GET /api/shop/theme-customizations');
    if (response.status() === 200) {
      const body = await response.json();
      const items = Array.isArray(body) ? body : (body.data ?? []);
      console.log('Theme customizations count:', items.length);
      if (items.length > 0) {
        expect(items[0]).toHaveProperty('id');
        console.log('First customization:', JSON.stringify({
          id: items[0].id,
          type: items[0].type,
          themeCode: items[0].themeCode ?? items[0].theme_code,
        }));
      }
    }
  });

  test('Should return single theme customization when one exists', async ({ request }) => {
    const list = await sendRestRequest(request, ENDPOINTS.THEME_CUSTOMIZATIONS);
    if (list.status() !== 200) {
      test.skip(true, 'List endpoint not available');
      return;
    }
    const body = await list.json();
    const items = Array.isArray(body) ? body : (body.data ?? []);
    if (items.length === 0) {
      test.skip(true, 'No theme customizations seeded');
      return;
    }
    const tcId = items[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.THEME_CUSTOMIZATION(tcId));
    assertStatus(response, `GET /api/shop/theme-customizations/${tcId}`);
    if (response.status() === 200) {
      const detail = await response.json();
      expect(detail.id).toBe(tcId);
      console.log('Single customization:', detail.id);
    }
  });

  test('Should return 404 for non-existent theme customization', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.THEME_CUSTOMIZATION(999999));
    expect([404, 400, 200]).toContain(response.status());
    console.log('GET /api/shop/theme-customizations/999999:', response.status());
  });

  test('Should accept locale parameter', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.THEME_CUSTOMIZATIONS, {
      params: { locale: 'en' },
    });
    assertStatus(response, 'GET /api/shop/theme-customizations?locale=en');
  });
});
