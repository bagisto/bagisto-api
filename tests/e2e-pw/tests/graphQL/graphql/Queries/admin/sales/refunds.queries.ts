// tests/graphQL/graphql/Queries/admin/sales/refunds.queries.ts
// AdminRefund GraphQL schema exposes `grandTotal` (NOT baseGrandTotal).
// Filter args (state) NOT exposed on adminRefunds in this build.

export const ADMIN_REFUNDS_LIST_QUERY = `
  query adminRefunds($first: Int, $after: String) {
    adminRefunds(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          orderId
          state
          grandTotal
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

export const ADMIN_REFUND_DETAIL_QUERY = `
  query adminRefund($id: ID!) {
    adminRefund(id: $id) {
      id
      _id
      orderId
      state
      grandTotal
    }
  }
`;
