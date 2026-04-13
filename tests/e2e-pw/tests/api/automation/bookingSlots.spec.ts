import { test, expect } from '@playwright/test';
import { env } from '../../config/env';
import { GET_BOOKING_SLOTS } from '../../graphql/Queries/booking.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Booking Slots GraphQL API Tests', () => {
  test('Should return booking slots when booking data is configured', async ({ request }) => {
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
});
