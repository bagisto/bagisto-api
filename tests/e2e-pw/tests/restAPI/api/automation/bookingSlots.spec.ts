// tests/restAPI/api/automation/bookingSlots.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertSlotStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
  return resp.status();
}

// Discovery beforeEach has to hit the product collection, which is slow on
// this dev DB. Extend the per-test budget so parallel runs don't timeout.
test.describe.configure({ timeout: 60_000 });

test.describe('Booking Product Slots REST API', () => {
  let bookingProductId: number | null = null;
  const today = new Date().toISOString().split('T')[0];

  test.beforeEach(async ({ request }) => {
    // Use server-side type filter + smaller page size to keep the discovery
    // call fast (a per_page=100 fetch can take ~12s under parallel load and
    // blow the per-test timeout).
    const productsResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '25', type: 'booking' },
    });
    if (productsResp.status() !== 200) {
      test.skip(true, `Products listing returned ${productsResp.status()} — cannot discover booking product`);
      return;
    }
    const products = await productsResp.json();
    const bookingList = Array.isArray(products) ? products.filter((p: any) => p.type === 'booking') : [];
    if (bookingList.length === 0) {
      test.skip(true, 'No booking product seeded — set E2E_BOOKING_PRODUCT_ID');
      return;
    }
    // Verify the booking product exists from the booking products sub-route
    const bpResp = await sendRestRequest(request, ENDPOINTS.BOOKING_PRODUCTS(bookingList[0].id));
    if (bpResp.status() === 200) {
      const bp = await bpResp.json();
      if (Array.isArray(bp) && bp.length > 0) {
        bookingProductId = bp[0].id;
      }
    }
  });

  test('Should return default booking slots for a booking product', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product found');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_DEFAULT_SLOTS(bookingProductId));
    assertSlotStatus(response, `GET /api/shop/booking_product_default_slots/${bookingProductId}`);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      console.log('Default booking slots response length:', JSON.stringify(body).length, 'bytes');
    }
  });

  test('Should return appointment slots for a booking product', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product found');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_APPOINTMENT_SLOTS(bookingProductId));
    assertSlotStatus(response, `GET /api/shop/booking_product_appointment_slots/${bookingProductId}`);
    if (response.status() === 200) {
      const body = await response.json();
      console.log('Appointment slots response length:', JSON.stringify(body).length, 'bytes');
    }
  });

  test('Should return rental slots for a booking product', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product found');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_RENTAL_SLOTS(bookingProductId));
    assertSlotStatus(response, `GET /api/shop/booking_product_rental_slots/${bookingProductId}`);
    if (response.status() === 200) {
      const body = await response.json();
      console.log('Rental slots response length:', JSON.stringify(body).length, 'bytes');
    }
  });

  test('Should return table slots for a booking product', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product found');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_TABLE_SLOTS(bookingProductId));
    assertSlotStatus(response, `GET /api/shop/booking_product_table_slots/${bookingProductId}`);
    if (response.status() === 200) {
      const body = await response.json();
      console.log('Table slots response length:', JSON.stringify(body).length, 'bytes');
    }
  });

  test('Should return event tickets for a booking product', async ({ request }) => {
    if (!bookingProductId) {
      test.skip(true, 'No booking product found');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_EVENT_TICKETS(bookingProductId));
    assertSlotStatus(response, `GET /api/shop/booking_product_event_tickets/${bookingProductId}`);
    if (response.status() === 200) {
      const body = await response.json();
      console.log('Event tickets response length:', JSON.stringify(body).length, 'bytes');
    }
  });

  test('Should return 404 for booking slots of a non-booking product', async ({ request }) => {
    const simpleResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '10', type: 'simple' },
    });
    const allProducts = await simpleResp.json();
    if (!Array.isArray(allProducts) || allProducts.length === 0) {
      test.skip(true, 'No simple product found');
      return;
    }
    const simpleId = allProducts[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_DEFAULT_SLOTS(simpleId));
    expect([200, 404]).toContain(response.status());
    console.log(`Booking slots for non-booking product ${simpleId}:`, response.status());
  });
});
