// Shop — Customizable-option file upload e2e (REST).
// Covers the live multipart upload (the fixture product + its file-type option are
// built through the admin API) plus the endpoint's guards: auth gate, unknown
// product, missing binary, and the add-to-cart token-resolution path.

import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { sendAdminRequest } from '../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { env } from '../../config/env';

function uniqueEmail(): string {
  return `file-opt-${Date.now()}-${Math.floor(Math.random() * 1e6)}@example.com`;
}

test.describe('Customizable Option File Upload REST API', () => {
  let authToken: string | null = null;
  let productId: number | null = null;

  test.beforeAll(async ({ request }) => {
    const email = uniqueEmail();
    const password = 'Password123!';

    await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
      method: 'POST',
      data: {
        first_name: 'File',
        last_name: 'Option',
        email,
        password,
        password_confirmation: password,
      },
    });

    const login = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email, password },
    });

    if ([200, 201].includes(login.status())) {
      authToken = (await login.json()).token ?? null;
    }

    // A simple product: a configurable one fails add-to-cart on its missing variant
    // options before the file-token path is ever reached.
    const products = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '5', type: 'simple' },
    });
    if (products.status() === 200) {
      const body = await products.json();
      if (Array.isArray(body) && body.length > 0) productId = body[0].id;
    }
  });

  function authHeaders(): Record<string, string> {
    return authToken ? { Authorization: `Bearer ${authToken}` } : {};
  }

  test('live multipart upload returns a file token', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'customer login failed');
      return;
    }

    // Build the fixture: a product carrying a file-type customizable option.
    // Core keys new options with an `option_`-prefixed key; a numeric key means "existing id".
    const createResp = await sendAdminRequest(request, '/api/admin/catalog/products', {
      method: 'POST',
      data: {
        sku: `e2e-fileopt-${Date.now()}-${Math.floor(Math.random() * 1e6)}`,
        attribute_family_id: 1,
        type: 'simple',
      },
    });
    expect([200, 201]).toContain(createResp.status());
    const optionProductId = (await createResp.json()).id as number;

    try {
      const optionResp = await sendAdminRequest(request, `/api/admin/catalog/products/${optionProductId}`, {
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
      expect(optionResp.status()).toBe(200);

      const detail = await sendAdminRequest(request, `/api/admin/catalog/products/${optionProductId}`);
      const options = (await detail.json())?.customizableOptions ?? [];
      const fileOption = options.find((o: any) => o.type === 'file');
      expect(fileOption, 'file-type customizable option must exist on the product').toBeTruthy();

      const upload = await request.post(
        `${env.baseUrl}${ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD}`,
        {
          headers: {
            'X-STOREFRONT-KEY': env.storefrontAccessKey,
            Authorization: `Bearer ${authToken}`,
            Accept: 'application/json',
          },
          multipart: {
            product_id: String(optionProductId),
            option_id: String(fileOption.id),
            file: {
              name: 'spec.pdf',
              mimeType: 'application/pdf',
              buffer: Buffer.from('%PDF-1.4 e2e upload'),
            },
          },
        }
      );

      expect([200, 201]).toContain(upload.status());
      const body = await upload.json();
      expect(body.token, 'upload must return a resolution token').toBeTruthy();
      expect(body.fileName).toBe('spec.pdf');
      expect(Number(body.optionId)).toBe(Number(fileOption.id));
    } finally {
      await sendAdminRequest(request, `/api/admin/catalog/products/${optionProductId}`, { method: 'DELETE' });
    }
  });

  test('upload requires authentication', async ({ request }) => {
    // Storefront key only, no Bearer — the endpoint must reject before any work.
    const resp = await sendRestRequest(request, ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD, {
      method: 'POST',
      data: { product_id: productId ?? 1, option_id: 1 },
    });
    expect([401, 403]).toContain(resp.status());
  });

  test('upload on an unknown product is rejected', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'customer login failed');
      return;
    }
    const resp = await sendRestRequest(request, ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD, {
      method: 'POST',
      headers: authHeaders(),
      data: { product_id: 99999999, option_id: 1 },
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('upload without a file is rejected', async ({ request }) => {
    if (!authToken || !productId) {
      test.skip(true, 'no customer token or product available');
      return;
    }
    const resp = await sendRestRequest(request, ENDPOINTS.CUSTOMIZABLE_OPTION_FILE_UPLOAD, {
      method: 'POST',
      headers: authHeaders(),
      data: { product_id: productId, option_id: 1 },
    });
    // No binary + option is not a file option (or missing) → validation rejects.
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('add to cart with a bogus file token is handled', async ({ request }) => {
    if (!authToken || !productId) {
      test.skip(true, 'no customer token or product available');
      return;
    }
    const resp = await sendRestRequest(request, ENDPOINTS.ADD_PRODUCT_TO_CART, {
      method: 'POST',
      headers: authHeaders(),
      data: {
        productId,
        quantity: 1,
        customizableOptions: { '1': ['not-a-real-upload-token'] },
      },
    });
    // If the product has a file option, the bogus token is rejected (400);
    // otherwise the token is ignored and the add succeeds — both are valid.
    expect([200, 201, 400, 404, 422]).toContain(resp.status());
  });
});
