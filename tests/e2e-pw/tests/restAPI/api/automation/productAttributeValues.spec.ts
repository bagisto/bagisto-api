// tests/restAPI/api/automation/productAttributeValues.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Product Attribute Values REST API', () => {
  let productId: number;

  test.beforeEach(async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '5' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (!Array.isArray(body) || body.length === 0) {
      test.skip(true, 'No products found');
      return;
    }
    productId = body[0].id;
  });

  test('Should return attribute values for a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_ATTRIBUTE_VALUES(productId));
    expect([200, 404]).toContain(response.status());
    console.log(`GET /api/shop/products/${productId}/attribute-values:`, response.status());
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body) && body.length > 0) {
        console.log(`Attribute values count for product ${productId}:`, body.length);
        console.log('First attribute:', JSON.stringify({ id: body[0].id, code: body[0].code, value: body[0].value }));
      } else {
        console.log(`No attribute values found for product ${productId}`);
      }
    }
  });

  test('Should return attribute values for a non-existent product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_ATTRIBUTE_VALUES(999999));
    expect([200, 404]).toContain(response.status());
    console.log(`GET /api/shop/products/999999/attribute-values:`, response.status());
  });
});
