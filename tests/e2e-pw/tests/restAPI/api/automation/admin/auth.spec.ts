import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_AUTH } from '../../../rest/endpoints/admin.auth.endpoints';
import { ADMIN_SETTINGS } from '../../../rest/endpoints/admin.settings.endpoints';
import {
  TestAdmin,
  createTestAdmin,
  createTestRole,
  deleteTestAdmin,
  deleteTestRole,
  login,
  loginTokens,
  withBearer,
} from '../../../rest/helpers/adminAccounts';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 90_000 });

test.describe('admin auth rest api', () => {
  let admin: TestAdmin | null = null;
  let roleId: number | null = null;

  test.beforeEach(async ({ request }) => {
    admin = await createTestAdmin(request);
  });

  test.afterEach(async ({ request }) => {
    await deleteTestAdmin(request, admin);
    admin = null;

    if (roleId) {
      await deleteTestRole(request, roleId);
      roleId = null;
    }
  });

  test('login returns an access token, a refresh token and the session', async ({ request }) => {
    const response = await login(request, admin!, { device_name: 'e2e browser', client_type: 'web' });

    expect(response.status()).toBe(200);

    const body = await response.json();

    expect(body.tokenType).toBe('Bearer');
    expect(body.accessToken.split('.')).toHaveLength(3);
    expect(body.refreshToken).toMatch(/^\d+\|[0-9a-f]{64}$/);
    expect(body.expiresIn).toBeGreaterThan(0);
    expect(body.twoFactorRequired).toBe(false);
    expect(body.session.name).toBe('e2e browser');
    expect(body.session.clientType).toBe('web');
    expect(body.admin.email).toBe(admin!.email);

    const profile = await withBearer(request, body.accessToken, ADMIN_AUTH.PROFILE);

    expect(profile.status()).toBe(200);

    const rows = await profile.json();
    const row = Array.isArray(rows) ? rows[0] : rows;

    expect(row.email).toBe(admin!.email);
    expect(row.tokenKind).toBe('session');
    expect(row.tokenId).toBe(body.session.id);
  });

  test('a wrong password is refused without saying whether the email exists', async ({ request }) => {
    const wrongPassword = await login(request, { email: admin!.email, password: 'not-the-password' });
    const unknownEmail = await login(request, { email: `nobody-${Date.now()}@example.com`, password: 'not-the-password' });

    expect(wrongPassword.status()).toBe(401);
    expect(unknownEmail.status()).toBe(401);

    const wrongBody = await wrongPassword.json();
    const unknownBody = await unknownEmail.json();

    expect(wrongBody.error).toBe('invalid_credentials');
    expect(unknownBody.error).toBe('invalid_credentials');
    expect(wrongBody.message).toBe(unknownBody.message);
    expect(wrongBody.accessToken).toBeUndefined();
  });

  test('an inactive admin cannot log in', async ({ request }) => {
    const inactive = await createTestAdmin(request, { status: 0 });

    try {
      const response = await login(request, inactive);

      expect(response.status()).toBe(403);
      expect((await response.json()).error).toBe('account_inactive');
    } finally {
      await deleteTestAdmin(request, inactive);
    }
  });

  test('refresh rotates the pair and a replayed refresh token revokes the session', async ({ request }) => {
    const first = await loginTokens(request, admin!);

    const rotated = await sendRestRequest(request, ADMIN_AUTH.REFRESH, {
      method: 'POST',
      data: { refresh_token: first.refreshToken },
    });

    expect(rotated.status()).toBe(200);

    const pair = await rotated.json();

    expect(pair.refreshToken).not.toBe(first.refreshToken);
    expect(pair.session.id).toBe(first.sessionId);
    expect((await withBearer(request, pair.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(200);

    const replayed = await sendRestRequest(request, ADMIN_AUTH.REFRESH, {
      method: 'POST',
      data: { refresh_token: first.refreshToken },
    });

    expect(replayed.status()).toBe(401);
    expect((await replayed.json()).error).toBe('refresh_token_invalid');

    const afterTheft = await withBearer(request, pair.accessToken, ADMIN_AUTH.PROFILE);

    expect(afterTheft.status()).toBe(401);
    expect((await afterTheft.json()).error).toBe('unauthenticated');
  });

  test('a refresh token is not accepted as a bearer token', async ({ request }) => {
    const tokens = await loginTokens(request, admin!);

    const response = await withBearer(request, tokens.refreshToken, ADMIN_AUTH.PROFILE);

    expect(response.status()).toBe(401);
    expect((await response.json()).error).toBe('unauthenticated');
  });

  test('logout ends only the current session', async ({ request }) => {
    const current = await loginTokens(request, admin!);
    const other = await loginTokens(request, admin!);

    const logout = await withBearer(request, current.accessToken, ADMIN_AUTH.LOGOUT, 'POST');

    expect(logout.status()).toBe(200);
    expect((await logout.json()).success).toBe(true);

    expect((await withBearer(request, current.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(401);
    expect((await withBearer(request, other.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(200);
  });

  test('logout-all ends every session of the admin', async ({ request }) => {
    const first = await loginTokens(request, admin!);
    const second = await loginTokens(request, admin!);

    const response = await withBearer(request, first.accessToken, ADMIN_AUTH.LOGOUT_ALL, 'POST');

    expect(response.status()).toBe(200);
    expect((await response.json()).revoked).toBe(2);

    expect((await withBearer(request, first.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(401);
    expect((await withBearer(request, second.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(401);
    expect((await sendAdminRequest(request, ADMIN_AUTH.PROFILE)).status()).toBe(200);
  });

  test('two logins with different client types are two sessions and revoking one keeps the other', async ({ request }) => {
    const web = await loginTokens(request, admin!, { client_type: 'web' });
    const mobile = await loginTokens(request, admin!, { client_type: 'mobile' });

    const list = await withBearer(request, web.accessToken, ADMIN_AUTH.SESSIONS);

    expect(list.status()).toBe(200);

    const sessions = (await list.json()).data;

    expect(sessions.map((session: any) => session.id).sort()).toEqual([web.sessionId, mobile.sessionId].sort());
    expect(sessions.find((session: any) => session.id === mobile.sessionId).clientType).toBe('mobile');
    expect(sessions.find((session: any) => session.id === web.sessionId).isCurrent).toBe(true);

    const revoke = await withBearer(request, web.accessToken, ADMIN_AUTH.SESSION(mobile.sessionId), 'DELETE');

    expect(revoke.status()).toBe(204);
    expect((await withBearer(request, mobile.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(401);
    expect((await withBearer(request, web.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(200);
  });

  test('another admin sessions are listed and revoked through the settings endpoints', async ({ request }) => {
    const session = await loginTokens(request, admin!, { client_type: 'pos' });

    const list = await sendAdminRequest(request, ADMIN_AUTH.USER_SESSIONS(admin!.id));

    expect(list.status()).toBe(200);
    expect((await list.json()).data.map((row: any) => row.id)).toContain(session.sessionId);

    const revoke = await sendAdminRequest(request, ADMIN_AUTH.USER_REVOKE_SESSIONS(admin!.id), { method: 'POST' });

    expect(revoke.status()).toBe(200);
    expect((await withBearer(request, session.accessToken, ADMIN_AUTH.PROFILE)).status()).toBe(401);
  });

  test('a limited role gets 403 without the permission and 200 with it', async ({ request }) => {
    roleId = await createTestRole(request, ['settings', 'settings.users']);

    const limited = await createTestAdmin(request, { roleId });

    try {
      const tokens = await loginTokens(request, limited);

      const allowed = await withBearer(request, tokens.accessToken, ADMIN_SETTINGS.USERS);
      const denied = await withBearer(request, tokens.accessToken, ADMIN_SETTINGS.ROLES);
      const sessionsDenied = await withBearer(request, tokens.accessToken, ADMIN_AUTH.API_SESSIONS);

      expect(allowed.status()).toBe(200);
      expect(denied.status()).toBe(403);
      expect(sessionsDenied.status()).toBe(403);
    } finally {
      await deleteTestAdmin(request, limited);
    }
  });

  test('profile read without a bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ADMIN_AUTH.PROFILE);

    expect(response.status()).toBe(401);
    expect((await response.json()).error).toBe('unauthenticated');
  });

  test('profile read with a garbage bearer token returns 401', async ({ request }) => {
    const response = await withBearer(request, 'this-is-not-a-real-token', ADMIN_AUTH.PROFILE);

    expect(response.status()).toBe(401);
    expect((await response.json()).error).toBe('unauthenticated');
  });

  test('the shared session reaches admin data endpoints', async ({ request }) => {
    const response = await sendAdminRequest(request, '/api/admin/customers', { params: { per_page: '1' } });

    expect(response.status()).toBe(200);

    const profile = await sendAdminRequest(request, ADMIN_AUTH.PROFILE);
    const rows = await profile.json();

    expect((Array.isArray(rows) ? rows[0] : rows).email).toBe(env.adminEmail);
  });

  test('the removed login path is not routable', async ({ request }) => {
    const response = await sendRestRequest(request, ADMIN_AUTH.REMOVED_LOGIN, {
      method: 'POST',
      data: { email: env.adminEmail, password: 'whatever' },
    });

    expect([404, 405, 500]).toContain(response.status());
  });
});
