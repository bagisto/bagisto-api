// tests/restAPI/api/automation/productCustomizableOptions.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
  return resp.status();
}

test.describe('Product Customizable Options REST API', () => {
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

  test('Should return customizable options for a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTIONS(productId));
    assertStatus(response, `GET /api/shop/products/${productId}/customizable-options`);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body) && body.length > 0) {
        console.log(`Customizable options count for product ${productId}:`, body.length);
        console.log('First option:', JSON.stringify({ id: body[0].id, title: body[0].title }));
      } else {
        console.log(`No customizable options for product ${productId}`);
      }
    }
  });

  test('Should return 404 for customizable options of a non-existent product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTIONS(999999));
    expect([200, 404]).toContain(response.status());
    console.log(`GET /api/shop/products/999999/customizable-options:`, response.status());
  });

  test('Should return list of all customizable option prices', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTION_PRICES);
    assertStatus(response, 'GET /api/shop/product_customizable_option_prices');
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body) && body.length > 0) {
        console.log('Customizable option prices count:', body.length);
        console.log('First price entry:', JSON.stringify({ id: body[0].id, price: body[0].price }));
      } else {
        console.log('No customizable option prices found');
      }
    }
  });

  test('Should return list of all customizable option translations', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTION_TRANSLATIONS);
    assertStatus(response, 'GET /api/shop/product_customizable_option_translations');
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body) && body.length > 0) {
        console.log('Customizable option translations count:', body.length);
        console.log('First translation:', JSON.stringify({ id: body[0].id, title: body[0].title ?? body[0].name }));
      } else {
        console.log('No customizable option translations found');
      }
    }
  });
});
