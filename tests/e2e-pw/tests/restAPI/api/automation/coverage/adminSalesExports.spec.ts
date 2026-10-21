import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  ADMIN_SALES_COVERAGE as API,
  ADMIN_SALES_MESSAGES as MSG,
  EXPORT_CONTENT_TYPES,
  csvHeaderRow,
  invoiceOrder,
  parseCsvRow,
  placeOrder,
  expectProblem,
  expectUnauthenticated,
  safeJson,
} from '../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 180_000 });

type ExportFormat = keyof typeof EXPORT_CONTENT_TYPES;

interface SalesExport {
  label: string;
  path: string;
  filename: string;
  headers: string[];
}

const SALES_EXPORTS: SalesExport[] = [
  {
    label: 'orders',
    path: API.ORDERS_EXPORT,
    filename: 'orders',
    headers: ['ID', 'Status', 'Grand Total', 'Payment Method', 'Channel', 'Customer', 'Email', 'Order Date'],
  },
  {
    label: 'invoices',
    path: API.INVOICES_EXPORT,
    filename: 'invoices',
    headers: ['ID', 'Order ID', 'Status', 'Grand Total', 'Invoice Date'],
  },
  {
    label: 'shipments',
    path: API.SHIPMENTS_EXPORT,
    filename: 'shipments',
    headers: ['ID', 'Order ID', 'Total Qty', 'Inventory Source', 'Shipped To', 'Shipment Date'],
  },
  {
    label: 'refunds',
    path: API.REFUNDS_EXPORT,
    filename: 'refunds',
    headers: ['ID', 'Order ID', 'Refunded Amount', 'Billed To', 'Refund Date'],
  },
  {
    label: 'transactions',
    path: API.TRANSACTIONS_EXPORT,
    filename: 'transactions',
    headers: ['ID', 'Transaction ID', 'Invoice ID', 'Order ID', 'Status', 'Date'],
  },
  {
    label: 'bookings',
    path: API.BOOKINGS_EXPORT,
    filename: 'bookings',
    headers: ['ID', 'Order ID', 'Qty', 'From', 'To', 'Booking Date'],
  },
];

const XLSX_MAGIC = '504b0304';

const XLS_MAGIC = 'd0cf11e0';

function csvRows(body: string): string[][] {
  return body.replace(/^\uFEFF/, '').split(/\r?\n/).slice(1).filter(line => line.length > 0).map(parseCsvRow);
}

function acceptFor(format: ExportFormat): Record<string, string> {
  return { Accept: EXPORT_CONTENT_TYPES[format] };
}

async function downloadExport(
  request: APIRequestContext,
  path: string,
  format: ExportFormat,
  params: Record<string, string> = {},
): Promise<APIResponse> {
  return sendAdminRequest(request, path, {
    params: { format, ...params },
    headers: acceptFor(format),
  });
}

async function expectAttachment(response: APIResponse, format: ExportFormat, filename: string): Promise<Buffer> {
  expect(response.status(), await response.text()).toBe(200);
  expect(String(response.headers()['content-type'])).toContain(EXPORT_CONTENT_TYPES[format]);
  expect(String(response.headers()['content-disposition'])).toBe(`attachment; filename="${filename}.${format}"`);

  const bytes = await response.body();
  expect(bytes.byteLength).toBeGreaterThan(0);

  return bytes;
}

test.describe('admin sales exports rest api', () => {
  for (const salesExport of SALES_EXPORTS) {
    test(`should export ${salesExport.label} as a csv attachment with its header row`, async ({ request }) => {
      const response = await downloadExport(request, salesExport.path, 'csv');
      const bytes = await expectAttachment(response, 'csv', salesExport.filename);

      expect(csvHeaderRow(bytes.toString('utf8'))).toEqual(salesExport.headers);
    });

    test(`should export ${salesExport.label} as xls and xlsx workbooks`, async ({ request }) => {
      const xlsx = await expectAttachment(await downloadExport(request, salesExport.path, 'xlsx'), 'xlsx', salesExport.filename);
      expect(xlsx.subarray(0, 4).toString('hex')).toBe(XLSX_MAGIC);

      const xls = await expectAttachment(await downloadExport(request, salesExport.path, 'xls'), 'xls', salesExport.filename);
      expect(xls.subarray(0, 4).toString('hex')).toBe(XLS_MAGIC);
    });

    test(`should reject an unsupported ${salesExport.label} export format`, async ({ request }) => {
      const response = await sendAdminRequest(request, salesExport.path, {
        params: { format: 'pdf' },
        headers: acceptFor('csv'),
      });

      await expectProblem(response, 422, MSG.EXPORT_FORMAT_UNSUPPORTED);
    });

    test(`should refuse the ${salesExport.label} export without an admin token`, async ({ request }) => {
      const response = await sendRestRequest(request, salesExport.path, {
        params: { format: 'csv' },
        headers: acceptFor('csv'),
      });

      await expectUnauthenticated(response);
    });
  }

  test('should list a placed order and its invoice in the filtered exports', async ({ request }) => {
    const order = await placeOrder(request);

    const orders = await expectAttachment(
      await downloadExport(request, API.ORDERS_EXPORT, 'csv', { order_id: order.incrementId }),
      'csv',
      'orders',
    );
    const orderRow = csvRows(orders.toString('utf8')).find(row => row[0] === order.incrementId);

    expect(orderRow, 'placed order missing from the orders export').toBeTruthy();
    expect(orderRow![1]).toBe('pending');
    expect(orderRow![6]).toBe(order.customer.email);

    const invoice = await invoiceOrder(request, order.orderId);
    const invoices = await expectAttachment(
      await downloadExport(request, API.INVOICES_EXPORT, 'csv', { id: String(invoice.id) }),
      'csv',
      'invoices',
    );
    const invoiceRows = csvRows(invoices.toString('utf8'));

    expect(invoiceRows).toHaveLength(1);
    expect(invoiceRows[0][1]).toBe(order.incrementId);
    expect(invoiceRows[0][2]).toBe(invoice.state);
  });
});

