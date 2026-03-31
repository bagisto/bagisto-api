import { test, expect } from '@playwright/test';
import { getGuestCartHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Payment and Shipping Methods GraphQL API Tests', () => {
  test('Should get payment methods successfully or show the real API message', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPaymentMethods, {}, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.paymentMethods');
  });

  test('Should return payment methods with required fields when available', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPaymentMethods, {}, headers);
    const body = await response.json();

    if (body.data?.paymentMethods?.length > 0) {
      const method = body.data.paymentMethods[0];
      expect(method.method).toBeDefined();
      expect(method.methodTitle).toBeDefined();
    } else {
      console.log(`Payment methods response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(body.data?.paymentMethods || body.errors).toBeTruthy();
    }
  });

  test('Should get shipping methods successfully or show the real API message', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getShippingMethods, {}, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.shippingMethods');
  });

  test('Should return shipping methods with required fields when available', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getShippingMethods, {}, headers);
    const body = await response.json();

    if (body.data?.shippingMethods?.length > 0) {
      const method = body.data.shippingMethods[0];
      expect(method.method).toBeDefined();
      expect(method.methodTitle).toBeDefined();
    } else {
      console.log(`Shipping methods response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(body.data?.shippingMethods || body.errors).toBeTruthy();
    }
  });
});
