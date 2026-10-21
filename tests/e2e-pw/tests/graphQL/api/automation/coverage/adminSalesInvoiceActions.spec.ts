import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { env } from '../../../config/env';
import {
  ADMIN_COVERAGE_CREATE_TRANSACTION,
  ADMIN_COVERAGE_INVOICE_MASS_UPDATE_STATUS,
  ADMIN_COVERAGE_INVOICE_SEND_DUPLICATE,
  ADMIN_COVERAGE_REPORTING_CUSTOMERS_VIEW,
} from '../../../graphql/Queries/coverage/adminSales.queries';
import {
  ADMIN_SALES_MESSAGES as MSG,
  UNKNOWN_ID,
  invoiceOrder,
  invoiceState,
  placeOrder,
  setInvoiceState,
  uniqueStamp,
} from '../../../../restAPI/rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 240_000 });

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

async function expectAnonymousRefused(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<void> {
  const response = await sendGraphQLRequest(request, query, variables, {}, env.adminGraphqlEndpoint);

  expect(response.status()).toBe(401);
  expect((await response.json())?.message).toBe(MSG.UNAUTHENTICATED);
}

async function invoicedOrder(request: APIRequestContext) {
  const order = await placeOrder(request);
  const invoice = await invoiceOrder(request, order.orderId);

  return { order, invoice };
}

test.describe('admin invoice mass status update graphql', () => {
  test('should move the selected invoice to the requested state', async ({ request }) => {
    const { invoice } = await invoicedOrder(request);

    const body = await gql(request, ADMIN_COVERAGE_INVOICE_MASS_UPDATE_STATUS, {
      input: { indices: [invoice.id, UNKNOWN_ID], value: 'overdue' },
    });

    expectNoErrors(body);

    const result = body.data.createAdminInvoiceMassUpdateStatus.adminInvoiceMassUpdateStatus;
    expect(result.updated).toEqual([invoice.id]);
    expect(result.message).toBe(MSG.INVOICE_MASS_STATUS_SUCCESS);
    expect(await invoiceState(request, invoice.id)).toBe('overdue');
  });

  test('should report a status outside pending paid and overdue', async ({ request }) => {
    const { invoice } = await invoicedOrder(request);

    const body = await gql(request, ADMIN_COVERAGE_INVOICE_MASS_UPDATE_STATUS, {
      input: { indices: [invoice.id], value: 'refunded' },
    });

    expectError(body, MSG.INVOICE_MASS_STATUS_VALUE_INVALID);
    expect(await invoiceState(request, invoice.id)).toBe(invoice.state);
  });

  test('should report an empty selection', async ({ request }) => {
    expectError(
      await gql(request, ADMIN_COVERAGE_INVOICE_MASS_UPDATE_STATUS, { input: { indices: [], value: 'paid' } }),
      MSG.INVOICE_MASS_STATUS_INDICES_REQUIRED,
    );
  });

  test('should refuse the mass status update without an admin token', async ({ request }) => {
    await expectAnonymousRefused(request, ADMIN_COVERAGE_INVOICE_MASS_UPDATE_STATUS, {
      input: { indices: [UNKNOWN_ID], value: 'paid' },
    });
  });
});

test.describe('admin invoice send duplicate graphql', () => {
  test('should send the invoice to the requested address', async ({ request }) => {
    const { invoice } = await invoicedOrder(request);
    const email = `duplicate_${uniqueStamp()}@example.com`;

    const body = await gql(request, ADMIN_COVERAGE_INVOICE_SEND_DUPLICATE, { input: { invoiceId: invoice.id, email } });

    expectNoErrors(body);

    const result = body.data.createAdminInvoiceSendDuplicate.adminInvoiceSendDuplicate;
    expect(result._id).toBe(invoice.id);
    expect(result.email).toBe(email);
    expect(result.success).toBe(true);
    expect(result.message).toBe(`Invoice email sent to ${email}.`);
  });

  test('should default the recipient to the order customer', async ({ request }) => {
    const { order, invoice } = await invoicedOrder(request);

    const body = await gql(request, ADMIN_COVERAGE_INVOICE_SEND_DUPLICATE, { input: { invoiceId: invoice.id } });

    expectNoErrors(body);
    expect(body.data.createAdminInvoiceSendDuplicate.adminInvoiceSendDuplicate.email).toBe(order.customer.email);
  });

  test('should report an invalid recipient address', async ({ request }) => {
    const { invoice } = await invoicedOrder(request);

    expectError(
      await gql(request, ADMIN_COVERAGE_INVOICE_SEND_DUPLICATE, { input: { invoiceId: invoice.id, email: 'not-an-email' } }),
      MSG.INVOICE_EMAIL_INVALID,
    );
  });

  test('should report an unknown invoice', async ({ request }) => {
    expectError(
      await gql(request, ADMIN_COVERAGE_INVOICE_SEND_DUPLICATE, { input: { invoiceId: UNKNOWN_ID, email: 'someone@example.com' } }),
      MSG.INVOICE_NOT_FOUND,
    );
  });

  test('should refuse to send a duplicate without an admin token', async ({ request }) => {
    await expectAnonymousRefused(request, ADMIN_COVERAGE_INVOICE_SEND_DUPLICATE, {
      input: { invoiceId: UNKNOWN_ID, email: 'someone@example.com' },
    });
  });
});

test.describe('admin transaction create graphql', () => {
  test('should record a payment that settles a pending invoice', async ({ request }) => {
    const { order, invoice } = await invoicedOrder(request);
    await setInvoiceState(request, [invoice.id], 'pending');

    const body = await gql(request, ADMIN_COVERAGE_CREATE_TRANSACTION, {
      input: { invoiceId: invoice.id, paymentMethod: 'cashondelivery', amount: invoice.baseGrandTotal },
    });

    expectNoErrors(body);

    const transaction = body.data.createAdminTransaction.adminTransaction;
    expect(transaction._id).toBeGreaterThan(0);
    expect(transaction.invoiceId).toBe(invoice.id);
    expect(transaction.orderId).toBe(order.orderId);
    expect(transaction.orderIncrementId).toBe(order.incrementId);
    expect(transaction.amount).toBeCloseTo(invoice.baseGrandTotal, 2);
    expect(transaction.status).toBe('paid');
    expect(transaction.paymentMethod).toBe('cashondelivery');
    expect(transaction.transactionId).toMatch(/^[0-9a-f]{40}$/);
    expect(await invoiceState(request, invoice.id)).toBe('paid');

    expectError(
      await gql(request, ADMIN_COVERAGE_CREATE_TRANSACTION, {
        input: { invoiceId: invoice.id, paymentMethod: 'cashondelivery', amount: 1 },
      }),
      MSG.TRANSACTION_ALREADY_PAID,
    );
  });

  test('should report a payment larger than the invoice total', async ({ request }) => {
    const { invoice } = await invoicedOrder(request);
    await setInvoiceState(request, [invoice.id], 'pending');

    const body = await gql(request, ADMIN_COVERAGE_CREATE_TRANSACTION, {
      input: { invoiceId: invoice.id, paymentMethod: 'cashondelivery', amount: invoice.baseGrandTotal + 1000 },
    });

    expectError(body, MSG.TRANSACTION_AMOUNT_EXCEEDS);
    expect(await invoiceState(request, invoice.id)).toBe('pending');
  });

  test('should report a missing invoice id', async ({ request }) => {
    expectError(
      await gql(request, ADMIN_COVERAGE_CREATE_TRANSACTION, { input: { paymentMethod: 'cashondelivery', amount: 1 } }),
      MSG.TRANSACTION_INVOICE_REQUIRED,
    );
  });

  test('should report an unknown invoice for a payment', async ({ request }) => {
    expectError(
      await gql(request, ADMIN_COVERAGE_CREATE_TRANSACTION, {
        input: { invoiceId: UNKNOWN_ID, paymentMethod: 'cashondelivery', amount: 1 },
      }),
      MSG.TRANSACTION_INVOICE_MISSING,
    );
  });

  test('should refuse to record a payment without an admin token', async ({ request }) => {
    await expectAnonymousRefused(request, ADMIN_COVERAGE_CREATE_TRANSACTION, {
      input: { invoiceId: UNKNOWN_ID, paymentMethod: 'cashondelivery', amount: 1 },
    });
  });
});

test.describe('admin customer reporting view graphql', () => {
  test('should return the customers view as a table of columns and records', async ({ request }) => {
    const body = await gql(request, ADMIN_COVERAGE_REPORTING_CUSTOMERS_VIEW, { type: 'customers-with-most-orders' });

    expectNoErrors(body);

    const view = body.data.viewStatsAdminReportingCustomers;
    expect(view.entity).toBe('customers');
    expect(view.type).toBe('customers-with-most-orders');
    expect(Array.isArray(view.statistics.columns)).toBe(true);
    expect(view.statistics.columns.length).toBeGreaterThan(0);
    expect(Array.isArray(view.statistics.records)).toBe(true);
    expect(view.dateRange).toBeTruthy();
  });

  test('should default the customers view to the total customers table', async ({ request }) => {
    const body = await gql(request, ADMIN_COVERAGE_REPORTING_CUSTOMERS_VIEW, {});

    expectNoErrors(body);
    expect(body.data.viewStatsAdminReportingCustomers.type).toBe('total-customers');
  });

  test('should report an unknown customers view type', async ({ request }) => {
    expectError(await gql(request, ADMIN_COVERAGE_REPORTING_CUSTOMERS_VIEW, { type: 'total-sales' }), MSG.REPORTING_INVALID_TYPE);
  });

  test('should refuse the customers view without an admin token', async ({ request }) => {
    await expectAnonymousRefused(request, ADMIN_COVERAGE_REPORTING_CUSTOMERS_VIEW, { type: 'total-customers' });
  });
});
