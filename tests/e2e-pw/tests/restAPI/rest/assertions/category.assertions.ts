// rest/assertions/category.assertions.ts
import { expect } from '@playwright/test';

export function assertCategoriesResponse(body: any) {
  expect(body).toBeDefined();
  expect(Array.isArray(body)).toBeTruthy();
  expect(body.length).toBeGreaterThanOrEqual(0);
}

export function assertCategoryFields(category: any) {
  expect(category).toHaveProperty('id');

  // `translations` lists the per-locale rows as references; the row for the
  // requested locale is embedded under the singular `translation`.
  expect(category).toHaveProperty('translations');
  expect(Array.isArray(category.translations)).toBeTruthy();

  expect(category).toHaveProperty('translation');
  if (category.translation) {
    expect(category.translation).toHaveProperty('name');
    expect(category.translation).toHaveProperty('slug');
  }
}