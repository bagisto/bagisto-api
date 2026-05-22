// utils/assertions/productReviews.assertions.ts

import { expect } from '@playwright/test';

/**
 * Asserts that product reviews are valid
 */
export const assertProductReviewsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('productReviews');
  expect(body.data.productReviews).not.toBeNull();

  const productReviews = body.data.productReviews;
  
  // Validate page info
  expect(productReviews.pageInfo).toBeDefined();
  expect(typeof productReviews.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof productReviews.pageInfo.endCursor).toBe('string');

  // Validate total count
  expect(typeof productReviews.totalCount).toBe('number');
  expect(productReviews.totalCount).toBeGreaterThanOrEqual(0);

  // Validate edges
  expect(Array.isArray(productReviews.edges)).toBeTruthy();

  productReviews.edges.forEach((edge: any) => {
    // Validate cursor
    expect(typeof edge.cursor).toBe('string');
    
    // Validate review node
    const review = edge.node;
    expect(review).not.toBeNull();
    
    expect(review.id).toBeDefined();
    expect(['string', 'number']).toContain(typeof review._id);
    expect(typeof review.name).toBe('string');
    expect(review.name.length).toBeGreaterThan(0);
    expect(typeof review.title).toBe('string');
    expect(review.title.length).toBeGreaterThan(0);
    expect(typeof review.rating).toBe('number');
    expect(review.rating).toBeGreaterThan(0);
    expect(review.rating).toBeLessThanOrEqual(5);
    expect(typeof review.comment).toBe('string');
    expect(['string', 'number']).toContain(typeof review.status);
    expect(review.createdAt).toBeDefined();
    expect(review.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    expect(review.updatedAt).toBeDefined();
    expect(review.updatedAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  });

  // Print review count for debugging
  console.log(`\n========== PRODUCT REVIEWS FOUND ==========`);
  console.log(`Total Reviews: ${productReviews.totalCount}`);
  console.log(`Reviews Returned: ${productReviews.edges.length}`);
  console.log(`Has Next Page: ${productReviews.pageInfo.hasNextPage}`);
  if (productReviews.pageInfo.hasNextPage) {
    console.log(`End Cursor: ${productReviews.pageInfo.endCursor}`);
  }
  console.log('==========================================\n');
};

/**
 * Asserts that no product reviews are found
 */
export const assertProductReviewsNoResults = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('productReviews');
  expect(body.data.productReviews).not.toBeNull();

  const productReviews = body.data.productReviews;
  
  expect(productReviews.totalCount).toBe(0);
  expect(productReviews.edges.length).toBe(0);

  console.log('\n===== NO PRODUCT REVIEWS FOUND =====\n');
};

/**
 * Asserts that a product review was created successfully
 */
