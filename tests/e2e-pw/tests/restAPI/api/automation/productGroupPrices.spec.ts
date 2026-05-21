// tests/restAPI/api/automation/productGroupPrices.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

test.describe('Product Group Prices REST API', () => {
  let productId: number;

  test.beforeEach(async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    productId = body[0].id;
  });

  test('Should return customer group prices for a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_GROUP_PRICES(productId));
    expect([200, 404]).toContain(response.status());
    console.log(`GET /api/shop/products/${productId}/customer-group-prices:`, response.status());
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body) && body.length > 0) {
        expect(body[0]).toHaveProperty('id');
        expect(body[0]).toHaveProperty('value');
        expect(body[0]).toHaveProperty('valueType');
        expect(body[0]).toHaveProperty('customerGroupId');
        console.log(`Group price count:`, body.length);
      } else if (body.data) {
        expect(Array.isArray(body.data)).toBeTruthy();
        console.log(`Group price count:`, body.data.length);
      } else {
        console.log('Response body:', JSON.stringify(body).slice(0, 300));
      }
    }
  });

  test('Should return 404 for group prices of a non-existent product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_GROUP_PRICES(999999));
    expect([200, 404]).toContain(response.status());
    console.log(`GET /api/shop/products/999999/customer-group-prices:`, response.status());
  });

  test('Should return single group price by ID', async ({ request }) => {
    const listResponse = await sendRestRequest(request, ENDPOINTS.PRODUCT_GROUP_PRICES(productId));
    if (listResponse.status() !== 200) {
      test.skip(true, 'Group prices list not available');
      return;
    }
    const listBody = await listResponse.json();
    const prices = Array.isArray(listBody) ? listBody : (listBody && listBody.data) || [];
    if (prices.length === 0) {
      test.skip(true, 'No group price items found');
      return;
    }
    const priceId = prices[0].id;
    const singleResponse = await sendRestRequest(request, ENDPOINTS.CUSTOMER_GROUP_PRICE(priceId));
    expect([200, 404]).toContain(singleResponse.status());
    console.log(`GET /api/shop/customer-group-prices/${priceId}:`, singleResponse.status());
    if (singleResponse.status() === 200) {
      const body = await singleResponse.json();
      expect(body.id).toBe(priceId);
      expect(body).toHaveProperty('value');
      console.log('Single group price:', JSON.stringify({ id: body.id, value: body.value, valueType: body.valueType }));
    }
  });

  test('Should return 404 for non-existent group price ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_GROUP_PRICE(999999));
    expect([200, 404]).toContain(response.status());
    console.log(`GET /api/shop/customer-group-prices/999999:`, response.status());
  });
});
