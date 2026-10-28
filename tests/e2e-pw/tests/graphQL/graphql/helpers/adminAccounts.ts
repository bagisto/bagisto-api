import { APIRequestContext, APIResponse, expect } from '@playwright/test';
import { sendAdminGraphQLRequest, sendPublicAdminGraphQLRequest } from './adminGraphqlClient';
import { ADMIN_LOGIN, ADMIN_PROFILE } from '../Queries/admin/auth.queries';
import { ADMIN_USER_CREATE, ADMIN_USER_DELETE } from '../Queries/admin/settings/users.queries';
import { ADMIN_ROLE_CREATE, ADMIN_ROLE_DELETE } from '../Queries/admin/settings/roles.queries';

export const TEST_ADMIN_PASSWORD = 'e2e-Secret-123';

export interface TestAdmin {
  id: number;
  email: string;
  password: string;
}

export interface LoginTokens {
  accessToken: string;
  refreshToken: string;
  sessionId: number;
}

export function uniqueSuffix(): string {
  return `${Date.now().toString(36)}${Math.floor(Math.random() * 1e6).toString(36)}`;
}

function idFrom(value: unknown): number {
  return typeof value === 'string'
    ? Number(value.split('/').pop())
    : Number(value);
}

export function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

export async function signedInAdminRoleId(request: APIRequestContext): Promise<number> {
  const body = await (await sendAdminGraphQLRequest(request, ADMIN_PROFILE)).json();

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return Number(body.data.readAdminProfile.roleId);
}

export async function createTestRole(request: APIRequestContext, permissions: string[]): Promise<number> {
  const body = await (await sendAdminGraphQLRequest(request, ADMIN_ROLE_CREATE, {
    name: `E2E auth role ${uniqueSuffix()}`,
    description: 'Created by the admin auth e2e suite.',
    permissionType: 'custom',
    permissions,
  })).json();

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return idFrom(body.data.createAdminSettingsRole.adminSettingsRole._id);
}

export async function deleteTestRole(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminGraphQLRequest(request, ADMIN_ROLE_DELETE, { id: `/api/admin/settings/roles/${id}` });
}

export async function createTestAdmin(
  request: APIRequestContext,
  options: { roleId?: number; status?: number } = {},
): Promise<TestAdmin> {
  const email = `e2e-gql-auth-${uniqueSuffix()}@example.com`;

  const body = await (await sendAdminGraphQLRequest(request, ADMIN_USER_CREATE, {
    name: `E2E GraphQL Auth ${uniqueSuffix()}`,
    email,
    password: TEST_ADMIN_PASSWORD,
    roleId: options.roleId ?? await signedInAdminRoleId(request),
    status: options.status ?? 1,
  })).json();

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return { id: idFrom(body.data.createAdminSettingsUser.adminSettingsUser._id), email, password: TEST_ADMIN_PASSWORD };
}

export async function deleteTestAdmin(request: APIRequestContext, admin: TestAdmin | null): Promise<void> {
  if (admin) {
    await sendAdminGraphQLRequest(request, ADMIN_USER_DELETE, { id: `/api/admin/settings/users/${admin.id}` });
  }
}

export async function login(
  request: APIRequestContext,
  admin: Pick<TestAdmin, 'email' | 'password'>,
  input: Record<string, string> = {},
): Promise<APIResponse> {
  return sendPublicAdminGraphQLRequest(request, ADMIN_LOGIN, {
    input: { email: admin.email, password: admin.password, ...input },
  });
}

export async function loginTokens(
  request: APIRequestContext,
  admin: Pick<TestAdmin, 'email' | 'password'>,
  input: Record<string, string> = {},
): Promise<LoginTokens> {
  const body = await (await login(request, admin, input)).json();

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const pair = body.data.createAdminLogin.adminLogin;

  return { accessToken: pair.accessToken, refreshToken: pair.refreshToken, sessionId: Number(pair.session.id) };
}

export async function asBearer(
  request: APIRequestContext,
  token: string,
  query: string,
  variables: Record<string, any> = {},
): Promise<APIResponse> {
  return sendAdminGraphQLRequest(request, query, variables, bearer(token));
}
