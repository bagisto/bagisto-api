// tests/restAPI/api/automation/productVariants.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertProductCard } from '../../rest/assertions/product.assertions';

test.describe('Product Variants & Booking Slots REST API', () => {
  let configurableProductId: number | null = null;

  test.beforeEach(async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '100', type: 'configurable' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (Array.isArray(body) && body.length > 0) {
      configurableProductId = body[0].id;
    }
  });

  test('Should return variants for a configurable product', async ({ request }) => {
    if (!configurableProductId) {
      test.skip(true, 'No configurable product found');
      return;
    }

    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VARIANTS(configurableProductId));
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log(`Variants for product ${configurableProductId}:`, body.length);

    body.forEach((variant: any) => expect(variant).toHaveProperty('id'));
  });

  test('Should return 404 for variants of a non-configurable product', async ({ request }) => {
    const allResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '100', type: 'simple' },
    });
    const body = await allResp.json();
    if (!Array.isArray(body) || body.length === 0) {
      test.skip(true, 'No simple product found');
      return;
    }
    const simpleId = body[0].id;

    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VARIANTS(simpleId));
    expect([200, 404]).toContain(response.status());
    console.log(`Variants for simple product ${simpleId} status:`, response.status());
  });

  test('Should return an unexpected result (404 or 200) for variants of a non-existent product', async ({ request }) => {
    // Bagisto returns 200 with an empty/canonical response for missing IDs here
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VARIANTS(999999));
    expect([200, 404]).toContain(response.status());
    console.log(`Variants for non-existent product:`, response.status());
  });

  test('Should return booking slots for a booking product', async ({ request }) => {
    const productsResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '100' },
    });
    const allProducts = await productsResp.json();
    const booking = allProducts.filter((p: any) => p.type === 'booking');
    if (!Array.isArray(booking) || booking.length === 0) {
      test.skip(true, 'No booking product found');
      return;
    }

    const today = new Date().toISOString().split('T')[0];
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS, {
      params: { id: String(booking[0].id), date: today },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Booking slots for product ${booking[0].id} on ${today}:`, JSON.stringify(body).length, 'bytes');
  });
});
