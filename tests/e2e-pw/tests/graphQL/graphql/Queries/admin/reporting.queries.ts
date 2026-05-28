// tests/graphQL/graphql/Queries/admin/reporting.queries.ts
//
// Admin reporting GraphQL operations (4 sub-pages). Schema verified 2026-05-26.
//
// Each AdminReporting* payload: { id (IRI), entity, type, dateRange, statistics }
// where `statistics` is an Iterable scalar (free-form JSON blob).

export const ADMIN_REPORTING_OVERVIEW_QUERY = `
  query adminReportingOverview($type: String, $start: String, $end: String, $channel: String) {
    statsAdminReportingOverview(type: $type, start: $start, end: $end, channel: $channel) {
      id
      entity
      type
      dateRange
      statistics
    }
  }
`;

export const ADMIN_REPORTING_SALES_QUERY = `
  query adminReportingSales($type: String, $start: String, $end: String, $channel: String) {
    statsAdminReportingSales(type: $type, start: $start, end: $end, channel: $channel) {
      id
      entity
      type
      dateRange
      statistics
    }
  }
`;

export const ADMIN_REPORTING_CUSTOMERS_QUERY = `
  query adminReportingCustomers($type: String, $start: String, $end: String, $channel: String) {
    statsAdminReportingCustomers(type: $type, start: $start, end: $end, channel: $channel) {
      id
      entity
      type
      dateRange
      statistics
    }
  }
`;

export const ADMIN_REPORTING_PRODUCTS_QUERY = `
  query adminReportingProducts($type: String, $start: String, $end: String, $channel: String) {
    statsAdminReportingProducts(type: $type, start: $start, end: $end, channel: $channel) {
      id
      entity
      type
      dateRange
      statistics
    }
  }
`;
