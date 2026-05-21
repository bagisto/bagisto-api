// tests/restAPI/api/automation/attribute.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  assertAttributesResponse,
  assertAttributeFields,
} from '../../rest/assertions/attribute.assertions';

test.describe('Attributes REST API', () => {
  test('Should return attributes list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTES);

    expect(response.status()).toBe(200);
    const body = await response.json();
    assertAttributesResponse(body);
    console.log('Total attributes:', body.length);
    
    if (body.length > 0) {
      console.log('First attribute:', JSON.stringify({
        id: body[0].id,
        code: body[0].code,
        type: body[0].type,
        adminName: body[0].adminName,
      }, null, 2));
    }
  });

  test('Should return attributes with translation', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTES);

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();

    if (body.length > 0) {
      const attribute = body[0];
      expect(attribute).toHaveProperty('id');
      expect(attribute).toHaveProperty('code');
      expect(attribute).toHaveProperty('type');
      expect(attribute).toHaveProperty('translation');
      console.log('Attribute has translation:', attribute.translation?.name);
    }
  });

  test('Should return single attribute by ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTES);
    const body = await response.json();
    
    if (body.length > 0) {
      const firstId = body[0].id;
      const singleResponse = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE(firstId));
      expect(singleResponse.status()).toBe(200);
      const singleBody = await singleResponse.json();
      expect(singleBody.id).toBe(firstId);
      console.log('Single attribute by ID:', singleBody.code);
    }
  });
});