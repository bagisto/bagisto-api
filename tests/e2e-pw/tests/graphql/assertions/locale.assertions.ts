import { expect } from '@playwright/test';

/* ---------------------------------------------------
 * COMMON GRAPHQL ASSERTIONS
 * --------------------------------------------------- */
export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}

/* ---------------------------------------------------
 * LOCALE ASSERTIONS
 * --------------------------------------------------- */
export function assertLocalesResponse(body: any) {
  expect(body.data.locales).toBeTruthy();
  expect(body.data.locales.edges).toBeTruthy();
  expect(Array.isArray(body.data.locales.edges)).toBe(true);
  expect(body.data.locales.edges.length).toBeGreaterThan(0);
}

export function assertValidLocale(locale: any) {
  // Structural validation
  expect(locale).toHaveProperty('id');
  expect(locale).toHaveProperty('code');
  expect(locale).toHaveProperty('name');
  expect(locale).toHaveProperty('direction');

  // Value validation
  expect(typeof locale.id).toBe('string');
  expect(locale.id.length).toBeGreaterThan(0);

  expect(typeof locale.code).toBe('string');
  expect(locale.code.trim().length).toBeGreaterThan(0);

  expect(typeof locale.name).toBe('string');
  expect(locale.name.trim().length).toBeGreaterThan(0);

  expect(['ltr', 'rtl']).toContain(locale.direction);
}
