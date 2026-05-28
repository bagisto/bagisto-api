import { APIRequestContext, test } from '@playwright/test';
import { env } from '../../config/env';

const RETRY_ON_STATUS = [429];
const MAX_RETRIES = 1;
const BASE_DELAY_MS = 1000;
const MAX_DELAY_MS = 2000;

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

    if (lastResponse.status() === 429) {
      try {
        const bodyText = await lastResponse.text();
        const parsed = JSON.parse(bodyText);
        console.log(`Rate limited on ${url}, retry_after: ${parsed.retry_after}`);
      } catch {
        console.log(`Rate limited on ${url}`);
      }
      if (attempt === retries) break;
      await sleep(1000);
      continue;
    }

    if (!RETRY_ON_STATUS.includes(lastResponse.status())) {
      break;
    }

    let delay = BASE_DELAY_MS * Math.pow(2, attempt);
    try {
      const bodyText = await lastResponse.text();
      const parsed = JSON.parse(bodyText);
      if (parsed.retry_after) {
        const retryAfter = parseInt(parsed.retry_after, 10);
        if (!isNaN(retryAfter) && retryAfter > 0) {
          delay = Math.min(retryAfter * 1000, MAX_DELAY_MS);
        }
      }
    } catch {
      // Non-JSON or missing retry_after in body; fall back to exponential backoff.
    }
    await sleep(delay);
  }

  return lastResponse!;
}

export async function sendRestRequest(
  request: APIRequestContext,
  endpoint: string,
  options: {
    method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
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

  // Central rate-limit safety net: when the API surrenders after all retries
  // and still returns 429, skip the calling test instead of letting it fail
  // assertion arrays that don't include 429. test.skip() throws a special
  // exception that Playwright recognises — let it propagate up through the
  // test body so the test is marked skipped rather than failed.
  if (response.status() === 429) {
    test.skip(true, `Rate limited on ${url}`);
  }

  return response;
}