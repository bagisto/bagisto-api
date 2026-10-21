import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { env } from '../../../config/env';
import {
  ADMIN_COVERAGE_DECLINE_EU_WITHDRAWAL,
  ADMIN_COVERAGE_EU_WITHDRAWAL,
  ADMIN_COVERAGE_EU_WITHDRAWALS,
  ADMIN_COVERAGE_MARK_REFUNDED_EU_WITHDRAWAL,
} from '../../../graphql/Queries/coverage/adminWithdrawal.queries';
import {
  ADMIN_SALES_MESSAGES as MSG,
  UNKNOWN_ID,
  placeOrderWithWithdrawal,
  uniqueStamp,
} from '../../../../restAPI/rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 180_000 });

function withdrawalIri(id: number): string {
  return `/api/admin/eu-withdrawals/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectNoErrors(body: any): void {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
}

function expectError(body: any, message: string): void {
  expect(graphQLErrorMessages(body), JSON.stringify(body)).toContain(message);
}

async function withdrawalStatus(request: APIRequestContext, id: number): Promise<string> {
  const body = await gql(request, ADMIN_COVERAGE_EU_WITHDRAWAL, { id: withdrawalIri(id) });

  expectNoErrors(body);

  return body.data.adminEuWithdrawal.status;
}

interface AnonymousCall {
  label: string;
  query: string;
  variables: Record<string, any>;
}

const ANONYMOUS_CALLS: AnonymousCall[] = [
  { label: 'the withdrawals listing', query: ADMIN_COVERAGE_EU_WITHDRAWALS, variables: { first: 1 } },
  { label: 'the withdrawal detail', query: ADMIN_COVERAGE_EU_WITHDRAWAL, variables: { id: withdrawalIri(UNKNOWN_ID) } },
  { label: 'declining a withdrawal', query: ADMIN_COVERAGE_DECLINE_EU_WITHDRAWAL, variables: { input: { id: withdrawalIri(UNKNOWN_ID), declinedReason: 'x' } } },
  { label: 'refunding a withdrawal', query: ADMIN_COVERAGE_MARK_REFUNDED_EU_WITHDRAWAL, variables: { input: { id: withdrawalIri(UNKNOWN_ID), refundNote: 'x' } } },
];

test.describe('admin eu withdrawals graphql', () => {
  for (const call of ANONYMOUS_CALLS) {
    test(`should refuse ${call.label} without an admin token`, async ({ request }) => {
      const response = await sendGraphQLRequest(request, call.query, call.variables, {}, env.adminGraphqlEndpoint);

      expect(response.status()).toBe(401);
      expect((await response.json())?.message).toBe(MSG.UNAUTHENTICATED);
    });
  }

  test('should list the withdrawal filed for an order', async ({ request }) => {
    const reason = `graphql withdrawal ${uniqueStamp()}`;
    const { order, withdrawalId } = await placeOrderWithWithdrawal(request, reason);

    const body = await gql(request, ADMIN_COVERAGE_EU_WITHDRAWALS, { customer_email: order.customer.email, first: 5 });

    expectNoErrors(body);

    const connection = body.data.adminEuWithdrawals;
    expect(connection.totalCount).toBe(1);
    expect(connection.edges).toHaveLength(1);

    const node = connection.edges[0].node;
    expect(node._id).toBe(withdrawalId);
    expect(node.orderId).toBe(order.orderId);
    expect(node.orderIncrementId).toBe(order.incrementId);
    expect(node.customerEmail).toBe(order.customer.email);
    expect(node.status).toBe('received');
    expect(node.reasonText).toBe(reason);
  });

  test('should return no withdrawals for a status the order does not have', async ({ request }) => {
    const { order } = await placeOrderWithWithdrawal(request, 'graphql status filter');

    const body = await gql(request, ADMIN_COVERAGE_EU_WITHDRAWALS, { customer_email: order.customer.email, status: 'refunded' });

    expectNoErrors(body);
    expect(body.data.adminEuWithdrawals.edges).toEqual([]);
    expect(body.data.adminEuWithdrawals.totalCount).toBe(0);
  });

  test('should return a withdrawal by its iri', async ({ request }) => {
    const { order, withdrawalId } = await placeOrderWithWithdrawal(request, 'graphql detail');

    const body = await gql(request, ADMIN_COVERAGE_EU_WITHDRAWAL, { id: withdrawalIri(withdrawalId) });

    expectNoErrors(body);

    const node = body.data.adminEuWithdrawal;
    expect(node._id).toBe(withdrawalId);
    expect(node.orderId).toBe(order.orderId);
    expect(node.customerId).toBe(order.customer.id);
    expect(node.isGuest).toBe(false);
    expect(node.reasonText).toBe('graphql detail');
    expect(typeof node.uuid).toBe('string');
    expect(node.declinedAt).toBeNull();
  });

  test('should report an unknown withdrawal', async ({ request }) => {
    expectError(await gql(request, ADMIN_COVERAGE_EU_WITHDRAWAL, { id: withdrawalIri(UNKNOWN_ID) }), MSG.WITHDRAWAL_NOT_FOUND);
  });

  test('should decline a withdrawal with a reason', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'graphql decline');
    const declinedReason = `graphql declined ${uniqueStamp()}`;

    const body = await gql(request, ADMIN_COVERAGE_DECLINE_EU_WITHDRAWAL, {
      input: { id: withdrawalIri(withdrawalId), declinedReason },
    });

    expectNoErrors(body);

    const node = body.data.declineAdminEuWithdrawal.adminEuWithdrawal;
    expect(node._id).toBe(withdrawalId);
    expect(node.status).toBe('declined');
    expect(node.declinedReason).toBe(declinedReason);
    expect(typeof node.declinedAt).toBe('string');
    expect(typeof node.declinedByUserId).toBe('number');
    expect(node.message).toBe(MSG.WITHDRAWAL_DECLINED);
    expect(await withdrawalStatus(request, withdrawalId)).toBe('declined');
  });

  test('should report a decline without a reason', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'graphql decline without reason');

    const body = await gql(request, ADMIN_COVERAGE_DECLINE_EU_WITHDRAWAL, { input: { id: withdrawalIri(withdrawalId) } });

    expectError(body, MSG.WITHDRAWAL_DECLINE_REASON_REQUIRED);
    expect(await withdrawalStatus(request, withdrawalId)).toBe('received');
  });

  test('should report an unknown withdrawal when declining', async ({ request }) => {
    expectError(
      await gql(request, ADMIN_COVERAGE_DECLINE_EU_WITHDRAWAL, { input: { id: withdrawalIri(UNKNOWN_ID), declinedReason: 'unknown' } }),
      MSG.WITHDRAWAL_NOT_FOUND,
    );
  });

  test('should mark a declined withdrawal as refunded and clear the decline', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'graphql refund');
    expectNoErrors(await gql(request, ADMIN_COVERAGE_DECLINE_EU_WITHDRAWAL, {
      input: { id: withdrawalIri(withdrawalId), declinedReason: 'declined first' },
    }));

    const refundNote = `graphql refund ${uniqueStamp()}`;
    const body = await gql(request, ADMIN_COVERAGE_MARK_REFUNDED_EU_WITHDRAWAL, {
      input: { id: withdrawalIri(withdrawalId), refundNote },
    });

    expectNoErrors(body);

    const node = body.data.markRefundedAdminEuWithdrawal.adminEuWithdrawal;
    expect(node._id).toBe(withdrawalId);
    expect(node.status).toBe('refunded');
    expect(node.refundNote).toBe(refundNote);
    expect(typeof node.refundedAt).toBe('string');
    expect(node.declinedReason).toBeNull();
    expect(node.declinedAt).toBeNull();
    expect(node.message).toBe(MSG.WITHDRAWAL_REFUNDED);
  });

  test('should report a refund note longer than 500 characters', async ({ request }) => {
    const { withdrawalId } = await placeOrderWithWithdrawal(request, 'graphql long refund note');

    const body = await gql(request, ADMIN_COVERAGE_MARK_REFUNDED_EU_WITHDRAWAL, {
      input: { id: withdrawalIri(withdrawalId), refundNote: 'x'.repeat(501) },
    });

    expectError(body, MSG.WITHDRAWAL_REFUND_NOTE_TOO_LONG);
    expect(await withdrawalStatus(request, withdrawalId)).toBe('received');
  });

  test('should report an unknown withdrawal when refunding', async ({ request }) => {
    expectError(
      await gql(request, ADMIN_COVERAGE_MARK_REFUNDED_EU_WITHDRAWAL, { input: { id: withdrawalIri(UNKNOWN_ID), refundNote: 'unknown' } }),
      MSG.WITHDRAWAL_NOT_FOUND,
    );
  });
});
