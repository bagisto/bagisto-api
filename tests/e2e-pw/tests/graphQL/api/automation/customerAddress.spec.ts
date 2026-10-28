import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_ADD_UPDATE_CUSTOMER_ADDRESS,
  CREATE_ADD_UPDATE_CUSTOMER_ADDRESS_WITH_COMPANY,
  GET_CUSTOMER_ADDRESSES,
  GET_CUSTOMER_ADDRESSES_MINIMAL,
  GET_CUSTOMER_ADDRESSES_PAGINATED,
  GET_CUSTOMER_ADDRESSES_WITH_COMPANY,
} from '../../graphql/Queries/customerAddress.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

const INVALID_ID_FORMAT = /invalid[- ]id[- ]format/i;
const ADDRESS_NOT_FOUND = 'Address not found or does not belong to this customer';
const ADDRESS_AUTH_REQUIRED = 'Authentication token is required';

const READ_CUSTOMER_ADDRESS = `
  query GetCustomerAddress($id: ID!) {
    customerAddress(id: $id) {
      _id
      firstName
      lastName
      city
      country
      postcode
    }
  }
`;

const DELETE_CUSTOMER_ADDRESS_RETURNING_ID = `
  mutation deleteCustomerAddress($input: createDeleteCustomerAddressInput!) {
    createDeleteCustomerAddress(input: $input) {
      deleteCustomerAddress {
        addressId
        city
      }
    }
  }
`;

function addressInput(overrides: Record<string, any> = {}): Record<string, any> {
  return {
    firstName: 'John',
    lastName: 'Doe',
    email: `playwright.addr+${Date.now()}-${Math.floor(Math.random() * 100000)}@example.com`,
    phone: '918888888888',
    address1: '123 Main Street',
    address2: 'NY',
    postcode: '10001',
    city: 'New York',
    state: 'NY',
    country: 'US',
    ...overrides,
  };
}

async function gql(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any>,
  headers: Record<string, string> = {}
): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, headers);
  expect(response.status()).toBe(200);

  return response.json();
}

async function createAddress(
  request: APIRequestContext,
  headers: Record<string, string>,
  overrides: Record<string, any> = {}
): Promise<any> {
  const body = await gql(request, CREATE_ADD_UPDATE_CUSTOMER_ADDRESS_WITH_COMPANY, { input: addressInput(overrides) }, headers);
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();

  const created = body.data.createAddUpdateCustomerAddress.addUpdateCustomerAddress;
  expect(created.addressId).toBeGreaterThan(0);

  return created;
}

