import { test, expect, APIRequestContext } from '@playwright/test';
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
import { expectConnection, graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { env } from '../../config/env';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../fixtures/e2eFixtures';

const UNAUTHENTICATED = 'Unauthenticated. Please login to perform this action';
const ADMIN_INVOICE_STATE = 'paid';

const LOGIN = `
  mutation createCustomerLogin($email: String!, $password: String!) {
    createCustomerLogin(input: { email: $email, password: $password }) {
      customerLogin {
        token
        success
      }
    }
  }
`;

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

async function customerHeaders(request: APIRequestContext, customer: E2eCustomerFixture): Promise<Record<string, string>> {
  const body = await gql(request, LOGIN, { email: customer.email, password: customer.password });
  expect(body.errors).toBeUndefined();
  expect(body.data.createCustomerLogin.customerLogin.success).toBe(true);

  return { Authorization: `Bearer ${body.data.createCustomerLogin.customerLogin.token}` };
}

function nodeIds(connection: any): number[] {
  return connection.edges.map((edge: any) => Number(edge.node._id));
}

function invoicePdfPath(invoiceId: number): string {
  return `/api/shop/customer-invoices/${invoiceId}/pdf`;
}

test.describe('customer invoice graphql api', () => {
  test('should list the invoice of the customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES, { first: 50 }, headers),
      'data.customerInvoices'
    );

    const invoice = connection.edges.find((edge: any) => edge.node._id === fx.order!.invoiceId)?.node;
    expect(invoice, `invoice ${fx.order!.invoiceId} is not listed`).toBeTruthy();
    expect(invoice.state).toBe(ADMIN_INVOICE_STATE);
    expect(invoice.downloadUrl.endsWith(invoicePdfPath(fx.order!.invoiceId))).toBe(true);
  });

  test('should list no invoices for a customer without orders', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES, { first: 5 }, headers),
      'data.customerInvoices'
    );

    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('should filter the invoices by state', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const paid = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES_BY_STATE, { first: 50, state: ADMIN_INVOICE_STATE }, headers),
      'data.customerInvoices'
    );
    expect(nodeIds(paid)).toContain(fx.order!.invoiceId);
    for (const edge of paid.edges) {
      expect(edge.node.state).toBe(ADMIN_INVOICE_STATE);
    }

    const refunded = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES_BY_STATE, { first: 50, state: 'refunded' }, headers),
      'data.customerInvoices'
    );
    expect(nodeIds(refunded)).not.toContain(fx.order!.invoiceId);
  });

  test('should page through the invoices', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const firstPage = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES_PAGINATED, { first: 1, after: null }, headers),
      'data.customerInvoices'
    );

    expect(firstPage.edges).toHaveLength(1);
    expect(firstPage.pageInfo.hasNextPage).toBe(firstPage.totalCount > 1);

    if (firstPage.totalCount > 1) {
      const secondPage = expectConnection(
        await gql(request, GET_CUSTOMER_INVOICES_PAGINATED, { first: 1, after: firstPage.pageInfo.endCursor }, headers),
        'data.customerInvoices'
      );
      expect(secondPage.edges).toHaveLength(1);
      expect(secondPage.edges[0].node._id).not.toBe(firstPage.edges[0].node._id);
    }
  });

  test('should return an invoice by id with its items', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const body = await gql(
      request,
      GET_CUSTOMER_INVOICE,
      { id: `/api/shop/customer-invoices/${fx.order!.invoiceId}` },
      headers
    );

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    const invoice = body.data.customerInvoice;
    expect(invoice._id).toBe(fx.order!.invoiceId);
    expect(invoice.state).toBe(ADMIN_INVOICE_STATE);
    expect(invoice.items.edges.length).toBeGreaterThan(0);
    for (const edge of invoice.items.edges) {
      expect(typeof edge.node.sku).toBe('string');
      expect(edge.node.qty).toBeGreaterThan(0);
    }
  });

  test('should refuse an invoice of another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const headers = await getCustomerAuthHeaders(request);
    const id = String(fx.order!.invoiceId);
    const body = await gql(request, GET_CUSTOMER_INVOICE, { id }, headers);

    expect(body.data?.customerInvoice ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([`Customer invoice with ID "${id}" not found`]);
  });

  test('should filter the invoices by order with and without items', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const byOrder = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES_BY_ORDER, { first: 5, orderId: fx.order!.id }, headers),
      'data.customerInvoices'
    );
    expect(nodeIds(byOrder)).toEqual([fx.order!.invoiceId]);
    expect(byOrder.totalCount).toBe(1);

    const withItems = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES_BY_ORDER_WITH_ITEMS, { first: 1, orderId: fx.order!.id }, headers),
      'data.customerInvoices'
    );
    expect(withItems.edges).toHaveLength(1);
    const items = withItems.edges[0].node.items.edges.map((edge: any) => edge.node);
    expect(items.length).toBeGreaterThan(0);
    expect(items.map((item: any) => item.invoiceId)).toEqual(items.map(() => fx.order!.invoiceId));
    expect(items.map((item: any) => item.productId)).toContain(fx.order!.productId);
  });

  test('should list no invoices for an order the customer does not own', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const headers = await getCustomerAuthHeaders(request);
    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_INVOICES_BY_ORDER, { first: 5, orderId: fx.order!.id }, headers),
      'data.customerInvoices'
    );

    expect(connection.totalCount).toBe(0);
  });

  test('should download the invoice pdf from its download url', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const body = await gql(request, GET_CUSTOMER_INVOICE_DOWNLOAD_URL, { id: String(fx.order!.invoiceId) }, headers);

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    const pdfPath = invoicePdfPath(fx.order!.invoiceId);
    expect(body.data.customerInvoice.downloadUrl.endsWith(pdfPath)).toBe(true);

    const downloadResponse = await request.get(`${env.baseUrl}${pdfPath}`, {
      headers: { 'X-STOREFRONT-KEY': env.storefrontAccessKey, ...headers },
    });

    expect(downloadResponse.status()).toBe(200);
    expect(downloadResponse.headers()['content-type']).toContain('application/pdf');
  });

  test('should refuse the invoice list without a customer token', async ({ request }) => {
    const body = await gql(request, GET_CUSTOMER_INVOICES, { first: 5 });

    expect(body.data?.customerInvoices ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(UNAUTHENTICATED);
  });
});
