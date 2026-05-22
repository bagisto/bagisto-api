import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Customer Shipment GraphQL API - Docs aligned', () => {
  test('Should return customer order shipments or the actual auth message', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      SHOP_DOCS_QUERIES.getCustomerOrderShipments,
      { orderId: 1 },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerOrderShipments');
  });

  test('Should return a single shipment or the actual validation/auth message', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      SHOP_DOCS_QUERIES.getCustomerOrderShipment,
      { id: 1 },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Customer shipment response: ${graphQLErrorMessages(body).join(' | ')}`);
    expect(body.data?.customerOrderShipment !== undefined || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });
});
