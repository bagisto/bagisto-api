import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { SHOP_ACCOUNT } from '../../../rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';

const PAID_CONTENT_KEYS = ['url', 'file', 'fileName'];

interface ApiResult {
  status: number;
  body: any;
}

async function get(request: APIRequestContext, endpoint: string): Promise<ApiResult> {
  const response = await sendRestRequest(request, endpoint);

  return { status: response.status(), body: await response.json() };
}

function idFromReference(value: any): number | null {
  if (typeof value === 'string') {
    const match = value.match(/(\d+)$/);

    return match ? Number(match[1]) : null;
  }

  if (value && typeof value === 'object' && value.id !== undefined) {
    return Number(value.id);
  }

  return null;
}

function refersTo(value: any, id: number): boolean {
  return idFromReference(value) === id;
}

function expectNoPaidContent(link: Record<string, any>): void {
  for (const key of PAID_CONTENT_KEYS) {
    expect(Object.keys(link)).not.toContain(key);
  }
}

async function productLinks(request: APIRequestContext, productId: number): Promise<any[]> {
  const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINKS_OF(productId));
  expect(status).toBe(200);
  expect(Array.isArray(body)).toBe(true);

  return body;
}

async function productSamples(request: APIRequestContext, productId: number): Promise<any[]> {
  const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLES_OF(productId));
  expect(status).toBe(200);
  expect(Array.isArray(body)).toBe(true);

  return body;
}

test.describe('shop account product downloadable links rest api', () => {
  test('should return a downloadable link by id without its paid content', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));

    const links = await productLinks(request, fx.downloadable!.productId);
    expect(links.length).toBeGreaterThan(0);

    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINK(links[0].id));

    expect(status).toBe(200);
    expect(body.id).toBe(links[0].id);
    expect(typeof body.type).toBe('string');
    expect(typeof body.price).toBe('number');
    expect(refersTo(body.product, fx.downloadable!.productId)).toBe(true);
    expectNoPaidContent(body);
  });

  test('should list downloadable links without their paid content', async ({ request }) => {
    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINKS);

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    for (const link of body) {
      expect(typeof link.id).toBe('number');
      expectNoPaidContent(link);
    }
  });

  test('should return 404 for a downloadable link that does not exist', async ({ request }) => {
    const { status } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINK(999999999));

    expect(status).toBe(404);
  });

  test('should return a downloadable link translation by id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));

    const links = await productLinks(request, fx.downloadable!.productId);
    expect(links.length).toBeGreaterThan(0);

    const translationId = idFromReference(links[0].translation) ?? idFromReference(links[0].translations?.[0]);
    test.skip(translationId === null, 'The fixture downloadable link has no translation.');

    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINK_TRANSLATION(translationId!));

    expect(status).toBe(200);
    expect(body.id).toBe(translationId);
    expect(typeof body.locale).toBe('string');
    expect(body.locale.length).toBeGreaterThan(0);
  });

  test('should list downloadable link translations', async ({ request }) => {
    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINK_TRANSLATIONS);

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    for (const translation of body) {
      expect(typeof translation.id).toBe('number');
      expect(typeof translation.locale).toBe('string');
    }
  });

  test('should return 404 for a downloadable link translation that does not exist', async ({ request }) => {
    const { status } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_LINK_TRANSLATION(999999999));

    expect(status).toBe(404);
  });
});

test.describe('shop account product downloadable samples rest api', () => {
  test('should return a downloadable sample by id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));

    const samples = await productSamples(request, fx.downloadable!.productId);
    test.skip(samples.length === 0, 'The fixture downloadable product has no samples.');

    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLE(samples[0].id));

    expect(status).toBe(200);
    expect(body.id).toBe(samples[0].id);
    expect(typeof body.type).toBe('string');
    expect(refersTo(body.product, fx.downloadable!.productId)).toBe(true);
  });

  test('should list downloadable samples', async ({ request }) => {
    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLES);

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    for (const sample of body) {
      expect(typeof sample.id).toBe('number');
      expect(typeof sample.type).toBe('string');
    }
  });

  test('should return 404 for a downloadable sample that does not exist', async ({ request }) => {
    const { status } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLE(999999999));

    expect(status).toBe(404);
  });

  test('should return a downloadable sample translation by id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.downloadable, missingFixtureReason('downloadable'));

    const samples = await productSamples(request, fx.downloadable!.productId);
    test.skip(samples.length === 0, 'The fixture downloadable product has no samples.');

    const translationId = idFromReference(samples[0].translation) ?? idFromReference(samples[0].translations?.[0]);
    test.skip(translationId === null, 'The fixture downloadable sample has no translation.');

    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLE_TRANSLATION(translationId!));

    expect(status).toBe(200);
    expect(body.id).toBe(translationId);
    expect(typeof body.locale).toBe('string');
    expect(body.locale.length).toBeGreaterThan(0);
  });

  test('should list downloadable sample translations', async ({ request }) => {
    const { status, body } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLE_TRANSLATIONS);

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    for (const translation of body) {
      expect(typeof translation.id).toBe('number');
      expect(typeof translation.locale).toBe('string');
    }
  });

  test('should return 404 for a downloadable sample translation that does not exist', async ({ request }) => {
    const { status } = await get(request, SHOP_ACCOUNT.PRODUCT_DOWNLOADABLE_SAMPLE_TRANSLATION(999999999));

    expect(status).toBe(404);
  });
});
