// tests/graphQL/graphql/helpers/adminGraphqlClient.ts
//
// Wraps sendGraphQLRequest with admin Bearer injection (post 2026-05-27
// refactor). Token is pre-issued and immutable for the test run — no login
// round-trip, no 401-retry. The underlying sendGraphQLRequest still adds
// X-STOREFRONT-KEY (the GraphQL endpoint serves both shop + admin and the
// storefront key is still required at the transport boundary).

import { APIRequestContext, APIResponse } from '@playwright/test';
import { sendGraphQLRequest } from './graphqlClient';
import { adminGraphQLHeaders } from '../../config/adminAuth';

export async function sendAdminGraphQLRequest(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  extraHeaders: Record<string, string> = {}
): Promise<APIResponse> {
  return sendGraphQLRequest(request, query, variables, {
    ...adminGraphQLHeaders(),
    ...extraHeaders, // caller wins on conflict
  });
}
