import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { SHOP_CATALOG } from '../../../rest/endpoints/coverage/shopCatalog.endpoints';
import { pngImage, htmlDocument } from '../../../rest/helpers/mediaFixtures';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 60_000 });

const MISSING_KEY_MESSAGE = 'X-STOREFRONT-KEY header is required';

const INVALID_IMAGE_MESSAGE = 'One or more of the uploaded files has an unsupported file type.';

async function safeJson(response: APIResponse): Promise<any> {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

async function postImageSearch(
  request: APIRequestContext,
  multipart: Record<string, string | { name: string; mimeType: string; buffer: Buffer }>,
  withKey = true
): Promise<APIResponse> {
  const headers: Record<string, string> = { Accept: 'application/json' };

  if (withKey) {
    headers['X-STOREFRONT-KEY'] = env.storefrontAccessKey;
  }

  return request.post(`${env.baseUrl}${SHOP_CATALOG.IMAGE_SEARCHES}`, { headers, multipart });
}

test.describe('shop catalog image search rest api', () => {
  test('should store an uploaded png and report the search engine used', async ({ request }) => {
    const response = await postImageSearch(request, {
      image: { name: `probe-${Date.now()}.png`, mimeType: 'image/png', buffer: pngImage() },
    });
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(body.imageUrl).toMatch(/product-search\/[A-Za-z0-9]+\.png$/);
    expect(['tensorflow', 'ai']).toContain(body.engine);
    expect(typeof body.keywords).toBe('string');

    if (body.engine === 'tensorflow') {
      expect(body.keywords).toBe('');
    }
  });

  test('should refuse an image search without an image file', async ({ request }) => {
    const response = await postImageSearch(request, { note: 'no image attached' });
    expect(response.status()).toBe(422);
    expect(errorMessage(await safeJson(response))).toBe(INVALID_IMAGE_MESSAGE);
  });

  test('should refuse an html document sent under a png name', async ({ request }) => {
    const response = await postImageSearch(request, {
      image: { name: 'probe.png', mimeType: 'image/png', buffer: htmlDocument() },
    });
    expect(response.status()).toBe(422);
    expect(errorMessage(await safeJson(response))).toBe(INVALID_IMAGE_MESSAGE);
  });

  test('should refuse an image search without a storefront key', async ({ request }) => {
    const response = await postImageSearch(
      request,
      { image: { name: 'probe.png', mimeType: 'image/png', buffer: pngImage() } },
      false
    );
    expect(response.status()).toBe(401);

    const body = await safeJson(response);
    expect(body.message).toBe(MISSING_KEY_MESSAGE);
    expect(body.error).toBe('missing_key');
  });
});
