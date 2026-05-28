// tests/restAPI/rest/assertions/customer.assertions.ts
import { expect } from '@playwright/test';

export function assertCustomerFields(customer: any) {
  expect(customer).toHaveProperty('id');
  expect(customer).toHaveProperty('email');
  expect(customer).toHaveProperty('firstName');
  expect(customer).toHaveProperty('lastName');
  expect(typeof customer.id).toBe('number');
  expect(typeof customer.email).toBe('string');
}

export function assertCustomerProfile(customer: any) {
  assertCustomerFields(customer);
  expect(customer).toHaveProperty('name');
  expect(customer).toHaveProperty('status');
}
