import { test, expect, APIRequestContext } from '@playwright/test';
import { getGuestCartHeaders } from '../../config/auth';
import { GET_CHECKOUT_ADDRESSES } from '../../graphql/Queries/checkout.queries';
import { SHOP_CART_QUERIES } from '../../graphql/Queries/coverage/shopCart.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import {
  SHOP_CART_OFFLINE_PAYMENT_METHODS,
  addCartLine,
  bearer,
  buildBilling,
  findSaleableSimpleProducts,
  registerShopCustomer,
  uniqueSuffix,
} from '../../../restAPI/rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

const MESSAGES = {
  SHIPPING_METHOD_SAVED: 'Shipping method saved successfully',
  INVALID_SHIPPING_METHOD: 'Invalid or unavailable shipping method',
  PAYMENT_METHOD_SAVED: 'Payment method saved successfully',
  INVALID_PAYMENT_METHOD: 'Invalid or unavailable payment method',
  ORDER_PLACED: 'Order placed successfully',
  CART_EMPTY: 'Cart is empty',
};

const SAVE_ADDRESS = `
  mutation createCheckoutAddress($input: createCheckoutAddressInput!) {
    createCheckoutAddress(input: $input) {
      checkoutAddress {
        success
        billingFirstName
        billingEmail
        billingAddress
        billingCity
        billingCountry
        billingState
        billingPostcode
        shippingFirstName
      }
    }
  }
`;

const SAVE_SHIPPING_METHOD = `
  mutation createCheckoutShippingMethod($shippingMethod: String!) {
    createCheckoutShippingMethod(input: { shippingMethod: $shippingMethod }) {
      checkoutShippingMethod {
        success
        message
      }
    }
  }
`;

const SAVE_PAYMENT_METHOD = `
  mutation createCheckoutPaymentMethod($paymentMethod: String!) {
    createCheckoutPaymentMethod(input: { paymentMethod: $paymentMethod }) {
      checkoutPaymentMethod {
        success
        message
      }
    }
  }
`;

const PLACE_ORDER = `
  mutation createCheckoutOrder {
    createCheckoutOrder(input: {}) {
      checkoutOrder {
        success
        message
        orderId
        redirect
        redirectUrl
      }
    }
  }
`;

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

function expectNoErrors(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
}

async function customerCartWithAddress(request: APIRequestContext): Promise<Record<string, string>> {
  const [productId] = await findSaleableSimpleProducts(request, 1);
  const customer = await registerShopCustomer(request);
  const headers = bearer(customer.token);

  await addCartLine(request, customer.token, productId, 1);

  const body = await runGraphQL(request, SAVE_ADDRESS, { input: buildBilling(uniqueSuffix()) }, headers);

  expectNoErrors(body);

  return headers;
}

test.describe('payment and shipping methods graphql api tests', () => {
  test('should get checkout addresses collection for the guest cart', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, GET_CHECKOUT_ADDRESSES, {}, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    const connection = expectConnection(body, 'data.collectionGetCheckoutAddresses');
    expect(Array.isArray(connection.edges)).toBe(true);
  });

  test('should save the checkout address on the customer cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const billing = buildBilling(uniqueSuffix());

    await addCartLine(request, customer.token, productId, 1);

    const body = await runGraphQL(request, SAVE_ADDRESS, { input: billing }, bearer(customer.token));

    expectNoErrors(body);

    const address = body.data.createCheckoutAddress.checkoutAddress;

    expect(address.success).toBe(true);
    expect(address.billingFirstName).toBe(billing.billingFirstName);
    expect(address.billingEmail).toBe(billing.billingEmail);
    expect(address.billingAddress).toContain(billing.billingAddress);
    expect(address.billingCity).toBe(billing.billingCity);
    expect(address.billingCountry).toBe(billing.billingCountry);
    expect(address.billingState).toBe(billing.billingState);
    expect(address.billingPostcode).toBe(billing.billingPostcode);
    expect(address.shippingFirstName).toBe(billing.billingFirstName);
  });

  test('should place an order through the shipping and payment method mutations', async ({ request }) => {
    const headers = await customerCartWithAddress(request);

    const rates = await runGraphQL(request, SHOP_CART_QUERIES.collectionShippingRates, {}, headers);
    expectNoErrors(rates);

    const shippingMethods: string[] = rates.data.collectionShippingRates.map((rate: any) => rate.method).filter(Boolean);
    test.skip(shippingMethods.length === 0, 'no shipping carrier is available for a US address');

    const shipping = await runGraphQL(request, SAVE_SHIPPING_METHOD, { shippingMethod: shippingMethods[0] }, headers);
    expectNoErrors(shipping);
    expect(shipping.data.createCheckoutShippingMethod.checkoutShippingMethod).toEqual({
      success: true,
      message: MESSAGES.SHIPPING_METHOD_SAVED,
    });

    const methods = await runGraphQL(request, SHOP_CART_QUERIES.collectionPaymentMethods, {}, headers);
    expectNoErrors(methods);

    const available: string[] = methods.data.collectionPaymentMethods.map((method: any) => method.method);
    const offline = SHOP_CART_OFFLINE_PAYMENT_METHODS.find((code) => available.includes(code));
    test.skip(!offline, `no offline payment method is enabled (available: ${available.join(', ')})`);

    const payment = await runGraphQL(request, SAVE_PAYMENT_METHOD, { paymentMethod: offline }, headers);
    expectNoErrors(payment);
    expect(payment.data.createCheckoutPaymentMethod.checkoutPaymentMethod).toEqual({
      success: true,
      message: MESSAGES.PAYMENT_METHOD_SAVED,
    });

    const order = await runGraphQL(request, PLACE_ORDER, {}, headers);
    expectNoErrors(order);

    const placed = order.data.createCheckoutOrder.checkoutOrder;

    expect(placed.success).toBe(true);
    expect(placed.message).toBe(MESSAGES.ORDER_PLACED);
    expect(placed.redirect).toBe(false);
    expect(placed.redirectUrl).toBeNull();
    expect(Number(placed.orderId)).toBeGreaterThan(0);
  });

  test('should reject a shipping method the cart does not offer', async ({ request }) => {
    const headers = await customerCartWithAddress(request);

    const body = await runGraphQL(request, SAVE_SHIPPING_METHOD, { shippingMethod: 'unknowncarrier_unknown' }, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.INVALID_SHIPPING_METHOD]);
    expect(body.data?.createCheckoutShippingMethod ?? null).toBeNull();
  });

  test('should reject a payment method that is not configured', async ({ request }) => {
    const headers = await customerCartWithAddress(request);

    const body = await runGraphQL(request, SAVE_PAYMENT_METHOD, { paymentMethod: 'unknownpayment' }, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.INVALID_PAYMENT_METHOD]);
    expect(body.data?.createCheckoutPaymentMethod ?? null).toBeNull();
  });

  test('should refuse to place an order for an empty guest cart', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);

    const body = await runGraphQL(request, PLACE_ORDER, {}, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.CART_EMPTY]);
    expect(body.data?.createCheckoutOrder ?? null).toBeNull();
  });
});
