import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertProductVariantFields } from '../../rest/assertions/productVariant.assertions';
import {
  UNKNOWN_ID,
  createAdminProduct,
  deleteAdminProducts,
  safeJson,
  shopBookingProductId,
  updateAdminProduct,
} from '../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

function dateOffset(days: number): string {
  return new Date(Date.now() + days * 86_400_000).toISOString().slice(0, 10);
}

test.describe('product variants rest api', () => {
  test('should return variants for a configurable product', async ({ request }) => {
    const listResponse = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '10', type: 'configurable' },
    });
    const listBody = await safeJson(listResponse);

    expect(listResponse.status(), JSON.stringify(listBody)).toBe(200);

    const configurable = Array.isArray(listBody) ? listBody.find((row: any) => row.type === 'configurable') : undefined;

    test.skip(!configurable, 'the catalog has no configurable product');

    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VARIANTS(configurable.id));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    body.forEach((variant: any) => assertProductVariantFields(variant));
  });

  test('should return an empty variant list for the variants of a missing product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VARIANTS(UNKNOWN_ID));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });
});

test.describe('product variants of a simple product rest api', () => {
  let createdIds: number[];
  let productId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should return an empty variant list for a simple product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_VARIANTS(productId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });
});

test.describe('booking slots of a booking product rest api', () => {
  let createdIds: number[];
  let bookingProductId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];

    const productId = (await createAdminProduct(request, 'booking')).id;
    createdIds.push(productId);

    const updated = await updateAdminProduct(request, productId, {
      booking: {
        type: 'appointment',
        qty: '1',
        location: 'E2E Clinic',
        available_every_week: '1',
        duration: '45',
        break_time: '15',
        same_slot_all_days: '1',
        slots: [{ from: '10:00', to: '12:00' }],
      },
    });

    expect(updated.bookingProduct?.type).toBe('appointment');

    bookingProductId = await shopBookingProductId(request, productId);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should return the booking slots of a booking product for a date', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS, {
      params: { id: String(bookingProductId), date: dateOffset(7) },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

    for (const slot of body) {
      expect(typeof slot.from).toBe('string');
      expect(typeof slot.to).toBe('string');
      expect(typeof slot.timestamp).toBe('string');
    }
  });
});
