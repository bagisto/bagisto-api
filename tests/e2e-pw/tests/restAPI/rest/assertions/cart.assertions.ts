// tests/restAPI/rest/assertions/cart.assertions.ts
import { expect } from '@playwright/test';

export function assertCartBody(body: any) {
  expect(body).toBeDefined();
  expect(body).toHaveProperty('items');
  expect(Array.isArray(body.items)).toBeTruthy();
}

export function assertCartItemFields(item: any) {
  expect(item).toHaveProperty('id');
  expect(item).toHaveProperty('productId');
  expect(item).toHaveProperty('qty');
  expect(typeof item.id).toBe('number');
  expect(typeof item.productId).toBe('number');
  expect(typeof item.qty).toBe('number');
}

export function assertCartResponseFields(body: any) {
  expect(body).toHaveProperty('items');
  expect(body).toHaveProperty('grand_total');
  expect(body).toHaveProperty('sub_total');
}
