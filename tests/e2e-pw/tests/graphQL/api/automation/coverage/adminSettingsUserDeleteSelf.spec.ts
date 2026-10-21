import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import {
  ADMIN_PROFILE_QUERY,
  ADMIN_SETTINGS_USER_DELETE_SELF_MUTATION,
} from '../../../graphql/Queries/coverage/adminSettings.queries';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 60_000 });

const PASSWORD_REQUIRED_MESSAGE = 'The password field is required to delete your own account.';
const INCORRECT_PASSWORD_MESSAGE = 'The password is incorrect.';

function wrongPassword(): string {
  return `e2e-wrong-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

async function deleteSelf(request: APIRequestContext, input: Record<string, any>) {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_SETTINGS_USER_DELETE_SELF_MUTATION, { input });
  expect(resp.status()).toBe(200);
  return resp.json();
}

async function expectTokenAdminStillExists(request: APIRequestContext) {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_PROFILE_QUERY);
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  expect(body.errors).toBeUndefined();
  expect(body.data?.readAdminProfile?.email).toBe(env.adminEmail);
}

test.describe('admin settings user delete self graphql', () => {
  test('should refuse to delete the calling admin when the password is wrong', async ({ request }) => {
    const body = await deleteSelf(request, { password: wrongPassword() });

    expect(body.data?.createAdminSettingsUserDeleteSelf ?? null).toBeNull();
    expect(body.errors?.[0]?.message).toBe(INCORRECT_PASSWORD_MESSAGE);
    await expectTokenAdminStillExists(request);
  });

  test('should refuse to delete the calling admin without a password', async ({ request }) => {
    const body = await deleteSelf(request, {});

    expect(body.data?.createAdminSettingsUserDeleteSelf ?? null).toBeNull();
    expect(body.errors?.[0]?.message).toBe(PASSWORD_REQUIRED_MESSAGE);
    await expectTokenAdminStillExists(request);
  });

  test('should refuse to delete the calling admin with an empty password', async ({ request }) => {
    const body = await deleteSelf(request, { password: '' });

    expect(body.data?.createAdminSettingsUserDeleteSelf ?? null).toBeNull();
    expect(body.errors?.[0]?.message).toBe(PASSWORD_REQUIRED_MESSAGE);
    await expectTokenAdminStillExists(request);
  });

  test('should refuse delete self without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(
      request,
      ADMIN_SETTINGS_USER_DELETE_SELF_MUTATION,
      { input: { password: wrongPassword() } },
      {},
      env.adminGraphqlEndpoint,
    );

    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
    await expectTokenAdminStillExists(request);
  });
});