test.describe('admin customer reporting view and export rest api', () => {
  const TYPE = 'customers-with-most-sales';

  test('should return the customers view as a table of columns and records', async ({ request }) => {
    const response = await sendAdminRequest(request, API.REPORTING_CUSTOMERS_VIEW, { params: { type: TYPE } });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body[0].entity).toBe('customers');
    expect(body[0].type).toBe(TYPE);
    expect(Array.isArray(body[0].statistics.columns)).toBe(true);
    expect(body[0].statistics.columns.length).toBeGreaterThan(0);
    expect(Array.isArray(body[0].statistics.records)).toBe(true);
  });

  test('should default the customers view to the total customers table', async ({ request }) => {
    const response = await sendAdminRequest(request, API.REPORTING_CUSTOMERS_VIEW);
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body[0].entity).toBe('customers');
    expect(body[0].type).toBe('total-customers');
  });

  test('should reject an unknown customers view type', async ({ request }) => {
    const response = await sendAdminRequest(request, API.REPORTING_CUSTOMERS_VIEW, { params: { type: 'total-sales' } });

    await expectProblem(response, 400, MSG.REPORTING_INVALID_TYPE);
  });

  test('should refuse the customers view without an admin token', async ({ request }) => {
    await expectUnauthenticated(await sendRestRequest(request, API.REPORTING_CUSTOMERS_VIEW));
  });

  test('should export the customers table as a csv whose header matches the view columns', async ({ request }) => {
    const view = await safeJson(await sendAdminRequest(request, API.REPORTING_CUSTOMERS_VIEW, { params: { type: TYPE } }));
    const labels = view[0].statistics.columns.map((column: any) => String(column.label ?? column.key ?? ''));

    const response = await downloadExport(request, API.REPORTING_CUSTOMERS_EXPORT, 'csv', { type: TYPE });
    const bytes = await expectAttachment(response, 'csv', `customers-${TYPE}`);

    expect(csvHeaderRow(bytes.toString('utf8'))).toEqual(labels);
  });

  test('should export the customers table as an xlsx workbook', async ({ request }) => {
    const response = await downloadExport(request, API.REPORTING_CUSTOMERS_EXPORT, 'xlsx', { type: TYPE });
    const bytes = await expectAttachment(response, 'xlsx', `customers-${TYPE}`);

    expect(bytes.subarray(0, 4).toString('hex')).toBe(XLSX_MAGIC);
  });

  test('should reject an unsupported customers export format', async ({ request }) => {
    const response = await sendAdminRequest(request, API.REPORTING_CUSTOMERS_EXPORT, {
      params: { type: TYPE, format: 'pdf' },
      headers: acceptFor('csv'),
    });

    await expectProblem(response, 422, MSG.REPORTING_EXPORT_FORMAT_UNSUPPORTED);
  });

  test('should reject an unknown customers export type', async ({ request }) => {
    const response = await sendAdminRequest(request, API.REPORTING_CUSTOMERS_EXPORT, {
      params: { type: 'total-sales', format: 'csv' },
      headers: acceptFor('csv'),
    });

    await expectProblem(response, 400, MSG.REPORTING_INVALID_TYPE);
  });

  test('should refuse the customers export without an admin token', async ({ request }) => {
    const response = await sendRestRequest(request, API.REPORTING_CUSTOMERS_EXPORT, {
      params: { type: TYPE, format: 'csv' },
      headers: acceptFor('csv'),
    });

    await expectUnauthenticated(response);
  });
});
