import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

const ROOT_CATEGORY_TRANSLATION_ID = 1;

test.describe('category translations rest api', () => {
  test('should not expose a category translations collection', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY_TRANSLATIONS);

    expect(response.status()).toBe(404);
  });

  test('should not expose a category translation by id', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY_TRANSLATION(ROOT_CATEGORY_TRANSLATION_ID));

    expect(response.status()).toBe(404);
  });

  test('should return 404 for non-existent category translation', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY_TRANSLATION(999999999));

    expect(response.status()).toBe(404);
  });
});
