import { expect } from '@playwright/test';

/* ---------------------------------------------------
 * COMMON
 * --------------------------------------------------- */

export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}


/* ---------------------------------------------------
 * ATOMIC ASSERTIONS (Reusable)
 * --------------------------------------------------- */

/**
 * Validate theme customization translation (single object)
 */
export function assertThemeCustomizationTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(typeof translation.locale).toBe('string');
  expect(typeof translation.options).toBe('string');

  // Validate JSON format only
  expect(() => JSON.parse(translation.options)).not.toThrow();
}


/**
 * Validate basic theme customization fields
 */
export function assertBasicThemeCustomization(node: any) {
  expect(node).toBeTruthy();

  expect(node).toHaveProperty('id');
  expect(node).toHaveProperty('_id');
  expect(node).toHaveProperty('type');
  expect(node).toHaveProperty('name');
  expect(node).toHaveProperty('status');
  expect(node).toHaveProperty('themeCode');

  expect(typeof node.id).toBe('string');
  expect(node.id).toMatch(/\/api\/shop\/theme-customizations\/\d+/);

  expect(typeof node._id).toBe('number');
  expect(typeof node.type).toBe('string');
  expect(typeof node.name).toBe('string');
  expect(typeof node.status).toBe('string');
  expect(typeof node.themeCode).toBe('string');
  // ✅ Validate translation ONLY if it exists
  if (node.translation !== undefined && node.translation !== null) {
    assertThemeCustomizationTranslation(node.translation);
  }
}


/**
 * Validate translations connection (like category translations)
 */
export function assertTranslationsConnection(translations: any) {
  expect(translations).toBeDefined();
  expect(Array.isArray(translations.edges)).toBeTruthy();

  if (translations.totalCount !== undefined)
    expect(typeof translations.totalCount).toBe('number');

  if (translations.pageInfo) {
    expect(typeof translations.pageInfo.hasNextPage).toBe('boolean');
    expect(typeof translations.pageInfo.hasPreviousPage).toBe('boolean');
  }

  translations.edges.forEach((edge: any) => {
    expect(edge.cursor).toBeTruthy();

    const node = edge.node;

    expect(typeof node.id).toBe('string');
    expect(typeof node._id).toBe('number');
    expect(typeof node.themeCustomizationId).toBe('string');
    expect(typeof node.locale).toBe('string');
    expect(typeof node.options).toBe('string');

    expect(() => JSON.parse(node.options)).not.toThrow();
  });
}


/**
 * Validate GraphQL connection (themeCustomizations)
 */
export function assertThemeCustomizationsConnection(connection: any) {
  expect(connection).toBeDefined();
  expect(Array.isArray(connection.edges)).toBeTruthy();

  connection.edges.forEach((edge: any) => {
    expect(edge.node).toBeTruthy();
    assertBasicThemeCustomization(edge.node);
  });

  if (connection.totalCount !== undefined)
    expect(typeof connection.totalCount).toBe('number');

  if (connection.pageInfo)
    expect(typeof connection.pageInfo.hasNextPage).toBe('boolean');
}


/* ---------------------------------------------------
 * QUERY-SPECIFIC CONTRACT ASSERTIONS
 * --------------------------------------------------- */

/**
 * For GET_THEME_CUSTOMIZATIONS_BASIC
 * (No filter, no enforced translations connection)
 */
export function assertGetThemeCustomizationsBasicResponse(body: any) {
  assertNoGraphQLErrors(body);

  const connection = body.data.themeCustomizations;

  assertThemeCustomizationsConnection(connection);
}


/**
 * For GET_THEME_CUSTOMIZATIONS_FILTERED (type filter)
 * Must enforce filter + translations connection
 */
export function assertGetThemeCustomizationsFilteredResponse(
  body: any,
  expectedType: string
) {
  assertNoGraphQLErrors(body);

  const connection = body.data.themeCustomizations;

  assertThemeCustomizationsConnection(connection);

  connection.edges.forEach((edge: any) => {
    const node = edge.node;

    // ✅ Validate filter applied
    expect(node.type).toBe(expectedType);

    // ✅ Enforce translations connection exists
    assertTranslationsConnection(node.translations);
  });
}

