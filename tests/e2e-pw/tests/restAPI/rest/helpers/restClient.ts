import { APIRequestContext } from '@playwright/test';
import { env } from '../../config/env';

export async function sendRestRequest(
  request: APIRequestContext,
  endpoint: string,
  options: {
    method?: 'GET' | 'POST' | 'PATCH' | 'DELETE';
    data?: Record<string, any>;
    headers?: Record<string, string>;
    params?: Record<string, string>;
  } = {}
) {
  const { method = 'GET', data, headers = {}, params } = options;

  let url = `${env.baseUrl}${endpoint}`;

  if (params) {
    const searchParams = new URLSearchParams(params).toString();
    url = `${url}?${searchParams}`;
  }

  return request.fetch(url, {
    method,
    data,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-STOREFRONT-KEY': env.storefrontAccessKey!,
      ...headers,
    },
  });
}