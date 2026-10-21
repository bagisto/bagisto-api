import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_PRODUCT_IMAGE_CREATE_MUTATION,
  ADMIN_PRODUCT_IMAGE_REORDER_MUTATION,
  ADMIN_PRODUCT_IMAGE_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/productImages.queries';
import {
  ADMIN_PRODUCT_CREATE_MUTATION,
  ADMIN_PRODUCT_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/products.queries';

test.describe.configure({ timeout: 60_000 });

const UPLOAD_UNSUPPORTED_MESSAGE =
  'Image upload over GraphQL is not supported. Use POST /api/admin/catalog/products/{productId}/images with multipart/form-data.';

async function createSimpleProduct(request: APIRequestContext): Promise<{ id: string; _id: number }> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CREATE_MUTATION, {
    sku: `e2e-gql-img-${Date.now()}-${Math.floor(Math.random() * 100000)}`,
    attributeFamilyId: 1,
    type: 'simple',
  });
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  const product = body.data.createAdminCatalogProduct.adminCatalogProduct;
  return { id: product.id, _id: Number(product._id) };
}

test.describe('admin catalog product images graphql', () => {
  let product: { id: string; _id: number } | null;

  test.beforeEach(async ({ request }) => {
    product = null;
    product = await createSimpleProduct(request);
  });

  test.afterEach(async ({ request }) => {
    if (product) {
      await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, { id: product.id });
    }
  });

  test('should refuse an image upload over graphql and point to the rest endpoint', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_CREATE_MUTATION, {
      productId: product!._id,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toBe(UPLOAD_UNSUPPORTED_MESSAGE);
    expect(body.data?.createAdminCatalogProductImage ?? null).toBeNull();
  });

  test('should refuse a reorder naming an image the product does not have', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_REORDER_MUTATION, {
      id: `/api/admin/catalog/products/${product!._id}/images/reorder`,
      productId: product!._id,
      order: [{ id: 999999999, position: 1 }],
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toBe('Image 999999999 does not belong to this product.');
    expect(body.data?.reorderAdminCatalogProductImage ?? null).toBeNull();
  });

  test('should refuse deleting an image that does not exist', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_DELETE_MUTATION, {
      id: `/api/admin/catalog/products/${product!._id}/images/999999999`,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.errors) && body.errors.length > 0).toBe(true);
    expect(body.data?.deleteAdminCatalogProductImage ?? null).toBeNull();
  });
});
