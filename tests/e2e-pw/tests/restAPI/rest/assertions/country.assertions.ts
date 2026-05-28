// tests/restAPI/rest/assertions/country.assertions.ts
import { expect } from '@playwright/test';

export function assertCountryFields(country: any) {
  expect(country).toHaveProperty('id');
  expect(country).toHaveProperty('code');
  expect(country).toHaveProperty('name');
  expect(typeof country.id).toBe('number');
  expect(typeof country.code).toBe('string');
  expect(typeof country.name).toBe('string');
}

export function assertCountryWithStates(country: any) {
  assertCountryFields(country);
  expect(country).toHaveProperty('states');
  expect(Array.isArray(country.states)).toBeTruthy();
}

export function assertCountryWithTranslations(country: any) {
  assertCountryFields(country);
  expect(country).toHaveProperty('translations');
  expect(Array.isArray(country.translations)).toBeTruthy();
}

export function assertCountryStateFields(state: any) {
  expect(state).toHaveProperty('id');
  expect(state).toHaveProperty('country_id');
  expect(state).toHaveProperty('code');
  expect(state).toHaveProperty('name');
}
