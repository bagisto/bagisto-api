import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CART_QUERIES } from '../../../graphql/Queries/coverage/shopCart.queries';
import {
  SHOP_CART_MESSAGES,
  ShopCheckoutBilling,
  addCartLine,
  bearer,
  buildBilling,
  chooseFirstShippingMethod,
  choosePaymentMethod,
  createGuestCart,
  findSaleableSimpleProducts,
  listPaymentMethodCodes,
  placeCustomerOrder,
  randomUuid,
  registerShopCustomer,
  saveCheckoutAddress,
  uniqueSuffix,
} from '../../../../restAPI/rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 180_000 });

async function runGraphQL(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  headers: Record<string, string> = {}
): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, headers);

  expect(response.status()).toBe(200);

  return response.json();
}

const STRIPE_PLACEHOLDER_SECRET_KEY = 'API_TEST_KEY';

const STRIPE_CONFIGURATION_VALUES = `
  query valuesAdminConfigurationValues($slug: String!, $channel: String, $locale: String) {
    valuesAdminConfigurationValues(slug: $slug, channel: $channel, locale: $locale) {
      values
    }
  }
`;

async function configuredStripeSecretKey(request: APIRequestContext): Promise<string | null> {
  const response = await sendAdminGraphQLRequest(request, STRIPE_CONFIGURATION_VALUES, {
    slug: 'sales.payment_methods',
    channel: 'default',
    locale: 'en',
  });

  expect(response.status()).toBe(200);

  const body = await response.json();

  expectNoErrors(body);

  const values = body.data.valuesAdminConfigurationValues.values;
  const sandbox = String(values['sales.payment_methods.stripe.sandbox'] ?? '') === '1';
  const key = values[sandbox ? 'sales.payment_methods.stripe.api_test_key' : 'sales.payment_methods.stripe.api_key'];

  return typeof key === 'string' && key !== '' ? key : null;
}

function isRealStripeSecretKey(key: string | null): boolean {
  return key !== null && key !== STRIPE_PLACEHOLDER_SECRET_KEY;
}

function expectNoErrors(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
}

test.describe('shop checkout message graphql api', () => {
  test('should answer with the checkout message of an order the customer placed', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const order = await placeCustomerOrder(request, customer, productId);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.checkoutMessage,
      { orderId: order.orderId },
      bearer(customer.token)
    );

    expectNoErrors(body);

    const message = body.data.checkoutMessage;

    expect(message).not.toBeNull();
    expect(message.message === null || typeof message.message === 'string', JSON.stringify(message)).toBe(true);
  });

  test('should not reveal an order placed by another customer', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await registerShopCustomer(request);
    const order = await placeCustomerOrder(request, owner, productId);
    const intruder = await registerShopCustomer(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.checkoutMessage,
      { orderId: order.orderId },
      bearer(intruder.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ORDER_NOT_FOUND(order.orderId));
    expect(body.data?.checkoutMessage ?? null).toBeNull();
  });

  test('should refuse the checkout message without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.checkoutMessage, { orderId: 1 });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.checkoutMessage ?? null).toBeNull();
  });
});

test.describe('shop collection payment methods graphql api', () => {
  test('should list the payment methods available to the bearer cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    await addCartLine(request, customer.token, productId, 1);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.collectionPaymentMethods, {}, bearer(customer.token));

    expectNoErrors(body);

    const methods = body.data.collectionPaymentMethods;

    expect(Array.isArray(methods)).toBe(true);
    expect(methods.length).toBeGreaterThan(0);

    for (const method of methods) {
      expect(typeof method.method).toBe('string');
      expect(typeof method.title).toBe('string');
      expect(typeof method.isAllowed).toBe('boolean');
    }

    const graphQLCodes = methods.map((method: any) => method.method);

    for (const code of await listPaymentMethodCodes(request, customer.token)) {
      expect(graphQLCodes).toContain(code);
    }
  });

  test('should refuse payment methods without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.collectionPaymentMethods);

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.collectionPaymentMethods ?? null).toBeNull();
  });

  test('should refuse payment methods for an unknown cart token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.collectionPaymentMethods, {}, bearer(randomUuid()));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
    expect(body.data?.collectionPaymentMethods ?? null).toBeNull();
  });
});

