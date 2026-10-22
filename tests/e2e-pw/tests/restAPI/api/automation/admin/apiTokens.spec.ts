import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { withBearer } from '../../../rest/helpers/adminAccounts';
import { ADMIN_AUTH } from '../../../rest/endpoints/admin.auth.endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 60_000 });

async function findTokenRow(request: APIRequestContext, token: string): Promise<any> {
  const response = await sendAdminRequest(request, ADMIN_AUTH.API_TOKENS, { params: { per_page: '50' } });

  expect(response.status()).toBe(200);

  const rows = (await response.json()).data;

  return rows.find((row: any) => row.tokenPreview === token.slice(-8));
}

function profileRow(body: any): any {
  return Array.isArray(body) ? body[0] : body;
}

test.describe('admin api tokens rest api', () => {
  test.beforeEach(() => {
    test.skip(!env.adminApiToken, 'ADMIN_API_TOKEN is not set');
  });

  test('the localhost api token reads the profile as its admin', async ({ request }) => {
    const response = await withBearer(request, env.adminApiToken!, ADMIN_AUTH.PROFILE);

    expect(response.status()).toBe(200);

    const profile = profileRow(await response.json());

    expect(profile.email).toBe(env.adminEmail);
    expect(profile.tokenKind).toBe('api_token');
    expect(typeof profile.tokenId).toBe('number');
  });

  test('the localhost api token reaches admin data endpoints', async ({ request }) => {
    const response = await sendAdminRequest(request, '/api/admin/customers', {
      params: { per_page: '1' },
      headers: { Authorization: `Bearer ${env.adminApiToken}` },
    });

    expect(response.status()).toBe(200);
    expect(Array.isArray((await response.json()).data)).toBe(true);
  });

  test('a token bound to another domain is refused', async ({ request }) => {
    test.skip(!env.adminApiTokenOtherDomain, 'ADMIN_API_TOKEN_OTHER_DOMAIN is not set');

    const response = await withBearer(request, env.adminApiTokenOtherDomain!, ADMIN_AUTH.PROFILE);

    expect(response.status()).toBe(403);
    expect((await response.json()).error).toBe('api_token_domain_mismatch');
  });

  test('api tokens are listed and read without their string', async ({ request }) => {
    const row = await findTokenRow(request, env.adminApiToken!);

    expect(row).toBeTruthy();
    expect(row.domain).toBe('localhost');
    expect(row.status).toBe('active');

    const detail = await sendAdminRequest(request, ADMIN_AUTH.API_TOKEN(row.id));

    expect(detail.status()).toBe(200);

    const text = await detail.text();
    const body = JSON.parse(text);

    expect(body.id).toBe(row.id);
    expect(body.domain).toBe('localhost');
    expect(text).not.toContain(env.adminApiToken!);
  });

  test('a revoked api token is refused as revoked', async ({ request }) => {
    test.skip(!env.adminApiTokenRevocable, 'ADMIN_API_TOKEN_REVOCABLE_REST is not set');

    const row = await findTokenRow(request, env.adminApiTokenRevocable!);

    expect(row).toBeTruthy();

    if (row.status === 'active') {
      const revoke = await sendAdminRequest(request, ADMIN_AUTH.API_TOKEN(row.id), { method: 'DELETE' });

      expect(revoke.status()).toBe(204);
    }

    const response = await withBearer(request, env.adminApiTokenRevocable!, ADMIN_AUTH.PROFILE);

    expect(response.status()).toBe(401);
    expect((await response.json()).error).toBe('api_token_revoked');

    const again = await sendAdminRequest(request, ADMIN_AUTH.API_TOKEN(row.id), { method: 'DELETE' });

    expect(again.status()).toBe(409);
  });

  test('an api token cannot create api tokens', async ({ request }) => {
    const response = await withBearer(request, env.adminApiToken!, ADMIN_AUTH.API_TOKENS, 'POST', {
      name: 'minted by an api token',
      domain: 'localhost',
    });

    expect(response.status()).toBe(405);
    expect(typeof (await response.json()).message).toBe('string');

    const listed = await sendAdminRequest(request, ADMIN_AUTH.API_TOKENS, { params: { per_page: '50' } });
    const names = (await listed.json()).data.map((row: any) => row.name);

    expect(names).not.toContain('minted by an api token');
  });

  test('an api token cannot use the login session endpoints', async ({ request }) => {
    const response = await withBearer(request, env.adminApiToken!, ADMIN_AUTH.SESSIONS);

    expect(response.status()).toBe(403);
    expect((await response.json()).error).toBe('access_token_required');
  });
});
