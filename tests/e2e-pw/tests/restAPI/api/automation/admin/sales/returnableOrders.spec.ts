import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../../rest/helpers/restClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';

test.describe.configure({ timeout: 60_000 });

const ROW_KEYS = [
  'id',
  'incrementId',
  'status',
  'customerName',
  'isGuest',
  'grandTotal',
  'formattedGrandTotal',
  'orderCurrencyCode',
  'methodTitle',
  'totalQtyOrdered',
  'totalRmaQty',
  'returnableQty',
  'createdAt',
];

const INELIGIBLE_STATUSES = ['canceled', 'closed', 'fraud', 'pending_payment'];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

async function listReturnableOrders(request: APIRequestContext, params: Record<string, string> = {}) {
  const resp = await sendAdminRequest(request, ADMIN_SALES.RMA_RETURNABLE_ORDERS, { params });
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  expect(Array.isArray(body?.data)).toBe(true);
  expect(body?.meta).toBeTruthy();
  return body;
}

test.describe('admin rma returnable orders rest api', () => {
  test('should refuse the order picker without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_SALES.RMA_RETURNABLE_ORDERS);
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should list only orders that still have a quantity to return in a data and meta envelope', async ({ request }) => {
    const body = await listReturnableOrders(request, { per_page: '20' });

    expect(body.meta).toEqual(expect.objectContaining({
      currentPage: 1,
      perPage: 20,
      total: expect.any(Number),
      lastPage: expect.any(Number),
    }));
    expect(body.data.length).toBeLessThanOrEqual(20);

    for (const row of body.data) {
      for (const key of ROW_KEYS) {
        expect(row, `row ${row.id} carries ${key}`).toHaveProperty(key);
      }
      expect(typeof row.isGuest).toBe('boolean');
      expect(INELIGIBLE_STATUSES).not.toContain(row.status);
      expect(row.returnableQty).toBe(row.totalQtyOrdered - row.totalRmaQty);
      expect(row.returnableQty).toBeGreaterThan(0);
    }
  });

  test('should cap the page size at fifty', async ({ request }) => {
    const body = await listReturnableOrders(request, { per_page: '500' });
    expect(body.meta.perPage).toBe(50);
    expect(body.data.length).toBeLessThanOrEqual(50);
  });

  test('should return no orders for an increment id no order has', async ({ request }) => {
    const body = await listReturnableOrders(request, { increment_id: `no-such-order-${Date.now()}` });
    expect(body.data).toEqual([]);
    expect(body.meta.total).toBe(0);
  });

  test('should narrow the list to the order whose increment id and id are requested', async ({ request }) => {
    const first = (await listReturnableOrders(request, { per_page: '1' })).data[0];
    test.skip(!first, 'no order a return can be opened for exists to read');

    const byIncrement = await listReturnableOrders(request, { increment_id: first.incrementId });
    expect(byIncrement.data.map((row: any) => row.id)).toContain(first.id);
    for (const row of byIncrement.data) {
      expect(String(row.incrementId).toLowerCase()).toContain(String(first.incrementId).toLowerCase());
    }

    const byId = await listReturnableOrders(request, { id: String(first.id) });
    expect(byId.data.map((row: any) => row.id)).toEqual([first.id]);
  });
});
