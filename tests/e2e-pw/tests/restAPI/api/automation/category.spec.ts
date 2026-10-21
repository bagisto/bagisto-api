import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { REST_GAPS_MESSAGES, numericIdOf } from '../../rest/helpers/coverage/restGaps.helpers';
import {
  assertCategoriesResponse,
  assertCategoryFields,
} from '../../rest/assertions/category.assertions';

const ROOT_CATEGORY_ID = 1;

test.describe('categories rest api', () => {
  test('should return category list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORIES);
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    assertCategoriesResponse(body);
    expect(body.length).toBeGreaterThan(0);
    expect(body.length).toBeLessThanOrEqual(10);

    for (const category of body) {
      assertCategoryFields(category);
    }
  });

  test('should return pagination headers for categories', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORIES, {
      params: { page: '1', per_page: '2' },
    });
    const body = await response.json();
    const headers = response.headers();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.length).toBeLessThanOrEqual(2);
    expect(headers['x-page']).toBe('1');
    expect(headers['x-per-page']).toBe('2');
    expect(Number(headers['x-total-count'])).toBeGreaterThanOrEqual(body.length);
    expect(Number(headers['x-total-pages'])).toBe(Math.max(1, Math.ceil(Number(headers['x-total-count']) / 2)));
  });

  test('should return single category by id', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY(ROOT_CATEGORY_ID));
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(numericIdOf(body.id)).toBe(ROOT_CATEGORY_ID);
    assertCategoryFields(body);
  });

  test('should return category tree from root', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY_TREE, {
      params: { parentId: String(ROOT_CATEGORY_ID), depth: '2' },
    });

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBe(true);

    for (const node of body) {
      expect(numericIdOf(node.id)).toBeGreaterThan(0);
    }
  });

  test('should return 404 for non-existent category id', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY(999999));

    expect(response.status()).toBe(404);
    expect((await response.json()).detail).toBe(REST_GAPS_MESSAGES.GENERIC_NOT_FOUND);
  });
});
