// tests/graphQL/graphql/Queries/admin/dashboard.queries.ts
//
// Admin dashboard GraphQL operations. Schema verified 2026-05-26.
//
// AdminDashboard payload shape: { id (IRI), type, dateRange, statistics }
// where `statistics` is an Iterable scalar (free-form JSON blob).

export const ADMIN_DASHBOARD_STATS_QUERY = `
  query adminDashboardStats($type: String, $start: String, $end: String, $channel: String) {
    statsAdminDashboard(type: $type, start: $start, end: $end, channel: $channel) {
      id
      type
      dateRange
      statistics
    }
  }
`;
