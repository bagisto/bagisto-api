import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';
import {
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
  RMA_STATUS,
  UNKNOWN_ID,
  expectAdminUnauthenticated,
  expectDetail,
  rowsOf,
  safeJson,
  uniqueStamp,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

function uniqueText(label: string): string {
  return `e2e rest gap ${label} ${uniqueStamp()}`;
}

async function readReturn(request: APIRequestContext, id: number): Promise<any> {
  const response = await sendAdminRequest(request, REST_GAPS.RMA_REQUEST(id));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

async function returnableItem(request: APIRequestContext, orderId: number): Promise<any | undefined> {
  const response = await sendAdminRequest(request, REST_GAPS.RMA_REQUEST_ORDER_ITEMS, { params: { order_id: String(orderId) } });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return rowsOf(body).find((item: any) => item.forReturnQuantity >= 1);
}

async function updateStatus(request: APIRequestContext, id: number, data: Record<string, any>) {
  return sendAdminRequest(request, REST_GAPS.RMA_REQUEST_UPDATE_STATUS(id), { method: 'POST', data });
}

async function reopen(request: APIRequestContext, id: number) {
  return sendAdminRequest(request, REST_GAPS.RMA_REQUEST_REOPEN(id), { method: 'POST', data: {} });
}

test.describe('admin rma request status and reopen rest api', () => {
  let reasonId: number;
  let orderItem: any;

  test.beforeEach(async ({ request }) => {
    reasonId = 0;

    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    orderItem = await returnableItem(request, fx.order!.id);
    test.skip(!orderItem, 'the order fixture has no item with a quantity left to return');

    const response = await sendAdminRequest(request, REST_GAPS.RMA_REASONS, {
      method: 'POST',
      data: { title: uniqueText('reason'), status: 1, position: 1, resolution_type: ['return'] },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    reasonId = body.id;
  });

  test.afterEach(async ({ request }) => {
    if (reasonId) {
      await sendAdminRequest(request, REST_GAPS.RMA_REASON(reasonId), { method: 'DELETE' });
    }
  });

  async function openReturn(request: APIRequestContext): Promise<any> {
    const response = await sendAdminRequest(request, REST_GAPS.RMA_REQUESTS, {
      method: 'POST',
      data: {
        order_id: e2eFixtures().order!.id,
        order_item_id: orderItem.orderItemId,
        rma_qty: 1,
        resolution_type: 'return',
        rma_reason_id: reasonId,
        information: uniqueText('information'),
        package_condition: 'open',
      },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(typeof body.id).toBe('number');
    expect(body.statusId).toBe(RMA_STATUS.PENDING);

    return body;
  }

  test('should decline a pending return and record the status note', async ({ request }) => {
    const created = await openReturn(request);

    const response = await updateStatus(request, created.id, { rma_status_id: RMA_STATUS.DECLINED });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBe(created.id);
    expect(body.statusId).toBe(RMA_STATUS.DECLINED);
    expect(body.canReopen).toBe(true);
    expect(body.messagesCount).toBe(created.messagesCount + 1);

    expect((await readReturn(request, created.id)).statusId).toBe(RMA_STATUS.DECLINED);
  });

  test('should refuse a status that does not exist and keep the return pending', async ({ request }) => {
    const created = await openReturn(request);

    await expectDetail(await updateStatus(request, created.id, { rma_status_id: UNKNOWN_ID }), 422, MSG.RETURN_INVALID_STATUS);

    expect((await readReturn(request, created.id)).statusId).toBe(RMA_STATUS.PENDING);
  });

  test('should refuse a status change that names no status', async ({ request }) => {
    const created = await openReturn(request);

    await expectDetail(await updateStatus(request, created.id, {}), 422, MSG.RETURN_INVALID_STATUS);

    expect((await readReturn(request, created.id)).statusId).toBe(RMA_STATUS.PENDING);
  });

  test('should refuse to reopen a pending return then reopen it once declined', async ({ request }) => {
    const created = await openReturn(request);

    await expectDetail(await reopen(request, created.id), 422, MSG.RETURN_REOPEN_NOT_ALLOWED);
    expect((await readReturn(request, created.id)).statusId).toBe(RMA_STATUS.PENDING);

    const declinedResponse = await updateStatus(request, created.id, { rma_status_id: RMA_STATUS.DECLINED });
    const declined = await safeJson(declinedResponse);
    expect(declinedResponse.status(), JSON.stringify(declined)).toBe(201);

    const response = await reopen(request, created.id);
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBe(created.id);
    expect(body.statusId).toBe(RMA_STATUS.PENDING);
    expect(body.canReopen).toBe(false);
    expect(body.messagesCount).toBe(declined.messagesCount + 1);

    expect((await readReturn(request, created.id)).statusId).toBe(RMA_STATUS.PENDING);
  });
});

test.describe('admin rma request action refusals rest api', () => {
  test('should report a status change on a return that does not exist', async ({ request }) => {
    await expectDetail(await updateStatus(request, UNKNOWN_ID, { rma_status_id: RMA_STATUS.DECLINED }), 404, MSG.RETURN_NOT_FOUND);
  });

  test('should report a reopen of a return that does not exist', async ({ request }) => {
    await expectDetail(await reopen(request, UNKNOWN_ID), 404, MSG.RETURN_NOT_FOUND);
  });

  test('should refuse a status change without an admin token', async ({ request }) => {
    await expectAdminUnauthenticated(
      await sendRestRequest(request, REST_GAPS.RMA_REQUEST_UPDATE_STATUS(UNKNOWN_ID), {
        method: 'POST',
        data: { rma_status_id: RMA_STATUS.DECLINED },
      })
    );
  });

  test('should refuse a reopen without an admin token', async ({ request }) => {
    await expectAdminUnauthenticated(
      await sendRestRequest(request, REST_GAPS.RMA_REQUEST_REOPEN(UNKNOWN_ID), { method: 'POST', data: {} })
    );
  });
});
