import { APIRequestContext, APIResponse } from '@playwright/test';
import { sendGraphQLRequest } from './graphqlClient';
import { adminGraphQLHeaders, withAdminToken } from '../../config/adminAuth';
import { env } from '../../config/env';

function hasAuthorization(headers: Record<string, string>): boolean {
  return Object.keys(headers).some(name => name.toLowerCase() === 'authorization');
}

export async function sendAdminGraphQLRequest(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  extraHeaders: Record<string, string> = {}
): Promise<APIResponse> {
  if (hasAuthorization(extraHeaders)) {
    return sendGraphQLRequest(request, query, variables, extraHeaders, env.adminGraphqlEndpoint);
  }

  return withAdminToken(request, token => sendGraphQLRequest(
    request,
    query,
    variables,
    {
      ...adminGraphQLHeaders(token),
      ...extraHeaders,
    },
    env.adminGraphqlEndpoint
  ));
}

export async function sendPublicAdminGraphQLRequest(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  headers: Record<string, string> = {}
): Promise<APIResponse> {
  return sendGraphQLRequest(request, query, variables, headers, env.adminGraphqlEndpoint);
}
