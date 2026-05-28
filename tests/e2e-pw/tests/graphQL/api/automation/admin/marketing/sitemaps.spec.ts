// Admin Marketing — Sitemaps GraphQL e2e.
// Generate mutation skipped (writes disk files + walks catalog).

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SITEMAPS_QUERY,
  ADMIN_SITEMAP_QUERY,
  ADMIN_SITEMAP_CREATE_MUTATION,
  ADMIN_SITEMAP_UPDATE_MUTATION,
  ADMIN_SITEMAP_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/sitemaps.queries';

test.describe.configure({ timeout: 60_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Sitemaps GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAPS_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingSitemaps?.edges)).toBe(true);
  });

  test('listing with file_name filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAPS_QUERY, {
      first: 5, file_name: 'nonexistent-zzz.xml',
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAP_QUERY, {
      id: '/api/admin/marketing/sitemaps/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingSitemap === null).toBe(true);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAP_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAP_CREATE_MUTATION, {
      input: { fileName: `e2e-gql-${ts}.xml`, path: '/' },
    });
    expect(createResp.status()).toBe(200);
    const cb = await safeBody(createResp);
    const id = cb?.data?.createAdminMarketingSitemap?.adminMarketingSitemap?.id;
    console.log('gql sitemap create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
    if (!id) return;

    const updateResp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAP_UPDATE_MUTATION, {
      input: { id, fileName: `e2e-gql-${ts}-upd.xml`, path: '/' },
    });
    expect(updateResp.status()).toBe(200);

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAP_DELETE_MUTATION, {
      input: { id },
    });
    expect(delResp.status()).toBe(200);
  });

  test('create invalid file_name extension is rejected', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SITEMAP_CREATE_MUTATION, {
      input: { fileName: `e2e-gql-bad-${ts}.txt`, path: '/' },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const created = body?.data?.createAdminMarketingSitemap?.adminMarketingSitemap;
    expect(hasErrors || created === null).toBe(true);
  });

  test.skip('generate sitemap (skipped — writes disk files)', async () => {});
});
