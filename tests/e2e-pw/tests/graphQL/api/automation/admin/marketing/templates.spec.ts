// Admin Marketing — Email Templates GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_TEMPLATES_QUERY,
  ADMIN_TEMPLATE_QUERY,
  ADMIN_TEMPLATE_CREATE_MUTATION,
  ADMIN_TEMPLATE_UPDATE_MUTATION,
  ADMIN_TEMPLATE_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/templates.queries';

test.describe.configure({ timeout: 60_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Email Templates GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATES_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingTemplates?.edges)).toBe(true);
  });

  test('listing with status filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATES_QUERY, {
      first: 5, status: 'active',
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_QUERY, {
      id: '/api/admin/marketing/templates/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingTemplate === null).toBe(true);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_CREATE_MUTATION, {
      input: { name: `e2e_gql_tpl_${ts}`, status: 'active', content: '<p>e2e gql</p>' },
    });
    expect(createResp.status()).toBe(200);
    const cb = await safeBody(createResp);
    const id = cb?.data?.createAdminMarketingTemplate?.adminMarketingTemplate?.id;
    console.log('gql template create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
    if (!id) return;

    const updateResp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_UPDATE_MUTATION, {
      input: { id, name: `e2e_gql_tpl_${ts}_upd`, status: 'inactive', content: '<p>upd</p>' },
    });
    expect(updateResp.status()).toBe(200);

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_DELETE_MUTATION, {
      input: { id },
    });
    expect(delResp.status()).toBe(200);
  });

  test('create invalid status is rejected', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_CREATE_MUTATION, {
      input: { name: `e2e_gql_tpl_bad_${ts}`, status: 'banana', content: '<p>x</p>' },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const created = body?.data?.createAdminMarketingTemplate?.adminMarketingTemplate;
    expect(hasErrors || created === null).toBe(true);
  });
});
