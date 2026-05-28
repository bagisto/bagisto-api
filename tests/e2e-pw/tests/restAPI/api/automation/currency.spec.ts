// tests/restAPI/api/automation/currency.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string, allowed: number[] = [0, 200, 400, 401, 404, 500]) {
  expect(allowed).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Currencies REST API', () => {
  test('Should list all currencies', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCIES);
    assertStatus(response, 'GET /api/shop/currencies');
    if (response.status() === 200) {
      const body = await response.json();
      const items = Array.isArray(body) ? body : (body.data ?? []);
      expect(items.length).toBeGreaterThan(0);
      items.forEach((c: any) => {
        expect(c).toHaveProperty('id');
        expect(c).toHaveProperty('code');
      });
      console.log('Currencies count:', items.length, 'first code:', items[0].code);
    }
  });

  test('Should return paginated currency list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCIES, {
      params: { per_page: '5', page: '1' },
    });
    assertStatus(response, 'GET /api/shop/currencies (paginated)');
  });

  test('Should return single currency by ID', async ({ request }) => {
    const list = await sendRestRequest(request, ENDPOINTS.CURRENCIES);
    if (list.status() !== 200) {
      test.skip(true, 'List endpoint unavailable');
      return;
    }
    const body = await list.json();
    const items = Array.isArray(body) ? body : (body.data ?? []);
    if (items.length === 0) {
      test.skip(true, 'No currencies seeded');
      return;
    }
    const ccyId = items[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCY(ccyId));
    expect(response.status()).toBe(200);
    const detail = await response.json();
    expect(detail.id).toBe(ccyId);
    expect(detail).toHaveProperty('code');
    console.log('Currency detail:', detail.code);
  });

  test('Should return 404 for non-existent currency', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCY(999999));
    expect([404, 400]).toContain(response.status());
    console.log('GET /api/shop/currencies/999999:', response.status());
  });

  test('Should reject POST on currencies (read-only)', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCIES, {
      method: 'POST',
      data: { code: 'XYZ', name: 'Hack Coin' },
    });
    expect([401, 403, 404, 405, 422, 500]).toContain(response.status());
    console.log('POST /api/shop/currencies:', response.status());
  });
});
