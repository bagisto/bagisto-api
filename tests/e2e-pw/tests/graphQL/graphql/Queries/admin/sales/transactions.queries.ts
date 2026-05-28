// tests/graphQL/graphql/Queries/admin/sales/transactions.queries.ts
// Filter args (status, etc.) NOT exposed on adminTransactions in this build.

export const ADMIN_TRANSACTIONS_LIST_QUERY = `
  query adminTransactions($first: Int, $after: String) {
    adminTransactions(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          transactionId
          orderId
          invoiceId
          status
          amount
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

export const ADMIN_TRANSACTION_DETAIL_QUERY = `
  query adminTransaction($id: ID!) {
    adminTransaction(id: $id) {
      id
      _id
      transactionId
      orderId
      status
    }
  }
`;
