import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  REST_GAPS,
  REST_GAPS_MESSAGES,
  UNKNOWN_ID,
  collectPages,
  createAdminProduct,
  deleteAdminProducts,
  expectDetail,
  numericIdOf,
  safeJson,
  shopBookingProductId,
  uniqueStamp,
  updateAdminProduct,
} from '../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 180_000 });

function dateOffset(days: number): string {
  return new Date(Date.now() + days * 86_400_000).toISOString().slice(0, 10);
}

interface SlotCase {
  type: string;
  label: string;
  collection: string;
  item: (id: number) => string;
  booking: () => Record<string, any>;
  expectRow: (row: any) => void;
}

const SLOT_CASES: SlotCase[] = [
  {
    type: 'default',
    label: 'default booking slots',
    collection: REST_GAPS.BOOKING_DEFAULT_SLOTS,
    item: ENDPOINTS.BOOKING_DEFAULT_SLOTS,
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
    label: 'appointment slots',
    collection: REST_GAPS.BOOKING_APPOINTMENT_SLOTS,
    item: ENDPOINTS.BOOKING_APPOINTMENT_SLOTS,
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
    },
  },
  {
    type: 'rental',
    label: 'rental slots',
    collection: REST_GAPS.BOOKING_RENTAL_SLOTS,
    item: ENDPOINTS.BOOKING_RENTAL_SLOTS,
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
    label: 'table slots',
    collection: REST_GAPS.BOOKING_TABLE_SLOTS,
    item: ENDPOINTS.BOOKING_TABLE_SLOTS,
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
    },
  },
  {
    type: 'event',
    label: 'event tickets',
    collection: REST_GAPS.BOOKING_EVENT_TICKETS,
    item: ENDPOINTS.BOOKING_EVENT_TICKETS,
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
];

for (const slotCase of SLOT_CASES) {
  test.describe(`booking product ${slotCase.type} slot rest api`, () => {
    let createdIds: number[];
    let bookingProductId: number;

    test.beforeEach(async ({ request }) => {
      createdIds = [];

      const productId = (await createAdminProduct(request, 'booking')).id;
      createdIds.push(productId);

      const updated = await updateAdminProduct(request, productId, { booking: slotCase.booking() });
      expect(updated.bookingProduct?.type).toBe(slotCase.type);

      bookingProductId = await shopBookingProductId(request, productId);
    });

    test.afterEach(async ({ request }) => {
      await deleteAdminProducts(request, createdIds);
    });

    test(`should return ${slotCase.label} for a booking product`, async ({ request }) => {
      const rows = await collectPages(
        request,
        slotCase.collection,
        (row: any) => Number(row.bookingProductId) === bookingProductId
      );

      expect(rows).toHaveLength(1);

      const response = await sendRestRequest(request, slotCase.item(numericIdOf(rows[0].id)));
      const body = await safeJson(response);

      expect(response.status(), JSON.stringify(body)).toBe(200);
      expect(body).toEqual(rows[0]);
      slotCase.expectRow(body);
    });
  });
}

test.describe('booking product slot refusals rest api', () => {
  for (const slotCase of SLOT_CASES) {
    test(`should return 404 for ${slotCase.label} that do not exist`, async ({ request }) => {
      await expectDetail(await sendRestRequest(request, slotCase.item(UNKNOWN_ID)), 404, REST_GAPS_MESSAGES.GENERIC_NOT_FOUND);
    });
  }
});
