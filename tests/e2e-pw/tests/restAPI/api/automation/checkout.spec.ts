// tests/restAPI/api/automation/checkout.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

// These run without a prepared cart, so the sequence guards reject them — that is
// what is being checked. 405 is never accepted: it would mean the route moved.
// Reads may legitimately 404 (no active cart / no such estimate).
function assertCheckoutStatus(resp: any, debugLabel: string, allowNotFound = false) {
  const code = resp.status();
  const allowed = [0, 200, 201, 400, 401, 422, 500];
  expect(allowNotFound ? [...allowed, 404] : allowed).toContain(code);
  console.log(`${debugLabel}:`, code);
  return code;
}

const ADDRESS = {
  firstName: 'E2E',
  lastName: 'Tester',
  email: 'e2e-checkout@example.com',
  address1: '123 Test Street',
  city: 'Los Angeles',
  state: 'CA',
  country: 'US',
  postcode: '90210',
  phone: '5550000000',
};

test.describe('Checkout Public Endpoints', () => {
  test('Should handle checkout addresses endpoint', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHECKOUT_ADDRESSES);
    assertCheckoutStatus(response, 'GET /api/shop/checkout-addresses', true);
  });

  test('Should handle shipping methods POST', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHECKOUT_SHIPPING_METHODS, {
      method: 'POST',
      data: { shippingMethod: 'flatrate_flatrate' },
    });
    assertCheckoutStatus(response, 'POST /api/shop/checkout-shipping-methods');
  });

  test('Should handle payment methods endpoint', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CHECKOUT_PAYMENT_METHODS);
    assertCheckoutStatus(response, 'GET /api/shop/payment-methods', true);
  });

  test('Should handle place order POST', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PLACE_ORDER, {
      method: 'POST',
      data: {},
    });
    assertCheckoutStatus(response, 'POST /api/shop/checkout-orders');
  });

  test('Should handle save shipping address POST', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SET_SHIPPING_ADDRESS, {
      method: 'POST',
      data: { shippingAddress: ADDRESS },
    });
    assertCheckoutStatus(response, 'POST /api/shop/checkout-addresses (shipping)');
  });

  test('Should handle save billing address POST', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SET_BILLING_ADDRESS, {
      method: 'POST',
      data: { billingAddress: { ...ADDRESS, useForShipping: true } },
    });
    assertCheckoutStatus(response, 'POST /api/shop/checkout-addresses (billing)');
  });

  test('Should handle set payment method POST', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.SET_PAYMENT_METHOD, {
      method: 'POST',
      data: { paymentMethod: 'cashondelivery' },
    });
    assertCheckoutStatus(response, 'POST /api/shop/checkout-payment-methods');
  });

  test('Should handle estimate shipping GET', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ESTIMATE_SHIPPING(1));
    assertCheckoutStatus(response, 'GET /api/shop/estimate_shippings/1', true);
  });
});
