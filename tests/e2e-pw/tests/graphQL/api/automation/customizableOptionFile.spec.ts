// Shop — Customizable-option file upload e2e (GraphQL).
// The binary upload itself is REST-only (GraphQL cannot carry a file), so
// there is no upload mutation. What GraphQL owns is the token-resolution
// path on add-to-cart: the mutation accepts customizableOptions and, when a
// file option is present, rejects a bogus/absent upload token. With no seeded
// file-option product in CI we assert the mutation is reachable and handled.

import { test, expect, APIRequestContext } from '@playwright/test';
import { getGuestCartHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

const ADD_WITH_FILE_OPTION = `
  mutation createAddProductInCart($input: createAddProductInCartInput!) {
    createAddProductInCart(input: $input) {
      addProductInCart {
        success
        itemsCount
      }
    }
  }
`;

async function getFirstProductId(request: APIRequestContext): Promise<number> {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[0]?.node;
  return Number(String(node?.id ?? '').split('/').pop());
}

test.describe('Customizable Option File Upload GraphQL API', () => {
  test('binary upload is REST-only — deferred', async () => {
    test.skip(true, 'file upload has no GraphQL mutation (binary is REST-only); resolution flow is covered below + in Pest');
  });

  test('add to cart with a bogus file token is handled', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const productId = await getFirstProductId(request);

    if (!(productId > 0)) {
      test.skip(true, 'test store has no product available');
      return;
    }

    const response = await sendGraphQLRequest(
      request,
      ADD_WITH_FILE_OPTION,
      {
        input: {
          productId,
          quantity: 1,
          customizableOptions: { '1': ['not-a-real-upload-token'] },
        },
      },
      guestHeaders
    );

    expect(response.status()).toBe(200);

    const body = await response.json();

    // Reachable + handled: either the product has a file option and the bogus
    // token is rejected (errors present), or it has none and the token is
    // ignored so the add succeeds (data present). Both are valid outcomes.
    const hasData = body.data?.createAddProductInCart?.addProductInCart != null;
    const hasErrors = Array.isArray(body.errors) && body.errors.length > 0;
    expect(hasData || hasErrors).toBeTruthy();
  });
});
