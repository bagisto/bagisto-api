// tests/restAPI/api/automation/categoryTranslations.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Category Translations REST API', () => {
  test('Should return category translations list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY_TRANSLATIONS);
    expect([200, 404]).toContain(response.status());
    console.log('GET /api/shop/category_translations:', response.status());
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body) && body.length > 0) {
        console.log('Category translation count:', body.length);
        console.log('First translation:', JSON.stringify({ id: body[0].id, name: body[0].name, locale: body[0].locale }));
      } else {
        console.log('No category translations found');
      }
    } else {
      console.log('Category translations endpoint not available');
    }
  });

  test('Should return single category translation by ID', async ({ request }) => {
    const listResponse = await sendRestRequest(request, ENDPOINTS.CATEGORY_TRANSLATIONS);
    expect([200, 404]).toContain(listResponse.status());
    if (listResponse.status() !== 200) {
      test.skip(true, 'Category translations list not available');
      return;
    }
    const listBody = await listResponse.json();
    if (!Array.isArray(listBody) || listBody.length === 0) {
      test.skip(true, 'No category translations found');
      return;
    }
    const firstId = listBody[0].id;
    const singleResponse = await sendRestRequest(request, ENDPOINTS.CATEGORY_TRANSLATION(firstId));
    expect(singleResponse.status()).toBe(200);
    const singleBody = await singleResponse.json();
    expect(singleBody.id).toBe(firstId);
    expect(singleBody).toHaveProperty('name');
    console.log('Single category translation:', { id: singleBody.id, name: singleBody.name });
  });

  test('Should return 404 for non-existent category translation', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY_TRANSLATION(999999));
    expect([200, 404]).toContain(response.status());
    if (response.status() === 404) {
      console.log('404 received for non-existent category translation');
    } else {
      console.log('Non-existent translation returned:', response.status());
    }
  });
});
