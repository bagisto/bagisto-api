import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { CREATE_DOWNLOADABLE_PRODUCT_DOWNLOAD_LINK } from '../../graphql/Queries/customerDownloadableProducts.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

const MISSING_PURCHASED_LINK_ID = 999999999;

test.describe('downloadable product download link graphql api', () => {
  test('should refuse a download link to an anonymous caller', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_DOWNLOADABLE_PRODUCT_DOWNLOAD_LINK, {
      downloadableLinkPurchasedId: MISSING_PURCHASED_LINK_ID,
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body)).toContain('Unauthorized: Customer authentication required');
    expect(body.data?.createDownloadableProductDownloadLink ?? null).toBeNull();
  });

  test('should report a purchased link that does not exist to an authenticated customer', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    const response = await sendGraphQLRequest(
      request,
      CREATE_DOWNLOADABLE_PRODUCT_DOWNLOAD_LINK,
      { downloadableLinkPurchasedId: MISSING_PURCHASED_LINK_ID },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body)).toContain('Downloadable link not found');
    expect(body.data?.createDownloadableProductDownloadLink ?? null).toBeNull();
  });
});
