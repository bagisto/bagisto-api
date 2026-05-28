// tests/graphQL/graphql/Queries/admin/sales/invoices.queries.ts
// Schema-verified field names:
//   AdminInvoice exposes `incrementId`, `grandTotal`, `formattedGrandTotal`
//   (NOT orderIncrementId / baseGrandTotal / formattedBaseGrandTotal —
//   those exist in the REST payload but the GraphQL schema only surfaces
//   the non-base variants in this build).
// Filter args (state, etc.) are NOT exposed on adminInvoices — `extraArgs`
// not declared on the QueryCollection; passing them aborts the query.

export const ADMIN_INVOICES_LIST_QUERY = `
  query adminInvoices($first: Int, $after: String) {
    adminInvoices(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          incrementId
          orderId
          state
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

export const ADMIN_INVOICE_DETAIL_QUERY = `
  query adminInvoice($id: ID!) {
    adminInvoice(id: $id) {
      id
      _id
      incrementId
      state
      grandTotal
    }
  }
`;
