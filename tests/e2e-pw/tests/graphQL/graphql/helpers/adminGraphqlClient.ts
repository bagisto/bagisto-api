// tests/graphQL/graphql/helpers/adminGraphqlClient.ts
//
// Wraps sendGraphQLRequest with admin Bearer injection. Admin operations are
// served by the dedicated admin-scoped endpoint /api/admin/graphql — the
// storefront /api/graphql does NOT expose admin operations. The admin token
// is generated at test-setup time (never hardcoded).

import { APIRequestContext, APIResponse } from '@playwright/test';
import { sendGraphQLRequest } from './graphqlClient';
import { adminGraphQLHeaders } from '../../config/adminAuth';
import { env } from '../../config/env';

export async function sendAdminGraphQLRequest(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  extraHeaders: Record<string, string> = {}
): Promise<APIResponse> {
  return sendGraphQLRequest(
    request,
    query,
    variables,
    {
      ...adminGraphQLHeaders(),
      ...extraHeaders, // caller wins on conflict
    },
    env.adminGraphqlEndpoint
  );
}
