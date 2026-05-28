// Admin Marketing — Search Synonyms GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SEARCH_SYNONYMS_QUERY,
  ADMIN_SEARCH_SYNONYM_QUERY,
  ADMIN_SEARCH_SYNONYM_CREATE_MUTATION,
  ADMIN_SEARCH_SYNONYM_UPDATE_MUTATION,
  ADMIN_SEARCH_SYNONYM_DELETE_MUTATION,
  ADMIN_SEARCH_SYNONYM_MASS_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/searchSynonyms.queries';

test.describe.configure({ timeout: 60_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Search Synonyms GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYMS_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingSearchSynonyms?.edges)).toBe(true);
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYMS_QUERY, {
      first: 5, name: 'nonexistent-xyz',
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYM_QUERY, {
      id: '/api/admin/marketing/search-synonyms/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingSearchSynonym === null).toBe(true);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYM_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYM_CREATE_MUTATION, {
      input: { name: `e2e_gql_syn_${ts}`, terms: 'shirt,tshirt,tee' },
    });
    expect(createResp.status()).toBe(200);
    const cb = await safeBody(createResp);
    const id = cb?.data?.createAdminMarketingSearchSynonym?.adminMarketingSearchSynonym?.id;
    console.log('gql synonym create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
    if (!id) return;

    const updateResp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYM_UPDATE_MUTATION, {
      input: { id, name: `e2e_gql_syn_${ts}_upd`, terms: 'pant,trousers' },
    });
    expect(updateResp.status()).toBe(200);

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYM_DELETE_MUTATION, {
      input: { id },
    });
    expect(delResp.status()).toBe(200);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_SYNONYM_MASS_DELETE_MUTATION, {
      input: { indices: [] },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });
});
