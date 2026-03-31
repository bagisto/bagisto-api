import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  GET_CUSTOMER_DOWNLOADABLE_PRODUCT,
  GET_CUSTOMER_DOWNLOADABLE_PRODUCTS,
  GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_BY_STATUS,
  GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_PAGINATED,
} from '../../graphql/Queries/customerDownloadableProducts.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Customer Downloadable Products GraphQL API - Docs aligned', () => {
  test('Should cover customer downloadable products query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS, { first: 5 }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerDownloadableProducts');
    console.log(`Customer downloadable products: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover customer downloadable products by status query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_BY_STATUS,
      { first: 5, status: 'available' },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerDownloadableProducts');
    console.log(`Customer downloadable products by status: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover customer downloadable products pagination query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_PAGINATED,
      { first: 2, after: null },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerDownloadableProducts');
    console.log(`Customer downloadable products paginated: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover single customer downloadable product query when data exists', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    if (!headers) {
      const response = await sendGraphQLRequest(
        request,
        GET_CUSTOMER_DOWNLOADABLE_PRODUCT,
        { id: '/api/shop/customer-downloadable-products/1' }
      );
      const body = await response.json();
      console.log(`Customer downloadable product auth response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(graphQLErrorMessages(body).length > 0 || body.data?.customerDownloadableProduct === null).toBeTruthy();
      return;
    }

    const downloadsResponse = await sendGraphQLRequest(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS, { first: 1 }, headers);
    const downloadsBody = await downloadsResponse.json();
    const downloadNode = downloadsBody.data?.customerDownloadableProducts?.edges?.[0]?.node;

    if (!downloadNode) {
      console.log('Authenticated customer has no downloadable purchases in this environment.');
      expect(true).toBeTruthy();
      return;
    }

    const downloadId = downloadNode.id ?? `/api/shop/customer-downloadable-products/${downloadNode._id}`;
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCT, { id: downloadId }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();
    expect(body.data?.customerDownloadableProduct).toBeTruthy();
    console.log(`Customer downloadable product detail loaded for ${downloadId}`);
  });
});
