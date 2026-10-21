import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { numericIdOf } from '../../rest/helpers/coverage/restGaps.helpers';

test.describe('attribute options rest api', () => {
  test('should return attribute options', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_OPTIONS);
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body.length).toBeGreaterThan(0);
    expect(body.length).toBeLessThanOrEqual(10);

    for (const option of body) {
      expect(numericIdOf(option.id)).toBeGreaterThan(0);
      expect(option).toHaveProperty('adminName');
      expect(Array.isArray(option.translations)).toBe(true);
    }

    const single = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_OPTION(numericIdOf(body[0].id)));

    expect(single.status()).toBe(200);
    expect(await single.json()).toEqual(body[0]);
  });
});
