import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import { sendRestRequest } from '../../../../restAPI/rest/helpers/restClient';
import { ENDPOINTS } from '../../../../restAPI/rest/endpoints/endpoints';
import { ADMIN_RMA } from '../../../../restAPI/rest/endpoints/coverage/adminRma.endpoints';
import { e2eFixtures, missingFixtureReason } from '../../../../fixtures/e2eFixtures';
import {
  RESEND_CONFIRMATION_ADMIN_EU_WITHDRAWAL,
  ADMIN_EU_WITHDRAWAL_QUERY,
} from '../../../graphql/Queries/coverage/adminRma.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/eu-withdrawals/999999999';

const RESENT = 'Confirmation email resent successfully.';

const SEND_FAILED = 'The confirmation email could not be sent.';

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  return resp.json();
}

function firstError(body: any): string | undefined {
  return body.errors?.[0]?.message;
}

async function fileCustomerWithdrawal(request: APIRequestContext): Promise<number | null> {
  const fx = e2eFixtures();

  const login = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email: fx.customer!.email, password: fx.customer!.password },
  });
  expect([200, 201], await login.text()).toContain(login.status());
  const token = (await login.json()).token;

  const resp = await sendRestRequest(request, ADMIN_RMA.SHOP_EU_WITHDRAWALS, {
    method: 'POST',
    data: { order_id: fx.order!.id },
    headers: { Authorization: `Bearer ${token}` },
  });

  if (resp.status() === 404) {
    return null;
  }

  expect([200, 201], await resp.text()).toContain(resp.status());
  return (await resp.json()).id;
}

test.describe('admin eu withdrawal resend confirmation graphql', () => {
  test('should refuse to resend a confirmation without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, RESEND_CONFIRMATION_ADMIN_EU_WITHDRAWAL, { id: UNKNOWN_IRI }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should report a resend for a withdrawal that does not exist', async ({ request }) => {
    const body = await gql(request, RESEND_CONFIRMATION_ADMIN_EU_WITHDRAWAL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe('Withdrawal declaration not found.');
    expect(body.data?.resendConfirmationAdminEuWithdrawal ?? null).toBeNull();
  });

  test('should resend the confirmation of a withdrawal the customer filed', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.order, missingFixtureReason('order'));

    const withdrawalId = await fileCustomerWithdrawal(request);
    test.skip(withdrawalId === null, 'eu withdrawal is not enabled for the order fixture channel');

    const iri = `/api/admin/eu-withdrawals/${withdrawalId}`;
    const body = await gql(request, RESEND_CONFIRMATION_ADMIN_EU_WITHDRAWAL, { id: iri });
    const detail = (await gql(request, ADMIN_EU_WITHDRAWAL_QUERY, { id: iri })).data.adminEuWithdrawal;

    if (body.errors) {
      expect(firstError(body)).toBe(SEND_FAILED);
      expect(typeof detail.confirmationError).toBe('string');
      return;
    }

    const result = body.data.resendConfirmationAdminEuWithdrawal.adminEuWithdrawal;
    expect(result).toEqual(expect.objectContaining({ _id: withdrawalId, orderId: fx.order!.id, message: RESENT, confirmationError: null }));
    expect(detail.confirmationError).toBeNull();
    expect(typeof (detail.finalConfirmationSentAt ?? detail.confirmationSentAt)).toBe('string');
  });
});
