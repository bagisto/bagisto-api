import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess } from '../../graphql/helpers/testSupport';

const INVALID_ID_FORMAT = /invalid[- ]id[- ]format/i;

test.describe('cms pages graphql api tests', () => {
  test('should get all cms pages successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPages, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectConnection(body, 'data.pages');
  });

  test('should return cms pages with pagination', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPages, { first: 5 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const pages = expectConnection(body, 'data.pages');
    expect(typeof pages.pageInfo.hasNextPage).toBe('boolean');
  });

  test('should get cms page by valid id', async ({ request }) => {
    const allResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPages, { first: 1 });
    expect(allResponse.status()).toBe(200);
    const allBody = await allResponse.json();
    const pages = expectConnection(allBody, 'data.pages');
    expect(pages.edges.length, 'the store seeds cms pages such as about-us').toBeGreaterThan(0);

    const listed = pages.edges[0].node;
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPage, { id: listed.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.page');
    expect(body.data.page.id).toBe(listed.id);
    expect(body.data.page._id).toBe(listed._id);
  });

  test('should refuse a cms page id that is neither numeric nor an iri', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPage, { id: 'invalid-id-99999' });
    expect(response.status()).toBe(200);
    const body = await response.json();

    expect(body.data?.page ?? null).toBeNull();
    expect(body.errors).toHaveLength(1);
    expect(body.errors[0].message).toMatch(INVALID_ID_FORMAT);
  });

  test('should reject the page query when the required id is missing', async ({ request }) => {
    const invalidQuery = `
      query getCmsPageDetail {
        page {
          id
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data ?? null).toBeNull();
    expect(body.errors[0].message).toBe('Field "page" argument "id" of type "ID!" is required but not provided.');
  });
});
