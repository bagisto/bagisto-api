import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_ACCOUNT_QUERIES as Q } from '../../../graphql/Queries/coverage/shopAccount.queries';
import { sendRestRequest } from '../../../../restAPI/rest/helpers/restClient';
import { SHOP_ACCOUNT } from '../../../../restAPI/rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

async function restIds(request: APIRequestContext, endpoint: string): Promise<number[]> {
  const response = await sendRestRequest(request, endpoint);
  expect(response.status()).toBe(200);

  const body = await response.json();
  expect(Array.isArray(body)).toBe(true);

  return body.map((row: any) => Number(row.id));
}

test.describe('shop account product downloadable links graphql api', () => {
  test('should return a downloadable link by id with its product', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));

    const linkIds = await restIds(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINKS_OF(fx.downloadable!.productId));
    expect(linkIds.length).toBeGreaterThan(0);

    const body = await gql(request, Q.productDownloadableLink, { id: `/api/shop/product-downloadable-links/${linkIds[0]}` });

    expect(body.errors).toBeUndefined();
    const link = body.data.productDownloadableLink;
    expect(link._id).toBe(linkIds[0]);
    expect(typeof link.type).toBe('string');
    expect(typeof link.price).toBe('string');
    expect(link.product._id).toBe(fx.downloadable!.productId);
  });

  test('should page through downloadable links', async ({ request }) => {
    const first = await gql(request, Q.productDownloadableLinks, { first: 2 });

    expect(first.errors).toBeUndefined();
    const connection = first.data.productDownloadableLinks;
    expect(connection.edges.length).toBeLessThanOrEqual(2);
    for (const edge of connection.edges) {
      expect(typeof edge.node._id).toBe('number');
      expect(typeof edge.node.type).toBe('string');
    }

    test.skip(!connection.pageInfo.hasNextPage, 'Only one page of downloadable links exists.');

    const next = await gql(request, Q.productDownloadableLinks, { first: 2, after: connection.pageInfo.endCursor });
    expect(next.errors).toBeUndefined();

    const firstIds = connection.edges.map((edge: any) => edge.node._id);
    for (const edge of next.data.productDownloadableLinks.edges) {
      expect(firstIds).not.toContain(edge.node._id);
    }
  });

  test('should report a downloadable link that does not exist', async ({ request }) => {
    const body = await gql(request, Q.productDownloadableLink, { id: '/api/shop/product-downloadable-links/999999999' });

    expect(body.data?.productDownloadableLink ?? null).toBeNull();
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });
});

test.describe('shop account product downloadable samples graphql api', () => {
  test('should return a downloadable sample by id with its product', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));

    const sampleIds = await restIds(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLES_OF(fx.downloadable!.productId));
    test.skip(sampleIds.length === 0, 'The fixture downloadable product has no samples.');

    const body = await gql(request, Q.productDownloadableSample, { id: `/api/shop/product-downloadable-samples/${sampleIds[0]}` });

    expect(body.errors).toBeUndefined();
    const sample = body.data.productDownloadableSample;
    expect(sample._id).toBe(sampleIds[0]);
    expect(typeof sample.type).toBe('string');
    expect(sample.product._id).toBe(fx.downloadable!.productId);
  });

  test('should page through downloadable samples', async ({ request }) => {
    const first = await gql(request, Q.productDownloadableSamples, { first: 2 });

    expect(first.errors).toBeUndefined();
    const connection = first.data.productDownloadableSamples;
    expect(connection.edges.length).toBeLessThanOrEqual(2);
    for (const edge of connection.edges) {
      expect(typeof edge.node._id).toBe('number');
      expect(typeof edge.node.type).toBe('string');
    }

    test.skip(!connection.pageInfo.hasNextPage, 'Only one page of downloadable samples exists.');

    const next = await gql(request, Q.productDownloadableSamples, { first: 2, after: connection.pageInfo.endCursor });
    expect(next.errors).toBeUndefined();

    const firstIds = connection.edges.map((edge: any) => edge.node._id);
    for (const edge of next.data.productDownloadableSamples.edges) {
      expect(firstIds).not.toContain(edge.node._id);
    }
  });

  test('should report a downloadable sample that does not exist', async ({ request }) => {
    const body = await gql(request, Q.productDownloadableSample, { id: '/api/shop/product-downloadable-samples/999999999' });

    expect(body.data?.productDownloadableSample ?? null).toBeNull();
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });
});
