import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_SETTINGS_COVERAGE } from '../../../rest/endpoints/coverage/adminSettings.endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 60_000 });

const PASSWORD_REQUIRED_MESSAGE = 'The password field is required to delete your own account.';
const INCORRECT_PASSWORD_MESSAGE = 'The password is incorrect.';

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

function wrongPassword(): string {
  return `e2e-wrong-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

async function expectTokenAdminStillExists(request: APIRequestContext) {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.ADMIN_PROFILE);
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  const profile = Array.isArray(body) ? body[0] : body;
  expect(profile?.email).toBe(env.adminEmail);
}

test.describe('admin settings user delete self rest api', () => {
  test('should refuse to delete the calling admin when the password is wrong', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.USERS_DELETE_SELF, {
      method: 'POST',
      data: { password: wrongPassword() },
    });

    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe(INCORRECT_PASSWORD_MESSAGE);
    await expectTokenAdminStillExists(request);
  });

  test('should refuse to delete the calling admin without a password', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.USERS_DELETE_SELF, {
      method: 'POST',
      data: {},
    });

    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe(PASSWORD_REQUIRED_MESSAGE);
    await expectTokenAdminStillExists(request);
  });

  test('should refuse to delete the calling admin with an empty password', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.USERS_DELETE_SELF, {
      method: 'POST',
      data: { password: '' },
    });

    expect(resp.status()).toBe(422);
    expect(errorMessage(await safeJson(resp))).toBe(PASSWORD_REQUIRED_MESSAGE);
    await expectTokenAdminStillExists(request);
  });

  test('should refuse delete self without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_SETTINGS_COVERAGE.USERS_DELETE_SELF, {
      method: 'POST',
      data: { password: wrongPassword() },
    });

    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
    await expectTokenAdminStillExists(request);
  });
});
