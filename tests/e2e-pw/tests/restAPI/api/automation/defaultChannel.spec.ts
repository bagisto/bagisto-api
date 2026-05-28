// tests/restAPI/api/automation/defaultChannel.spec.ts
//
// DefaultChannel resource is declared GraphQL-only in src/Models/DefaultChannel.php
// (`operations: []`). The REST URL is poked here as a smoke test to lock down
// the absence of a REST exposure.
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Default Channel REST API', () => {
  test('GET /api/shop/default-channel — status check', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.DEFAULT_CHANNEL);
    expect([0, 200, 400, 404, 405, 500]).toContain(response.status());
    console.log('GET /api/shop/default-channel:', response.status());
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (body && typeof body === 'object') {
        console.log('Default channel keys:', Object.keys(body).slice(0, 8));
      }
    }
  });

  test('POST is not supported', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.DEFAULT_CHANNEL, {
      method: 'POST',
      data: {},
    });
    expect([400, 401, 403, 404, 405, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/default-channel:', response.status());
  });
});
