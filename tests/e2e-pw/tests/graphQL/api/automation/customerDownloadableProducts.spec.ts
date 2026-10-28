import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  GET_CUSTOMER_DOWNLOADABLE_PRODUCT,
  GET_CUSTOMER_DOWNLOADABLE_PRODUCTS,
  GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_BY_STATUS,
  GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_PAGINATED,
} from '../../graphql/Queries/customerDownloadableProducts.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../fixtures/e2eFixtures';

const UNAUTHENTICATED = 'Unauthenticated. Please login to perform this action';

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

test.describe('customer downloadable products graphql api', () => {
  test('should list the downloadable purchase of the customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS, { first: 50 }, headers),
      'data.customerDownloadableProducts'
    );

    const purchase = connection.edges.find((edge: any) => edge.node._id === fx.downloadable!.purchasedLinkId)?.node;
    expect(purchase, `purchased link ${fx.downloadable!.purchasedLinkId} is not listed`).toBeTruthy();
    expect(purchase.order._id).toBe(fx.downloadable!.orderId);
    expect(typeof purchase.productName).toBe('string');
    expect(typeof purchase.status).toBe('string');
  });

  test('should list no downloadable purchases for a customer who bought none', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS, { first: 5 }, headers),
      'data.customerDownloadableProducts'
    );

    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('should filter the downloadable purchases by status', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const detail = await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCT, { id: String(fx.downloadable!.purchasedLinkId) }, headers);
    expect(detail.errors, graphQLErrorMessages(detail).join(' | ')).toBeUndefined();
    const status = detail.data.customerDownloadableProduct.status;

    const matching = expectConnection(
      await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_BY_STATUS, { first: 50, status }, headers),
      'data.customerDownloadableProducts'
    );
    expect(nodeIds(matching)).toContain(fx.downloadable!.purchasedLinkId);
    for (const edge of matching.edges) {
      expect(edge.node.status).toBe(status);
    }

    const otherStatus = status === 'pending' ? 'expired' : 'pending';
    const other = expectConnection(
      await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_BY_STATUS, { first: 50, status: otherStatus }, headers),
      'data.customerDownloadableProducts'
    );
    expect(nodeIds(other)).not.toContain(fx.downloadable!.purchasedLinkId);
  });

  test('should page through the downloadable purchases', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const firstPage = expectConnection(
      await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_PAGINATED, { first: 1, after: null }, headers),
      'data.customerDownloadableProducts'
    );

    expect(firstPage.edges).toHaveLength(1);
    expect(firstPage.pageInfo.hasNextPage).toBe(firstPage.totalCount > 1);

    if (firstPage.totalCount > 1) {
      const secondPage = expectConnection(
        await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_PAGINATED, { first: 1, after: firstPage.pageInfo.endCursor }, headers),
        'data.customerDownloadableProducts'
      );
      expect(secondPage.edges).toHaveLength(1);
      expect(secondPage.edges[0].node._id).not.toBe(firstPage.edges[0].node._id);
    }
  });

  test('should return a downloadable purchase by id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const body = await gql(
      request,
      GET_CUSTOMER_DOWNLOADABLE_PRODUCT,
      { id: `/api/shop/customer-downloadable-products/${fx.downloadable!.purchasedLinkId}` },
      headers
    );

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    expect(body.data.customerDownloadableProduct._id).toBe(fx.downloadable!.purchasedLinkId);
    expect(body.data.customerDownloadableProduct.order._id).toBe(fx.downloadable!.orderId);
  });

  test('should refuse a downloadable purchase of another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));

    const headers = await getCustomerAuthHeaders(request);
    const id = String(fx.downloadable!.purchasedLinkId);
    const body = await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCT, { id }, headers);

    expect(body.data?.customerDownloadableProduct ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([`Customer downloadable product with ID "${id}" not found`]);
  });

  test('should refuse the downloadable purchases without a customer token', async ({ request }) => {
    const body = await gql(request, GET_CUSTOMER_DOWNLOADABLE_PRODUCTS, { first: 5 });

    expect(body.data?.customerDownloadableProducts ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(UNAUTHENTICATED);
  });
});
