import { test, expect } from '@playwright/test';
import { env } from '../../config/env';
import { GET_BOOKING_SLOTS } from '../../graphql/Queries/booking.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('booking slots graphql api tests', () => {
  test('should return booking slots when booking data is configured', async ({ request }) => {
    test.skip(!env.bookingProductId, 'Set BAGISTO_BOOKING_PRODUCT_ID to run booking slots coverage.');

    const response = await sendGraphQLRequest(request, GET_BOOKING_SLOTS, {
      id: Number(env.bookingProductId),
      date: env.bookingDate,
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);

    if (messages.length > 0) {
      console.log(`Booking slots response: ${messages.join(' | ')}`);
    }

    expect(messages.length).toBe(0);
    expect(Array.isArray(body.data?.bookingSlots)).toBe(true);
  });

  test('should identify every booking slot by a unique iri built from the booking product, date and slot', async ({ request }) => {
    test.skip(!env.bookingProductId, 'Set BAGISTO_BOOKING_PRODUCT_ID to run booking slots coverage.');

    const bookingProductId = Number(env.bookingProductId);

    const response = await sendGraphQLRequest(request, GET_BOOKING_SLOTS, {
      id: bookingProductId,
      date: env.bookingDate,
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `booking slots errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const slots: any[] = body.data?.bookingSlots ?? [];
    test.skip(slots.length === 0, `No booking slots on ${env.bookingDate}; set BAGISTO_BOOKING_DATE to a date with slots.`);

    for (const slot of slots) {
      expect(typeof slot.slotId).toBe('string');
      expect(slot.slotId.length).toBeGreaterThan(0);
      expect(slot.id).toBe(
        `/api/shop/booking-slots/${encodeURIComponent(`${bookingProductId}-${env.bookingDate}-${slot.slotId}`)}`
      );
    }

    const ids = slots.map((slot) => slot.id);
    expect(new Set(ids).size).toBe(ids.length);
  });
});
