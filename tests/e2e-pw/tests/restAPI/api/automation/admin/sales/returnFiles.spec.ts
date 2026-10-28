import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../../rest/helpers/restClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';

test.describe.configure({ timeout: 90_000 });

const BINARY_HEADERS = { 'Accept': 'application/octet-stream' };

const UNKNOWN_ID = 999999999;

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function rowsOf(body: any): any[] {
  if (Array.isArray(body)) return body;
  return Array.isArray(body?.data) ? body.data : [];
}

async function recentReturnIds(request: APIRequestContext): Promise<number[]> {
  const resp = await sendAdminRequest(request, ADMIN_SALES.RMA_REQUESTS, { params: { per_page: '10' } });
  expect(resp.status()).toBe(200);
  return rowsOf(await safeJson(resp)).map((row: any) => row.id);
}

test.describe('admin rma return files rest api', () => {
  test('should refuse a return image request without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_SALES.RMA_REQUEST_IMAGE(UNKNOWN_ID), {
      headers: BINARY_HEADERS,
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should refuse a message attachment request without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_SALES.RMA_MESSAGE_ATTACHMENT(UNKNOWN_ID), {
      headers: BINARY_HEADERS,
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should return 404 and no file for an unknown return image', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SALES.RMA_REQUEST_IMAGE(UNKNOWN_ID), {
      headers: BINARY_HEADERS,
    });
    expect(resp.status()).toBe(404);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect(String(resp.headers()['content-type'] ?? '')).not.toMatch(/^image\//);
  });

  test('should return 404 and no file for an unknown message attachment', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SALES.RMA_MESSAGE_ATTACHMENT(UNKNOWN_ID), {
      headers: BINARY_HEADERS,
    });
    expect(resp.status()).toBe(404);
    expect(resp.headers()['content-disposition']).toBeUndefined();
  });

  test('should point return image urls at the admin image endpoint and serve the image', async ({ request }) => {
    const images: any[] = [];

    for (const id of await recentReturnIds(request)) {
      const detail = await sendAdminRequest(request, ADMIN_SALES.RMA_REQUEST(id));
      expect(detail.status()).toBe(200);
      images.push(...((await safeJson(detail))?.images ?? []));
    }

    test.skip(images.length === 0, 'no return request with an image exists to read');

    for (const image of images) {
      expect(image.url).toMatch(new RegExp(`/api/admin/rma/requests/images/${image.id}$`));
    }

    const resp = await sendAdminRequest(request, ADMIN_SALES.RMA_REQUEST_IMAGE(images[0].id), {
      headers: BINARY_HEADERS,
    });
    expect(resp.status()).toBe(200);
    expect(String(resp.headers()['content-type'])).toMatch(/^image\//);
    expect((await resp.body()).length).toBeGreaterThan(0);
  });

  test('should point message attachment urls at the admin attachment endpoint and download the file', async ({ request }) => {
    const messages: any[] = [];

    for (const id of await recentReturnIds(request)) {
      const list = await sendAdminRequest(request, ADMIN_SALES.RMA_MESSAGES, { params: { return_id: String(id) } });
      expect(list.status()).toBe(200);
      messages.push(...rowsOf(await safeJson(list)));
    }

    for (const message of messages.filter((row: any) => !row.attachment)) {
      expect(message.attachmentUrl).toBeNull();
    }

    const withAttachment = messages.filter((row: any) => row.attachment);

    test.skip(withAttachment.length === 0, 'no return message with an attachment exists to read');

    for (const message of withAttachment) {
      expect(message.attachmentUrl).toMatch(new RegExp(`/api/admin/rma/messages/${message.id}/attachment$`));
    }

    const resp = await sendAdminRequest(request, ADMIN_SALES.RMA_MESSAGE_ATTACHMENT(withAttachment[0].id), {
      headers: BINARY_HEADERS,
    });
    expect(resp.status()).toBe(200);
    const disposition = String(resp.headers()['content-disposition']);
    expect(disposition).toContain('attachment');
    expect(disposition).toContain(withAttachment[0].attachment);
  });
});
