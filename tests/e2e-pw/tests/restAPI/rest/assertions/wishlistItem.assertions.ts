// tests/restAPI/rest/assertions/wishlistItem.assertions.ts
import { expect } from '@playwright/test';

export function assertWishlistItemFields(item: any) {
  expect(item).toHaveProperty('id');
  expect(item).toHaveProperty('name');
  expect(typeof item.id).toBe('number');
}
