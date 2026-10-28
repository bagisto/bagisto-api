export const ADMIN_REPORTING_SALES_VIEW_COVERAGE_QUERY = `
  query viewStatsAdminReportingSales($type: String, $start: String, $end: String, $channel: String) {
    viewStatsAdminReportingSales(type: $type, start: $start, end: $end, channel: $channel) {
      entity
      type
      dateRange
      statistics
    }
  }
`;

export const ADMIN_REPORTING_PRODUCTS_VIEW_COVERAGE_QUERY = `
  query viewStatsAdminReportingProducts($type: String, $start: String, $end: String, $channel: String) {
    viewStatsAdminReportingProducts(type: $type, start: $start, end: $end, channel: $channel) {
      entity
      type
      dateRange
      statistics
    }
  }
`;
