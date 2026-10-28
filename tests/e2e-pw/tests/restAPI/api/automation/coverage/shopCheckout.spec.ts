import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  SHOP_CART_ENDPOINTS,
  SHOP_CART_MESSAGES,
  addCartLine,
  bearer,
  buildBilling,
  chooseFirstShippingMethod,
  choosePaymentMethod,
  createGuestCart,
  expectProblem,
  findSaleableSimpleProducts,
  listPaymentMethodCodes,
  placeCustomerOrder,
  randomUuid,
  readJson,
  registerShopCustomer,
  saveCheckoutAddress,
  uniqueSuffix,
} from '../../../rest/helpers/coverage/shopCart.helpers';
import { adminConfigValue } from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 180_000 });

const STRIPE_PAYMENT_SLUG = 'sales.payment_methods';

const STRIPE_PLACEHOLDER_SECRET_KEY = 'API_TEST_KEY';

async function configuredStripeSecretKey(request: APIRequestContext): Promise<string | null> {
  const sandbox = await adminConfigValue(request, STRIPE_PAYMENT_SLUG, `${STRIPE_PAYMENT_SLUG}.stripe.sandbox`);
  const keyField = sandbox === '1' ? 'api_test_key' : 'api_key';
  const key = await adminConfigValue(request, STRIPE_PAYMENT_SLUG, `${STRIPE_PAYMENT_SLUG}.stripe.${keyField}`);

  return key === '' ? null : key;
}

function isRealStripeSecretKey(key: string | null): boolean {
  return key !== null && key !== STRIPE_PLACEHOLDER_SECRET_KEY;
}

test.describe('shop checkout message rest api', () => {
  test('should answer with a null checkout message of an order the customer placed while the feature is disabled', async ({ request }) => {
    const magicAi = await adminConfigValue(request, 'magic_ai.general.settings', 'magic_ai.general.settings.enabled');
    const checkoutMessage = await adminConfigValue(
      request,
      'magic_ai.storefront_features.checkout_message',
      'magic_ai.storefront_features.checkout_message.enabled'
    );

    test.skip(
      magicAi === '1' && checkoutMessage === '1',
      'magic ai checkout messages are enabled on this store, so the generated text depends on the ai provider'
    );

    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const order = await placeCustomerOrder(request, customer, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_MESSAGE(order.orderId), {
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual({ message: null });
  });

  test('should not reveal an order placed by another customer', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await registerShopCustomer(request);
    const order = await placeCustomerOrder(request, owner, productId);
    const intruder = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_MESSAGE(order.orderId), {
      headers: bearer(intruder.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.ORDER_NOT_FOUND(order.orderId));
  });

  test('should refuse the checkout message without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_MESSAGE(1));

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('should report an order that does not exist as not found', async ({ request }) => {
    const guest = await createGuestCart(request);
    const orderId = 2147480000;

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_MESSAGE(orderId), {
      headers: bearer(guest.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.ORDER_NOT_FOUND(orderId));
  });
});

test.describe('shop checkout payment initiate rest api', () => {
  test('should create a stripe checkout session for a cart paying with stripe', async ({ request }) => {
    test.skip(
      !isRealStripeSecretKey(await configuredStripeSecretKey(request)),
      'stripe has no real secret key configured on this store, so a checkout session cannot be created'
    );

    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const line = await addCartLine(request, customer.token, productId, 1);

    await saveCheckoutAddress(request, customer.token, buildBilling(uniqueSuffix()));
    await chooseFirstShippingMethod(request, customer.token);

    const available = await listPaymentMethodCodes(request, customer.token);

    test.skip(!available.includes('stripe'), 'stripe is not an available payment method on this store');

    await choosePaymentMethod(request, customer.token, 'stripe');

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_INITIATE, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.cartId).toBe(line.cartId);
    expect(body.sessionId).toMatch(/^cs_/);
    expect(body.redirectUrl).toMatch(/^https:\/\/checkout\.stripe\.com\//);
    expect(body.message).toBe(SHOP_CART_MESSAGES.ORDER_REDIRECT_REQUIRED);
  });

  test('should refuse a cart that is not paying with stripe', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, productId, 1);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_INITIATE, {
      method: 'POST',
      data: {},
      headers: bearer(guest.token),
    });

    await expectProblem(response, 422, SHOP_CART_MESSAGES.INVALID_PAYMENT_METHOD);
  });

  test('should refuse to start a payment without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_INITIATE, {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('should refuse to start a payment for an unknown cart token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_INITIATE, {
      method: 'POST',
      data: {},
      headers: bearer(randomUuid()),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
  });
});

test.describe('shop checkout payment confirm rest api', () => {
  test('should reject a confirmation without a session id', async ({ request }) => {
    const guest = await createGuestCart(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_CONFIRM, {
      method: 'POST',
      data: {},
      headers: bearer(guest.token),
    });

    await expectProblem(response, 422, SHOP_CART_MESSAGES.STRIPE_INVALID_SESSION);
  });

  test('should refuse a confirmation without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_CONFIRM, {
      method: 'POST',
      data: { sessionId: `cs_test_${uniqueSuffix()}` },
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('should refuse a confirmation for an unknown cart token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_CONFIRM, {
      method: 'POST',
      data: { sessionId: `cs_test_${uniqueSuffix()}` },
      headers: bearer(randomUuid()),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
  });

  test('should reject a session that stripe does not report as paid', async ({ request }) => {
    const guest = await createGuestCart(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENTS_CONFIRM, {
      method: 'POST',
      data: { sessionId: `cs_test_${uniqueSuffix()}` },
      headers: bearer(guest.token),
    });

    await expectProblem(response, 422, SHOP_CART_MESSAGES.STRIPE_SESSION_INVALID);
  });
});
