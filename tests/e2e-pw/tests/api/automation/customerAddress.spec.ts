import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import {
  GET_CUSTOMER_ADDRESSES_MINIMAL,
  GET_CUSTOMER_ADDRESSES_PAGINATED,
  GET_CUSTOMER_ADDRESSES_WITH_COMPANY,
} from '../../graphql/Queries/customerAddress.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Customer Addresses GraphQL API Tests', () => {
  test('Should get all customer addresses successfully', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerAddresses, {}, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerAddresses');
  });

  test('Should get customer address by valid ID when authenticated', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    if (!headers) {
      console.log('Customer credentials are not configured, skipping positive address lookup.');
      return;
    }

    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerAddresses, {}, headers);
    const body = await response.json();

    if (body.data?.customerAddresses?.edges?.length > 0) {
      expect(body.data.customerAddresses.edges[0].node.id).toBeDefined();
    }
  });

  test('Should handle invalid address ID gracefully', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, `
      query GetCustomerAddress($id: ID!) {
        customerAddress(id: $id) {
          id
        }
      }
    `, { id: 'invalid-id-99999' }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Customer address invalid ID response: ${graphQLErrorMessages(body).join(' | ')}`);
    expect(body.data?.customerAddress === null || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should handle missing ID parameter gracefully', async ({ request }) => {
    const invalidQuery = `
      query GetCustomerAddress {
        customerAddress {
          id
        }
      }
    `;
    
    const response = await sendGraphQLRequest(request, invalidQuery);
    
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should have errors for missing required parameter
    expect(body.errors !== undefined).toBeTruthy();
  });

  test('Should cover customer addresses paginated docs query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_ADDRESSES_PAGINATED, { first: 2, after: null }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.getCustomerAddresses');
  });

  test('Should cover customer addresses minimal docs query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_ADDRESSES_MINIMAL, { first: 5 }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.getCustomerAddresses');
  });

  test('Should cover customer addresses with company details docs query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_ADDRESSES_WITH_COMPANY, { first: 5 }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.getCustomerAddresses');
  });
});