export const assertCreateProductReviewResponse = (body: any, input: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('createProductReview');
  expect(body.data.createProductReview).not.toBeNull();
  expect(body.data.createProductReview).toHaveProperty('productReview');
  expect(body.data.createProductReview.productReview).not.toBeNull();

  const productReview = body.data.createProductReview.productReview;
  
  // Verify the created review has all necessary properties
  expect(productReview.id).toBeDefined();
  expect(['string', 'number']).toContain(typeof productReview._id);
  expect(productReview.title).toEqual(input.title);
  expect(productReview.comment).toEqual(input.comment);
  expect(productReview.rating).toEqual(input.rating);
  expect(productReview.name).toEqual(input.name);
  expect(['string', 'number']).toContain(typeof productReview.status);
  
  // Email might not be returned in the response
  if (input.email && productReview.email) {
    expect(productReview.email).toEqual(input.email);
  }
  
  // createdAt and updatedAt might be null initially
  if (productReview.createdAt) {
    expect(productReview.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  }
  if (productReview.updatedAt) {
    expect(productReview.updatedAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    if (productReview.createdAt) {
      expect(productReview.createdAt).toEqual(productReview.updatedAt); // Should be same initially
    }
  }
  
  // Additional validation for status - if provided, should match
  if (input.status !== undefined) {
    expect(productReview.status).toEqual(input.status);
  }

  // Print created review details for debugging
  console.log('\n========== PRODUCT REVIEW CREATED ==========');
  console.log(`Review ID: ${productReview.id}`);
  console.log(`Title: ${productReview.title}`);
  console.log(`Rating: ${productReview.rating}/5`);
  console.log(`Author: ${productReview.name}`);
  console.log(`Status: ${productReview.status}`);
  console.log('===========================================\n');
};

/**
 * Asserts that a product review was created with attachments
 */
export const assertCreateProductReviewWithAttachmentsResponse = (body: any, input: any) => {
  assertCreateProductReviewResponse(body, input);
  
  const productReview = body.data.createProductReview.productReview;
  
  // Validate attachments if provided
  if (input.attachments) {
    expect(productReview.attachments).toBeDefined();
    const attachments = JSON.parse(input.attachments);
    expect(Array.isArray(attachments)).toBeTruthy();
  }
  
  console.log('\n========== PRODUCT REVIEW WITH ATTACHMENTS CREATED ==========');
  console.log(`Attachments: ${productReview.attachments || 'none'}`);
  console.log('===========================================\n');
};

/**
 * Asserts that a product review was created with clientMutationId
 */
export const assertCreateProductReviewCompleteResponse = (body: any, input: any) => {
  assertCreateProductReviewWithAttachmentsResponse(body, input);
  
  // Validate clientMutationId
  expect(body.data.createProductReview).toHaveProperty('clientMutationId');
  if (input.clientMutationId) {
    expect(body.data.createProductReview.clientMutationId).toEqual(input.clientMutationId);
  }
  
  console.log('\n========== PRODUCT REVIEW COMPLETE ==========');
  console.log(`Client Mutation ID: ${body.data.createProductReview.clientMutationId}`);
  console.log('==========================================\n');
};

/**
 * Asserts that a product review was updated successfully
 */
export const assertUpdateProductReviewResponse = (body: any, input: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('updateProductReview');
  expect(body.data.updateProductReview).not.toBeNull();
  expect(body.data.updateProductReview).toHaveProperty('productReview');
  expect(body.data.updateProductReview.productReview).not.toBeNull();

  const productReview = body.data.updateProductReview.productReview;
  
  // Verify the updated review has all necessary properties
  expect(productReview.id).toBeDefined();
  expect(['string', 'number']).toContain(typeof productReview._id);
  
  // Validate updated fields
  if (input.title !== undefined) {
    expect(productReview.title).toEqual(input.title);
  }
  if (input.comment !== undefined) {
    expect(productReview.comment).toEqual(input.comment);
  }
  if (input.rating !== undefined) {
    expect(productReview.rating).toEqual(input.rating);
  }
  if (input.name !== undefined) {
    expect(productReview.name).toEqual(input.name);
  }
  if (input.status !== undefined) {
    expect(productReview.status).toEqual(input.status);
  }

  // Print updated review details for debugging
  console.log('\n========== PRODUCT REVIEW UPDATED ==========');
  console.log(`Review ID: ${productReview.id}`);
  console.log(`Title: ${productReview.title}`);
  console.log(`Rating: ${productReview.rating}/5`);
  console.log(`Author: ${productReview.name}`);
  console.log(`Status: ${productReview.status}`);
  console.log('===========================================\n');
};

/**
 * Asserts that a product review was updated with clientMutationId
 */
export const assertUpdateProductReviewCompleteResponse = (body: any, input: any) => {
  assertUpdateProductReviewResponse(body, input);
  
  // Validate clientMutationId
  expect(body.data.updateProductReview).toHaveProperty('clientMutationId');
  if (input.clientMutationId) {
    expect(body.data.updateProductReview.clientMutationId).toEqual(input.clientMutationId);
  }
  
  console.log('\n========== PRODUCT REVIEW UPDATE COMPLETE ==========');
  console.log(`Client Mutation ID: ${body.data.updateProductReview.clientMutationId}`);
  console.log('==========================================\n');
};

/**
 * Asserts that a product review was deleted successfully
 */
export const assertDeleteProductReviewResponse = (body: any, input: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('deleteProductReview');
  expect(body.data.deleteProductReview).not.toBeNull();
  expect(body.data.deleteProductReview).toHaveProperty('productReview');
  expect(body.data.deleteProductReview.productReview).not.toBeNull();

  const productReview = body.data.deleteProductReview.productReview;
  
  // Verify the deleted review has id
  expect(productReview.id).toBeDefined();

  // Print deleted review details for debugging
  console.log('\n========== PRODUCT REVIEW DELETED ==========');
  console.log(`Review ID: ${productReview.id}`);
  console.log('==========================================\n');
};

/**
 * Asserts that a product review was deleted with clientMutationId
 */
export const assertDeleteProductReviewCompleteResponse = (body: any, input: any) => {
  assertDeleteProductReviewResponse(body, input);
  
  // Validate clientMutationId
  expect(body.data.deleteProductReview).toHaveProperty('clientMutationId');
  if (input.clientMutationId) {
    expect(body.data.deleteProductReview.clientMutationId).toEqual(input.clientMutationId);
  }
  
  console.log('\n========== PRODUCT REVIEW DELETE COMPLETE ==========');
  console.log(`Client Mutation ID: ${body.data.deleteProductReview.clientMutationId}`);
  console.log('==========================================\n');
};

/**
 * Asserts GraphQL error response
 */
export const assertGraphQLError = (body: any, expectedMessage?: string) => {
  expect(body).toHaveProperty('errors');
  expect(Array.isArray(body.errors)).toBeTruthy();
  expect(body.errors.length).toBeGreaterThan(0);
  
  if (expectedMessage) {
    const errorMessages = body.errors.map((e: any) => e.message).join(' ');
    expect(errorMessages).toContain(expectedMessage);
  }
  
  console.log('\n========== GRAPHQL ERROR ==========');
  console.log(JSON.stringify(body.errors, null, 2));
  console.log('==================================\n');
};

/**
 * Asserts that required field is missing
 */
export const assertRequiredFieldError = (body: any, fieldName: string) => {
  assertGraphQLError(body, fieldName);
};
