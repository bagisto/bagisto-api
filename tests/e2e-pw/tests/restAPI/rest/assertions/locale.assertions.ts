// tests/restAPI/rest/assertions/locale.assertions.ts
import { expect } from '@playwright/test';

export function assertLocaleFields(locale: any) {
  expect(locale).toHaveProperty('id');
  expect(locale).toHaveProperty('code');
  expect(locale).toHaveProperty('name');
  expect(typeof locale.id).toBe('number');
  expect(typeof locale.code).toBe('string');
  expect(typeof locale.name).toBe('string');
}

export function assertLocaleWithLogo(locale: any) {
  assertLocaleFields(locale);
  expect(locale).toHaveProperty('direction');
  expect(['ltr', 'rtl']).toContain(locale.direction);
  expect(locale).toHaveProperty('logoPath');
  expect(locale).toHaveProperty('logoUrl');
}
