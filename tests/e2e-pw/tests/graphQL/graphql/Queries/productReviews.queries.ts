// graphql/queries/productReviews.queries.ts

// Get Product Reviews - Basic
export const GET_PRODUCT_REVIEWS = `
  query productReviews($first: Int, $after: String) {
    productReviews(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          name
          title
          rating
          comment
          status
          createdAt
          updatedAt
        }
        cursor
      }
      pageInfo {
        hasNextPage
        endCursor
      }
      totalCount
    }
  }
`;

export const GET_PRODUCT_REVIEWS_BY_PRODUCT_ID = `
  query productReviewsByProduct($first: Int, $product_id: Int) {
    productReviews(first: $first, product_id: $product_id) {
      edges {
        cursor
        node {
          id
          _id
          name
          title
          rating
          comment
          status
          createdAt
          updatedAt
        }
      }
      totalCount
    }
  }
`;

export const GET_PRODUCT_REVIEWS_BY_STATUS = `
  query productReviewsByStatus($first: Int, $status: String) {
    productReviews(first: $first, status: $status) {
      edges {
        node {
          id
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

export const GET_PRODUCT_REVIEWS_BY_RATING = `
  query productReviewsByRating($first: Int, $rating: Int) {
    productReviews(first: $first, rating: $rating) {
      edges {
        node {
          id
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

// ============================================
// CREATE PRODUCT REVIEW MUTATIONS
// ============================================

// Create Product Review - Basic
export const CREATE_PRODUCT_REVIEW = `
  mutation createProductReview($input: createProductReviewInput!) {
    createProductReview(input: $input) {
      productReview {
        id
        _id
        name
        title
        rating
        comment
        status
        createdAt
        updatedAt
      }
    }
  }
`;

// Create Product Review - With Attachments
export const CREATE_PRODUCT_REVIEW_WITH_ATTACHMENTS = `
  mutation createProductReview($input: createProductReviewInput!) {
    createProductReview(input: $input) {
      productReview {
        id
        _id
        name
        title
        rating
        comment
        status
        attachments
        createdAt
        updatedAt
      }
    }
  }
`;

// Create Product Review - Complete with Metadata (clientMutationId)
export const CREATE_PRODUCT_REVIEW_COMPLETE = `
  mutation createProductReview($input: createProductReviewInput!) {
    createProductReview(input: $input) {
      productReview {
        id
        _id
        name
        title
        rating
        comment
        status
        attachments
        createdAt
        updatedAt
      }
      clientMutationId
    }
  }
`;

// ============================================
// UPDATE PRODUCT REVIEW MUTATIONS
// ============================================

// Update Product Review - Basic
export const UPDATE_PRODUCT_REVIEW = `
  mutation updateProductReview($input: updateProductReviewInput!) {
    updateProductReview(input: $input) {
      productReview {
        id
        _id
        name
        title
        rating
        comment
        status
      }
    }
  }
`;

// Update Product Review - Complete Details
export const UPDATE_PRODUCT_REVIEW_COMPLETE = `
  mutation updateProductReview($input: updateProductReviewInput!) {
    updateProductReview(input: $input) {
      productReview {
        id
        _id
        name
        title
        rating
        comment
        status
      }
      clientMutationId
    }
  }
`;

// ============================================
// DELETE PRODUCT REVIEW MUTATIONS
// ============================================

// Delete Product Review - Basic
export const DELETE_PRODUCT_REVIEW = `
  mutation deleteProductReview($input: deleteProductReviewInput!) {
    deleteProductReview(input: $input) {
      productReview {
        id
      }
    }
  }
`;

// Delete Product Review - With Tracking (clientMutationId)
export const DELETE_PRODUCT_REVIEW_WITH_TRACKING = `
  mutation deleteProductReview($input: deleteProductReviewInput!) {
    deleteProductReview(input: $input) {
      productReview {
        id
      }
      clientMutationId
    }
  }
`;
