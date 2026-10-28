import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../../rest/endpoints/endpoints';

test.describe.configure({ timeout: 60_000 });

const STAT_TYPES = [
  'over-all',
  'today',
  'stock-threshold-products',
  'total-sales',
  'top-selling-products',
  'top-customers',
];

test.describe('admin dashboard rest api', () => {
  test('default stats returns the over-all stats', async ({ request }) => {
    const response = await sendAdminRequest(request, ENDPOINTS.ADMIN_DASHBOARD_STATS);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body).toHaveLength(1);
    expect(body[0].type).toBe('over-all');
    expect(body[0]).toHaveProperty('dateRange');
    expect(body[0]).toHaveProperty('statistics');
  });

  for (const type of STAT_TYPES) {
    test(`stats type ${type} returns 200`, async ({ request }) => {
      const response = await sendAdminRequest(request, ENDPOINTS.ADMIN_DASHBOARD_STATS, {
        params: { type },
      });
      expect(response.status()).toBe(200);

      const body = await response.json();
      expect(body).toHaveLength(1);
      expect(body[0].type).toBe(type);
      expect(body[0]).toHaveProperty('statistics');
    });
  }

  test('stats with date range and channel returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ENDPOINTS.ADMIN_DASHBOARD_STATS, {
      params: {
        start: '2026-01-01',
        end: '2026-05-26',
        channel: 'default',
      },
    });
    expect(response.status()).toBe(200);
    expect((await response.json())[0].type).toBe('over-all');
  });

  test('stats type invalid is rejected with 400', async ({ request }) => {
    const response = await sendAdminRequest(request, ENDPOINTS.ADMIN_DASHBOARD_STATS, {
      params: { type: 'definitely-not-a-real-type' },
    });
    expect(response.status()).toBe(400);
    expect((await response.json()).detail).toBe(
      'Unknown dashboard stat type. Valid types: over-all, today, stock-threshold-products, total-sales, top-selling-products, top-customers.',
    );
  });
});
