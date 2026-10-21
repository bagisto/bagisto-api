import { APIRequestContext, APIResponse } from '@playwright/test';
import { sendRestRequest } from './restClient';
import { adminHeaders } from './adminAuth';
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
      ...callerHeaders,
    },
  });
}

export async function sendAdminMultipartRequest(
  request: APIRequestContext,
  endpoint: string,
  fields: Record<string, string | number | MultipartFile>
): Promise<APIResponse> {
  return request.fetch(`${env.baseUrl}${endpoint}`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-STOREFRONT-KEY': env.storefrontAccessKey,
      ...adminHeaders(),
    },
    multipart: fields,
  });
}
