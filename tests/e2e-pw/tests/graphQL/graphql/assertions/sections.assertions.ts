import { expect } from '@playwright/test';

export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}

export function assertSectionTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(typeof translation.locale).toBe('string');
  expect(typeof translation.options).toBe('string');

  expect(() => JSON.parse(translation.options)).not.toThrow();
}

export function assertBasicSection(node: any) {
  expect(node).toBeTruthy();

  expect(node).toHaveProperty('id');
  expect(node).toHaveProperty('_id');
  expect(node).toHaveProperty('type');
  expect(node).toHaveProperty('name');
  expect(node).toHaveProperty('status');
  expect(node).toHaveProperty('themeCode');

  expect(typeof node.id).toBe('string');
  expect(node.id).toMatch(/\/api\/shop\/sections\/\d+/);

  expect(typeof node._id).toBe('number');
  expect(typeof node.type).toBe('string');
  expect(typeof node.name).toBe('string');
  expect(typeof node.status).toBe('boolean');
  expect(typeof node.themeCode).toBe('string');
  if (node.translation !== undefined && node.translation !== null) {
    assertSectionTranslation(node.translation);
  }
}

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
    expect(typeof node.sectionId).toBe('number');
    expect(typeof node.locale).toBe('string');
    expect(typeof node.options).toBe('string');

    expect(() => JSON.parse(node.options)).not.toThrow();
  });
}

export function assertSectionsConnection(connection: any) {
  expect(connection).toBeDefined();
  expect(Array.isArray(connection.edges)).toBeTruthy();

  connection.edges.forEach((edge: any) => {
    expect(edge.node).toBeTruthy();
    assertBasicSection(edge.node);
  });

  if (connection.totalCount !== undefined)
    expect(typeof connection.totalCount).toBe('number');

  if (connection.pageInfo)
    expect(typeof connection.pageInfo.hasNextPage).toBe('boolean');
}

export function assertGetSectionsBasicResponse(body: any) {
  assertNoGraphQLErrors(body);

  const connection = body.data.sections;

  assertSectionsConnection(connection);
}

export function assertGetSectionsFilteredResponse(
  body: any,
  expectedType: string
) {
  assertNoGraphQLErrors(body);

  const connection = body.data.sections;

  assertSectionsConnection(connection);

  connection.edges.forEach((edge: any) => {
    const node = edge.node;

    expect(node.type).toBe(expectedType);

    assertTranslationsConnection(node.translations);
  });
}

export function assertGetSectionsCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);

  const connection = body.data.sections;

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

    assertBasicSection(node);

    expect(typeof node.channelId).toBe('number');
    expect(typeof node.createdAt).toBe('string');
    expect(typeof node.updatedAt).toBe('string');

    expect(() => new Date(node.createdAt)).not.toThrow();
    expect(() => new Date(node.updatedAt)).not.toThrow();

    expect(node.translation).toBeDefined();
    assertSectionTranslation(node.translation);

    assertTranslationsConnection(node.translations);
  });
}

export function assertGetSectionByIdBasicResponse(body: any) {
  assertNoGraphQLErrors(body);

  const node = body.data.section;

  expect(node).toBeDefined();

  assertBasicSection(node);

  expect(node.translation).toBeDefined();
  assertSectionTranslation(node.translation);

  expect(node.sortOrder).toBeUndefined();
  expect(node.channelId).toBeUndefined();
  expect(node.createdAt).toBeUndefined();
  expect(node.updatedAt).toBeUndefined();
  expect(node.translations).toBeUndefined();
}

export function assertExtendedSection(node: any) {
  assertBasicSection(node);

  expect(typeof node.sortOrder).toBe('number');

  if (node.channelId !== undefined)
    expect(typeof node.channelId).toBe('number');

  if (node.createdAt !== undefined)
    expect(typeof node.createdAt).toBe('string');

  if (node.updatedAt !== undefined)
    expect(typeof node.updatedAt).toBe('string');

  if (node.translation)
    assertSectionTranslation(node.translation);
}

export function assertGetSectionByNumericIdResponse(body: any) {
  assertNoGraphQLErrors(body);

  const node = body.data.section;

  expect(node).toBeDefined();

  assertBasicSection(node);

  expect(typeof node.sortOrder).toBe('number');

  expect(node.translation).toBeDefined();
  assertSectionTranslation(node.translation);

  expect(node.channelId).toBeUndefined();
  expect(node.createdAt).toBeUndefined();
  expect(node.updatedAt).toBeUndefined();
  expect(node.translations).toBeUndefined();
}

export function assertGraphQLError( body: any, expectedMessage?: string) {
  expect(body.errors, 'Expected GraphQL errors but none were returned')
    .toBeDefined();
  const messages = body.errors.map((e: any) => e.message);
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
