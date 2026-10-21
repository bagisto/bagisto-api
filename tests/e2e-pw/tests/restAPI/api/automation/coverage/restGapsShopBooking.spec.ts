import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  REST_GAPS,
  collectPages,
  createAdminProduct,
  deleteAdminProducts,
  expectMissingStorefrontKey,
  getWithoutStorefrontKey,
  safeJson,
  shopBookingProductId,
  uniqueStamp,
  updateAdminProduct,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 180_000 });

function dateOffset(days: number): string {
  const date = new Date(Date.now() + days * 86_400_000);

  return date.toISOString().slice(0, 10);
}

interface BookingCase {
  type: string;
  endpoint: string;
  booking: () => Record<string, any>;
  expectRow: (row: any) => void;
}

const BOOKING_CASES: BookingCase[] = [
  {
    type: 'default',
    endpoint: REST_GAPS.BOOKING_DEFAULT_SLOTS,
    booking: () => ({
      type: 'default',
      qty: '1',
      location: 'E2E Studio',
      available_every_week: '1',
      booking_type: 'many',
      duration: '60',
      break_time: '10',
      slots: [{ from: '09:00', to: '17:00' }],
    }),
    expectRow: (row: any) => {
      expect(row.bookingType).toBe('many');
      expect(Number(row.duration)).toBe(60);
      expect(Number(row.breakTime)).toBe(10);
    },
  },
  {
    type: 'appointment',
    endpoint: REST_GAPS.BOOKING_APPOINTMENT_SLOTS,
    booking: () => ({
      type: 'appointment',
      qty: '1',
      location: 'E2E Clinic',
      available_every_week: '1',
      duration: '45',
      break_time: '15',
      same_slot_all_days: '1',
      slots: [{ from: '10:00', to: '12:00' }],
    }),
    expectRow: (row: any) => {
      expect(Number(row.duration)).toBe(45);
      expect(Number(row.breakTime)).toBe(15);
      expect(Boolean(Number(row.sameSlotAllDays))).toBe(true);
    },
  },
  {
    type: 'event',
    endpoint: REST_GAPS.BOOKING_EVENT_TICKETS,
    booking: () => ({
      type: 'event',
      qty: '100',
      location: 'E2E Hall',
      available_from: `${dateOffset(1)} 09:00:00`,
      available_to: `${dateOffset(30)} 18:00:00`,
      tickets: {
        ticket_0: {
          en: { name: `E2E Ticket ${uniqueStamp()}`, description: 'E2E ticket description' },
          price: '25',
          qty: '40',
        },
      },
    }),
    expectRow: (row: any) => {
      expect(Number(row.price)).toBe(25);
      expect(Number(row.qty)).toBe(40);
    },
  },
  {
    type: 'rental',
    endpoint: REST_GAPS.BOOKING_RENTAL_SLOTS,
    booking: () => ({
      type: 'rental',
      qty: '1',
      location: 'E2E Garage',
      available_every_week: '1',
      renting_type: 'daily',
      daily_price: '40',
      same_slot_all_days: '1',
    }),
    expectRow: (row: any) => {
      expect(row.rentingType).toBe('daily');
      expect(Number(row.dailyPrice)).toBe(40);
    },
  },
  {
    type: 'table',
    endpoint: REST_GAPS.BOOKING_TABLE_SLOTS,
    booking: () => ({
      type: 'table',
      qty: '1',
      location: 'E2E Restaurant',
      available_every_week: '1',
      price_type: 'guest',
      guest_limit: '4',
      duration: '45',
      break_time: '15',
      prevent_scheduling_before: '0',
      same_slot_all_days: '1',
      slots: [{ from: '12:00', to: '14:00' }],
    }),
    expectRow: (row: any) => {
      expect(row.priceType).toBe('guest');
      expect(Number(row.guestLimit)).toBe(4);
      expect(Number(row.duration)).toBe(45);
    },
  },
];

async function rowsForBookingProduct(request: APIRequestContext, endpoint: string, bookingProductId: number): Promise<any[]> {
  return collectPages(request, endpoint, (row: any) => Number(row.bookingProductId) === bookingProductId);
}

for (const bookingCase of BOOKING_CASES) {
  test.describe(`shop booking ${bookingCase.type} slot collection rest api`, () => {
    let createdIds: number[];
    let bookingProductId: number;

    test.beforeEach(async ({ request }) => {
      createdIds = [];

      const productId = (await createAdminProduct(request, 'booking')).id;
      createdIds.push(productId);

      const updated = await updateAdminProduct(request, productId, { booking: bookingCase.booking() });
      expect(updated.bookingProduct?.type).toBe(bookingCase.type);

      bookingProductId = await shopBookingProductId(request, productId);
    });

    test.afterEach(async ({ request }) => {
      await deleteAdminProducts(request, createdIds);
    });

    test(`should list the ${bookingCase.type} configuration of a new booking product`, async ({ request }) => {
      const rows = await rowsForBookingProduct(request, bookingCase.endpoint, bookingProductId);

      expect(rows).toHaveLength(1);
      expect(typeof rows[0].id).toBe('number');
      bookingCase.expectRow(rows[0]);
    });

    test(`should drop the ${bookingCase.type} configuration from the listing once its product is deleted`, async ({ request }) => {
      await deleteAdminProducts(request, createdIds);
      createdIds = [];

      expect(await rowsForBookingProduct(request, bookingCase.endpoint, bookingProductId)).toEqual([]);
    });
  });
}

test.describe('shop booking slot collections paging and refusals rest api', () => {
  for (const bookingCase of BOOKING_CASES) {
    test(`should cap the ${bookingCase.type} collection at the requested page size`, async ({ request }) => {
      const response = await sendRestRequest(request, bookingCase.endpoint, { params: { page: '1', per_page: '1' } });
      const body = await safeJson(response);

      expect(response.status(), JSON.stringify(body)).toBe(200);
      expect(Array.isArray(body)).toBe(true);
      expect(body.length).toBeLessThanOrEqual(1);
    });

    test(`should refuse the ${bookingCase.type} collection without a storefront key`, async ({ request }) => {
      await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, bookingCase.endpoint));
    });
  }
});
