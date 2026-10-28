import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { sendAdminRequest, sendAdminMultipartRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { mp4Video, pngImage } from '../../../../restAPI/rest/helpers/mediaFixtures';
import { ADMIN_CATALOG_COVERAGE } from '../../../../restAPI/rest/endpoints/coverage/adminCatalog.endpoints';
import { env } from '../../../config/env';
import {
  ADMIN_PRODUCT_VIDEO_CREATE_COVERAGE_MUTATION,
  ADMIN_PRODUCT_VIDEO_DELETE_COVERAGE_MUTATION,
  ADMIN_PRODUCT_IMAGE_UPDATE_COVERAGE_MUTATION,
} from '../../../graphql/Queries/coverage/adminCatalog.queries';

test.describe.configure({ timeout: 90_000 });

const VIDEO_UPLOAD_UNSUPPORTED =
  'Video upload over GraphQL is not supported. Use POST /api/admin/catalog/products/{productId}/videos with multipart/form-data.';
const PRODUCT_NOT_FOUND = 'Product not found.';
const IMAGE_NOT_FOUND = 'Product image not found.';
const VIDEO_DELETED = 'Product video deleted successfully.';
const IMAGE_UPDATED = 'Product image updated successfully.';
const IMAGE_UPDATE_EMPTY = 'Send an alt text or a position to update.';

async function createSimpleProduct(request: APIRequestContext): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS, {
    method: 'POST',
    data: {
      sku: `e2e-cov-media-${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`,
      type: 'simple',
      attribute_family_id: 1,
    },
  });
  expect(resp.status()).toBe(201);
  const id = (await resp.json()).id;
  expect(typeof id).toBe('number');
  return id;
}

async function productDetail(request: APIRequestContext, productId: number): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT(productId));
  expect(resp.status()).toBe(200);
  return resp.json();
}

