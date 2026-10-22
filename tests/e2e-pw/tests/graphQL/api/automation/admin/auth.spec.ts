import { test, expect } from '@playwright/test';
import { env } from '../../../config/env';
import { sendAdminGraphQLRequest, sendPublicAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import {
  TestAdmin,
  asBearer,
  createTestAdmin,
  createTestRole,
  deleteTestAdmin,
  deleteTestRole,
  login,
  loginTokens,
} from '../../../graphql/helpers/adminAccounts';
import {
  ADMIN_API_SESSIONS,
  ADMIN_LOGOUT,
  ADMIN_LOGOUT_ALL,
  ADMIN_PROFILE,
  ADMIN_SESSIONS,
  ADMIN_SESSION_DELETE,
  ADMIN_TOKEN_REFRESH,
  ADMIN_USER_REVOKE_SESSIONS,
  ADMIN_USER_SESSIONS,
} from '../../../graphql/Queries/admin/auth.queries';
import { ADMIN_USERS_LIST } from '../../../graphql/Queries/admin/settings/users.queries';
import { ADMIN_ROLES_LIST } from '../../../graphql/Queries/admin/settings/roles.queries';

test.describe.configure({ timeout: 90_000 });

test.describe('admin auth graphql api', () => {
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

  test('createAdminLogin is public and returns a token pair', async ({ request }) => {
    const response = await login(request, admin!, { deviceName: 'e2e tablet', clientType: 'pos' });

    expect(response.status()).toBe(200);

    const body = await response.json();

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const pair = body.data.createAdminLogin.adminLogin;

    expect(pair.tokenType).toBe('Bearer');
    expect(pair.accessToken.split('.')).toHaveLength(3);
    expect(pair.twoFactorRequired).toBe(false);
    expect(pair.session.clientType).toBe('pos');
    expect(pair.session.name).toBe('e2e tablet');

    const profile = (await (await asBearer(request, pair.accessToken, ADMIN_PROFILE)).json()).data.readAdminProfile;

    expect(profile.email).toBe(admin!.email);
    expect(profile.tokenKind).toBe('session');
    expect(profile.tokenId).toBe(pair.session.id);
  });

  test('a wrong password and an unknown email get the same error', async ({ request }) => {
    const wrong = await (await login(request, { email: admin!.email, password: 'not-the-password' })).json();
    const unknown = await (await login(request, { email: `nobody-${Date.now()}@example.com`, password: 'not-the-password' })).json();

    expect(wrong.errors[0].extensions.code).toBe('invalid_credentials');
    expect(unknown.errors[0].extensions.code).toBe('invalid_credentials');
    expect(wrong.errors[0].message).toBe(unknown.errors[0].message);
  });

  test('an inactive admin cannot log in', async ({ request }) => {
    const inactive = await createTestAdmin(request, { status: 0 });

    try {
      const body = await (await login(request, inactive)).json();

      expect(body.errors[0].extensions.code).toBe('account_inactive');
    } finally {
      await deleteTestAdmin(request, inactive);
    }
  });

  test('a document mixing a public mutation with a protected field is refused', async ({ request }) => {
    const response = await sendPublicAdminGraphQLRequest(request, `
      mutation {
        createAdminLogin(input: { email: "${admin!.email}", password: "${admin!.password}" }) { adminLogin { accessToken } }
        createAdminLogoutAll(input: {}) { adminLogoutAll { revoked } }
      }
    `);

    expect(response.status()).toBe(401);
    expect((await response.json()).errors[0].extensions.code).toBe('unauthenticated');
  });

  test('refresh rotates the pair and a replayed refresh token revokes the session', async ({ request }) => {
    const first = await loginTokens(request, admin!);

    const rotated = await (await sendPublicAdminGraphQLRequest(request, ADMIN_TOKEN_REFRESH, {
      input: { refreshToken: first.refreshToken },
    })).json();

    expect(rotated.errors, JSON.stringify(rotated.errors)).toBeUndefined();

    const pair = rotated.data.createAdminTokenRefresh.adminTokenRefresh;

    expect(pair.refreshToken).not.toBe(first.refreshToken);
    expect(pair.session.id).toBe(first.sessionId);

    const replayed = await (await sendPublicAdminGraphQLRequest(request, ADMIN_TOKEN_REFRESH, {
      input: { refreshToken: first.refreshToken },
    })).json();

    expect(replayed.errors[0].extensions.code).toBe('refresh_token_invalid');

    const afterTheft = await asBearer(request, pair.accessToken, ADMIN_PROFILE);

    expect(afterTheft.status()).toBe(401);
  });

  test('logout ends the current session and logout-all ends the rest', async ({ request }) => {
    const first = await loginTokens(request, admin!);
    const second = await loginTokens(request, admin!);
    const third = await loginTokens(request, admin!);

    const logout = await (await asBearer(request, first.accessToken, ADMIN_LOGOUT)).json();

    expect(logout.data.createAdminLogout.adminLogout.success).toBe(true);
    expect((await asBearer(request, first.accessToken, ADMIN_PROFILE)).status()).toBe(401);
    expect((await asBearer(request, second.accessToken, ADMIN_PROFILE)).status()).toBe(200);

    const all = await (await asBearer(request, second.accessToken, ADMIN_LOGOUT_ALL)).json();

    expect(all.data.createAdminLogoutAll.adminLogoutAll.revoked).toBe(2);
    expect((await asBearer(request, third.accessToken, ADMIN_PROFILE)).status()).toBe(401);
  });

  test('two logins with different client types are two sessions and revoking one keeps the other', async ({ request }) => {
    const web = await loginTokens(request, admin!, { clientType: 'web' });
    const mobile = await loginTokens(request, admin!, { clientType: 'mobile' });

    const listed = await (await asBearer(request, web.accessToken, ADMIN_SESSIONS)).json();
    const nodes = listed.data.adminSessions.edges.map((edge: any) => edge.node);

    expect(nodes.map((node: any) => node._id).sort()).toEqual([web.sessionId, mobile.sessionId].sort());
    expect(nodes.find((node: any) => node._id === web.sessionId).isCurrent).toBe(true);
    expect(nodes.find((node: any) => node._id === mobile.sessionId).clientType).toBe('mobile');

    const revoked = await (await asBearer(request, web.accessToken, ADMIN_SESSION_DELETE, {
      input: { id: `/api/admin/auth/sessions/${mobile.sessionId}` },
    })).json();

    expect(revoked.data.deleteAdminSession.adminSession.status).toBe('revoked');
    expect((await asBearer(request, mobile.accessToken, ADMIN_PROFILE)).status()).toBe(401);
    expect((await asBearer(request, web.accessToken, ADMIN_PROFILE)).status()).toBe(200);
  });

  test('another admin sessions are listed and revoked by an admin with the permission', async ({ request }) => {
    const session = await loginTokens(request, admin!, { clientType: 'desktop' });

    const listed = await (await sendAdminGraphQLRequest(request, ADMIN_USER_SESSIONS, { adminId: admin!.id })).json();

    expect(listed.errors, JSON.stringify(listed.errors)).toBeUndefined();
    expect(listed.data.adminSettingsUserSessions.edges.map((edge: any) => edge.node._id)).toContain(session.sessionId);

    const revoked = await (await sendAdminGraphQLRequest(request, ADMIN_USER_REVOKE_SESSIONS, {
      input: { id: `/api/admin/settings/users/${admin!.id}` },
    })).json();

    expect(revoked.data.revokeSessionsAdminSettingsUser.adminSettingsUser._id).toBe(admin!.id);
    expect((await asBearer(request, session.accessToken, ADMIN_PROFILE)).status()).toBe(401);
  });

  test('a limited role gets an error without the permission and data with it', async ({ request }) => {
    roleId = await createTestRole(request, ['settings', 'settings.users']);

    const limited = await createTestAdmin(request, { roleId });

    try {
      const tokens = await loginTokens(request, limited);

      const allowed = await (await asBearer(request, tokens.accessToken, ADMIN_USERS_LIST, { first: 1 })).json();
      const denied = await (await asBearer(request, tokens.accessToken, ADMIN_ROLES_LIST, { first: 1 })).json();
      const sessionsDenied = await (await asBearer(request, tokens.accessToken, ADMIN_API_SESSIONS)).json();

      expect(allowed.errors).toBeUndefined();
      expect(Array.isArray(allowed.data.adminSettingsUsers.edges)).toBe(true);
      expect(denied.errors?.length).toBeGreaterThan(0);
      expect(sessionsDenied.errors?.length).toBeGreaterThan(0);
    } finally {
      await deleteTestAdmin(request, limited);
    }
  });

  test('readAdminProfile without a bearer token is rejected', async ({ request }) => {
    const response = await sendPublicAdminGraphQLRequest(request, ADMIN_PROFILE);

    expect(response.status()).toBe(401);
    expect((await response.json()).errors[0].extensions.code).toBe('unauthenticated');
  });

  test('the shared session reads the signed-in admin', async ({ request }) => {
    const body = await (await sendAdminGraphQLRequest(request, ADMIN_PROFILE)).json();

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.readAdminProfile.email).toBe(env.adminEmail);
  });
});
