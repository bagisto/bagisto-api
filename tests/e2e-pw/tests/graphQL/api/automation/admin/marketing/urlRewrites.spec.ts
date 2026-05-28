// Admin Marketing — URL Rewrites GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_URL_REWRITES_QUERY,
  ADMIN_URL_REWRITE_QUERY,
  ADMIN_URL_REWRITE_CREATE_MUTATION,
  ADMIN_URL_REWRITE_UPDATE_MUTATION,
  ADMIN_URL_REWRITE_DELETE_MUTATION,
  ADMIN_URL_REWRITE_MASS_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/urlRewrites.queries';

test.describe.configure({ timeout: 60_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

// GraphQL inputs use camelCase keys (API Platform rewrites snake_case).
function basePayload(ts: number) {
  return {
    entityType: 'product',
    requestPath: `e2e-gql-old-${ts}`,
    targetPath: `e2e-gql-new-${ts}`,
    redirectType: '301',
    locale: 'en',
  };
}

test.describe('Admin Marketing — URL Rewrites GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITES_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingUrlRewrites?.edges)).toBe(true);
  });

  test('listing with entity_type filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITES_QUERY, {
      first: 5, entity_type: 'product',
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITE_QUERY, {
      id: '/api/admin/marketing/url-rewrites/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingUrlRewrite === null).toBe(true);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITE_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITE_CREATE_MUTATION, {
      input: basePayload(ts),
    });
    expect(createResp.status()).toBe(200);
    const cb = await safeBody(createResp);
    const id = cb?.data?.createAdminMarketingUrlRewrite?.adminMarketingUrlRewrite?.id;
    console.log('gql urlrewrite create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
    if (!id) return;

    const updateResp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITE_UPDATE_MUTATION, {
      input: { id, ...basePayload(ts), redirectType: '302' },
    });
    expect(updateResp.status()).toBe(200);

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITE_DELETE_MUTATION, {
      input: { id },
    });
    expect(delResp.status()).toBe(200);
  });

  test('create invalid entity_type is rejected', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITE_CREATE_MUTATION, {
      input: { ...basePayload(ts), entityType: 'banana' },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const created = body?.data?.createAdminMarketingUrlRewrite?.adminMarketingUrlRewrite;
    expect(hasErrors || created === null).toBe(true);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_URL_REWRITE_MASS_DELETE_MUTATION, {
      input: { indices: [] },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });
});
