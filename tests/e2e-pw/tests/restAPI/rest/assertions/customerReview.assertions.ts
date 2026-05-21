// tests/restAPI/rest/assertions/customerReview.assertions.ts
import { expect } from '@playwright/test';

export function assertCustomerReviewFields(review: any) {
  expect(review).toHaveProperty('id');
  expect(review).toHaveProperty('title');
  expect(review).toHaveProperty('rating');
  expect(review).toHaveProperty('status');
  expect(typeof review.id).toBe('number');
  expect(typeof review.rating).toBe('number');
}

export function assertProductReviewFields(review: any) {
  expect(review).toHaveProperty('id');
  expect(review).toHaveProperty('title');
  expect(review).toHaveProperty('rating');
  expect(review).toHaveProperty('comment');
  expect(review).toHaveProperty('status');
  expect(review).toHaveProperty('author_name');
}
