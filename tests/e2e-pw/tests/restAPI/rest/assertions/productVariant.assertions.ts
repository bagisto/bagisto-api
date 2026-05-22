// tests/restAPI/rest/assertions/productVariant.assertions.ts
import { expect } from '@playwright/test';

export function assertProductVariantFields(variant: any) {
  expect(variant).toHaveProperty('id');
  expect(variant).toHaveProperty('sku');
  expect(variant).toHaveProperty('name');
  expect(variant).toHaveProperty('price');
  expect(typeof variant.id).toBe('number');
}