test.describe('shop collection shipping rates graphql api', () => {
  test('should list shipping rates once the cart has a shipping address', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    await addCartLine(request, customer.token, productId, 1);
    await saveCheckoutAddress(request, customer.token, buildBilling(uniqueSuffix()));

    const body = await runGraphQL(request, SHOP_CART_QUERIES.collectionShippingRates, {}, bearer(customer.token));

    expectNoErrors(body);

    const rates = body.data.collectionShippingRates;

    expect(Array.isArray(rates)).toBe(true);
    expect(rates.length).toBeGreaterThan(0);

    for (const rate of rates) {
      expect(typeof rate.method).toBe('string');
      expect(rate.method.length).toBeGreaterThan(0);
      expect(typeof rate.carrier).toBe('string');
      expect(typeof rate.price).toBe('number');
      expect(typeof rate.formattedPrice).toBe('string');
    }
  });

  test('should return no shipping rates before a shipping address is saved', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, productId, 1);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.collectionShippingRates, {}, bearer(guest.token));

    expectNoErrors(body);
    expect(body.data.collectionShippingRates).toEqual([]);
  });

  test('should refuse shipping rates without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.collectionShippingRates);

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.collectionShippingRates ?? null).toBeNull();
  });

  test('should refuse shipping rates for an unknown cart token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.collectionShippingRates, {}, bearer(randomUuid()));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
    expect(body.data?.collectionShippingRates ?? null).toBeNull();
  });
});

test.describe('shop create mutation checkout address graphql api', () => {
  test('should save the billing address on the customer cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const line = await addCartLine(request, customer.token, productId, 1);
    const billing = buildBilling(uniqueSuffix());

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMutationCheckoutAddress,
      { input: billing },
      bearer(customer.token)
    );

    expectNoErrors(body);
    expect(body.data.createMutationCheckoutAddress.mutationCheckoutAddress.id).toBeTruthy();

    const saved = await runGraphQL(
      request,
      SHOP_CART_QUERIES.readCheckoutAddress,
      { id: `/api/shop/checkout-addresses/${line.cartId}` },
      bearer(customer.token)
    );

    expectNoErrors(saved);

    const address = saved.data.readCheckoutAddress;

    expect(address.customerId).toBe(customer.id);
    expect(address.billingFirstName).toBe(billing.billingFirstName);
    expect(address.billingLastName).toBe(billing.billingLastName);
    expect(address.billingEmail).toBe(billing.billingEmail);
    expect(address.billingAddress).toContain(billing.billingAddress);
    expect(address.billingCity).toBe(billing.billingCity);
    expect(address.billingCountry).toBe(billing.billingCountry);
    expect(address.billingPostcode).toBe(billing.billingPostcode);
    expect(address.billingPhoneNumber).toBe(billing.billingPhoneNumber);
    expect(address.shippingFirstName).toBe(billing.billingFirstName);
  });

  test('should reject a billing address without a phone number', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const line = await addCartLine(request, customer.token, productId, 1);
    const billing: Partial<ShopCheckoutBilling> = buildBilling(uniqueSuffix());

    delete billing.billingPhoneNumber;

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMutationCheckoutAddress,
      { input: billing },
      bearer(customer.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.CHECKOUT_FIELD_REQUIRED('billingPhoneNumber'));

    const saved = await runGraphQL(
      request,
      SHOP_CART_QUERIES.readCheckoutAddress,
      { id: `/api/shop/checkout-addresses/${line.cartId}` },
      bearer(customer.token)
    );

    expectNoErrors(saved);
    expect(saved.data.readCheckoutAddress.billingFirstName).toBeNull();
  });

  test('should refuse to save an address without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createMutationCheckoutAddress, {
      input: buildBilling(uniqueSuffix()),
    });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createMutationCheckoutAddress ?? null).toBeNull();
  });

  test('should refuse to save an address for an unknown cart token', async ({ request }) => {
    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMutationCheckoutAddress,
      { input: buildBilling(uniqueSuffix()) },
      bearer(randomUuid())
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
    expect(body.data?.createMutationCheckoutAddress ?? null).toBeNull();
  });
});

