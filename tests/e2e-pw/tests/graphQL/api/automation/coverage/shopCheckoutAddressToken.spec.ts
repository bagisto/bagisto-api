import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CART_QUERIES } from '../../../graphql/Queries/coverage/shopCart.queries';
import {
  SHOP_CART_MESSAGES,
  ShopCustomer,
  bearer,
  createGuestCart,
  lettersFrom,
  registerShopCustomer,
  uniqueSuffix,
} from '../../../../restAPI/rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

interface AddressInput {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  address1: string;
  city: string;
  state: string;
  country: string;
  postcode: string;
}

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

function buildAddress(): AddressInput {
  const suffix = uniqueSuffix();

  return {
    firstName: `Address${lettersFrom(suffix)}`,
    lastName: 'Tester',
    email: `shop.address.${suffix}@playwrighttest.local`,
    phone: '2125551234',
    address1: `${suffix} Token Street`,
    city: 'Los Angeles',
    state: 'CA',
    country: 'US',
    postcode: '90001',
  };
}

async function createAddress(request: APIRequestContext, customer: ShopCustomer, input: AddressInput): Promise<number> {
  const body = await runGraphQL(request, SHOP_CART_QUERIES.createCustomerAddressToken, { input }, bearer(customer.token));

  expectNoErrors(body);

  const addressId = Number(body.data.createCustomerAddressToken.customerAddressToken.addressId);

  expect(addressId).toBeGreaterThan(0);

  return addressId;
}

async function readAddress(request: APIRequestContext, customer: ShopCustomer, addressId: number): Promise<any> {
  return runGraphQL(request, SHOP_CART_QUERIES.readCustomerAddressToken, { id: String(addressId) }, bearer(customer.token));
}

test.describe('shop create customer address token graphql api', () => {
  test('should add an address to the bearer customer', async ({ request }) => {
    const customer = await registerShopCustomer(request);
    const input = buildAddress();

    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCustomerAddressToken, { input }, bearer(customer.token));

    expectNoErrors(body);

    const address = body.data.createCustomerAddressToken.customerAddressToken;

    expect(Number(address.addressId)).toBeGreaterThan(0);
    expect(address.firstName).toBe(input.firstName);
    expect(address.lastName).toBe(input.lastName);
    expect(address.address1).toBe(input.address1);
    expect(address.city).toBe(input.city);
    expect(address.state).toBe(input.state);
    expect(address.country).toBe(input.country);
    expect(address.postcode).toBe(input.postcode);
    expect(address.phone).toBe(input.phone);
    expect(address.defaultAddress).toBe(false);
  });

  test('should update an address of the bearer customer when an address id is sent', async ({ request }) => {
    const customer = await registerShopCustomer(request);
    const addressId = await createAddress(request, customer, buildAddress());

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createCustomerAddressToken,
      { input: { addressId, city: 'San Diego' } },
      bearer(customer.token)
    );

    expectNoErrors(body);
    expect(Number(body.data.createCustomerAddressToken.customerAddressToken.addressId)).toBe(addressId);
    expect(body.data.createCustomerAddressToken.customerAddressToken.city).toBe('San Diego');

    const saved = await readAddress(request, customer, addressId);

    expectNoErrors(saved);
    expect(saved.data.readCustomerAddressToken.city).toBe('San Diego');
  });

  test('should reject an address without its street', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createCustomerAddressToken,
      { input: { firstName: 'Incomplete', lastName: 'Address' } },
      bearer(customer.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ADDRESS_FIELD_REQUIRED('address1'));
    expect(body.data?.createCustomerAddressToken ?? null).toBeNull();
  });

  test('should not update an address of another customer', async ({ request }) => {
    const owner = await registerShopCustomer(request);
    const input = buildAddress();
    const addressId = await createAddress(request, owner, input);
    const intruder = await registerShopCustomer(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createCustomerAddressToken,
      { input: { addressId, city: 'Hijacked' } },
      bearer(intruder.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ADDRESS_NOT_FOUND);

    const saved = await readAddress(request, owner, addressId);

    expectNoErrors(saved);
    expect(saved.data.readCustomerAddressToken.city).toBe(input.city);
  });

  test('should refuse to add an address without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createCustomerAddressToken, { input: buildAddress() });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createCustomerAddressToken ?? null).toBeNull();
  });
});

