import { APIRequestContext } from '@playwright/test';
import { env } from '../../config/env';

const RETRY_ON_STATUS = [429, 503];
const MAX_RETRIES = 5;
const BASE_DELAY_MS = 1000;

function sleep(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function fetchWithRetry(
  request: APIRequestContext,
  url: string,
  init: RequestInit & { retries?: number }
): Promise<APIResponse> {
  const { retries = 0, ...rest } = init;
  let lastResponse: APIResponse | null = null;

  for (let attempt = 0; attempt <= retries; attempt++) {
    lastResponse = await request.fetch(url, rest);

    if (!RETRY_ON_STATUS.includes(lastResponse.status()) || attempt === retries) {
      break;
    }

    const delay = BASE_DELAY_MS * Math.pow(2, attempt);
    await sleep(delay);
  }

  return lastResponse!;
}

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

  const response = await fetchWithRetry(request, url, {
    method,
    data,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-STOREFRONT-KEY': env.storefrontAccessKey!,
      ...headers,
    },
    retries: MAX_RETRIES,
  });

  return response;
}