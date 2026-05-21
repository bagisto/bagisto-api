// tests/restAPI/api/automation/locales.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Locales REST API', () => {
  test('Should return all locales', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.LOCALES);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Locales count:', body.length);

    if (body.length > 0) {
      console.log('All locales:', body.map((l: any) => l.code || l.id).join(', '));
    }
  });

  test('Should return single locale by ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.LOCALES);
    const body = await response.json();

    if (body.length > 0) {
      const localeId = body[0].id;
      const singleResp = await sendRestRequest(request, ENDPOINTS.LOCALE(localeId));
      expect(singleResp.status()).toBe(200);
      const singleBody = await singleResp.json();
      expect(singleBody.id).toBe(localeId);
      expect(singleBody).toHaveProperty('code');
      expect(singleBody).toHaveProperty('name');
      expect(['ltr', 'rtl']).toContain(singleBody.direction);
      console.log('Single locale:', JSON.stringify({
        id: singleBody.id, code: singleBody.code, name: singleBody.name, dir: singleBody.direction,
      }, null, 2));
    }
  });

  test('Should return 404 for non-existent locale', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.LOCALE(999999));
    expect(response.status()).toBe(404);
    console.log('404 for non-existent locale');
  });
});