test.describe('customer addresses graphql api tests', () => {
  test('should list the address the customer created', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const created = await createAddress(request, headers, { city: 'Listville' });

    const body = await gql(request, GET_CUSTOMER_ADDRESSES, { first: 10 }, headers);
    const connection = expectConnection(body, 'data.getCustomerAddresses');

    expect(connection.totalCount).toBe(1);
    expect(connection.edges).toHaveLength(1);
    expect(connection.edges[0].node._id).toBe(created.addressId);
    expect(connection.edges[0].node.city).toBe('Listville');
    expect(connection.edges[0].node.addressType).toBe('customer');
  });

  test('should get customer address by valid id when authenticated', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const created = await createAddress(request, headers, { city: 'Lookup City' });

    const body = await gql(request, READ_CUSTOMER_ADDRESS, { id: String(created.addressId) }, headers);
    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    expect(body.data.customerAddress._id).toBe(created.addressId);
    expect(body.data.customerAddress.city).toBe('Lookup City');
    expect(body.data.customerAddress.country).toBe('US');
  });

  test('should refuse an address id that is neither numeric nor an iri', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const body = await gql(request, READ_CUSTOMER_ADDRESS, { id: 'invalid-id-99999' }, headers);

    expect(body.data?.customerAddress ?? null).toBeNull();
    expect(body.errors).toHaveLength(1);
    expect(body.errors[0].message).toMatch(INVALID_ID_FORMAT);
  });

  test('should refuse an address another customer owns', async ({ request }) => {
    const ownerHeaders = await getCustomerAuthHeaders(request);
    const created = await createAddress(request, ownerHeaders);
    const otherHeaders = await getCustomerAuthHeaders(request);

    const body = await gql(request, READ_CUSTOMER_ADDRESS, { id: String(created.addressId) }, otherHeaders);

    expect(body.data?.customerAddress ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([ADDRESS_NOT_FOUND]);
  });

  test('should reject the address query when the required id is missing', async ({ request }) => {
    const invalidQuery = `
      query GetCustomerAddress {
        customerAddress {
          id
        }
      }
    `;

    const body = await gql(request, invalidQuery, {});

    expect(body.data ?? null).toBeNull();
    expect(body.errors[0].message).toBe('Field "customerAddress" argument "id" of type "ID!" is required but not provided.');
  });

  test('should cover customer addresses paginated docs query', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const first = await createAddress(request, headers, { city: 'Page One' });
    const second = await createAddress(request, headers, { city: 'Page Two' });

    const firstPage = expectConnection(
      await gql(request, GET_CUSTOMER_ADDRESSES_PAGINATED, { first: 1, after: null }, headers),
      'data.getCustomerAddresses'
    );
    expect(firstPage.totalCount).toBe(2);
    expect(firstPage.edges).toHaveLength(1);
    expect(firstPage.pageInfo.hasNextPage).toBe(true);

    const secondPage = expectConnection(
      await gql(request, GET_CUSTOMER_ADDRESSES_PAGINATED, { first: 1, after: firstPage.pageInfo.endCursor }, headers),
      'data.getCustomerAddresses'
    );
    expect(secondPage.edges).toHaveLength(1);
    expect(secondPage.pageInfo.hasNextPage).toBe(false);

    const seen = [firstPage.edges[0].node._id, secondPage.edges[0].node._id].sort();
    expect(seen).toEqual([first.addressId, second.addressId].sort());
  });

  test('should cover customer addresses minimal docs query', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    await createAddress(request, headers, { city: 'Minimal City', postcode: '10002' });

    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_ADDRESSES_MINIMAL, { first: 5 }, headers),
      'data.getCustomerAddresses'
    );
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node.city).toBe('Minimal City');
    expect(connection.edges[0].node.postcode).toBe('10002');
    expect(connection.edges[0].node.country).toBe('US');
  });

  test('should cover customer addresses with company details docs query', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const company = `Acme List ${Date.now()}`;
    await createAddress(request, headers, { companyName: company, vatId: 'VAT123' });

    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_ADDRESSES_WITH_COMPANY, { first: 5 }, headers),
      'data.getCustomerAddresses'
    );
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node.companyName).toBe(company);
    expect(connection.edges[0].node.vatId).toBe('VAT123');
  });

  test('should create, update and delete a customer address', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const input = addressInput();

    const createBody = await gql(request, CREATE_ADD_UPDATE_CUSTOMER_ADDRESS, { input }, headers);
    expect(createBody.errors, graphQLErrorMessages(createBody).join(' | ')).toBeUndefined();
    const created = createBody.data.createAddUpdateCustomerAddress.addUpdateCustomerAddress;
    expect(created.addressId).toBeGreaterThan(0);
    expect(created.city).toBe('New York');
    expect(created.address1).toBe('123 Main Street');
    expect(created.address2).toBe('NY');

    const updateBody = await gql(
      request,
      CREATE_ADD_UPDATE_CUSTOMER_ADDRESS,
      { input: { ...input, addressId: created.addressId, city: 'Boston' } },
      headers
    );
    expect(updateBody.errors, graphQLErrorMessages(updateBody).join(' | ')).toBeUndefined();
    const updated = updateBody.data.createAddUpdateCustomerAddress.addUpdateCustomerAddress;
    expect(updated.addressId).toBe(created.addressId);
    expect(updated.city).toBe('Boston');

    const deleteBody = await gql(
      request,
      DELETE_CUSTOMER_ADDRESS_RETURNING_ID,
      { input: { addressId: created.addressId } },
      headers
    );
    expect(deleteBody.errors, graphQLErrorMessages(deleteBody).join(' | ')).toBeUndefined();
    expect(deleteBody.data.createDeleteCustomerAddress.deleteCustomerAddress.addressId).toBe(created.addressId);
    expect(deleteBody.data.createDeleteCustomerAddress.deleteCustomerAddress.city).toBe('Boston');

    const listBody = await gql(request, GET_CUSTOMER_ADDRESSES, { first: 10 }, headers);
    expect(expectConnection(listBody, 'data.getCustomerAddresses').totalCount).toBe(0);
  });

  test('should refuse to delete an address that is not the customer\'s', async ({ request }) => {
    const ownerHeaders = await getCustomerAuthHeaders(request);
    const created = await createAddress(request, ownerHeaders);
    const otherHeaders = await getCustomerAuthHeaders(request);

    const body = await gql(
      request,
      DELETE_CUSTOMER_ADDRESS_RETURNING_ID,
      { input: { addressId: created.addressId } },
      otherHeaders
    );
    expect(body.data?.createDeleteCustomerAddress ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([ADDRESS_NOT_FOUND]);

    const ownerList = await gql(request, GET_CUSTOMER_ADDRESSES, { first: 10 }, ownerHeaders);
    expect(expectConnection(ownerList, 'data.getCustomerAddresses').totalCount).toBe(1);
  });

  test('should refuse an address mutation without a customer token', async ({ request }) => {
    const body = await gql(request, CREATE_ADD_UPDATE_CUSTOMER_ADDRESS, { input: addressInput() });

    expect(body.data?.createAddUpdateCustomerAddress ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([ADDRESS_AUTH_REQUIRED]);
  });
});

