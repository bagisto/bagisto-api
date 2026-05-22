// tests/restAPI/api/automation/category.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  assertCategoriesResponse,
  assertCategoryFields,
} from '../../rest/assertions/category.assertions';

test.describe('Categories REST API', () => {
  test('Should return category list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORIES);

    expect(response.status()).toBe(200);
    const body = await response.json();
    assertCategoriesResponse(body);
    console.log('Total categories:', body.length);

    if (body.length > 0) {
      const category = body[0];
      assertCategoryFields(category);
      console.log('First category:', {
        id: category.id,
        name: category.translation?.name,
        slug: category.translation?.slug,
      });
    }
  });

  test('Should return pagination headers for categories', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORIES, {
      params: { page: '1', per_page: '10' },
    });

    expect(response.status()).toBe(200);
    const headers = response.headers();
    expect(headers).toHaveProperty('x-total-count');
    console.log('Category pagination:', {
      total: headers['x-total-count'],
      page: headers['x-page'],
      per_page: headers['x-per-page'],
    });
  });

  test('Should return single category by ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORIES);
    const body = await response.json();

    if (body.length > 0) {
      const firstId = body[0].id;
      const singleResponse = await sendRestRequest(request, ENDPOINTS.CATEGORY(firstId));
      expect(singleResponse.status()).toBe(200);
      const singleBody = await singleResponse.json();
      expect(singleBody.id).toBe(firstId);
      assertCategoryFields(singleBody);
      console.log('Single category:', singleBody.translation?.name);
    }
  });

  test('Should return category tree from root', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY_TREE, {
      params: { parentId: '1', depth: '2' },
    });

    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Category tree nodes:', body.length);

    if (body.length > 0) {
      expect(body[0]).toHaveProperty('id');
      console.log('Root tree category:', {
        id: body[0].id,
        name: body[0].translation?.name,
        childrenCount: body[0].children?.length ?? 0,
      });
    }
  });

  test('Should return 404 for non-existent category ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CATEGORY(999999));
    expect(response.status()).toBe(404);
    console.log('404 received for non-existent category: 999999');
  });
});