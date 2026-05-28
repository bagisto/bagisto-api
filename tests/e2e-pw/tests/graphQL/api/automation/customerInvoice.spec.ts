import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  GET_CUSTOMER_INVOICE,
  GET_CUSTOMER_INVOICE_DOWNLOAD_URL,
  GET_CUSTOMER_INVOICES,
  GET_CUSTOMER_INVOICES_BY_ORDER,
  GET_CUSTOMER_INVOICES_BY_ORDER_WITH_ITEMS,
  GET_CUSTOMER_INVOICES_BY_STATE,
  GET_CUSTOMER_INVOICES_PAGINATED,
} from '../../graphql/Queries/customerInvoice.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages, pick } from '../../graphql/helpers/testSupport';
import { env } from '../../config/env';

test.describe('Customer Invoice GraphQL API - Docs aligned', () => {
  test('Should cover customer invoices query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_INVOICES, { first: 5 }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerInvoices');
    console.log(`Customer invoices: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover customer invoices by state query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_INVOICES_BY_STATE,
      { first: 5, state: 'paid' },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerInvoices');
    console.log(`Customer invoices by state: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover customer invoices pagination query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_INVOICES_PAGINATED,
      { first: 2, after: null },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerInvoices');
    console.log(`Customer invoices paginated: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover single customer invoice query when data exists', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    if (!headers) {
      const response = await sendGraphQLRequest(request, GET_CUSTOMER_INVOICE, { id: '/api/shop/customer-invoices/1' });
      const body = await response.json();
      console.log(`Customer invoice auth response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(graphQLErrorMessages(body).length > 0 || body.data?.customerInvoice === null).toBeTruthy();
      return;
    }

    const invoicesResponse = await sendGraphQLRequest(request, GET_CUSTOMER_INVOICES, { first: 1 }, headers);
    const invoicesBody = await invoicesResponse.json();
    const invoiceNode = invoicesBody.data?.customerInvoices?.edges?.[0]?.node;

    if (!invoiceNode) {
      console.log('Authenticated customer has no invoices in this environment.');
      expect(true).toBeTruthy();
      return;
    }

    const invoiceId = invoiceNode.id ?? `/api/shop/customer-invoices/${invoiceNode._id}`;
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_INVOICE, { id: invoiceId }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();
    expect(body.data?.customerInvoice).toBeTruthy();
    console.log(`Customer invoice detail loaded for ${invoiceId}`);
  });

  test('Should cover customer invoice by order and by order with items queries', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const byOrderResponse = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_INVOICES_BY_ORDER,
      { first: 5, orderId: 1 },
      headers
    );
    expect(byOrderResponse.status()).toBe(200);
    const byOrderBody = await byOrderResponse.json();
    expectAuthAwareResult(byOrderBody, 'data.customerInvoices');
    console.log(`Customer invoices by order: ${graphQLErrorMessages(byOrderBody).join(' | ') || 'success'}`);

    const withItemsResponse = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_INVOICES_BY_ORDER_WITH_ITEMS,
      { first: 1, orderId: 1 },
      headers
    );
    expect(withItemsResponse.status()).toBe(200);
    const withItemsBody = await withItemsResponse.json();
    expectAuthAwareResult(withItemsBody, 'data.customerInvoices');
    console.log(`Customer invoices by order with items: ${graphQLErrorMessages(withItemsBody).join(' | ') || 'success'}`);
  });

  test('Should cover customer invoice download url query when data exists', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    if (!headers) {
      const response = await sendGraphQLRequest(request, GET_CUSTOMER_INVOICE_DOWNLOAD_URL, { id: '/api/shop/customer-invoices/1' });
      const body = await response.json();
      console.log(`Customer invoice download auth response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(graphQLErrorMessages(body).length > 0 || body.data?.customerInvoice === null).toBeTruthy();
      return;
    }

    const invoicesResponse = await sendGraphQLRequest(request, GET_CUSTOMER_INVOICES, { first: 1 }, headers);
    const invoicesBody = await invoicesResponse.json();
    const invoiceNode = invoicesBody.data?.customerInvoices?.edges?.[0]?.node;

    if (!invoiceNode) {
      console.log('Authenticated customer has no invoice to download.');
      expect(true).toBeTruthy();
      return;
    }

    const invoiceId = invoiceNode.id ?? `/api/shop/customer-invoices/${invoiceNode._id}`;
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_INVOICE_DOWNLOAD_URL, { id: invoiceId }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();

    const downloadUrl = pick(body, 'data.customerInvoice.downloadUrl');
    console.log(`Invoice download URL: ${downloadUrl ?? 'not returned'}`);

    if (downloadUrl) {
      const downloadResponse = await request.get(downloadUrl, {
        headers: {
          'X-STOREFRONT-KEY': env.storefrontAccessKey,
          ...headers,
        },
      });

      expect(downloadResponse.ok()).toBeTruthy();
      console.log(`Invoice download content-type: ${downloadResponse.headers()['content-type']}`);
    }
  });
});
