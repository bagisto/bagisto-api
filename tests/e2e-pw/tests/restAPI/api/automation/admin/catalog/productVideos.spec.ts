import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest, sendAdminMultipartRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';
import { htmlDocument, mp4Video } from '../../../../rest/helpers/mediaFixtures';

test.describe.configure({ timeout: 60_000 });

const INVALID_TYPE_MESSAGE = 'Video type must be one of: mp4, webm, ogg, mov.';

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
      sku: `e2e-vid-${Date.now()}-${Math.floor(Math.random() * 100000)}`,
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

async function productVideos(request: APIRequestContext, productId: number): Promise<any[]> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(productId));
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  expect(Array.isArray(body?.videos)).toBe(true);
  return body.videos;
}

async function uploadVideo(
  request: APIRequestContext,
  productId: number,
  name: string,
  mimeType: string,
  buffer: Buffer
) {
  return sendAdminMultipartRequest(request, ADMIN_CATALOG.PRODUCT_VIDEOS(productId), {
    video: { name, mimeType, buffer },
  });
}

const refusedVideos = [
  { label: 'an mp4 sent under a php name', name: 'clip.php', mimeType: 'video/mp4', bytes: mp4Video },
  { label: 'an mp4 sent under an html name', name: 'clip.html', mimeType: 'video/mp4', bytes: mp4Video },
  { label: 'an html document sent under an mp4 name', name: 'clip.mp4', mimeType: 'video/mp4', bytes: htmlDocument },
];

test.describe('admin catalog product videos rest api', () => {
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

  test('should store an uploaded mp4 under a generated name and delete it again', async ({ request }) => {
    const resp = await uploadVideo(request, productId, 'My Product Clip.mp4', 'video/mp4', mp4Video());
    expect(resp.status()).toBe(201);

    const body = await safeJson(resp);
    expect(body.productId).toBe(productId);
    expect(body.path).toMatch(new RegExp(`^products/${productId}/[A-Za-z0-9]{40}\\.mp4$`));

    const listed = (await productVideos(request, productId)).find((row: any) => row.id === body.id);
    expect(listed, 'uploaded video is listed on the product').toBeTruthy();
    expect(listed.path).toBe(body.path);

    const del = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_VIDEO(productId, body.id), {
      method: 'DELETE',
    });
    expect(del.status()).toBe(200);
    expect((await safeJson(del)).message).toBe('Product video deleted successfully.');

    expect((await productVideos(request, productId)).find((row: any) => row.id === body.id)).toBeUndefined();
  });

  for (const video of refusedVideos) {
    test(`should refuse ${video.label} and store nothing`, async ({ request }) => {
      const resp = await uploadVideo(request, productId, video.name, video.mimeType, video.bytes());
      expect(resp.status()).toBe(422);
      expect(errorMessage(await safeJson(resp))).toBe(INVALID_TYPE_MESSAGE);

      expect(await productVideos(request, productId)).toEqual([]);
    });
  }

  test('should refuse an upload without a video file', async ({ request }) => {
    const resp = await sendAdminMultipartRequest(request, ADMIN_CATALOG.PRODUCT_VIDEOS(productId), {
      position: 1,
    });
    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe('A video file is required (multipart field "video").');
  });

  test('should return 404 when uploading to a product that does not exist', async ({ request }) => {
    const resp = await uploadVideo(request, 999999999, 'clip.mp4', 'video/mp4', mp4Video());
    expect(resp.status()).toBe(404);
  });
});
