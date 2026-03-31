import { expect } from '@playwright/test';

/* ---------------------------------------------------
 * ATOMIC ASSERTIONS (Reusable)
 * --------------------------------------------------- */
export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}

export function assertBasicLocale(locale: any) {
  expect(locale).toBeTruthy();
  expect(locale).toHaveProperty('id');
  expect(locale).toHaveProperty('code');
  expect(locale).toHaveProperty('name');
  expect(locale).toHaveProperty('direction');

  expect(typeof locale.id).toBe('string');
  expect(locale.id.length).toBeGreaterThan(0);

  expect(typeof locale.code).toBe('string');
  expect(locale.code.trim().length).toBeGreaterThan(0);

  expect(typeof locale.name).toBe('string');
  expect(locale.name.trim().length).toBeGreaterThan(0);

  expect(['ltr', 'rtl']).toContain(locale.direction);
}

export function assertLocaleWithInternalId(locale: any) {
  assertBasicLocale(locale);

  expect(locale).toHaveProperty('_id');
  expect(typeof locale._id).toBe('number');
}

export function assertExtendedLocale(locale: any) {
  assertLocaleWithInternalId(locale);

  // logoPath & logoUrl must exist (may be null)
  // logoPath & logoUrl must exist (may be null or missing)
  if (locale.hasOwnProperty('logoPath') && locale.logoPath !== null) {
    expect(typeof locale.logoPath).toBe('string');
    expect(locale.logoPath.length).toBeGreaterThan(0);
  }

  if (locale.hasOwnProperty('logoUrl') && locale.logoUrl !== null) {
    expect(typeof locale.logoUrl).toBe('string');
    expect(locale.logoUrl.startsWith('http')).toBe(true);
  }

  if (locale.logoPath === null) expect(locale.logoUrl).toBeNull();
  if (locale.logoUrl === null) expect(locale.logoPath).toBeNull();
}

export function assertPaginationInfo(pageInfo: any) {
  expect(pageInfo).toBeTruthy();
  expect(typeof pageInfo.startCursor).toBe('string');
  expect(typeof pageInfo.endCursor).toBe('string');
  expect(pageInfo.startCursor.length).toBeGreaterThan(0);
  expect(pageInfo.endCursor.length).toBeGreaterThan(0);
  expect(typeof pageInfo.hasNextPage).toBe('boolean');
  expect(typeof pageInfo.hasPreviousPage).toBe('boolean');
}

/* ---------------------------------------------------
 * QUERY-SPECIFIC CONTRACT ASSERTIONS
 * --------------------------------------------------- */
export function assertGetAllLocalesResponse(body: any) {
  const locales = body.data.locales;
  expect(locales.edges.length).toBeGreaterThan(0);

  locales.edges.forEach((edge: any) => {
    assertBasicLocale(edge.node);
    expect(edge.node._id).toBeUndefined();
    expect(edge.node.logoPath).toBeUndefined();
    expect(edge.node.logoUrl).toBeUndefined();
  });

  expect(locales.pageInfo).toBeUndefined();
  expect(locales.totalCount).toBeUndefined();
}

export function assertGetCompleteLocalesResponse(body: any) {
  const locales = body.data.locales;
  expect(locales.edges.length).toBeGreaterThan(0);
  expect(locales.pageInfo).toBeDefined();
  expect(locales.totalCount).toBeDefined();

  assertPaginationInfo(locales.pageInfo);

  locales.edges.forEach((edge: any) => {
    expect(edge.cursor).toBeTruthy();
    assertExtendedLocale(edge.node);
  });

  expect(locales.totalCount).toBe(locales.edges.length);
}

export function assertGetLocalesWithPaginationResponse(body: any) {
  const locales = body.data.locales;
  expect(locales.edges.length).toBeGreaterThan(0);
  expect(locales.totalCount).toBeDefined();

  assertPaginationInfo(locales.pageInfo);

  locales.edges.forEach((edge: any) => {
    expect(edge.cursor).toBeTruthy();
    assertLocaleWithInternalId(edge.node);
    expect(edge.node.logoPath).toBeUndefined(); // logoPath is NOT returned here
  });
}

// Optional wrappers for readability in test files
export function assertSingleLocaleBasic(locale: any) {
  assertBasicLocale(locale);
}

export function assertSingleLocaleComplete(locale: any) {
  assertExtendedLocale(locale);
}
