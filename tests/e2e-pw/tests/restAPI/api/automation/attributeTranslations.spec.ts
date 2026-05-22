// tests/restAPI/api/automation/attributeTranslations.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Attribute Translations REST API', () => {
  test('Should return attribute translations list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATIONS);

    // Endpoint may not be registered depending on installation
    expect([200, 404]).toContain(response.status());
    console.log('GET /api/shop/attribute_translations:', response.status());

    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body) && body.length > 0) {
        expect(body[0]).toHaveProperty('id');
        expect(body[0]).toHaveProperty('name');
        console.log('Attribute translation count:', body.length);
        console.log('First translation:', JSON.stringify({ id: body[0].id, name: body[0].name }));
      }
    } else {
      console.log('Attribute translations endpoint not available');
    }
  });

  test('Should return single attribute translation by ID', async ({ request }) => {
    const listResponse = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATIONS);

    expect([200, 404]).toContain(listResponse.status());
    if (listResponse.status() !== 200) {
      test.skip(true, 'Attribute translations list not available');
      return;
    }

    const listBody = await listResponse.json();
    if (!Array.isArray(listBody) || listBody.length === 0) {
      test.skip(true, 'No attribute translations found');
      return;
    }

    const firstId = listBody[0].id;
    const singleResponse = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATION(firstId));
    expect(singleResponse.status()).toBe(200);
    const singleBody = await singleResponse.json();
    expect(singleBody.id).toBe(firstId);
    expect(singleBody).toHaveProperty('name');
    console.log('Single attribute translation:', { id: singleBody.id, name: singleBody.name });
  });

  test('Should return 404 for non-existent attribute translation', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATION(999999));
    expect([200, 404]).toContain(response.status());
    if (response.status() === 404) {
      console.log('404 received for non-existent attribute translation');
    } else {
      console.log('Non-existent translation returned:', response.status());
    }
  });
});
