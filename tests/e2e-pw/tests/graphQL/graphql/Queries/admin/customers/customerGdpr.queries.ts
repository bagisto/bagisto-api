// tests/graphQL/graphql/Queries/admin/customers/customerGdpr.queries.ts
//
// Admin Customer GDPR GraphQL operations (W2). GDPR rows are storefront-
// originated; in dev DB the table may be empty (most list/detail tests
// skip cleanly when no rows exist). Process(type=delete) is destructive
// (cascades customer delete) — only the download-data shape is exercised
// for happy-path destructive checks.

export const ADMIN_CUSTOMER_GDPR_LIST = `
  query adminCustomerGdprRequests(
    $first: Int
    $after: String
    $status: String
    $type: String
    $customer_id: Int
    $email: String
    $sort: String
    $order: String
  ) {
    adminCustomerGdprRequests(
      first: $first
      after: $after
      status: $status
      type: $type
      customer_id: $customer_id
      email: $email
      sort: $sort
      order: $order
    ) {
      edges {
        node {
          id
          _id
          customerId
          customerName
          email
          type
          status
          message
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GDPR_DETAIL = `
  query adminCustomerGdprRequest($id: ID!) {
    adminCustomerGdprRequest(id: $id) {
      id
      _id
      customerId
      customerName
      email
      type
      status
      message
      revokedAt
    }
  }
`;

export const ADMIN_CUSTOMER_GDPR_UPDATE = `
  mutation updateAdminCustomerGdprRequest(
    $id: ID!
    $status: String
    $message: String
  ) {
    updateAdminCustomerGdprRequest(
      input: { id: $id, status: $status, message: $message }
    ) {
      adminCustomerGdprRequest {
        id
        _id
        status
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GDPR_DELETE = `
  mutation deleteAdminCustomerGdprRequest($id: ID!) {
    deleteAdminCustomerGdprRequest(input: { id: $id }) {
      adminCustomerGdprRequest {
        id
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GDPR_PROCESS = `
  mutation createAdminCustomerGdprProcess(
    $requestId: String!
    $message: String
  ) {
    createAdminCustomerGdprProcess(
      input: { requestId: $requestId, message: $message }
    ) {
      adminCustomerGdprProcess {
        id
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GDPR_DOWNLOAD_DATA = `
  mutation createAdminCustomerGdprDownloadData($customerId: Int!) {
    createAdminCustomerGdprDownloadData(input: { customerId: $customerId }) {
      adminCustomerGdprDownloadData {
        id
        _id
        customerId
        customerEmail
        generatedAt
        data
      }
    }
  }
`;
