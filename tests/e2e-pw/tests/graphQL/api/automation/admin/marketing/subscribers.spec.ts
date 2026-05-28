// Admin Marketing — Newsletter Subscribers GraphQL e2e.
// NO create — origin = storefront.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SUBSCRIBERS_QUERY,
  ADMIN_SUBSCRIBER_QUERY,
  ADMIN_SUBSCRIBER_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/subscribers.queries';

test.describe.configure({ timeout: 60_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Newsletter Subscribers GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SUBSCRIBERS_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingSubscribers?.edges)).toBe(true);
  });

  test('listing with is_subscribed filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SUBSCRIBERS_QUERY, {
      first: 5, is_subscribed: 1,
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SUBSCRIBER_QUERY, {
      id: '/api/admin/marketing/subscribers/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingSubscriber === null).toBe(true);
  });

  test('toggle missing isSubscribed is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SUBSCRIBER_UPDATE_MUTATION, {
      input: { id: '/api/admin/marketing/subscribers/1' },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const upd = body?.data?.updateAdminMarketingSubscriber?.adminMarketingSubscriber;
    expect(hasErrors || upd === null).toBe(true);
  });

  test('toggle existing subscriber + restore', async ({ request }) => {
    const listResp = await sendAdminGraphQLRequest(request, ADMIN_SUBSCRIBERS_QUERY, { first: 1 });
    const listBody = await safeBody(listResp);
    const node = listBody?.data?.adminMarketingSubscribers?.edges?.[0]?.node;
    if (!node?.id) return;
    const was = node.isSubscribed ?? true;
    const toggleResp = await sendAdminGraphQLRequest(request, ADMIN_SUBSCRIBER_UPDATE_MUTATION, {
      input: { id: node.id, isSubscribed: !was },
    });
    expect(toggleResp.status()).toBe(200);
    // restore
    await sendAdminGraphQLRequest(request, ADMIN_SUBSCRIBER_UPDATE_MUTATION, {
      input: { id: node.id, isSubscribed: was },
    });
  });
});
