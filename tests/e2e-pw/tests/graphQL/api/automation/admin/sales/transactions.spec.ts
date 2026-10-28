import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { graphQLErrorMessages } from '../../../../graphql/helpers/testSupport';
import {
  ADMIN_TRANSACTIONS_LIST_QUERY,
  ADMIN_TRANSACTION_DETAIL_QUERY,
} from '../../../../graphql/Queries/admin/sales/transactions.queries';
import { ADMIN_COVERAGE_CREATE_TRANSACTION } from '../../../../graphql/Queries/coverage/adminSales.queries';
import {
  UNKNOWN_ID,
  invoiceOrder,
  placeOrder,
  setInvoiceState,
} from '../../../../../restAPI/rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 240_000 });

const TRANSACTION_NOT_FOUND = 'Transaction not found.';

const TRANSACTIONS_FOR_INVOICE = `
  query adminTransactions($first: Int, $invoiceId: Int) {
    adminTransactions(first: $first, invoice_id: $invoiceId) {
      edges {
        node {
          id
          _id
          transactionId
          orderId
          invoiceId
          status
        }
      }
    }
  }
`;

interface RecordedTransaction {
  id: number;
  transactionId: string;
  orderId: number;
  invoiceId: number;
}

async function runAdmin(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectNoErrors(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
}

function transactionIri(id: number): string {
  return `/api/admin/transactions/${id}`;
}

async function recordTransaction(request: APIRequestContext): Promise<RecordedTransaction> {
  const order = await placeOrder(request);
  const invoice = await invoiceOrder(request, order.orderId);
  await setInvoiceState(request, [invoice.id], 'pending');

  const body = await runAdmin(request, ADMIN_COVERAGE_CREATE_TRANSACTION, {
    input: { invoiceId: invoice.id, paymentMethod: 'cashondelivery', amount: invoice.baseGrandTotal },
  });
  expectNoErrors(body);

  const transaction = body.data.createAdminTransaction.adminTransaction;

  return {
    id: Number(transaction._id),
    transactionId: transaction.transactionId,
    orderId: order.orderId,
    invoiceId: invoice.id,
  };
}

test.describe('admin transactions graphql api', () => {
  test('should return a cursor connection for the listing', async ({ request }) => {
    const body = await runAdmin(request, ADMIN_TRANSACTIONS_LIST_QUERY, { first: 5 });
    expectNoErrors(body);

    const connection = body.data.adminTransactions;

    expect(Array.isArray(connection.edges)).toBe(true);
    expect(connection.edges.length).toBeLessThanOrEqual(5);
    expect(typeof connection.pageInfo.hasNextPage).toBe('boolean');
  });

  test('should list the transaction recorded for an invoice', async ({ request }) => {
    const transaction = await recordTransaction(request);

    const body = await runAdmin(request, TRANSACTIONS_FOR_INVOICE, { first: 10, invoiceId: transaction.invoiceId });
    expectNoErrors(body);

    expect(body.data.adminTransactions.edges.map((edge: any) => edge.node)).toEqual([
      {
        id: transactionIri(transaction.id),
        _id: transaction.id,
        transactionId: transaction.transactionId,
        orderId: transaction.orderId,
        invoiceId: transaction.invoiceId,
        status: 'paid',
      },
    ]);
  });

  test('should return the transaction by id', async ({ request }) => {
    const transaction = await recordTransaction(request);

    const body = await runAdmin(request, ADMIN_TRANSACTION_DETAIL_QUERY, { id: transactionIri(transaction.id) });
    expectNoErrors(body);

    expect(body.data.adminTransaction).toEqual({
      id: transactionIri(transaction.id),
      _id: transaction.id,
      transactionId: transaction.transactionId,
      orderId: transaction.orderId,
      status: 'paid',
    });
  });

  test('should report a transaction that does not exist', async ({ request }) => {
    const body = await runAdmin(request, ADMIN_TRANSACTION_DETAIL_QUERY, { id: transactionIri(UNKNOWN_ID) });

    expect(graphQLErrorMessages(body)).toEqual([TRANSACTION_NOT_FOUND]);
    expect(body.data?.adminTransaction ?? null).toBeNull();
  });
});
