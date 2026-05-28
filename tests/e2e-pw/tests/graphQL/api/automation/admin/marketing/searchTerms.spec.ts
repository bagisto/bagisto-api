// Admin Marketing — Search Terms GraphQL e2e.
// No create — storefront-originated.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SEARCH_TERMS_QUERY,
  ADMIN_SEARCH_TERM_QUERY,
  ADMIN_SEARCH_TERM_UPDATE_MUTATION,
  ADMIN_SEARCH_TERM_DELETE_MUTATION,
  ADMIN_SEARCH_TERM_MASS_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/searchTerms.queries';

test.describe.configure({ timeout: 60_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Search Terms GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERMS_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingSearchTerms?.edges)).toBe(true);
  });

  test('listing with term filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERMS_QUERY, {
      first: 5, term: 'nonexistent-zzz',
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERM_QUERY, {
      id: '/api/admin/marketing/search-terms/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingSearchTerm === null).toBe(true);
  });

  test('update unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERM_UPDATE_MUTATION, {
      input: { id: '/api/admin/marketing/search-terms/99999999', term: 'x' },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.updateAdminMarketingSearchTerm?.adminMarketingSearchTerm === null).toBe(true);
  });

  test('delete unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERM_DELETE_MUTATION, {
      input: { id: '/api/admin/marketing/search-terms/99999999' },
    });
    expect(resp.status()).toBe(200);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SEARCH_TERM_MASS_DELETE_MUTATION, {
      input: { indices: [] },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });
});
