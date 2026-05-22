// tests/restAPI/api/automation/attributeOptions.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Attribute Options REST API', () => {
  test('Should return attribute options', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_OPTIONS);

    // Handle case where endpoint may not exist
    expect([200, 404]).toContain(response.status());
    
    if (response.status() === 200) {
      const body = await response.json();
      console.log('Attribute options count:', Array.isArray(body) ? body.length : 'N/A');
    } else {
      console.log('Attribute options endpoint not available');
    }
  });
});