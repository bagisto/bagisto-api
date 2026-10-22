import { APIRequestContext, APIResponse } from '@playwright/test';
import { env } from '../../config/env';
import { AdminCredentials, currentAdminSession, withAdminSession } from '../../../fixtures/adminSession';

export function adminCredentials(): AdminCredentials {
  return {
    baseUrl: env.baseUrl,
    storefrontKey: env.storefrontAccessKey,
    email: env.adminEmail,
    password: env.adminPassword,
  };
}

export async function getAdminToken(request: APIRequestContext): Promise<string> {
  return (await currentAdminSession(request, adminCredentials())).accessToken;
}

export function adminHeaders(token: string): Record<string, string> {
  return {
    'Authorization': `Bearer ${token}`,
  };
}

export async function withAdminToken(
  request: APIRequestContext,
  send: (token: string) => Promise<APIResponse>,
): Promise<APIResponse> {
  return withAdminSession(request, adminCredentials(), send);
}
