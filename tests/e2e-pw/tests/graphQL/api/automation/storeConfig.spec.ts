import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { GET_STORE_CONFIG } from '../../graphql/Queries/storeConfig.queries';

test.describe('Store Config GraphQL API', () => {
  test('Should return the public store settings of the current channel', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_STORE_CONFIG);

    expect(response.status()).toBe(200);

    const body = await response.json();

    expect(body.errors).toBeUndefined();

    const config = body.data.storeConfig;

    expect(typeof config.channel).toBe('string');
    expect(typeof config.guestCheckoutEnabled).toBe('boolean');
    expect(typeof config.minimumOrderEnabled).toBe('boolean');
    expect(typeof config.customerReviewEnabled).toBe('boolean');
    expect(typeof config.guestReviewEnabled).toBe('boolean');
    expect(typeof config.compareEnabled).toBe('boolean');
    expect(typeof config.wishlistEnabled).toBe('boolean');
    expect(Array.isArray(config.socialLoginProviders)).toBe(true);
    expect(['excluding_tax', 'including_tax', 'both']).toContain(config.taxCartDisplayPrices);
  });

  test('Should not expose any secret setting', async ({ request }) => {
    const response = await sendGraphQLRequest(request, `query { storeConfig { googleClientSecret } }`);

    const body = await response.json();

    expect(body.errors).toBeDefined();
    expect(body.errors.length).toBeGreaterThan(0);
  });
});
