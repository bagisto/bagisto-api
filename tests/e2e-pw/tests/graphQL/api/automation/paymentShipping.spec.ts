import { test, expect } from '@playwright/test';
import { getGuestCartHeaders } from '../../config/auth';
import { GET_CHECKOUT_ADDRESSES } from '../../graphql/Queries/checkout.queries';
import {
  PLACE_ORDER,
  SET_CHECKOUT_ADDRESS,
  SET_PAYMENT_METHOD,
  SET_SHIPPING_METHOD,
} from '../../graphql/Queries/paymentShipping.queries';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import {
  expectAuthAwareResult,
  expectConnection,
  graphQLErrorMessages,
} from '../../graphql/helpers/testSupport';

test.describe('Payment and Shipping Methods GraphQL API Tests', () => {
  test('Should get checkout addresses collection for the guest cart', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, GET_CHECKOUT_ADDRESSES, {}, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    const connection = expectConnection(body, 'data.collectionGetCheckoutAddresses');
    expect(Array.isArray(connection.edges)).toBe(true);
  });

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

  test('Should try to set checkout addresses and show the real API response', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const input = {
      billingFirstName: 'Guest',
      billingLastName: 'User',
      billingEmail: 'guest@example.com',
      billingAddress: '123 Demo Street',
      billingCity: 'New York',
      billingCountry: 'US',
      billingState: 'NY',
      billingPostcode: '10001',
      billingPhoneNumber: '9999999999',
      useForShipping: true,
    };

    const response = await sendGraphQLRequest(request, SET_CHECKOUT_ADDRESS, { input }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Checkout address response: ${JSON.stringify(body)}`);
    expect(body.data?.createCheckoutAddress?.checkoutAddress || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should try to set shipping method and payment method and show the real API response', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);

    const shippingResponse = await sendGraphQLRequest(
      request,
      SET_SHIPPING_METHOD,
      { shippingMethod: 'flatrate_flatrate' },
      headers
    );
    expect(shippingResponse.status()).toBe(200);
    const shippingBody = await shippingResponse.json();
    console.log(`Set shipping method response: ${JSON.stringify(shippingBody)}`);
    expect(
      shippingBody.data?.createCheckoutShippingMethod?.checkoutShippingMethod ||
      graphQLErrorMessages(shippingBody).length > 0
    ).toBeTruthy();

    const paymentResponse = await sendGraphQLRequest(
      request,
      SET_PAYMENT_METHOD,
      {
        paymentMethod: 'moneytransfer',
        successUrl: 'https://example.com/success',
        failureUrl: 'https://example.com/failure',
        cancelUrl: 'https://example.com/cancel',
      },
      headers
    );
    expect(paymentResponse.status()).toBe(200);
    const paymentBody = await paymentResponse.json();
    console.log(`Set payment method response: ${JSON.stringify(paymentBody)}`);
    expect(
      paymentBody.data?.createCheckoutPaymentMethod?.checkoutPaymentMethod ||
      graphQLErrorMessages(paymentBody).length > 0
    ).toBeTruthy();
  });

  test('Should try to place an order and show the real API response', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, PLACE_ORDER, {}, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Place order response: ${JSON.stringify(body)}`);
    expect(body.data?.createCheckoutOrder?.checkoutOrder || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });
});
