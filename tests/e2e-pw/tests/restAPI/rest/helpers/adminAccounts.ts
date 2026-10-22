import { APIRequestContext, APIResponse, expect } from '@playwright/test';
import { sendAdminRequest } from './adminClient';
import { sendRestRequest } from './restClient';
import { ADMIN_AUTH } from '../endpoints/admin.auth.endpoints';
import { ADMIN_SETTINGS } from '../endpoints/admin.settings.endpoints';

export const TEST_ADMIN_PASSWORD = 'e2e-Secret-123';

export interface TestAdmin {
  id: number;
  email: string;
  password: string;
}

export function uniqueSuffix(): string {
  return `${Date.now().toString(36)}${Math.floor(Math.random() * 1e6).toString(36)}`;
}

export async function signedInAdminRoleId(request: APIRequestContext): Promise<number> {
  const response = await sendAdminRequest(request, ADMIN_AUTH.PROFILE);

  expect(response.status()).toBe(200);

  const body = await response.json();
  const profile = Array.isArray(body) ? body[0] : body;

  return Number(profile.roleId);
}

export async function createTestRole(request: APIRequestContext, permissions: string[]): Promise<number> {
  const response = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
    method: 'POST',
    data: {
      name: `E2E auth role ${uniqueSuffix()}`,
      description: 'Created by the admin auth e2e suite.',
      permission_type: 'custom',
      permissions,
    },
  });

  expect([200, 201]).toContain(response.status());

  const body = await response.json();

  return Number(body?.id ?? body?.data?.id);
}

export async function deleteTestRole(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(id), { method: 'DELETE' });
}

export async function createTestAdmin(
  request: APIRequestContext,
  options: { roleId?: number; status?: number } = {},
): Promise<TestAdmin> {
  const email = `e2e-auth-${uniqueSuffix()}@example.com`;

  const response = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
    method: 'POST',
    data: {
      name: `E2E Auth ${uniqueSuffix()}`,
      email,
      password: TEST_ADMIN_PASSWORD,
      role_id: options.roleId ?? await signedInAdminRoleId(request),
      status: options.status ?? 1,
    },
  });

  expect([200, 201]).toContain(response.status());

  const body = await response.json();

  return { id: Number(body?.id ?? body?.data?.id), email, password: TEST_ADMIN_PASSWORD };
}

export async function deleteTestAdmin(request: APIRequestContext, admin: TestAdmin | null): Promise<void> {
  if (admin) {
    await sendAdminRequest(request, ADMIN_SETTINGS.USER(admin.id), { method: 'DELETE' });
  }
}

export async function login(
  request: APIRequestContext,
  admin: Pick<TestAdmin, 'email' | 'password'>,
  data: Record<string, string> = {},
): Promise<APIResponse> {
  return sendRestRequest(request, ADMIN_AUTH.LOGIN, {
    method: 'POST',
    data: { email: admin.email, password: admin.password, ...data },
  });
}

export async function loginTokens(
  request: APIRequestContext,
  admin: Pick<TestAdmin, 'email' | 'password'>,
  data: Record<string, string> = {},
): Promise<{ accessToken: string; refreshToken: string; sessionId: number }> {
  const response = await login(request, admin, data);

  expect(response.status()).toBe(200);

  const body = await response.json();

  return { accessToken: body.accessToken, refreshToken: body.refreshToken, sessionId: Number(body.session.id) };
}

export function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

export async function withBearer(
  request: APIRequestContext,
  token: string,
  endpoint: string,
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' = 'GET',
  data?: Record<string, any>,
): Promise<APIResponse> {
  return sendRestRequest(request, endpoint, { method, data, headers: bearer(token) });
}