test.describe('shop create checkout payment session graphql api', () => {
  test('should create a stripe checkout session for a cart paying with stripe', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const line = await addCartLine(request, customer.token, productId, 1);

    await saveCheckoutAddress(request, customer.token, buildBilling(uniqueSuffix()));
    await chooseFirstShippingMethod(request, customer.token);

    const available = await listPaymentMethodCodes(request, customer.token);

    test.skip(!available.includes('stripe'), 'stripe is not an available payment method on this store');
    test.skip(
      !isRealStripeSecretKey(await configuredStripeSecretKey(request)),
      'stripe carries no real secret key on this store, only the core placeholder, which the stripe api rejects'
    );

    await choosePaymentMethod(request, customer.token, 'stripe');

    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCheckoutPaymentSession, {}, bearer(customer.token));

    expectNoErrors(body);

    const session = body.data.createCheckoutPaymentSession.checkoutPaymentSession;

    expect(session.success).toBe(true);
    expect(session.cartId).toBe(line.cartId);
    expect(session.sessionId).toMatch(/^cs_/);
    expect(session.redirectUrl).toMatch(/^https:\/\/checkout\.stripe\.com\//);
    expect(session.message).toBe(SHOP_CART_MESSAGES.ORDER_REDIRECT_REQUIRED);
  });

  test('should refuse a cart that is not paying with stripe', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, productId, 1);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCheckoutPaymentSession, {}, bearer(guest.token));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.INVALID_PAYMENT_METHOD);
    expect(body.data?.createCheckoutPaymentSession ?? null).toBeNull();
  });

  test('should refuse to start a payment without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCheckoutPaymentSession);

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createCheckoutPaymentSession ?? null).toBeNull();
  });

  test('should refuse to start a payment for an unknown cart token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCheckoutPaymentSession, {}, bearer(randomUuid()));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
    expect(body.data?.createCheckoutPaymentSession ?? null).toBeNull();
  });
});

test.describe('shop create checkout payment confirmation graphql api', () => {
  test('should reject a confirmation without a session id', async ({ request }) => {
    const guest = await createGuestCart(request);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCheckoutPaymentConfirmation, {}, bearer(guest.token));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.STRIPE_INVALID_SESSION);
    expect(body.data?.createCheckoutPaymentConfirmation ?? null).toBeNull();
  });

  test('should refuse a confirmation without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCheckoutPaymentConfirmation, {
      sessionId: `cs_test_${uniqueSuffix()}`,
    });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createCheckoutPaymentConfirmation ?? null).toBeNull();
  });

  test('should refuse a confirmation for an unknown cart token', async ({ request }) => {
    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createCheckoutPaymentConfirmation,
      { sessionId: `cs_test_${uniqueSuffix()}` },
      bearer(randomUuid())
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
    expect(body.data?.createCheckoutPaymentConfirmation ?? null).toBeNull();
  });

  test('should reject a session that stripe does not report as paid', async ({ request }) => {
    const guest = await createGuestCart(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createCheckoutPaymentConfirmation,
      { sessionId: `cs_test_${uniqueSuffix()}` },
      bearer(guest.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.STRIPE_SESSION_INVALID);
    expect(body.data?.createCheckoutPaymentConfirmation ?? null).toBeNull();
  });
});
