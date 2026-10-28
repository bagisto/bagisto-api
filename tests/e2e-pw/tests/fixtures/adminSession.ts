import fs from 'fs';
import path from 'path';
import { APIRequestContext, APIResponse } from '@playwright/test';

export const ADMIN_SESSION_FILE = path.resolve(__dirname, '../../.admin-session.json');

export const ACCESS_TOKEN_EXPIRED = 'access_token_expired';

export const REFRESH_TOKEN_INVALID = 'refresh_token_invalid';

export const UNAUTHENTICATED = 'unauthenticated';

export interface AdminSessionTokens {
  accessToken: string;
  refreshToken: string;
  accessTokenExpiresAt: string | null;
}

export interface AdminCredentials {
  baseUrl: string;
  storefrontKey: string;
  email: string;
  password: string;
}

let cached: AdminSessionTokens | null = null;

function trimmedBaseUrl(credentials: AdminCredentials): string {
  return credentials.baseUrl.replace(/\/+$/, '');
}

function jsonHeaders(credentials: AdminCredentials): Record<string, string> {
  return {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-STOREFRONT-KEY': credentials.storefrontKey,
  };
}

function tokensFrom(body: any): AdminSessionTokens {
  if (
    typeof body?.accessToken !== 'string'
    || typeof body?.refreshToken !== 'string'
  ) {
    throw new Error(`the admin API answered without a token pair: ${JSON.stringify(body).slice(0, 300)}`);
  }

  return {
    accessToken: body.accessToken,
    refreshToken: body.refreshToken,
    accessTokenExpiresAt: body.accessTokenExpiresAt ?? null,
  };
}

export function readStoredAdminSession(): AdminSessionTokens | null {
  try {
    const parsed = JSON.parse(fs.readFileSync(ADMIN_SESSION_FILE, 'utf8'));

    return typeof parsed?.accessToken === 'string' ? parsed : null;
  } catch {
    return null;
  }
}

export function storeAdminSession(tokens: AdminSessionTokens): void {
  cached = tokens;

  try {
    fs.writeFileSync(ADMIN_SESSION_FILE, `${JSON.stringify(tokens, null, 2)}\n`, { mode: 0o600 });
  } catch (error) {
    console.log(`[admin session] could not write ${ADMIN_SESSION_FILE}: ${error instanceof Error ? error.message : String(error)}`);
  }
}

export async function loginAdmin(
  request: APIRequestContext,
  credentials: AdminCredentials,
  clientType = 'other',
): Promise<AdminSessionTokens> {
  const response = await request.post(`${trimmedBaseUrl(credentials)}/api/admin/auth/login`, {
    headers: jsonHeaders(credentials),
    data: {
      email: credentials.email,
      password: credentials.password,
      device_name: 'playwright',
      client_type: clientType,
    },
  });

  if (response.status() !== 200) {
    throw new Error(`admin login answered ${response.status()}: ${(await response.text()).slice(0, 300)}`);
  }

  return tokensFrom(await response.json());
}

export async function refreshAdminSession(
  request: APIRequestContext,
  credentials: AdminCredentials,
  refreshToken: string,
): Promise<AdminSessionTokens | null> {
  const response = await request.post(`${trimmedBaseUrl(credentials)}/api/admin/auth/refresh`, {
    headers: jsonHeaders(credentials),
    data: { refresh_token: refreshToken },
  });

  if (response.status() !== 200) {
    return null;
  }

  return tokensFrom(await response.json());
}

export async function currentAdminSession(
  request: APIRequestContext,
  credentials: AdminCredentials,
): Promise<AdminSessionTokens> {
  cached ??= readStoredAdminSession();

  if (!cached) {
    storeAdminSession(await loginAdmin(request, credentials));
  }

  return cached!;
}

export async function errorCodeOf(response: APIResponse): Promise<string | null> {
  if (response.status() !== 401) {
    return null;
  }

  try {
    const body = await response.json();

    return body?.error ?? body?.errors?.[0]?.extensions?.code ?? null;
  } catch {
    return null;
  }
}

export async function recoverAdminSession(
  request: APIRequestContext,
  credentials: AdminCredentials,
  failedWith: AdminSessionTokens,
  errorCode: string,
): Promise<AdminSessionTokens> {
  const stored = readStoredAdminSession();

  if (
    stored
    && stored.accessToken !== failedWith.accessToken
  ) {
    cached = stored;

    return stored;
  }

  if (errorCode === ACCESS_TOKEN_EXPIRED) {
    const refreshed = await refreshAdminSession(request, credentials, failedWith.refreshToken);

    if (refreshed) {
      storeAdminSession(refreshed);

      return refreshed;
    }
  }

  storeAdminSession(await loginAdmin(request, credentials));

  return cached!;
}

export async function withAdminSession(
  request: APIRequestContext,
  credentials: AdminCredentials,
  send: (accessToken: string) => Promise<APIResponse>,
): Promise<APIResponse> {
  const tokens = await currentAdminSession(request, credentials);
  const response = await send(tokens.accessToken);
  const errorCode = await errorCodeOf(response);

  if (
    errorCode !== ACCESS_TOKEN_EXPIRED
    && errorCode !== UNAUTHENTICATED
  ) {
    return response;
  }

  const recovered = await recoverAdminSession(request, credentials, tokens, errorCode);

  return send(recovered.accessToken);
}
