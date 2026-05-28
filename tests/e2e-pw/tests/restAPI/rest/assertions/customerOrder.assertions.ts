// tests/restAPI/rest/assertions/customerOrder.assertions.ts
import { expect } from '@playwright/test';

export function assertCustomerOrderFields(order: any) {
  expect(order).toHaveProperty('id');
  expect(order).toHaveProperty('status');
  expect(order).toHaveProperty('items');
  expect(typeof order.id).toBe('number');
  expect(Array.isArray(order.items)).toBeTruthy();
}

export function assertCustomerOrderItemFields(item: any) {
  expect(item).toHaveProperty('id');
  expect(item).toHaveProperty('name');
  expect(item).toHaveProperty('qty');
  expect(item).toHaveProperty('unitPrice');
}
