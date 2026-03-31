import { expect } from '@playwright/test';

export function pick(body: Record<string, any>, path: string): any {
  return path.split('.').reduce((value, key) => value?.[key], body);
}

export function graphQLErrorMessages(body: Record<string, any>): string[] {
  return Array.isArray(body.errors) ? body.errors.map((error) => error.message ?? String(error)) : [];
}

export function logGraphQLMessages(label: string, body: Record<string, any>) {
  const messages = graphQLErrorMessages(body);

  if (messages.length > 0) {
    console.log(`${label}: ${messages.join(' | ')}`);
  }
}

export function expectGraphQLSuccess(body: Record<string, any>, path: string) {
  logGraphQLMessages('GraphQL errors', body);
  expect(body.errors).toBeUndefined();
  expect(pick(body, path)).toBeTruthy();
}

export function expectAuthAwareResult(body: Record<string, any>, path: string) {
  const payload = pick(body, path);

  if (payload !== undefined && payload !== null) {
    expect(payload).toBeDefined();
    return;
  }

  const messages = graphQLErrorMessages(body);
  console.log(`GraphQL message: ${messages.join(' | ')}`);
  expect(messages.length).toBeGreaterThan(0);
}

export function expectConnection(body: Record<string, any>, path: string) {
  expectGraphQLSuccess(body, path);
  const connection = pick(body, path);
  expect(Array.isArray(connection.edges)).toBe(true);
  return connection;
}
