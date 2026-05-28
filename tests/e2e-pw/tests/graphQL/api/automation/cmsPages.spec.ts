import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('CMS Pages GraphQL API Tests', () => {
  test('Should get all CMS pages successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPages, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectConnection(body, 'data.pages');
  });

  test('Should return CMS pages with pagination', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPages, { first: 5 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const pages = expectConnection(body, 'data.pages');
    expect(typeof pages.pageInfo.hasNextPage).toBe('boolean');
  });

  test('Should get CMS page by valid ID', async ({ request }) => {
    const allResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPages, { first: 1 });
    const allBody = await allResponse.json();

    if (allBody.data?.pages?.edges?.length > 0) {
      const pageId = allBody.data.pages.edges[0].node.id;
      const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPage, { id: pageId });
      expect(response.status()).toBe(200);
      const body = await response.json();
      expectGraphQLSuccess(body, 'data.page');
    } else {
      console.log('No CMS pages found - skipping single page test');
    }
  });

  test('Should handle invalid CMS page ID gracefully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getPage, { id: 'invalid-id-99999' });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data?.page === null || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should handle missing ID parameter gracefully', async ({ request }) => {
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
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });
});
