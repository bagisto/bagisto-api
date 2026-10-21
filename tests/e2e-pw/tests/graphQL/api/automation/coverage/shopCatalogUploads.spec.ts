import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CATALOG_QUERIES } from '../../../graphql/Queries/coverage/shopCatalog.queries';
import { getGuestCartHeaders } from '../../../config/auth';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { SHOP_CATALOG } from '../../../../restAPI/rest/endpoints/coverage/shopCatalog.endpoints';
import { pngImage, htmlDocument } from '../../../../restAPI/rest/helpers/mediaFixtures';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 120_000 });

const MISSING_KEY_MESSAGE = 'X-STOREFRONT-KEY header is required for this operation';

const UNAUTHENTICATED_MESSAGE = 'Unauthenticated. Please login to perform this action';

const PRODUCT_NOT_FOUND_MESSAGE = 'Product not found';

const NOT_A_FILE_OPTION_MESSAGE = 'The selected option is not a file option for this product.';

const INVALID_FILE_MESSAGE = 'The file is missing, has an unsupported extension, or is too large.';

const INVALID_IMAGE_MESSAGE = 'One or more of the uploaded files has an unsupported file type.';

function pdfBase64(): string {
  return Buffer.from('%PDF-1.4 e2e upload').toString('base64');
}

async function postWithoutStorefrontKey(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<APIResponse> {
  return request.post(`${env.baseUrl}${env.graphqlEndpoint}`, {
    data: { query, variables },
    headers: { 'Content-Type': 'application/json' },
  });
}

async function uploadOptionFile(
  request: APIRequestContext,
  input: Record<string, any>,
  headers: Record<string, string>
): Promise<any> {
  const response = await sendGraphQLRequest(request, SHOP_CATALOG_QUERIES.createCustomizableOptionFile, { input }, headers);
  expect(response.status()).toBe(200);

  return response.json();
}

async function searchByImage(request: APIRequestContext, input: Record<string, any>): Promise<any> {
  const response = await sendGraphQLRequest(request, SHOP_CATALOG_QUERIES.createImageSearch, { input });
  expect(response.status()).toBe(200);

  return response.json();
}

test.describe('shop customizable option file graphql', () => {
  let productId = 0;
  let optionId = 0;
  let cartHeaders: Record<string, string> = {};

  test.beforeEach(async ({ request }) => {
    productId = 0;
    optionId = 0;

    const created = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCTS, {
      method: 'POST',
      data: {
        sku: `e2e-gql-fileopt-${Date.now()}-${Math.floor(Math.random() * 1e6)}`,
        attribute_family_id: 1,
        type: 'simple',
      },
    });
    expect(created.status()).toBe(201);
    productId = (await created.json()).id;

    const updated = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(productId), {
      method: 'PUT',
      data: {
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

    cartHeaders = await getGuestCartHeaders(request);
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(productId), { method: 'DELETE' });
    }
  });

  test('should stage a base64 file for a file option and return a resolution token', async ({ request }) => {
    const body = await uploadOptionFile(
      request,
      { productId, optionId, file: pdfBase64(), fileName: 'design.pdf' },
      cartHeaders
    );
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const staged = body.data.createCustomizableOptionFile.customizableOptionFile;
    expect(staged.token).toMatch(/^[A-Za-z0-9]{48}$/);
    expect(staged.fileName).toBe('design.pdf');
    expect(staged.optionId).toBe(optionId);
  });

  test('should accept a data uri for a file option', async ({ request }) => {
    const body = await uploadOptionFile(
      request,
      { productId, optionId, file: `data:application/pdf;base64,${pdfBase64()}`, fileName: 'design.pdf' },
      cartHeaders
    );
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.createCustomizableOptionFile.customizableOptionFile.token).toMatch(/^[A-Za-z0-9]{48}$/);
  });

  test('should refuse a file whose extension the option does not support', async ({ request }) => {
    const body = await uploadOptionFile(
      request,
      { productId, optionId, file: pdfBase64(), fileName: 'design.exe' },
      cartHeaders
    );

    expect(graphQLErrorMessages(body)).toContain(INVALID_FILE_MESSAGE);
    expect(body.data?.createCustomizableOptionFile ?? null).toBeNull();
  });

  test('should refuse an option the product does not have as a file option', async ({ request }) => {
    const body = await uploadOptionFile(
      request,
      { productId, optionId: 999999999, file: pdfBase64(), fileName: 'design.pdf' },
      cartHeaders
    );

    expect(graphQLErrorMessages(body)).toContain(NOT_A_FILE_OPTION_MESSAGE);
    expect(body.data?.createCustomizableOptionFile ?? null).toBeNull();
  });

  test('should refuse a product that does not exist', async ({ request }) => {
    const body = await uploadOptionFile(
      request,
      { productId: 999999999, optionId, file: pdfBase64(), fileName: 'design.pdf' },
      cartHeaders
    );

    expect(graphQLErrorMessages(body)).toContain(PRODUCT_NOT_FOUND_MESSAGE);
    expect(body.data?.createCustomizableOptionFile ?? null).toBeNull();
  });

  test('should refuse an upload without a bearer token', async ({ request }) => {
    const body = await uploadOptionFile(
      request,
      { productId, optionId, file: pdfBase64(), fileName: 'design.pdf' },
      {}
    );

    expect(graphQLErrorMessages(body)).toContain(UNAUTHENTICATED_MESSAGE);
    expect(body.data?.createCustomizableOptionFile ?? null).toBeNull();
  });

  test('should refuse an upload without a storefront key', async ({ request }) => {
    const response = await postWithoutStorefrontKey(request, SHOP_CATALOG_QUERIES.createCustomizableOptionFile, {
      input: { productId, optionId, file: pdfBase64(), fileName: 'design.pdf' },
    });
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.message).toBe(MISSING_KEY_MESSAGE);
    expect(body.data).toBeUndefined();
  });
});

test.describe('shop image search graphql', () => {
  test('should store a base64 png and report the search engine used', async ({ request }) => {
    const body = await searchByImage(request, {
      image: pngImage().toString('base64'),
      fileName: `probe-${Date.now()}.png`,
      mimeType: 'image/png',
    });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const result = body.data.createImageSearch.imageSearch;
    expect(result.imageUrl).toMatch(/product-search\/[A-Za-z0-9]+\.png$/);
    expect(['tensorflow', 'ai']).toContain(result.engine);
    expect(typeof result.keywords).toBe('string');

    if (result.engine === 'tensorflow') {
      expect(result.keywords).toBe('');
    }
  });

  test('should refuse an html document sent as an image', async ({ request }) => {
    const body = await searchByImage(request, {
      image: htmlDocument().toString('base64'),
      fileName: 'probe.png',
      mimeType: 'image/png',
    });

    expect(graphQLErrorMessages(body)).toContain(INVALID_IMAGE_MESSAGE);
    expect(body.data?.createImageSearch ?? null).toBeNull();
  });

  test('should refuse an image search without a storefront key', async ({ request }) => {
    const response = await postWithoutStorefrontKey(request, SHOP_CATALOG_QUERIES.createImageSearch, {
      input: { image: pngImage().toString('base64'), fileName: 'probe.png', mimeType: 'image/png' },
    });
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.message).toBe(MISSING_KEY_MESSAGE);
    expect(body.data).toBeUndefined();
  });
});
