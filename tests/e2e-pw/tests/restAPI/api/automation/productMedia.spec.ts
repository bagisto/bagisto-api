import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { sendAdminMultipartRequest } from '../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { ADMIN_CATALOG } from '../../rest/endpoints/admin.catalog.endpoints';
import { mp4Video, pngImage } from '../../rest/helpers/mediaFixtures';
import {
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
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

const BOOKING_MESSAGES = {
  ID_REQUIRED: 'The booking product id is required.',
  DATE_REQUIRED: 'The date is required.',
  PRODUCT_NOT_FOUND: 'Booking product not found.',
};

interface BookingSlotCase {
  type: string;
  collection: string;
  item: (id: number) => string;
  booking: () => Record<string, any>;
}

function dateOffset(days: number): string {
  return new Date(Date.now() + days * 86_400_000).toISOString().slice(0, 10);
}

const BOOKING_SLOT_CASES: BookingSlotCase[] = [
  {
    type: 'default',
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
  },
  {
    type: 'appointment',
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
  },
  {
    type: 'rental',
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
  },
  {
    type: 'table',
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
  },
  {
    type: 'event',
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
  },
];

async function getJson(request: APIRequestContext, endpoint: string): Promise<any> {
  const response = await sendRestRequest(request, endpoint);
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

async function uploadImage(request: APIRequestContext, productId: number): Promise<any> {
  const response = await sendAdminMultipartRequest(request, ADMIN_CATALOG.PRODUCT_IMAGES(productId), {
    image: { name: `e2e-${uniqueStamp()}.png`, mimeType: 'image/png', buffer: pngImage() },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id, JSON.stringify(body)).toBe('number');

  return body;
}

async function uploadVideo(request: APIRequestContext, productId: number): Promise<any> {
  const response = await sendAdminMultipartRequest(request, ADMIN_CATALOG.PRODUCT_VIDEOS(productId), {
    video: { name: `e2e-${uniqueStamp()}.mp4`, mimeType: 'video/mp4', buffer: mp4Video() },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id, JSON.stringify(body)).toBe('number');

  return body;
}

test.describe('product media rest api', () => {
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

  test('should return all product images', async ({ request }) => {
    const body = await getJson(request, ENDPOINTS.ALL_PRODUCT_IMAGES);

    expect(Array.isArray(body)).toBe(true);
  });

  test('should return the uploaded images of a product', async ({ request }) => {
    expect(await getJson(request, ENDPOINTS.PRODUCT_IMAGES(productId))).toEqual([]);

    const image = await uploadImage(request, productId);
    const body = await getJson(request, ENDPOINTS.PRODUCT_IMAGES(productId));

    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(image.id);
    expect(body[0].path).toBe(image.path);
  });

  test('should return single image by id', async ({ request }) => {
    const image = await uploadImage(request, productId);
    const body = await getJson(request, ENDPOINTS.PRODUCT_IMAGE(image.id));

    expect(body.id).toBe(image.id);
    expect(body.path).toBe(image.path);
    expect(body.productId).toBe(productId);
  });

  test('should return all product videos', async ({ request }) => {
    const body = await getJson(request, ENDPOINTS.ALL_PRODUCT_VIDEOS);

    expect(Array.isArray(body)).toBe(true);
  });

  test('should return the uploaded videos of a product', async ({ request }) => {
    expect(await getJson(request, ENDPOINTS.PRODUCT_VIDEOS(productId))).toEqual([]);

    const video = await uploadVideo(request, productId);
    const body = await getJson(request, ENDPOINTS.PRODUCT_VIDEOS(productId));

    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(video.id);
    expect(body[0].path).toBe(video.path);
  });

  test('should return single video by id', async ({ request }) => {
    const video = await uploadVideo(request, productId);
    const body = await getJson(request, ENDPOINTS.PRODUCT_VIDEO(video.id));

    expect(body.id).toBe(video.id);
    expect(body.path).toBe(video.path);
  });

  test('should return all bundle option products', async ({ request }) => {
    const body = await getJson(request, ENDPOINTS.ALL_BUNDLE_OPTION_PRODUCTS);

    expect(Array.isArray(body)).toBe(true);
  });

  test('should return all customizable option prices', async ({ request }) => {
    const body = await getJson(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTION_PRICES);

    expect(Array.isArray(body)).toBe(true);
  });

  for (const [label, endpoint] of [
    ['bundle options', ENDPOINTS.PRODUCT_BUNDLE_OPTIONS],
    ['grouped products', ENDPOINTS.PRODUCT_GROUPED_PRODUCTS],
    ['downloadable links', ENDPOINTS.PRODUCT_DOWNLOADABLE_LINKS],
    ['downloadable samples', ENDPOINTS.PRODUCT_DOWNLOADABLE_SAMPLES],
    ['customizable options', ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTIONS],
    ['customer group prices', ENDPOINTS.PRODUCT_GROUP_PRICES],
    ['booking products', ENDPOINTS.BOOKING_PRODUCTS],
  ] as const) {
    test(`should return no ${label} for a plain simple product`, async ({ request }) => {
      expect(await getJson(request, endpoint(productId))).toEqual([]);
    });
  }
});

test.describe('product media of a missing product rest api', () => {
  test('should return an empty image list for a product that does not exist', async ({ request }) => {
    expect(await getJson(request, ENDPOINTS.PRODUCT_IMAGES(UNKNOWN_ID))).toEqual([]);
  });

  test('should return 404 for an image that does not exist', async ({ request }) => {
    await expectDetail(await sendRestRequest(request, ENDPOINTS.PRODUCT_IMAGE(UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should return 404 for a booking product that does not exist', async ({ request }) => {
    await expectDetail(await sendRestRequest(request, ENDPOINTS.BOOKING_PRODUCT(UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
  });
});

for (const bookingCase of BOOKING_SLOT_CASES) {
  test.describe(`product booking ${bookingCase.type} configuration rest api`, () => {
    let createdIds: number[];
    let productId: number;
    let bookingProductId: number;

    test.beforeEach(async ({ request }) => {
      createdIds = [];
      productId = (await createAdminProduct(request, 'booking')).id;
      createdIds.push(productId);

      const updated = await updateAdminProduct(request, productId, { booking: bookingCase.booking() });

      expect(updated.bookingProduct?.type).toBe(bookingCase.type);

      bookingProductId = await shopBookingProductId(request, productId);
    });

    test.afterEach(async ({ request }) => {
      await deleteAdminProducts(request, createdIds);
    });

    test(`should return the ${bookingCase.type} booking product by id`, async ({ request }) => {
      const body = await getJson(request, ENDPOINTS.BOOKING_PRODUCT(bookingProductId));

      expect(numericIdOf(body.id)).toBe(bookingProductId);
      expect(body.type).toBe(bookingCase.type);
    });

    test(`should return the ${bookingCase.type} slot configuration by id`, async ({ request }) => {
      const [row] = await collectPages(
        request,
        bookingCase.collection,
        (candidate: any) => Number(candidate.bookingProductId) === bookingProductId
      );

      expect(row, `the ${bookingCase.type} configuration is listed`).toBeTruthy();

      const body = await getJson(request, bookingCase.item(numericIdOf(row.id)));

      expect(numericIdOf(body.id)).toBe(numericIdOf(row.id));
      expect(Number(body.bookingProductId)).toBe(bookingProductId);
    });
  });

  test.describe(`product booking ${bookingCase.type} refusals rest api`, () => {
    test(`should return 404 for a ${bookingCase.type} slot configuration that does not exist`, async ({ request }) => {
      await expectDetail(await sendRestRequest(request, bookingCase.item(UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
    });
  });
}

test.describe('booking slot availability rest api', () => {
  test('should require the booking product id', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS, {
      params: { date: dateOffset(7) },
    });

    await expectDetail(response, 400, BOOKING_MESSAGES.ID_REQUIRED);
  });

  test('should require the date', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS, {
      params: { id: String(UNKNOWN_ID) },
    });

    await expectDetail(response, 400, BOOKING_MESSAGES.DATE_REQUIRED);
  });

  test('should refuse a booking product that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.BOOKING_SLOTS, {
      params: { id: String(UNKNOWN_ID), date: dateOffset(7) },
    });

    await expectDetail(response, 400, BOOKING_MESSAGES.PRODUCT_NOT_FOUND);
  });
});
