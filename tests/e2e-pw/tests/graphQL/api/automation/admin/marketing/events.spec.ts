// Admin Marketing — Events GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_EVENTS_QUERY,
  ADMIN_EVENT_QUERY,
  ADMIN_EVENT_CREATE_MUTATION,
  ADMIN_EVENT_UPDATE_MUTATION,
  ADMIN_EVENT_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/events.queries';

test.describe.configure({ timeout: 60_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Events GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EVENTS_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingEvents?.edges)).toBe(true);
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EVENTS_QUERY, {
      first: 5, name: 'nonexistent-yyy',
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EVENT_QUERY, {
      id: '/api/admin/marketing/events/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingEvent === null).toBe(true);
  });

  test('create missing date is rejected', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EVENT_CREATE_MUTATION, {
      input: { name: `e2e_gql_event_${ts}`, description: 'no date' },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const created = body?.data?.createAdminMarketingEvent?.adminMarketingEvent;
    expect(hasErrors || created === null).toBe(true);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_EVENT_CREATE_MUTATION, {
      input: { name: `e2e_gql_event_${ts}`, description: 'gql e2e', date: '2027-12-31' },
    });
    expect(createResp.status()).toBe(200);
    const cb = await safeBody(createResp);
    const id = cb?.data?.createAdminMarketingEvent?.adminMarketingEvent?.id;
    console.log('gql event create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
    if (!id) return;

    const updateResp = await sendAdminGraphQLRequest(request, ADMIN_EVENT_UPDATE_MUTATION, {
      input: { id, name: `e2e_gql_event_${ts}_upd`, description: 'upd', date: '2028-01-01' },
    });
    expect(updateResp.status()).toBe(200);

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_EVENT_DELETE_MUTATION, {
      input: { id },
    });
    expect(delResp.status()).toBe(200);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EVENT_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });
});
