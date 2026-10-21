import { test, expect } from '@playwright/test';
import { getGuestCartHeaders } from '../../config/auth';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { sendAdminRequest } from '../../../restAPI/rest/helpers/adminClient';
import { SHOP_CATALOG } from '../../../restAPI/rest/endpoints/coverage/shopCatalog.endpoints';

test.describe.configure({ timeout: 120_000 });

const FILE_TOKEN_INVALID_MESSAGE = 'The uploaded file reference is invalid or has expired. Upload the file again.';

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

test.describe('customizable option file upload graphql api', () => {
  let productId = 0;
  let optionId = 0;

  test.beforeEach(async ({ request }) => {
    productId = 0;
    optionId = 0;

    const created = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCTS, {
      method: 'POST',
      data: {
        sku: `e2e-gql-file-token-${Date.now()}-${Math.floor(Math.random() * 1e6)}`,
        attribute_family_id: 1,
        type: 'simple',
      },
    });
    expect(created.status()).toBe(201);
    productId = (await created.json()).id;

    const updated = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(productId), {
      method: 'PUT',
      data: {
        status: 1,
        customizable_options: {
          option_1: {
            label: 'Upload your design',
            type: 'file',
            is_required: 1,
            sort_order: 0,
            supported_file_extensions: 'pdf,jpg,png',
            prices: { price_1: { label: '', price: 0, sort_order: 0 } },
          },
        },
      },
    });
    expect(updated.status()).toBe(200);

    const detail = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(productId));
    expect(detail.status()).toBe(200);
    const fileOption = ((await detail.json())?.customizableOptions ?? []).find((option: any) => option.type === 'file');
    expect(fileOption, 'file-type customizable option exists on the product').toBeTruthy();
    optionId = Number(fileOption.id);
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(productId), { method: 'DELETE' });
    }
  });

  test('add to cart with a bogus file token is refused', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);

    const response = await sendGraphQLRequest(
      request,
      ADD_WITH_FILE_OPTION,
      {
        input: {
          productId,
          quantity: 1,
          customizableOptions: { [String(optionId)]: ['not-a-real-upload-token'] },
        },
      },
      guestHeaders
    );

    expect(response.status()).toBe(200);

    const body = await response.json();

    expect(graphQLErrorMessages(body)).toEqual([FILE_TOKEN_INVALID_MESSAGE]);
    expect(body.data?.createAddProductInCart ?? null).toBeNull();
  });
});
