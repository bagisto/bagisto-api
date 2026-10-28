import { test, expect, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_RMA } from '../../../rest/endpoints/coverage/adminRma.endpoints';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 999999999;

async function safeJson(resp: APIResponse): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

function rowsOf(body: any): any[] {
  if (Array.isArray(body)) return body;
  return Array.isArray(body?.data) ? body.data : [];
}

test.describe('admin rma returnable order items rest api', () => {
  test('should refuse the order item lookup without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.REQUEST_ORDER_ITEMS, { params: { order_id: '1' } });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should refuse the order item lookup without an order id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REQUEST_ORDER_ITEMS);
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe('The selected order is not valid.');
  });

  test('should return no items for an order that does not exist', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.REQUEST_ORDER_ITEMS, { params: { order_id: String(UNKNOWN_ID) } });
    expect(resp.status()).toBe(200);
    expect(rowsOf(await safeJson(resp))).toEqual([]);
  });

  test('should list the items of the order with their server side quantity caps', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const resp = await sendAdminRequest(request, ADMIN_RMA.REQUEST_ORDER_ITEMS, { params: { order_id: String(fx.order!.id) } });
    expect(resp.status()).toBe(200);
    const rows = rowsOf(await safeJson(resp));

    test.skip(rows.length === 0, 'the order fixture has no item inside its return window');

    for (const row of rows) {
      expect(typeof row.orderItemId).toBe('number');
      expect(typeof row.sku).toBe('string');
      expect(typeof row.name).toBe('string');
      expect(row.currentQuantity).toBe(row.qtyOrdered - row.rmaQuantity);
      expect(row.forReturnQuantity).toBeGreaterThanOrEqual(0);
      expect(row.forCancelQuantity).toBeGreaterThanOrEqual(0);
      expect(typeof row.rmaReturnPeriod).toBe('number');
    }

    expect(rows.map((row: any) => row.productId)).toContain(fx.order!.productId);
  });
});
