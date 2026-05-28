import { APIRequestContext } from '@playwright/test';
import { env } from '../../config/env';

export async function sendGraphQLRequest(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  headers: Record<string, string> = {}
) {
  return request.post(`${env.baseUrl}${env.graphqlEndpoint}`, {
    data: {
      query,
      variables,
    },
    headers: {
      'Content-Type': 'application/json',
      'X-STOREFRONT-KEY': env.storefrontAccessKey!,
      ...headers,
    },
  });
}
