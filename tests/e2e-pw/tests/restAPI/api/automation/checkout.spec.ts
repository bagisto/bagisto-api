import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  SHOP_CART_MESSAGES,
  bearer,
  expectProblem,
  randomUuid,
  readCart,
  readJson,
} from '../../rest/helpers/coverage/shopCart.helpers';

const BILLING = {
  billingFirstName: 'Checkout',
  billingLastName: 'Tester',
  billingEmail: 'checkout.tester@playwrighttest.local',
  billingAddress: '123 Test Street',
  billingCity: 'Los Angeles',
  billingState: 'CA',
  billingCountry: 'US',
  billingPostcode: '90210',
  billingPhoneNumber: '2125551234',
  useForShipping: true,
};

test.describe('checkout endpoints without a cart token', () => {
  test('listing checkout addresses without a bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHECKOUT_ADDRESSES);

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('listing shipping methods without a bearer token returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHECKOUT_SHIPPING_METHODS);

    await expectProblem(response, 404, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('saving a shipping method without a bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHECKOUT_SHIPPING_METHODS, {
      method: 'POST',
      data: { shippingMethod: 'flatrate_flatrate' },
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('listing payment methods without a bearer token returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHECKOUT_PAYMENT_METHODS);

    await expectProblem(response, 404, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('placing an order without a bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PLACE_ORDER, {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('saving a checkout address without a bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SET_BILLING_ADDRESS, {
      method: 'POST',
      data: BILLING,
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('saving a payment method without a bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SET_PAYMENT_METHOD, {
      method: 'POST',
      data: { paymentMethod: 'cashondelivery' },
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('the estimate shipping item route is not exposed', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING(1));

    expect(response.status(), JSON.stringify(await readJson(response))).toBe(404);
  });
});

test.describe('checkout endpoints with an unknown cart token', () => {
  test('saving a checkout address returns 404 and creates no cart', async ({ request }) => {
    const token = randomUuid();

    const response = await sendRestRequest(request, ENDPOINTS.SET_BILLING_ADDRESS, {
      method: 'POST',
      data: BILLING,
      headers: bearer(token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.INVALID_CART_TOKEN);

    await expectProblem(await readCart(request, token), 404, SHOP_CART_MESSAGES.CART_NOT_FOUND);
  });

  test('saving a payment method returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SET_PAYMENT_METHOD, {
      method: 'POST',
      data: { paymentMethod: 'cashondelivery' },
      headers: bearer(randomUuid()),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
  });

  test('placing an order returns 404', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PLACE_ORDER, {
      method: 'POST',
      data: {},
      headers: bearer(randomUuid()),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
  });
});
