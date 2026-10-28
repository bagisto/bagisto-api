import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CUSTOMER_CREATE } from '../../../../graphql/Queries/admin/customers/customers.queries';

test.describe.configure({ timeout: 120_000 });

const ADDRESS_NOT_FOUND = 'Address not found.';

const ADDRESS_FIELDS = `
  _id
  customerId
  firstName
  lastName
  companyName
  address
  city
  state
  country
  postcode
  phone
  defaultAddress
`;

const LIST_ADDRESSES = `
  query adminCustomerAddresses($customerId: Int!) {
    adminCustomerAddresses(customerId: $customerId) {
      edges {
        node {
          _id
          firstName
          city
          companyName
        }
      }
    }
  }
`;

const ADDRESS_DETAIL = `
  query adminCustomerAddress($customerId: Int!, $id: ID!) {
    adminCustomerAddress(customerId: $customerId, id: $id) {
      ${ADDRESS_FIELDS}
    }
  }
`;

const CREATE_ADDRESS = `
  mutation createAdminCustomerAddress(
    $customerId: Int!
    $firstName: String!
    $lastName: String!
    $companyName: String
    $address: String!
    $city: String!
    $state: String!
    $country: String!
    $postcode: String!
    $phone: String!
  ) {
    createAdminCustomerAddress(
      input: {
        customerId: $customerId
        firstName: $firstName
        lastName: $lastName
        companyName: $companyName
        address: $address
        city: $city
        state: $state
        country: $country
        postcode: $postcode
        phone: $phone
      }
    ) {
      adminCustomerAddress {
        ${ADDRESS_FIELDS}
      }
    }
  }
`;

const UPDATE_ADDRESS = `
  mutation updateAdminCustomerAddress($id: ID!, $customerId: Int, $city: String) {
    updateAdminCustomerAddress(input: { id: $id, customerId: $customerId, city: $city }) {
      adminCustomerAddress {
        _id
        city
        companyName
      }
    }
  }
`;

const DELETE_ADDRESS = `
  mutation deleteAdminCustomerAddress($id: ID!) {
    deleteAdminCustomerAddress(input: { id: $id }) {
      adminCustomerAddress {
        _id
        city
      }
    }
  }
`;

const DELETE_CUSTOMER = `
  mutation deleteAdminCustomer($id: ID!) {
    deleteAdminCustomer(input: { id: $id }) {
      adminCustomer {
        _id
      }
    }
  }
`;

let customerId: number;

function addressIri(ownerId: number, addressId: number): string {
  return `/api/admin/customers/${ownerId}/addresses/${addressId}`;
}

function addressVariables(ownerId: number, overrides: Record<string, any> = {}): Record<string, any> {
  return {
    customerId: ownerId,
    firstName: 'Addr',
    lastName: 'Owner',
    companyName: 'ACME Inc',
    address: '1 Main Street',
    city: 'NYC',
    state: 'NY',
    country: 'US',
    postcode: '10001',
    phone: '5551234567',
    ...overrides,
  };
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

function expectGraphQLError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(body.errors?.[0]?.message).toBe(message);
}

async function createCustomer(request: APIRequestContext): Promise<number> {
  const body = await gql(request, ADMIN_CUSTOMER_CREATE, {
    firstName: 'Addr',
    lastName: 'Owner',
    email: `e2e_gql_addr_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
    customerGroupId: 2,
    channelId: 1,
    sendPassword: false,
    password: 'e2epass123',
  });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.createAdminCustomer.adminCustomer._id;
}

async function createAddress(request: APIRequestContext, overrides: Record<string, any> = {}): Promise<any> {
  const variables = addressVariables(customerId, overrides);
  const body = await gql(request, CREATE_ADDRESS, variables);
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const created = body.data.createAdminCustomerAddress.adminCustomerAddress;
  expect(created._id).toBeGreaterThan(0);
  expect(created.customerId).toBe(customerId);
  expect(created.firstName).toBe(variables.firstName);
  expect(created.companyName).toBe(variables.companyName);
  expect(created.city).toBe(variables.city);
  expect(created.country).toBe(variables.country);
  expect(created.defaultAddress).toBe(false);

  return created;
}

async function listedAddressIds(request: APIRequestContext): Promise<number[]> {
  const body = await gql(request, LIST_ADDRESSES, { customerId });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomerAddresses.edges.map((edge: any) => edge.node._id);
}

test.describe('admin customer addresses graphql api', () => {
  test.beforeEach(async ({ request }) => {
    customerId = await createCustomer(request);
  });

  test.afterEach(async ({ request }) => {
    await sendAdminGraphQLRequest(request, DELETE_CUSTOMER, { id: `/api/admin/customers/${customerId}` });
  });

  test('list of a customer without addresses is empty', async ({ request }) => {
    expect(await listedAddressIds(request)).toEqual([]);
  });

  test('list returns the address the test created', async ({ request }) => {
    const created = await createAddress(request);

    const body = await gql(request, LIST_ADDRESSES, { customerId });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.adminCustomerAddresses.edges.map((edge: any) => edge.node)).toEqual([
      { _id: created._id, firstName: 'Addr', city: 'NYC', companyName: 'ACME Inc' },
    ]);
  });

  test('create detail and delete round trip', async ({ request }) => {
    const created = await createAddress(request);

    const detail = await gql(request, ADDRESS_DETAIL, { customerId, id: addressIri(customerId, created._id) });
    expect(detail.errors, JSON.stringify(detail.errors)).toBeUndefined();
    expect(detail.data.adminCustomerAddress).toEqual(created);

    const removal = await gql(request, DELETE_ADDRESS, { id: addressIri(customerId, created._id) });
    expect(removal.errors, JSON.stringify(removal.errors)).toBeUndefined();
    expect(removal.data.deleteAdminCustomerAddress.adminCustomerAddress._id).toBe(created._id);

    expect(await listedAddressIds(request)).toEqual([]);
    expectGraphQLError(
      await gql(request, ADDRESS_DETAIL, { customerId, id: addressIri(customerId, created._id) }),
      'adminCustomerAddress',
      ADDRESS_NOT_FOUND
    );
  });

  test('update persists the new city', async ({ request }) => {
    const created = await createAddress(request);
    const newCity = `City${Date.now()}`;

    const body = await gql(request, UPDATE_ADDRESS, {
      id: addressIri(customerId, created._id),
      customerId,
      city: newCity,
    });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.updateAdminCustomerAddress.adminCustomerAddress).toEqual({
      _id: created._id,
      city: newCity,
      companyName: 'ACME Inc',
    });

    const detail = await gql(request, ADDRESS_DETAIL, { customerId, id: addressIri(customerId, created._id) });
    expect(detail.data.adminCustomerAddress.city).toBe(newCity);
    expect(detail.data.adminCustomerAddress.address).toBe('1 Main Street');
  });

  test('create rejects blank required fields', async ({ request }) => {
    const body = await gql(request, CREATE_ADDRESS, addressVariables(customerId, {
      firstName: '',
      lastName: '',
      address: '',
      city: '',
      state: '',
      country: '',
      postcode: '',
      phone: '',
    }));

    expectGraphQLError(body, 'createAdminCustomerAddress', 'The first name field is required.');
    expect(await listedAddressIds(request)).toEqual([]);
  });

  test('detail of an unknown address reports address not found', async ({ request }) => {
    const body = await gql(request, ADDRESS_DETAIL, { customerId, id: addressIri(customerId, 99999999) });

    expectGraphQLError(body, 'adminCustomerAddress', ADDRESS_NOT_FOUND);
  });
});