/**
 * For GET_THEME_CUSTOMIZATIONS_COMPLETE_DETAILS
 * Must enforce all fields + translations connection + pagination
 */
export function assertGetThemeCustomizationsCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);

  const connection = body.data.themeCustomizations;

  // Connection-level validations
  expect(connection).toBeDefined();
  expect(Array.isArray(connection.edges)).toBeTruthy();
  expect(typeof connection.totalCount).toBe('number');

  expect(connection.pageInfo).toBeDefined();
  expect(typeof connection.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof connection.pageInfo.hasPreviousPage).toBe('boolean');
  expect(typeof connection.pageInfo.startCursor).toBe('string');
  expect(typeof connection.pageInfo.endCursor).toBe('string');

  connection.edges.forEach((edge: any) => {
    expect(edge.cursor).toBeTruthy();

    const node = edge.node;

    // Reuse basic validation
    assertBasicThemeCustomization(node);

    // 🔥 Enforce extra fields required for complete details
    expect(typeof node.channelId).toBe('string');
    expect(typeof node.createdAt).toBe('string');
    expect(typeof node.updatedAt).toBe('string');

    // Ensure valid ISO date format
    expect(() => new Date(node.createdAt)).not.toThrow();
    expect(() => new Date(node.updatedAt)).not.toThrow();

    // Enforce translation object (must exist in complete query)
    expect(node.translation).toBeDefined();
    assertThemeCustomizationTranslation(node.translation);

    // Enforce translations connection (must exist)
    assertTranslationsConnection(node.translations);
  });
}

/**
 * For GET_THEME_CUSTOMIZATION_BY_ID_BASIC
 * Single object response (no connection)
 */
export function assertGetThemeCustomizationByIdBasicResponse(body: any) {
  assertNoGraphQLErrors(body);

  const node = body.data.themeCustomization;

  expect(node).toBeDefined();

  // Use BASE only (not extended)
  assertBasicThemeCustomization(node);

  // Translation must exist in this query
  expect(node.translation).toBeDefined();
  assertThemeCustomizationTranslation(node.translation);

  // Ensure fields NOT requested do not exist
  expect(node.sortOrder).toBeUndefined();
  expect(node.channelId).toBeUndefined();
  expect(node.createdAt).toBeUndefined();
  expect(node.updatedAt).toBeUndefined();
  expect(node.translations).toBeUndefined();
}


/**
 * Validate extended fields (used in list + complete queries)
 */
export function assertExtendedThemeCustomization(node: any) {
  assertBasicThemeCustomization(node);

  expect(typeof node.sortOrder).toBe('number');

  if (node.channelId !== undefined)
    expect(typeof node.channelId).toBe('string');

  if (node.createdAt !== undefined)
    expect(typeof node.createdAt).toBe('string');

  if (node.updatedAt !== undefined)
    expect(typeof node.updatedAt).toBe('string');

  if (node.translation)
    assertThemeCustomizationTranslation(node.translation);
}

export function assertGetThemeCustomizationByNumericIdResponse(body: any) {
  assertNoGraphQLErrors(body);

  const node = body.data.themeCustomization;

  expect(node).toBeDefined();

  // Base fields
  assertBasicThemeCustomization(node);

  // sortOrder is REQUIRED in this query
  expect(typeof node.sortOrder).toBe('number');

  // Translation (single object)
  expect(node.translation).toBeDefined();
  assertThemeCustomizationTranslation(node.translation);

  // Fields NOT requested must be undefined
  expect(node.channelId).toBeUndefined();
  expect(node.createdAt).toBeUndefined();
  expect(node.updatedAt).toBeUndefined();
  expect(node.translations).toBeUndefined();
}

export function assertGraphQLError( body: any, expectedMessage?: string) {
  expect(body.errors, 'Expected GraphQL errors but none were returned')
    .toBeDefined();
  const messages = body.errors.map((e: any) => e.message);
  // Print clean error output to terminal
  console.log('\n===== GRAPHQL ERROR =====');
  body.errors.forEach((error: any, index: number) => {
    console.log(`Error ${index + 1}:`);
    console.log('Message:', error.message);
    console.log('Path:', error.path);
    console.log('-------------------------');
  });
  console.log('=========================\n');
  if (expectedMessage) {
    expect(messages.join(' '))
      .toContain(expectedMessage);
  }
}