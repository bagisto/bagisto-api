import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ENDPOINTS } from '../../../rest/endpoints/endpoints';
import { ADMIN_RMA } from '../../../rest/endpoints/coverage/adminRma.endpoints';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 999999999;

const RESENT = 'Confirmation email resent successfully.';

const SEND_FAILED = 'The confirmation email could not be sent.';

async function safeJson(resp: APIResponse): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

async function customerToken(request: APIRequestContext, email: string, password: string): Promise<string> {
  const resp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, { method: 'POST', data: { email, password } });
  expect([200, 201], await resp.text()).toContain(resp.status());
  const token = (await safeJson(resp))?.token;
  expect(typeof token).toBe('string');
  return token;
}

async function fileCustomerWithdrawal(request: APIRequestContext): Promise<number | null> {
  const fx = e2eFixtures();
  const token = await customerToken(request, fx.customer!.email, fx.customer!.password);

  const resp = await sendRestRequest(request, ADMIN_RMA.SHOP_EU_WITHDRAWALS, {
    method: 'POST',
    data: { order_id: fx.order!.id },
    headers: { Authorization: `Bearer ${token}` },
  });

  if (resp.status() === 404) {
    return null;
  }

  expect([200, 201], await resp.text()).toContain(resp.status());
  const id = (await safeJson(resp))?.id;
  expect(typeof id).toBe('number');
  return id;
}

test.describe('admin eu withdrawal resend confirmation rest api', () => {
  test('should refuse to resend a confirmation without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.EU_WITHDRAWAL_RESEND_CONFIRMATION(UNKNOWN_ID), { method: 'POST' });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should return 404 when resending the confirmation of an unknown withdrawal', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.EU_WITHDRAWAL_RESEND_CONFIRMATION(UNKNOWN_ID), { method: 'POST' });
    expect(resp.status()).toBe(404);
    expect(errorMessage(await safeJson(resp))).toBe('Withdrawal declaration not found.');
  });

  test('should resend the confirmation of a withdrawal the customer filed', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.order, missingFixtureReason('order'));

    const withdrawalId = await fileCustomerWithdrawal(request);
    test.skip(withdrawalId === null, 'eu withdrawal is not enabled for the order fixture channel');

    const resp = await sendAdminRequest(request, ADMIN_RMA.EU_WITHDRAWAL_RESEND_CONFIRMATION(withdrawalId!), { method: 'POST' });
    const body = await safeJson(resp);
    const detail = await safeJson(await sendAdminRequest(request, ADMIN_RMA.EU_WITHDRAWAL(withdrawalId!)));

    if (resp.status() === 422) {
      expect(errorMessage(body)).toBe(SEND_FAILED);
      expect(typeof detail.confirmationError).toBe('string');
      return;
    }

    expect(resp.status()).toBe(201);
    expect(body).toEqual(expect.objectContaining({ id: withdrawalId, orderId: fx.order!.id, message: RESENT }));
    expect(body.confirmationError).toBeNull();
    expect(detail.confirmationError).toBeNull();
    expect(typeof (detail.finalConfirmationSentAt ?? detail.confirmationSentAt)).toBe('string');
  });
});
