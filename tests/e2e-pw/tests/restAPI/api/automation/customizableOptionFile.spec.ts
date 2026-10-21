import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { sendAdminRequest } from '../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { env } from '../../config/env';
import { bearer, expectProblem, readJson, registerShopCustomer, ShopCustomer, uniqueSuffix } from '../../rest/helpers/coverage/shopCart.helpers';

const MSG = {
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  PRODUCT_NOT_FOUND: 'Product not found',
  FILE_INVALID: 'The file is missing, has an unsupported extension, or is too large.',
  FILE_TOKEN_INVALID: 'The uploaded file reference is invalid or has expired. Upload the file again.',
};

const MISSING_PRODUCT_ID = 2147480000;

interface FileOptionProduct {
  productId: number;
  optionId: number;
}

async function createFileOptionProduct(request: APIRequestContext): Promise<FileOptionProduct> {
  const createResp = await sendAdminRequest(request, '/api/admin/catalog/products', {
    method: 'POST',
    data: {
      sku: `e2e-fileopt-${uniqueSuffix()}`,
      attribute_family_id: 1,
      type: 'simple',
    },
  });
  const created = await readJson(createResp);
  expect(createResp.status(), JSON.stringify(created)).toBe(201);
  const productId = Number(created.id);

  const optionResp = await sendAdminRequest(request, `/api/admin/catalog/products/${productId}`, {
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
  const updated = await readJson(optionResp);
  expect(optionResp.status(), JSON.stringify(updated)).toBe(200);
  expect(updated.status).toBe(1);

  const fileOption = (updated.customizableOptions ?? []).find((option: any) => option.type === 'file');
  expect(fileOption, JSON.stringify(updated.customizableOptions)).toBeDefined();

  return { productId, optionId: Number(fileOption.id) };
}

async function deleteProduct(request: APIRequestContext, productId: number): Promise<void> {
  await sendAdminRequest(request, `/api/admin/catalog/products/${productId}`, { method: 'DELETE' });
}

async function uploadFile(request: APIRequestContext, customer: ShopCustomer, product: FileOptionProduct) {
  return request.post(`${env.baseUrl}${ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD}`, {
    headers: {
      'X-STOREFRONT-KEY': env.storefrontAccessKey,
      Authorization: `Bearer ${customer.token}`,
      Accept: 'application/json',
    },
    multipart: {
      product_id: String(product.productId),
      option_id: String(product.optionId),
      file: {
        name: 'spec.pdf',
        mimeType: 'application/pdf',
        buffer: Buffer.from('%PDF-1.4 e2e upload'),
      },
    },
  });
}

test.describe('customizable option file upload rest api', () => {
  let customer: ShopCustomer;

  test.beforeEach(async ({ request }) => {
    customer = await registerShopCustomer(request);
  });

  test('live multipart upload returns a file token', async ({ request }) => {
    const product = await createFileOptionProduct(request);

    try {
      const upload = await uploadFile(request, customer, product);
      const body = await readJson(upload);

      expect(upload.status(), JSON.stringify(body)).toBe(201);
      expect(typeof body.token).toBe('string');
      expect(body.token.length).toBeGreaterThan(0);
      expect(body.fileName).toBe('spec.pdf');
      expect(Number(body.optionId)).toBe(product.optionId);
    } finally {
      await deleteProduct(request, product.productId);
    }
  });

  test('upload requires authentication', async ({ request }) => {
    const resp = await sendRestRequest(request, ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD, {
      method: 'POST',
      data: { product_id: MISSING_PRODUCT_ID, option_id: 1 },
    });

    await expectProblem(resp, 401, MSG.UNAUTHENTICATED);
  });

  test('upload on an unknown product is rejected', async ({ request }) => {
    const resp = await sendRestRequest(request, ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD, {
      method: 'POST',
      headers: bearer(customer.token),
      data: { product_id: MISSING_PRODUCT_ID, option_id: 1 },
    });

    await expectProblem(resp, 404, MSG.PRODUCT_NOT_FOUND);
  });

  test('upload without a file is rejected', async ({ request }) => {
    const product = await createFileOptionProduct(request);

    try {
      const resp = await sendRestRequest(request, ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD, {
        method: 'POST',
        headers: bearer(customer.token),
        data: { product_id: product.productId, option_id: product.optionId },
      });

      await expectProblem(resp, 400, MSG.FILE_INVALID);
    } finally {
      await deleteProduct(request, product.productId);
    }
  });

  test('add to cart with a bogus file token is rejected', async ({ request }) => {
    const product = await createFileOptionProduct(request);

    try {
      const resp = await sendRestRequest(request, ENDPOINTS.ADD_PRODUCT_TO_CART, {
        method: 'POST',
        headers: bearer(customer.token),
        data: {
          productId: product.productId,
          quantity: 1,
          customizableOptions: { [String(product.optionId)]: ['not-a-real-upload-token'] },
        },
      });

      await expectProblem(resp, 400, MSG.FILE_TOKEN_INVALID);
    } finally {
      await deleteProduct(request, product.productId);
    }
  });
});
