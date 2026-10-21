import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { REST_GAPS_MESSAGES, numericIdOf } from '../../rest/helpers/coverage/restGaps.helpers';

test.describe('attribute translations rest api', () => {
  test('should return attribute translations list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATIONS);
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body.length).toBeGreaterThan(0);
    expect(body.length).toBeLessThanOrEqual(10);

    for (const translation of body) {
      expect(numericIdOf(translation.id)).toBeGreaterThan(0);
      expect(Number(translation.attributeId)).toBeGreaterThan(0);
      expect(typeof translation.locale).toBe('string');
      expect(translation).toHaveProperty('name');
    }
  });

  test('should return single attribute translation by id', async ({ request }) => {
    const listResponse = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATIONS);
    const listBody = await listResponse.json();

    expect(listResponse.status(), JSON.stringify(listBody)).toBe(200);
    expect(listBody.length).toBeGreaterThan(0);

    const expected = listBody[0];
    const singleResponse = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATION(numericIdOf(expected.id)));

    expect(singleResponse.status()).toBe(200);
    expect(await singleResponse.json()).toEqual(expected);
  });

  test('should return 404 for non-existent attribute translation', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ATTRIBUTE_TRANSLATION(999999999));

    expect(response.status()).toBe(404);
    expect((await response.json()).detail).toBe(REST_GAPS_MESSAGES.GENERIC_NOT_FOUND);
  });
});
