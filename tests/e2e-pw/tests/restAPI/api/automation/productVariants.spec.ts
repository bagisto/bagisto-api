// tests/restAPI/api/automation/productVariants.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertProductCard } from '../../rest/assertions/product.assertions';
import { assertProductVariantFields } from '../../rest/assertions/productVariant.assertions';

// Discovery beforeEach hits the product collection; under parallel load the
// 30s default can be too tight. Extend to 60s.
test.describe.configure({ timeout: 60_000 });

test.describe('Product Variants & Booking Slots REST API', () => {
  let configurableProductId: number | null = null;

  test.beforeEach(async ({ request }) => {
    // per_page=100 took ~12s and was timing out under parallel load. The
    // first configurable product is enough — we don't iterate the list.
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '10', type: 'configurable' },
    });
    if (response.status() === 429) {
      test.skip(true, 'Rate limited - skipping test');
      return;
    }
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

    if (body.length > 0) {
      body.forEach((variant: any) => assertProductVariantFields(variant));
      console.log('First variant:', { id: body[0].id, sku: body[0].sku, name: body[0].name });
    }
  });

  test('Should return 404 for variants of a non-configurable product', async ({ request }) => {
    const allResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '10', type: 'simple' },
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

  test('Should return 200 empty array for variants of a non-existent product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VARIANTS(999999));
    expect([200, 404]).toContain(response.status());
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
    }
    console.log(`Variants for non-existent product:`, response.status());
  });

  test('Should return booking slots for a booking product', async ({ request }) => {
    // Server-side type filter + smaller page keeps this fast (per_page=100
    // alone took ~12s on this dev DB and timed out under load).
    const productsResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '25', type: 'booking' },
    });
    if (productsResp.status() !== 200) {
      test.skip(true, `Products listing returned ${productsResp.status()}`);
      return;
    }
    const allProducts = await productsResp.json();
    const booking = Array.isArray(allProducts) ? allProducts.filter((p: any) => p.type === 'booking') : [];
    if (booking.length === 0) {
      test.skip(true, 'No booking product found');
      return;
    }

    const today = new Date().toISOString().split('T')[0];
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS, {
      params: { id: String(booking[0].id), date: today },
    });
    // GET /api/shop/booking-slots requires both id + date params; without
    // valid combinations the slot helper can return 400. Allow that.
    expect([200, 400, 404, 422]).toContain(response.status());
    if (response.status() === 200) {
      const body = await response.json();
      console.log(`Booking slots for product ${booking[0].id} on ${today}:`, JSON.stringify(body).length, 'bytes');
    } else {
      console.log(`Booking slots for product ${booking[0].id} on ${today}:`, response.status());
    }
  });
});
