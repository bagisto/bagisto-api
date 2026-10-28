import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('default channel rest api', () => {
  test('get is not exposed over rest', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.DEFAULT_CHANNEL);

    expect(response.status()).toBe(404);
  });

  test('post is not allowed', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.DEFAULT_CHANNEL, {
      method: 'POST',
      data: {},
    });

    expect(response.status()).toBe(405);
  });
});
