// rest/assertions/attribute.assertions.ts
import { expect } from '@playwright/test';

export function assertAttributesResponse(body: any) {
  expect(body).toBeDefined();
  expect(Array.isArray(body)).toBeTruthy();
  expect(body.length).toBeGreaterThan(0);
}

export function assertAttributeFields(attribute: any) {
  expect(attribute).toHaveProperty('id');
  expect(attribute).toHaveProperty('code');
  expect(attribute).toHaveProperty('type');
  expect(attribute).toHaveProperty('translation');
  expect(attribute.translation).toHaveProperty('name');
}

export function assertAttributeFieldsFull(attribute: any) {
  assertAttributeFields(attribute);
  expect(attribute).toHaveProperty('adminName');
  expect(attribute).toHaveProperty('isRequired');
  expect(attribute).toHaveProperty('isFilterable');
}

export function assertEmptyAttributesResponse(body: any) {
  expect(body).toBeDefined();
  expect(Array.isArray(body)).toBeTruthy();
  expect(body.length).toBe(0);
}

export function assertAttributeWithOptions(attribute: any) {
  expect(attribute).toHaveProperty('options');
  expect(Array.isArray(attribute.options)).toBeTruthy();
}