async function uploadVideo(request: APIRequestContext, productId: number): Promise<number> {
  const resp = await sendAdminMultipartRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT_VIDEOS(productId), {
    video: { name: 'coverage-clip.mp4', mimeType: 'video/mp4', buffer: mp4Video() },
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(body.productId).toBe(productId);
  return body.id;
}

async function uploadImage(request: APIRequestContext, productId: number): Promise<number> {
  const resp = await sendAdminMultipartRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT_IMAGES(productId), {
    image: { name: 'coverage-hero.png', mimeType: 'image/png', buffer: pngImage() },
    position: 1,
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(body.productId).toBe(productId);
  return body.id;
}

test.describe('admin catalog product media graphql', () => {
  let productId: number;

  test.beforeEach(async ({ request }) => {
    productId = 0;
    productId = await createSimpleProduct(request);
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT(productId), { method: 'DELETE' });
    }
  });

  test('should refuse a video upload over graphql and store nothing', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_VIDEO_CREATE_COVERAGE_MUTATION, {
      input: { productId },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toBe(VIDEO_UPLOAD_UNSUPPORTED);
    expect(body.data?.createAdminCatalogProductVideo ?? null).toBeNull();

    expect((await productDetail(request, productId)).videos).toEqual([]);
  });

  test('should refuse the video create mutation without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(
      request,
      ADMIN_PRODUCT_VIDEO_CREATE_COVERAGE_MUTATION,
      { input: { productId } },
      {},
      env.adminGraphqlEndpoint,
    );
    expect(resp.status()).toBe(401);
    expect((await resp.json()).message).toBe('Unauthenticated.');
  });

  test('should delete an uploaded video over graphql', async ({ request }) => {
    const videoId = await uploadVideo(request, productId);
    expect((await productDetail(request, productId)).videos.map((row: any) => row.id)).toContain(videoId);

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_VIDEO_DELETE_COVERAGE_MUTATION, {
      input: { id: ADMIN_CATALOG_COVERAGE.PRODUCT_VIDEO_IRI(videoId) },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.deleteAdminCatalogProductVideo.adminCatalogProductVideo).toEqual({
      _id: videoId,
      success: true,
      message: VIDEO_DELETED,
    });

    expect((await productDetail(request, productId)).videos).toEqual([]);
  });

  test('should refuse deleting a video that does not exist', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_VIDEO_DELETE_COVERAGE_MUTATION, {
      input: { id: ADMIN_CATALOG_COVERAGE.PRODUCT_VIDEO_IRI(999999999) },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toBe(PRODUCT_NOT_FOUND);
    expect(body.data?.deleteAdminCatalogProductVideo ?? null).toBeNull();
  });

  test('should keep the video when the delete mutation has no admin token', async ({ request }) => {
    const videoId = await uploadVideo(request, productId);

    const resp = await sendGraphQLRequest(
      request,
      ADMIN_PRODUCT_VIDEO_DELETE_COVERAGE_MUTATION,
      { input: { id: ADMIN_CATALOG_COVERAGE.PRODUCT_VIDEO_IRI(videoId) } },
      {},
      env.adminGraphqlEndpoint,
    );
    expect(resp.status()).toBe(401);
    expect((await resp.json()).message).toBe('Unauthenticated.');

    expect((await productDetail(request, productId)).videos.map((row: any) => row.id)).toContain(videoId);
  });

  test('should update the position and alt text of an uploaded image', async ({ request }) => {
    const imageId = await uploadImage(request, productId);
    const altText = `coverage alt ${Date.now().toString(36)}`;

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_UPDATE_COVERAGE_MUTATION, {
      input: {
        id: ADMIN_CATALOG_COVERAGE.PRODUCT_IMAGE_IRI(imageId),
        productId,
        imageId,
        altText,
        position: 5,
      },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const image = body.data.updateAdminCatalogProductImage.adminCatalogProductImage;
    expect(image._id).toBe(imageId);
    expect(image.productId).toBe(productId);
    expect(image.position).toBe(5);
    expect(image.altText).toBe(altText);
    expect(image.success).toBe(true);
    expect(image.message).toBe(IMAGE_UPDATED);
    expect(image.path).toMatch(new RegExp(`^products/${productId}/`));

    const stored = (await productDetail(request, productId)).images.find((row: any) => row.id === imageId);
    expect(stored.sortOrder).toBe(5);
    expect(stored.altText).toBe(altText);
  });

  test('should refuse an image update that carries neither alt text nor position', async ({ request }) => {
    const imageId = await uploadImage(request, productId);

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_UPDATE_COVERAGE_MUTATION, {
      input: { id: ADMIN_CATALOG_COVERAGE.PRODUCT_IMAGE_IRI(imageId), productId, imageId },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toBe(IMAGE_UPDATE_EMPTY);
    expect(body.data?.updateAdminCatalogProductImage ?? null).toBeNull();

    const stored = (await productDetail(request, productId)).images.find((row: any) => row.id === imageId);
    expect(stored.sortOrder).toBe(1);
  });

  test('should refuse updating an image the product does not have', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_IMAGE_UPDATE_COVERAGE_MUTATION, {
      input: {
        id: ADMIN_CATALOG_COVERAGE.PRODUCT_IMAGE_IRI(999999999),
        productId,
        imageId: 999999999,
        position: 2,
      },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toBe(IMAGE_NOT_FOUND);
    expect(body.data?.updateAdminCatalogProductImage ?? null).toBeNull();
  });

  test('should keep the image when the update mutation has no admin token', async ({ request }) => {
    const imageId = await uploadImage(request, productId);

    const resp = await sendGraphQLRequest(
      request,
      ADMIN_PRODUCT_IMAGE_UPDATE_COVERAGE_MUTATION,
      { input: { id: ADMIN_CATALOG_COVERAGE.PRODUCT_IMAGE_IRI(imageId), productId, imageId, position: 9 } },
      {},
      env.adminGraphqlEndpoint,
    );
    expect(resp.status()).toBe(401);
    expect((await resp.json()).message).toBe('Unauthenticated.');

    const stored = (await productDetail(request, productId)).images.find((row: any) => row.id === imageId);
    expect(stored.sortOrder).toBe(1);
  });
});
