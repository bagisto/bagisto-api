import { test, expect, APIRequestContext } from '@playwright/test';
import { env } from '../../../config/env';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { asBearer } from '../../../graphql/helpers/adminAccounts';
import {
  ADMIN_API_TOKEN,
  ADMIN_API_TOKENS,
  ADMIN_API_TOKEN_DELETE,
  ADMIN_PROFILE,
  ADMIN_SESSIONS,
} from '../../../graphql/Queries/admin/auth.queries';

test.describe.configure({ timeout: 60_000 });

async function findTokenNode(request: APIRequestContext, token: string): Promise<any> {
  const body = await (await sendAdminGraphQLRequest(request, ADMIN_API_TOKENS)).json();

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminApiTokens.edges
    .map((edge: any) => edge.node)
    .find((node: any) => node.tokenPreview === token.slice(-8));
}

test.describe('admin api tokens graphql api', () => {
  test.beforeEach(() => {
    test.skip(!env.adminApiToken, 'ADMIN_API_TOKEN is not set');
  });

  test('the localhost api token reads the profile as its admin', async ({ request }) => {
    const response = await asBearer(request, env.adminApiToken!, ADMIN_PROFILE);

    expect(response.status()).toBe(200);

    const body = await response.json();

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.readAdminProfile.email).toBe(env.adminEmail);
    expect(body.data.readAdminProfile.tokenKind).toBe('api_token');
  });

  test('a token bound to another domain is refused', async ({ request }) => {
    test.skip(!env.adminApiTokenOtherDomain, 'ADMIN_API_TOKEN_OTHER_DOMAIN is not set');

    const response = await asBearer(request, env.adminApiTokenOtherDomain!, ADMIN_PROFILE);

    expect(response.status()).toBe(403);
    expect((await response.json()).errors[0].extensions.code).toBe('api_token_domain_mismatch');
  });

  test('api tokens are listed and read without their string', async ({ request }) => {
    const node = await findTokenNode(request, env.adminApiToken!);

    expect(node).toBeTruthy();
    expect(node.domain).toBe('localhost');
    expect(node.status).toBe('active');

    const response = await sendAdminGraphQLRequest(request, ADMIN_API_TOKEN, { id: `/api/admin/settings/api-tokens/${node._id}` });
    const text = await response.text();
    const body = JSON.parse(text);

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.adminApiToken._id).toBe(node._id);
    expect(body.data.adminApiToken.domainMismatchCount).toBeGreaterThanOrEqual(0);
    expect(text).not.toContain(env.adminApiToken!);
  });

  test('a revoked api token is refused as revoked', async ({ request }) => {
    test.skip(!env.adminApiTokenRevocable, 'ADMIN_API_TOKEN_REVOCABLE_GRAPHQL is not set');

    const node = await findTokenNode(request, env.adminApiTokenRevocable!);

    expect(node).toBeTruthy();

    if (node.status === 'active') {
      const revoked = await (await sendAdminGraphQLRequest(request, ADMIN_API_TOKEN_DELETE, {
        input: { id: `/api/admin/settings/api-tokens/${node._id}` },
      })).json();

      expect(revoked.errors, JSON.stringify(revoked.errors)).toBeUndefined();
      expect(revoked.data.deleteAdminApiToken.adminApiToken.status).toBe('revoked');
    }

    const response = await asBearer(request, env.adminApiTokenRevocable!, ADMIN_PROFILE);

    expect(response.status()).toBe(401);
    expect((await response.json()).errors[0].extensions.code).toBe('api_token_revoked');
  });

  test('an api token cannot create api tokens', async ({ request }) => {
    const body = await (await asBearer(request, env.adminApiToken!, `
      mutation { createAdminApiToken(input: { name: "minted by an api token" }) { adminApiToken { _id } } }
    `)).json();

    expect(body.errors?.length).toBeGreaterThan(0);
    expect(body.data?.createAdminApiToken ?? null).toBeNull();
  });

  test('an api token cannot use the login session operations', async ({ request }) => {
    const body = await (await asBearer(request, env.adminApiToken!, ADMIN_SESSIONS)).json();

    expect(body.errors[0].extensions.code).toBe('access_token_required');
  });
});
