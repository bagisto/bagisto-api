// tests/graphQL/api/automation/admin/auth.spec.ts
//
// Admin Auth GraphQL API — post 2026-05-27 refactor.
// createAdminLogin / createAdminLogout / createAdminForgotPassword /
// createAdminProfileUpdate were removed. Only `readAdminProfile` survives.
// The new test "request without Bearer token is rejected" proves the auth
// gate works.

import { test, expect } from '@playwright/test';
import { env } from '../../../config/env';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';

test.describe.configure({ timeout: 60_000 });

const READ_ADMIN_PROFILE_QUERY = `
  query readAdminProfile {
    readAdminProfile {
      id
      name
      email
      success
    }
  }
`;

test.describe('Admin Auth GraphQL API', () => {
  test('readAdminProfile returns the authenticated admin', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(request, READ_ADMIN_PROFILE_QUERY);
    const status = response.status();
    console.log('gql admin profile read:', status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();

    const profile = body?.data?.readAdminProfile;
    expect(profile).toBeTruthy();
    expect(typeof profile.email).toBe('string');
    expect(profile.email).toBe(env.adminEmail);
  });

  test('readAdminProfile without Bearer token is rejected', async ({ request }) => {
    // sendGraphQLRequest still injects X-STOREFRONT-KEY, but NOT a Bearer.
    const response = await sendGraphQLRequest(request, READ_ADMIN_PROFILE_QUERY, {});
    const status = response.status();
    console.log('gql admin profile no bearer:', status);

    const body = await response.json();
    const errs = body?.errors ?? [];
    const profile = body?.data?.readAdminProfile;
    // GraphQL responses are typically HTTP 200 — the failure surfaces as
    // errors[] + readAdminProfile=null.
    expect(Array.isArray(errs) && errs.length > 0).toBeTruthy();
    expect(profile === null || profile === undefined).toBeTruthy();
  });

  test('removed createAdminLogin is no longer in schema', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      `mutation { createAdminLogin(input: { email: "a@b.co", password: "x" }) { adminLogin { id } } }`,
      {}
    );
    const body = await response.json();
    expect(body?.errors, 'createAdminLogin should be missing from the schema').toBeTruthy();
  });

  test('removed createAdminLogout is no longer in schema', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(
      request,
      `mutation { createAdminLogout(input: { all: false }) { adminLogout { success } } }`,
      {}
    );
    const body = await response.json();
    expect(body?.errors, 'createAdminLogout should be missing from the schema').toBeTruthy();
  });

  test('removed createAdminForgotPassword is no longer in schema', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      `mutation { createAdminForgotPassword(input: { email: "a@b.co" }) { adminForgotPassword { success } } }`,
      {}
    );
    const body = await response.json();
    expect(body?.errors, 'createAdminForgotPassword should be missing from the schema').toBeTruthy();
  });

  test('removed createAdminProfileUpdate is no longer in schema', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(
      request,
      `mutation { createAdminProfileUpdate(input: { name: "x", currentPassword: "y" }) { adminProfileUpdate { success } } }`,
      {}
    );
    const body = await response.json();
    expect(body?.errors, 'createAdminProfileUpdate should be missing from the schema').toBeTruthy();
  });
});