test.describe('customer address company name regression', () => {
  test.slow();

  test('should round-trip company name on create address', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const company = `Acme Corp ${Date.now()}`;

    const response = await sendGraphQLRequest(
      request,
      CREATE_ADD_UPDATE_CUSTOMER_ADDRESS_WITH_COMPANY,
      { input: addressInput({ companyName: company }) },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `create address errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const payload = body.data?.createAddUpdateCustomerAddress?.addUpdateCustomerAddress;
    expect(payload, `address create returned no payload: ${JSON.stringify(body)}`).toBeTruthy();
    expect(payload.companyName).toBe(company);
  });

  test('should round-trip company name on update address', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const originalCompany = `Old Co ${Date.now()}`;
    const newCompany = `New Co ${Date.now()}`;
    const baseInput = addressInput({ companyName: originalCompany });

    const createResponse = await sendGraphQLRequest(
      request,
      CREATE_ADD_UPDATE_CUSTOMER_ADDRESS_WITH_COMPANY,
      { input: baseInput },
      headers
    );
    expect(createResponse.status()).toBe(200);
    const createBody = await createResponse.json();
    const created = createBody.data?.createAddUpdateCustomerAddress?.addUpdateCustomerAddress;
    expect(created, `address create failed: ${JSON.stringify(createBody)}`).toBeTruthy();
    expect(created.companyName).toBe(originalCompany);

    const addressId = created.addressId;
    expect(addressId).toBeGreaterThan(0);

    const updateResponse = await sendGraphQLRequest(
      request,
      CREATE_ADD_UPDATE_CUSTOMER_ADDRESS_WITH_COMPANY,
      { input: { ...baseInput, addressId, companyName: newCompany } },
      headers
    );
    expect(updateResponse.status()).toBe(200);
    const updateBody = await updateResponse.json();
    expect(updateBody.errors, `update errored: ${graphQLErrorMessages(updateBody).join(' | ')}`).toBeUndefined();

    const updated = updateBody.data?.createAddUpdateCustomerAddress?.addUpdateCustomerAddress;
    expect(updated, `address update returned no payload: ${JSON.stringify(updateBody)}`).toBeTruthy();
    expect(updated.companyName).toBe(newCompany);
  });
});
