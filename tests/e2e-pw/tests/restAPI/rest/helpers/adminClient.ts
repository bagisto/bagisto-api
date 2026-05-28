// rest/helpers/adminClient.ts
//
// Wraps sendRestRequest with admin Bearer injection (post 2026-05-27 refactor).
// Since the token is pre-issued and immutable for the test run, there's no
// login round-trip and no 401-retry — a 401 here means real auth failure.

import { APIRequestContext } from '@playwright/test';
import { sendRestRequest } from './restClient';
import { adminHeaders } from './adminAuth';

export interface AdminRequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  data?: Record<string, any>;
  headers?: Record<string, string>;
  params?: Record<string, string>;
}

export async function sendAdminRequest(
  request: APIRequestContext,
  endpoint: string,
  options: AdminRequestOptions = {}
) {
  const { headers: callerHeaders = {}, ...rest } = options;

  return sendRestRequest(request, endpoint, {
    ...rest,
    headers: {
      ...adminHeaders(),
      ...callerHeaders, // caller wins on conflict
    },
  });
}
