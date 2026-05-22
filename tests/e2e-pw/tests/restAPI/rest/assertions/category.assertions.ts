// rest/assertions/category.assertions.ts
import { expect } from '@playwright/test';

export function assertCategoriesResponse(body: any) {
  expect(body).toBeDefined();
  expect(Array.isArray(body)).toBeTruthy();
  expect(body.length).toBeGreaterThanOrEqual(0);
}

export function assertCategoryFields(category: any) {
  expect(category).toHaveProperty('id');
  expect(category).toHaveProperty('translation');
  expect(category.translation).toHaveProperty('name');
  expect(category.translation).toHaveProperty('slug');
}