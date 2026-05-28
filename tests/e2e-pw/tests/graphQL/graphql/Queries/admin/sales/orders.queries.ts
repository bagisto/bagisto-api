// tests/graphQL/graphql/Queries/admin/sales/orders.queries.ts
//
// Admin Orders GraphQL operations — listing (cursor) + detail.
// Fields verified against AdminOrder / AdminOrderDetail resource shapes
// (snake_case props in PHP → camelCase in GraphQL via name converter).

// NOTE: the auto-generated adminOrders cursor query in this build does NOT
// expose filter args (status/channel) on the GraphQL schema — passing them
// triggers "Unknown argument" errors that abort the whole query. Filter-by-
// status would need extraArgs on the QueryCollection (CLAUDE.md Phase 1.1
// finding). Listing exposes only `first` / `after` for now.

export const ADMIN_ORDERS_LIST_QUERY = `
  query adminOrders($first: Int, $after: String) {
    adminOrders(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          incrementId
          status
          statusLabel
          customerEmail
          customerName
          grandTotal
          formattedGrandTotal
          createdAt
        }
        cursor
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;

// NOTE: AdminOrderDetail GraphQL exposes `customerFirstName` /
// `customerLastName` (no combined `customerName` — that field is on the
// listing/AdminOrder shape only).
export const ADMIN_ORDER_DETAIL_QUERY = `
  query adminOrderDetail($id: ID!) {
    adminOrderDetail(id: $id) {
      id
      _id
      incrementId
      status
      statusLabel
      customerEmail
      customerFirstName
      customerLastName
      grandTotal
    }
  }
`;
