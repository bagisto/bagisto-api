// tests/restAPI/rest/assertions/compareItem.assertions.ts
import { expect } from '@playwright/test';

export function assertCompareItemFields(item: any) {
  expect(item).toHaveProperty('id');
  expect(item).toHaveProperty('name');
  expect(typeof item.id).toBe('number');
}
