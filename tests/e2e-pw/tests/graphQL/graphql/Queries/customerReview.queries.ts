export const GET_CUSTOMER_REVIEWS = `
  query getCustomerReviews($first: Int, $after: String, $status: String, $rating: Int) {
    customerReviews(first: $first, after: $after, status: $status, rating: $rating) {
      edges {
        cursor
        node {
          id
          _id
          title
          comment
          rating
          status
          name
          product {
            id
            _id
            sku
            type
          }
          customer {
            id
            _id
          }
          createdAt
          updatedAt
        }
      }
      pageInfo {
        endCursor
        startCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_REVIEWS_BY_STATUS = `
  query getApprovedReviews($first: Int, $status: String) {
    customerReviews(first: $first, status: $status) {
      edges {
        node {
          _id
          title
          rating
          status
        }
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_REVIEWS_BY_RATING = `
  query getReviewsByRating($first: Int, $rating: Int) {
    customerReviews(first: $first, rating: $rating) {
      edges {
        node {
          _id
          title
          rating
          status
        }
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_REVIEWS_COMBINED_FILTERS = `
  query getApprovedReviewsByRating($first: Int, $status: String, $rating: Int) {
    customerReviews(first: $first, status: $status, rating: $rating) {
      edges {
        node {
          _id
          title
          comment
          rating
          status
        }
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_REVIEWS_PAGINATED = `
  query getCustomerReviewsNextPage($first: Int, $after: String) {
    customerReviews(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          title
          rating
          status
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_REVIEW = `
  query getCustomerReview($id: ID!) {
    customerReview(id: $id) {
      id
      _id
      title
      comment
      rating
      status
      name
      product {
        id
        _id
        sku
      }
      customer {
        id
        _id
      }
      createdAt
      updatedAt
    }
  }
`;

export const GET_CUSTOMER_REVIEW_INTROSPECTION = `
  query getCustomerReviewType {
    __type(name: "CustomerReview") {
      name
      kind
      fields {
        name
        type {
          name
          kind
        }
      }
    }
  }
`;
