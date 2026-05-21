// rest/assertions/product.assertions.ts
import { expect } from '@playwright/test';

export function assertProductCard(product: any) {
  expect(product).toHaveProperty('id');
  expect(product).toHaveProperty('sku');
  expect(product).toHaveProperty('type');
  expect(product).toHaveProperty('name');
  expect(product).toHaveProperty('price');
  expect(product).toHaveProperty('formattedPrice');
  expect(product).toHaveProperty('status');
  expect(typeof product.id).toBe('number');
  expect(typeof product.sku).toBe('string');
  expect(typeof product.price).toBe('number');
}

export function assertProductFull(product: any) {
  assertProductCard(product);
  expect(product).toHaveProperty('urlKey');
  expect(product).toHaveProperty('shortDescription');
  expect(product).toHaveProperty('type');
  expect(['simple', 'configurable', 'bundle', 'grouped', 'virtual', 'downloadable', 'booking']).toContain(product.type);
}