test.describe('shop read customer address token graphql api', () => {
  test('should return an address of the bearer customer', async ({ request }) => {
    const customer = await registerShopCustomer(request);
    const input = buildAddress();
    const addressId = await createAddress(request, customer, input);

    const body = await readAddress(request, customer, addressId);

    expectNoErrors(body);

    const address = body.data.readCustomerAddressToken;

    expect(String(address.id).endsWith(String(addressId))).toBe(true);
    expect(address.addressId).toBe(addressId);
    expect(address.firstName).toBe(input.firstName);
    expect(address.lastName).toBe(input.lastName);
    expect(address.address1).toBe(input.address1);
    expect(address.city).toBe(input.city);
    expect(address.postcode).toBe(input.postcode);
  });

  test('should not return an address of another customer', async ({ request }) => {
    const owner = await registerShopCustomer(request);
    const addressId = await createAddress(request, owner, buildAddress());
    const intruder = await registerShopCustomer(request);

    const body = await readAddress(request, intruder, addressId);

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ADDRESS_NOT_FOUND);
    expect(body.data?.readCustomerAddressToken ?? null).toBeNull();
  });

  test('should refuse to read an address without a bearer token', async ({ request }) => {
    const owner = await registerShopCustomer(request);
    const addressId = await createAddress(request, owner, buildAddress());

    const body = await runGraphQL(request, SHOP_CART_QUERIES.readCustomerAddressToken, { id: String(addressId) });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.readCustomerAddressToken ?? null).toBeNull();
  });

  test('should refuse to read an address with a guest cart token', async ({ request }) => {
    const owner = await registerShopCustomer(request);
    const addressId = await createAddress(request, owner, buildAddress());
    const guest = await createGuestCart(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.readCustomerAddressToken,
      { id: String(addressId) },
      bearer(guest.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ADDRESS_INVALID_TOKEN);
    expect(body.data?.readCustomerAddressToken ?? null).toBeNull();
  });
});

test.describe('shop create delete customer address token graphql api', () => {
  test('should delete an address of the bearer customer', async ({ request }) => {
    const customer = await registerShopCustomer(request);
    const input = buildAddress();
    const addressId = await createAddress(request, customer, input);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createDeleteCustomerAddressToken,
      { id: String(addressId), addressId },
      bearer(customer.token)
    );

    expectNoErrors(body);

    const deleted = body.data.createDeleteCustomerAddressToken.customerAddressToken;

    expect(Number(deleted.addressId)).toBe(addressId);
    expect(deleted.firstName).toBe(input.firstName);
    expect(deleted.city).toBe(input.city);

    const lookup = await readAddress(request, customer, addressId);

    expect(graphQLErrorMessages(lookup)).toContain(SHOP_CART_MESSAGES.ADDRESS_NOT_FOUND);
  });

  test('should not delete an address of another customer', async ({ request }) => {
    const owner = await registerShopCustomer(request);
    const addressId = await createAddress(request, owner, buildAddress());
    const intruder = await registerShopCustomer(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createDeleteCustomerAddressToken,
      { id: String(addressId), addressId },
      bearer(intruder.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ADDRESS_NOT_FOUND);

    const saved = await readAddress(request, owner, addressId);

    expectNoErrors(saved);
    expect(saved.data.readCustomerAddressToken.addressId).toBe(addressId);
  });

  test('should refuse to delete an address without a bearer token', async ({ request }) => {
    const owner = await registerShopCustomer(request);
    const addressId = await createAddress(request, owner, buildAddress());

    const body = await runGraphQL(request, SHOP_CART_QUERIES.createDeleteCustomerAddressToken, {
      id: String(addressId),
      addressId,
    });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);

    const saved = await readAddress(request, owner, addressId);

    expectNoErrors(saved);
    expect(saved.data.readCustomerAddressToken.addressId).toBe(addressId);
  });
});
