import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest, sendAdminMultipartRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';
import {
  htmlDocument,
  jpegImage,
  pngImage,
  svgDocument,
  truncatedPngImage,
} from '../../../../rest/helpers/mediaFixtures';

test.describe.configure({ timeout: 60_000 });

const INVALID_TYPE_MESSAGE = 'Image type must be one of: bmp, jpeg, jpg, png, webp.';

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

async function createSimpleProduct(request: APIRequestContext): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
    method: 'POST',
    data: {
      sku: `e2e-img-${Date.now()}-${Math.floor(Math.random() * 100000)}`,
      type: 'simple',
      attribute_family_id: 1,
    },
  });
  expect([200, 201]).toContain(resp.status());
  const body = await safeJson(resp);
  const id = body?.id ?? body?.data?.id;
  expect(typeof id).toBe('number');
  return id;
}

async function productImages(request: APIRequestContext, productId: number): Promise<any[]> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(productId));
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  expect(Array.isArray(body?.images)).toBe(true);
  return body.images;
}

async function uploadImage(
  request: APIRequestContext,
  productId: number,
  name: string,
  mimeType: string,
  buffer: Buffer
) {
  return sendAdminMultipartRequest(request, ADMIN_CATALOG.PRODUCT_IMAGES(productId), {
    image: { name, mimeType, buffer },
  });
}

const acceptedImages = [
  { name: 'My Holiday Photo.png', mimeType: 'image/png', bytes: pngImage },
  { name: 'hero.jpg', mimeType: 'image/jpeg', bytes: jpegImage },
];

const refusedImages = [
  { label: 'an svg image', name: 'logo.svg', mimeType: 'image/svg+xml', bytes: svgDocument },
  { label: 'a jpeg sent under a php name', name: 'shell.php', mimeType: 'image/jpeg', bytes: jpegImage },
  { label: 'a jpeg sent under an html name', name: 'page.html', mimeType: 'image/jpeg', bytes: jpegImage },
  { label: 'an html document sent under a png name', name: 'fake.png', mimeType: 'image/png', bytes: htmlDocument },
  { label: 'a png that cannot be decoded', name: 'broken.png', mimeType: 'image/png', bytes: truncatedPngImage },
];

test.describe('admin catalog product images rest api', () => {
  let productId: number;

  test.beforeEach(async ({ request }) => {
    productId = 0;
    productId = await createSimpleProduct(request);
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(productId), { method: 'DELETE' });
    }
  });

  for (const image of acceptedImages) {
    test(`should store an uploaded ${image.name.split('.').pop()} re-encoded as webp under a generated name`, async ({ request }) => {
      const resp = await uploadImage(request, productId, image.name, image.mimeType, image.bytes());
      expect(resp.status()).toBe(201);

      const body = await safeJson(resp);
      expect(body.productId).toBe(productId);
      expect(body.success).toBe(true);
      expect(body.path).toMatch(new RegExp(`^products/${productId}/[A-Za-z0-9]{40}\\.webp$`));
      expect(body.path).not.toContain('Holiday');
      expect(body.url).toMatch(/\.webp$/);

      const images = await productImages(request, productId);
      const stored = images.find((row: any) => row.id === body.id);
      expect(stored, 'uploaded image is listed on the product').toBeTruthy();
      expect(stored.path).toBe(body.path);
    });
  }

  for (const image of refusedImages) {
    test(`should refuse ${image.label} and store nothing`, async ({ request }) => {
      const resp = await uploadImage(request, productId, image.name, image.mimeType, image.bytes());
      expect(resp.status()).toBe(422);
      expect(errorMessage(await safeJson(resp))).toBe(INVALID_TYPE_MESSAGE);

      expect(await productImages(request, productId)).toEqual([]);
    });
  }

  test('should refuse an upload without an image file', async ({ request }) => {
    const resp = await sendAdminMultipartRequest(request, ADMIN_CATALOG.PRODUCT_IMAGES(productId), {
      position: 1,
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('An image file is required (multipart field "image").');

    expect(await productImages(request, productId)).toEqual([]);
  });

  test('should return 404 when uploading to a product that does not exist', async ({ request }) => {
    const resp = await uploadImage(request, 999999999, 'hero.png', 'image/png', pngImage());
    expect(resp.status()).toBe(404);
  });

  test('should delete an uploaded image from the product', async ({ request }) => {
    const upload = await uploadImage(request, productId, 'hero.png', 'image/png', pngImage());
    expect(upload.status()).toBe(201);
    const imageId = (await safeJson(upload)).id;

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_IMAGE(productId, imageId), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(body.success).toBe(true);
    expect(body.message).toBe('Product image deleted successfully.');

    const images = await productImages(request, productId);
    expect(images.find((row: any) => row.id === imageId)).toBeUndefined();
  });

  test('should reject a reorder with an empty order array', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_IMAGES_REORDER(productId), {
      method: 'PUT',
      data: { order: [] },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('The "order" array is required and must not be empty.');
  });

  test('should reject a reorder naming an image of another product', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_IMAGES_REORDER(productId), {
      method: 'PUT',
      data: { order: [{ id: 999999999, position: 1 }] },
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('Image 999999999 does not belong to this product.');
  });

  test('should return 404 when deleting an image the product does not have', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_IMAGE(productId, 999999999), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
  });

  test('should return 404 when deleting an image of a product that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_IMAGE(999999999, 999999998), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
  });
});
