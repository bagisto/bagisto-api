import { APIRequestContext, APIResponse } from '@playwright/test';
import { sendRestRequest } from './restClient';
import { adminHeaders, withAdminToken } from './adminAuth';
import { env } from '../../config/env';

export interface AdminRequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  data?: Record<string, any>;
  headers?: Record<string, string>;
  params?: Record<string, string>;
}

export interface MultipartFile {
  name: string;
  mimeType: string;
  buffer: Buffer;
}

function hasAuthorization(headers: Record<string, string>): boolean {
  return Object.keys(headers).some(name => name.toLowerCase() === 'authorization');
}

export async function sendAdminRequest(
  request: APIRequestContext,
  endpoint: string,
  options: AdminRequestOptions = {}
): Promise<APIResponse> {
  const { headers: callerHeaders = {}, ...rest } = options;

  if (hasAuthorization(callerHeaders)) {
    return sendRestRequest(request, endpoint, { ...rest, headers: callerHeaders });
  }

  return withAdminToken(request, token => sendRestRequest(request, endpoint, {
    ...rest,
    headers: {
      ...adminHeaders(token),
      ...callerHeaders,
    },
  }));
}

export async function sendAdminMultipartRequest(
  request: APIRequestContext,
  endpoint: string,
  fields: Record<string, string | number | MultipartFile>
): Promise<APIResponse> {
  return withAdminToken(request, token => request.fetch(`${env.baseUrl}${endpoint}`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-STOREFRONT-KEY': env.storefrontAccessKey,
      ...adminHeaders(token),
    },
    multipart: fields,
  }));
}
