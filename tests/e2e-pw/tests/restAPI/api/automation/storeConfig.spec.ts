import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Store Config REST API', () => {
  test('Should return the public store settings of the current channel', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.STORE_CONFIG);

    expect(response.status()).toBe(200);

    const body = await response.json();

    expect(Array.isArray(body)).toBe(true);
    expect(body.length).toBe(1);

    const config = body[0];

    expect(typeof config.channel).toBe('string');
    expect(typeof config.guestCheckoutEnabled).toBe('boolean');
    expect(typeof config.minimumOrderEnabled).toBe('boolean');
    expect(typeof config.customerReviewEnabled).toBe('boolean');
    expect(typeof config.guestReviewEnabled).toBe('boolean');
    expect(Array.isArray(config.socialLoginProviders)).toBe(true);
    expect(['excluding_tax', 'including_tax', 'both']).toContain(config.taxCartDisplayPrices);
  });

  test('Should not contain any secret key', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.STORE_CONFIG);

    const text = await response.text();

    expect(text).not.toContain('client_secret');
    expect(text).not.toContain('clientSecret');
    expect(text).not.toContain('api_key');
  });
});
