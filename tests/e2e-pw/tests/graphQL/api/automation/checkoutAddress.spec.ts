import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders, getGuestCartHeaders } from '../../config/auth';
import { ADD_PRODUCT_TO_CART } from '../../graphql/Queries/cart.queries';
import { READ_CHECKOUT_ADDRESS } from '../../graphql/Queries/checkout.queries';
import { SET_CHECKOUT_ADDRESS } from '../../graphql/Queries/paymentShipping.queries';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

async function getFirstProductId(request: APIRequestContext): Promise<number> {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[0]?.node;
  const numericId = Number(String(node?.id ?? '').split('/').pop());

  expect(numericId > 0, 'test store must have at least one product available').toBeTruthy();
  return numericId;
}

async function addProductAndGetCartId(
  request: APIRequestContext,
  headers: Record<string, string>
): Promise<number> {
  const productId = await getFirstProductId(request);
  const response = await sendGraphQLRequest(
    request,
    ADD_PRODUCT_TO_CART,
    { input: { productId, quantity: 1 } },
    headers
  );
  expect(response.status()).toBe(200);

  const body = await response.json();
  expect(body.errors, `add-to-cart errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

  const cartId = Number(body.data?.createAddProductInCart?.addProductInCart?._id);
  expect(cartId, 'unable to read the cart id from add-to-cart').toBeGreaterThan(0);
  return cartId;
}

function checkoutAddressIri(cartId: number): string {
  return `/api/shop/checkout-addresses/${cartId}`;
}

test.describe('read checkout address graphql api', () => {
  let billing: Record<string, string | boolean>;

  test.beforeEach(() => {
    const stamp = `${Date.now()}${Math.floor(Math.random() * 1000)}`;

    billing = {
      billingFirstName: `Billing${stamp}`,
      billingLastName: 'Reader',
      billingEmail: `checkout.address.${stamp}@playwrighttest.local`,
      billingAddress: `${stamp} Reader Street`,
      billingCity: 'Los Angeles',
      billingCountry: 'US',
      billingState: 'CA',
      billingPostcode: '90001',
      billingPhoneNumber: '2125551234',
      useForShipping: true,
    };
  });

  test('should return the billing address saved on the customer cart of the bearer token', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const cartId = await addProductAndGetCartId(request, headers);

    const saveResponse = await sendGraphQLRequest(request, SET_CHECKOUT_ADDRESS, { input: billing }, headers);
    expect(saveResponse.status()).toBe(200);

    const saveBody = await saveResponse.json();
    expect(saveBody.errors, `saving the address errored: ${graphQLErrorMessages(saveBody).join(' | ')}`).toBeUndefined();
    expect(saveBody.data?.createCheckoutAddress?.checkoutAddress?.success).toBe(true);

    const response = await sendGraphQLRequest(request, READ_CHECKOUT_ADDRESS, { id: checkoutAddressIri(cartId) }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `reading the address errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const address = body.data?.readCheckoutAddress;
    expect(address?.success).toBe(true);
    expect(typeof address?.customerId).toBe('number');
    expect(address?.billingFirstName).toBe(billing.billingFirstName);
    expect(address?.billingLastName).toBe(billing.billingLastName);
    expect(address?.billingEmail).toBe(billing.billingEmail);
    expect(address?.billingAddress).toContain(billing.billingAddress as string);
    expect(address?.billingCity).toBe(billing.billingCity);
    expect(address?.billingState).toBe(billing.billingState);
    expect(address?.billingCountry).toBe(billing.billingCountry);
    expect(address?.billingPostcode).toBe(billing.billingPostcode);
    expect(address?.billingPhoneNumber).toBe(billing.billingPhoneNumber);
  });

  test('should return the guest cart of the bearer token with no address before one is saved', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const cartId = await addProductAndGetCartId(request, headers);

    const response = await sendGraphQLRequest(request, READ_CHECKOUT_ADDRESS, { id: checkoutAddressIri(cartId) }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `reading the address errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const address = body.data?.readCheckoutAddress;
    expect(address?.success).toBe(true);
    expect(address?.customerId).toBeNull();
    expect(address?.billingFirstName).toBeNull();
    expect(address?.shippingFirstName).toBeNull();
  });

  test('should refuse to read a checkout address without a bearer token', async ({ request }) => {
    const headers = await getGuestCartHeaders(request);
    const cartId = await addProductAndGetCartId(request, headers);

    const response = await sendGraphQLRequest(request, READ_CHECKOUT_ADDRESS, { id: checkoutAddressIri(cartId) });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body)).toContain('Authentication token is required');
    expect(body.data?.readCheckoutAddress ?? null).toBeNull();
  });
});
