// rest/assertions/category.assertions.ts
import { expect } from '@playwright/test';

export function assertCategoriesResponse(body: any) {
  expect(body).toBeDefined();
  expect(Array.isArray(body)).toBeTruthy();
  expect(body.length).toBeGreaterThanOrEqual(0);
}

export function assertCategoryFields(category: any) {
  expect(category).toHaveProperty('id');
  // Category response surfaces `translations` (array of per-locale rows),
  // not a singular `translation`. When the category has at least one
  // translation row, assert the row shape; otherwise just confirm the array
  // exists (a brand-new category may have no translations yet).
  expect(category).toHaveProperty('translations');
  expect(Array.isArray(category.translations)).toBeTruthy();
  if (category.translations.length > 0) {
    const t = category.translations[0];
    expect(t).toHaveProperty('name');
    expect(t).toHaveProperty('slug');
  }
}