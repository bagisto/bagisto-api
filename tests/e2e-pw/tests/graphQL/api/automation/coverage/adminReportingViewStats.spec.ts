import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import { withAdminToken } from '../../../config/adminAuth';
import {
  ADMIN_REPORTING_SALES_VIEW_COVERAGE_QUERY,
  ADMIN_REPORTING_PRODUCTS_VIEW_COVERAGE_QUERY,
} from '../../../graphql/Queries/coverage/adminReporting.queries';

test.describe.configure({ timeout: 90_000 });

const INVALID_TYPE_MESSAGE = 'Unknown reporting stat type for this entity.';

const SALES_TOTAL_COLUMNS = [
  { key: 'label', label: 'Interval' },
  { key: 'count', label: 'Orders' },
  { key: 'formatted_total', label: 'Total' },
];

const PRODUCTS_SOLD_COLUMNS = [
  { key: 'label', label: 'Interval' },
  { key: 'total', label: 'Quantities' },
];

const PRODUCTS_REVENUE_COLUMNS = [
  { key: 'id', label: 'Id' },
  { key: 'name', label: 'Name' },
  { key: 'formatted_price', label: 'Price' },
  { key: 'formatted_revenue', label: 'Revenue' },
];

async function viewStats(
  request: APIRequestContext,
  query: string,
  field: string,
  variables: Record<string, any> = {},
): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data[field];
}

async function viewStatsError(
  request: APIRequestContext,
  query: string,
  field: string,
  variables: Record<string, any>,
): Promise<string | undefined> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  expect(body.data?.[field] ?? null).toBeNull();
  return body.errors?.[0]?.message;
}

function expectTable(row: any, columns: { key: string; label: string }[]) {
  expect(row.statistics.columns).toEqual(columns);
  expect(Array.isArray(row.statistics.records)).toBe(true);
  for (const record of row.statistics.records) {
    for (const column of columns) {
      expect(record, `record has ${column.key}`).toHaveProperty(column.key);
    }
  }
}

test.describe('admin reporting view stats graphql', () => {
  test('should return the total sales view as a table of columns and records', async ({ request }) => {
    const row = await viewStats(request, ADMIN_REPORTING_SALES_VIEW_COVERAGE_QUERY, 'viewStatsAdminReportingSales', {
      type: 'total-sales',
    });

    expect(row.entity).toBe('sales');
    expect(row.type).toBe('total-sales');
    expect(Object.keys(row.dateRange).sort()).toEqual(['current', 'previous']);
    expectTable(row, SALES_TOTAL_COLUMNS);
    expect(row.statistics.records.length).toBeGreaterThan(0);
  });

  test('should scope the sales view to the requested date range', async ({ request }) => {
    const row = await viewStats(request, ADMIN_REPORTING_SALES_VIEW_COVERAGE_QUERY, 'viewStatsAdminReportingSales', {
      type: 'total-sales',
      start: '2026-01-05',
      end: '2026-01-11',
    });

    expect(row.dateRange.current).toBe('05 Jan 2026 - 11 Jan 2026');
    expectTable(row, SALES_TOTAL_COLUMNS);
  });

  test('should return the same sales view over graphql as over rest', async ({ request }) => {
    const row = await viewStats(request, ADMIN_REPORTING_SALES_VIEW_COVERAGE_QUERY, 'viewStatsAdminReportingSales', {
      type: 'sales-by-coupon',
    });

    const rest = await withAdminToken(request, token => request.get(`${env.baseUrl}/api/admin/reporting/sales/view?type=sales-by-coupon`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    }));
    expect(rest.status()).toBe(200);
    const restRow = (await rest.json())[0];

    expect(row.type).toBe('sales-by-coupon');
    expect(row.statistics).toEqual(restRow.statistics);
  });

  test('should reject an unknown sales view type', async ({ request }) => {
    const message = await viewStatsError(request, ADMIN_REPORTING_SALES_VIEW_COVERAGE_QUERY, 'viewStatsAdminReportingSales', {
      type: 'bogus',
    });
    expect(message).toBe(INVALID_TYPE_MESSAGE);
  });

  test('should refuse the sales view without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_REPORTING_SALES_VIEW_COVERAGE_QUERY, {}, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json()).message).toBe('Unauthenticated.');
  });

  test('should return the products views as tables of columns and records', async ({ request }) => {
    const sold = await viewStats(request, ADMIN_REPORTING_PRODUCTS_VIEW_COVERAGE_QUERY, 'viewStatsAdminReportingProducts');
    expect(sold.entity).toBe('products');
    expect(sold.type).toBe('total-sold-quantities');
    expectTable(sold, PRODUCTS_SOLD_COLUMNS);

    const revenue = await viewStats(request, ADMIN_REPORTING_PRODUCTS_VIEW_COVERAGE_QUERY, 'viewStatsAdminReportingProducts', {
      type: 'top-selling-products-by-revenue',
    });
    expect(revenue.type).toBe('top-selling-products-by-revenue');
    expectTable(revenue, PRODUCTS_REVENUE_COLUMNS);
  });

  test('should reject a sales type on the products view', async ({ request }) => {
    const message = await viewStatsError(request, ADMIN_REPORTING_PRODUCTS_VIEW_COVERAGE_QUERY, 'viewStatsAdminReportingProducts', {
      type: 'total-sales',
    });
    expect(message).toBe(INVALID_TYPE_MESSAGE);
  });

  test('should refuse the products view without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_REPORTING_PRODUCTS_VIEW_COVERAGE_QUERY, {}, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json()).message).toBe('Unauthenticated.');
  });
});
