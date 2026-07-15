// tests/graphQL/graphql/Queries/admin/customers/customerReviews.queries.ts
//
// Admin Customer Reviews GraphQL operations (W2). Moderation-only (no create
// — reviews originate from the storefront). Status comes back as a string on
// the wire (e.g. "0" / "approved") per live probe 2026-05-26.

export const ADMIN_CUSTOMER_REVIEWS_LIST = `
  query adminCustomerReviews(
    $first: Int
    $after: String
    $status: String
    $rating: Int
    $product_id: Int
    $customer_id: Int
    $sort: String
    $order: String
  ) {
    adminCustomerReviews(
      first: $first
      after: $after
      status: $status
      rating: $rating
      product_id: $product_id
      customer_id: $customer_id
      sort: $sort
      order: $order
    ) {
      edges {
        node {
          id
          _id
          title
          comment
          rating
          status
          product {
            _id
            name
            sku
          }
          customer {
            _id
            name
            email
          }
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;

export const ADMIN_CUSTOMER_REVIEW_DETAIL = `
  query adminCustomerReview($id: ID!) {
    adminCustomerReview(id: $id) {
      id
      _id
      title
      comment
      rating
      status
      product {
        _id
        name
        sku
      }
      customer {
        _id
        name
        email
      }
    }
  }
`;

export const ADMIN_CUSTOMER_REVIEW_UPDATE = `
  mutation updateAdminCustomerReview($id: ID!, $status: String!) {
    updateAdminCustomerReview(input: { id: $id, status: $status }) {
      adminCustomerReview {
        id
        _id
        status
      }
    }
  }
`;

export const ADMIN_CUSTOMER_REVIEW_DELETE = `
  mutation deleteAdminCustomerReview($id: ID!) {
    deleteAdminCustomerReview(input: { id: $id }) {
      adminCustomerReview {
        id
      }
    }
  }
`;

export const ADMIN_CUSTOMER_REVIEW_MASS_DELETE = `
  mutation createAdminCustomerReviewMassDelete($indices: Iterable!) {
    createAdminCustomerReviewMassDelete(input: { indices: $indices }) {
      adminCustomerReviewMassDelete {
        id
      }
    }
  }
`;

export const ADMIN_CUSTOMER_REVIEW_MASS_UPDATE_STATUS = `
  mutation createAdminCustomerReviewMassUpdateStatus(
    $indices: Iterable!
    $value: String!
  ) {
    createAdminCustomerReviewMassUpdateStatus(
      input: { indices: $indices, value: $value }
    ) {
      adminCustomerReviewMassUpdateStatus {
        id
      }
    }
  }
`;
