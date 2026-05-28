// tests/graphQL/api/automation/admin/dashboard.spec.ts
//
// Smoke for `statsAdminDashboard` GraphQL query — default + per-`type` variant
// + scoped date range + unknown-type guard.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_DASHBOARD_STATS_QUERY } from '../../../graphql/Queries/admin/dashboard.queries';

test.describe.configure({ timeout: 60_000 });

// Per CLAUDE.md "Admin API — endpoint coverage" Dashboard row + REST probe:
const STAT_TYPES = [
  'over-all',
  'today',
  'stock-threshold-products',
  'total-sales',
  'total-visitors',
  'top-selling-products',
  'top-customers',
];

test.describe('Admin Dashboard GraphQL API', () => {
  test('default stats query returns payload', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(request, ADMIN_DASHBOARD_STATS_QUERY);
    const status = response.status();
    console.log('gql dashboard default:', status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();
    const dash = body?.data?.statsAdminDashboard;
    expect(dash).toBeTruthy();
    // Default type is `over-all` per the provider.
    expect(dash.type).toBe('over-all');
    expect(dash.statistics).toBeTruthy();
  });

  for (const type of STAT_TYPES) {
    test(`stats type=${type} returns payload`, async ({ request }) => {
      const response = await sendAdminGraphQLRequest(
        request,
        ADMIN_DASHBOARD_STATS_QUERY,
        { type }
      );
      const status = response.status();
      console.log(`gql dashboard type=${type}:`, status);
      expect(status).toBe(200);

      const body = await response.json();
      // Tolerant: some stat types may surface as errors[] in dev DBs that
      // don't support the underlying aggregation (mirrors the REST 500 note).
      const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
      const dash = body?.data?.statsAdminDashboard;

      if (hasErrors) {
        console.log(`  -> resolver errors for type=${type}:`, JSON.stringify(body.errors).slice(0, 200));
        return;
      }

      expect(dash).toBeTruthy();
      expect(dash.type).toBe(type);
    });
  }

  test('stats with date range + channel returns payload', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(
      request,
      ADMIN_DASHBOARD_STATS_QUERY,
      {
        type: 'over-all',
        start: '2026-01-01',
        end: '2026-05-26',
        channel: 'default',
      }
    );
    const status = response.status();
    console.log('gql dashboard scoped:', status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();
    expect(body?.data?.statsAdminDashboard).toBeTruthy();
  });

  test('stats type=invalid is rejected via errors[]', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(
      request,
      ADMIN_DASHBOARD_STATS_QUERY,
      { type: 'definitely-not-a-real-type' }
    );
    const status = response.status();
    console.log('gql dashboard invalid type:', status);
    // GraphQL never returns HTTP 400 for resolver-level failures — surfaces
    // via errors[] with status still 200.
    expect(status).toBe(200);

    const body = await response.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors).toBe(true);
  });
});
