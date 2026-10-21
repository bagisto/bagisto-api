import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';
import {
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
  UNKNOWN_ID,
  basenameOf,
  expectMissingStorefrontKey,
  getWithoutStorefrontKey,
  safeJson,
  shopLogin,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ mode: 'serial', timeout: 90_000 });

function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

async function readPurchase(request: APIRequestContext, token: string, id: number): Promise<any> {
  const response = await sendRestRequest(request, REST_GAPS.CUSTOMER_DOWNLOADABLE_PRODUCT(id), { headers: bearer(token) });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(body.id).toBe(id);

  return body;
}

async function download(request: APIRequestContext, id: number, token?: string) {
  return sendRestRequest(request, REST_GAPS.CUSTOMER_DOWNLOADABLE_PRODUCT_DOWNLOAD(id), {
    headers: { Accept: 'application/octet-stream', ...(token ? bearer(token) : {}) },
  });
}

async function expectRefusal(response: any, status: number, message: string, error: string): Promise<void> {
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(status);
  expect(body?.message).toBe(message);
  expect(body?.error).toBe(error);
  expect(response.headers()['content-disposition']).toBeUndefined();
}

test.describe('shop customer downloadable product download rest api', () => {
  test('should stream the purchased file and consume exactly one download', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const linkId = fx.downloadable!.purchasedLinkId;
    const token = await shopLogin(request, fx.customer!.email, fx.customer!.password);
    const before = await readPurchase(request, token, linkId);

    test.skip(Number(before.downloadBought) === 0, 'the purchased link has unlimited downloads, so no counter moves');
    test.skip(
      Number(before.downloadBought) - Number(before.downloadUsed) - Number(before.downloadCanceled ?? 0) <= 0,
      'the purchased link has no downloads left'
    );

    const response = await download(request, linkId, token);

    expect(response.status(), await response.text()).toBe(200);

    const disposition = String(response.headers()['content-disposition'] ?? '');
    expect(disposition).toContain('attachment');

    const source = before.type === 'file' ? before.file : before.url;

    if (typeof source === 'string') {
      expect(disposition).toContain(basenameOf(source));
    }

    expect((await response.body()).length).toBeGreaterThan(0);

    const after = await readPurchase(request, token, linkId);
    expect(Number(after.downloadUsed)).toBe(Number(before.downloadUsed) + 1);
  });

  test('should refuse another customer the purchased download and leave its counter alone', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const linkId = fx.downloadable!.purchasedLinkId;
    const ownerToken = await shopLogin(request, fx.customer!.email, fx.customer!.password);
    const otherToken = await shopLogin(request, fx.otherCustomer!.email, fx.otherCustomer!.password);
    const before = await readPurchase(request, ownerToken, linkId);

    await expectRefusal(await download(request, linkId, otherToken), 404, MSG.DOWNLOAD_NOT_FOUND, 'not_found');

    const after = await readPurchase(request, ownerToken, linkId);
    expect(Number(after.downloadUsed)).toBe(Number(before.downloadUsed));
  });

  test('should report a purchased download that does not exist', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await shopLogin(request, fx.customer!.email, fx.customer!.password);

    await expectRefusal(await download(request, UNKNOWN_ID, token), 404, MSG.DOWNLOAD_NOT_FOUND, 'not_found');
  });

  test('should refuse a purchased download without a customer token', async ({ request }) => {
    const fx = e2eFixtures();
    const linkId = fx.downloadable?.purchasedLinkId ?? UNKNOWN_ID;

    await expectRefusal(await download(request, linkId), 401, MSG.DOWNLOAD_UNAUTHENTICATED, 'unauthenticated');
  });

  test('should refuse a purchased download without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.CUSTOMER_DOWNLOADABLE_PRODUCT_DOWNLOAD(UNKNOWN_ID)));
  });
});
