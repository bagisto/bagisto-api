import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  ADMIN_SALES_COVERAGE as API,
  ADMIN_SALES_MESSAGES as MSG,
  UNKNOWN_ID,
  expectProblem,
  expectUnauthenticated,
  placeOrderWithWithdrawal,
  safeJson,
  uniqueStamp,
} from '../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 180_000 });

test.describe('admin eu withdrawals rest api', () => {
  test('should list the withdrawal filed for an order', async ({ request }) => {
    const reason = `changed my mind ${uniqueStamp()}`;
    const { order, withdrawalId } = await placeOrderWithWithdrawal(request, reason);

    const response = await sendAdminRequest(request, API.EU_WITHDRAWALS, {
      params: { customer_email: order.customer.email },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.meta.total).toBe(1);
    expect(body.data).toHaveLength(1);
    expect(body.data[0].id).toBe(withdrawalId);
    expect(body.data[0].orderId).toBe(order.orderId);
    expect(body.data[0].orderIncrementId).toBe(order.incrementId);
    expect(body.data[0].customerEmail).toBe(order.customer.email);
    expect(body.data[0].status).toBe('received');
    expect(body.data[0].reasonText).toBe(reason);
  });

  test('should filter the withdrawals by status', async ({ request }) => {
    const { order } = await placeOrderWithWithdrawal(request, 'status filter');

    const response = await sendAdminRequest(request, API.EU_WITHDRAWALS, {
      params: { customer_email: order.customer.email, status: 'declined' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.data).toEqual([]);
    expect(body.meta.total).toBe(0);
  });

  test('should refuse the withdrawals listing without an admin token', async ({ request }) => {
    await expectUnauthenticated(await sendRestRequest(request, API.EU_WITHDRAWALS));
  });

  test('should return a withdrawal by id', async ({ request }) => {
    const { order, withdrawalId } = await placeOrderWithWithdrawal(request, 'detail check');

    const response = await sendAdminRequest(request, API.EU_WITHDRAWAL(withdrawalId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(withdrawalId);
    expect(body.orderId).toBe(order.orderId);
    expect(body.customerId).toBe(order.customer.id);
    expect(body.isGuest).toBe(false);
    expect(body.status).toBe('received');
    expect(body.reasonText).toBe('detail check');
    expect(typeof body.uuid).toBe('string');
    expect(typeof body.receivedAt).toBe('string');
    expect(body.declinedAt).toBeNull();
    expect(body.refundedAt).toBeNull();
  });

  test('should return 404 for an unknown withdrawal', async ({ request }) => {
    await expectProblem(await sendAdminRequest(request, API.EU_WITHDRAWAL(UNKNOWN_ID)), 404, MSG.WITHDRAWAL_NOT_FOUND);
  });

  test('should refuse a withdrawal detail without an admin token', async ({ request }) => {
    await expectUnauthenticated(await sendRestRequest(request, API.EU_WITHDRAWAL(UNKNOWN_ID)));
  });

  test('should decline a withdrawal with a reason', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'to be declined');
    const declinedReason = `personalised goods ${uniqueStamp()}`;

    const response = await sendAdminRequest(request, API.EU_WITHDRAWAL_DECLINE(withdrawalId), {
      method: 'POST',
      data: { declined_reason: declinedReason },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBe(withdrawalId);
    expect(body.status).toBe('declined');
    expect(body.declinedReason).toBe(declinedReason);
    expect(typeof body.declinedAt).toBe('string');
    expect(typeof body.declinedByUserId).toBe('number');
    expect(body.message).toBe(MSG.WITHDRAWAL_DECLINED);

    const reread = await safeJson(await sendAdminRequest(request, API.EU_WITHDRAWAL(withdrawalId)));
    expect(reread.status).toBe('declined');
    expect(reread.declinedReason).toBe(declinedReason);
  });

  test('should reject a decline without a reason', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'decline without reason');

    const response = await sendAdminRequest(request, API.EU_WITHDRAWAL_DECLINE(withdrawalId), {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 422, MSG.WITHDRAWAL_DECLINE_REASON_REQUIRED);

    const reread = await safeJson(await sendAdminRequest(request, API.EU_WITHDRAWAL(withdrawalId)));
    expect(reread.status).toBe('received');
  });

  test('should return 404 when declining an unknown withdrawal', async ({ request }) => {
    const response = await sendAdminRequest(request, API.EU_WITHDRAWAL_DECLINE(UNKNOWN_ID), {
      method: 'POST',
      data: { declined_reason: 'unknown' },
    });

    await expectProblem(response, 404, MSG.WITHDRAWAL_NOT_FOUND);
  });

  test('should refuse a decline without an admin token', async ({ request }) => {
    const response = await sendRestRequest(request, API.EU_WITHDRAWAL_DECLINE(UNKNOWN_ID), {
      method: 'POST',
      data: { declined_reason: 'anonymous' },
    });

    await expectUnauthenticated(response);
  });

  test('should mark a declined withdrawal as refunded and clear the decline', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'to be refunded');
    const declined = await sendAdminRequest(request, API.EU_WITHDRAWAL_DECLINE(withdrawalId), {
      method: 'POST',
      data: { declined_reason: 'first declined' },
    });
    expect(declined.status(), await declined.text()).toBe(201);

    const refundNote = `refund ref ${uniqueStamp()}`;
    const response = await sendAdminRequest(request, API.EU_WITHDRAWAL_MARK_REFUNDED(withdrawalId), {
      method: 'POST',
      data: { refund_note: refundNote },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBe(withdrawalId);
    expect(body.status).toBe('refunded');
    expect(body.refundNote).toBe(refundNote);
    expect(typeof body.refundedAt).toBe('string');
    expect(body.declinedAt).toBeNull();
    expect(body.declinedReason).toBeNull();
    expect(body.message).toBe(MSG.WITHDRAWAL_REFUNDED);
  });

  test('should reject a refund note longer than 500 characters', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'long refund note');

    const response = await sendAdminRequest(request, API.EU_WITHDRAWAL_MARK_REFUNDED(withdrawalId), {
      method: 'POST',
      data: { refund_note: 'x'.repeat(501) },
    });

    await expectProblem(response, 422, MSG.WITHDRAWAL_REFUND_NOTE_TOO_LONG);

    const reread = await safeJson(await sendAdminRequest(request, API.EU_WITHDRAWAL(withdrawalId)));
    expect(reread.status).toBe('received');
  });

  test('should return 404 when refunding an unknown withdrawal', async ({ request }) => {
    const response = await sendAdminRequest(request, API.EU_WITHDRAWAL_MARK_REFUNDED(UNKNOWN_ID), {
      method: 'POST',
      data: { refund_note: 'unknown' },
    });

    await expectProblem(response, 404, MSG.WITHDRAWAL_NOT_FOUND);
  });

  test('should refuse a refund without an admin token', async ({ request }) => {
    const response = await sendRestRequest(request, API.EU_WITHDRAWAL_MARK_REFUNDED(UNKNOWN_ID), {
      method: 'POST',
      data: { refund_note: 'anonymous' },
    });

    await expectUnauthenticated(response);
  });
});
