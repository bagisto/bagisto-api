// tests/restAPI/rest/assertions/customerAddress.assertions.ts
import { expect } from '@playwright/test';

export function assertCustomerAddressFields(address: any) {
  expect(address).toHaveProperty('id');
  expect(address).toHaveProperty('first_name');
  expect(address).toHaveProperty('last_name');
  expect(address).toHaveProperty('city');
  expect(address).toHaveProperty('postcode');
  expect(address).toHaveProperty('country');
  expect(typeof address.id).toBe('number');
  expect(typeof address.city).toBe('string');
}
