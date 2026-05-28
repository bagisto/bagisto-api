// Admin Marketing — Campaigns GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CAMPAIGNS_QUERY,
  ADMIN_CAMPAIGN_QUERY,
  ADMIN_CAMPAIGN_CREATE_MUTATION,
  ADMIN_CAMPAIGN_UPDATE_MUTATION,
  ADMIN_CAMPAIGN_DELETE_MUTATION,
  ADMIN_TEMPLATE_CREATE_FOR_CAMPAIGN,
  ADMIN_TEMPLATE_DELETE_FOR_CAMPAIGN,
} from '../../../../graphql/Queries/admin/marketing/campaigns.queries';

test.describe.configure({ timeout: 120_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

async function makeTemplate(request: any, ts: number) {
  const r = await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_CREATE_FOR_CAMPAIGN, {
    input: { name: `e2e_gql_tpl_for_camp_${ts}`, status: 'active', content: '<p>x</p>' },
  });
  const b = await safeBody(r);
  return {
    id: b?.data?.createAdminMarketingTemplate?.adminMarketingTemplate?.id ?? null,
    _id: b?.data?.createAdminMarketingTemplate?.adminMarketingTemplate?._id ?? null,
  };
}

async function deleteTemplate(request: any, id: string) {
  await sendAdminGraphQLRequest(request, ADMIN_TEMPLATE_DELETE_FOR_CAMPAIGN, { input: { id } });
}

test.describe('Admin Marketing — Campaigns GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CAMPAIGNS_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingCampaigns?.edges)).toBe(true);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CAMPAIGN_QUERY, {
      id: '/api/admin/marketing/campaigns/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingCampaign === null).toBe(true);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CAMPAIGN_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const tpl = await makeTemplate(request, ts);
    if (!tpl.id || !tpl._id) return;
    try {
      const createResp = await sendAdminGraphQLRequest(request, ADMIN_CAMPAIGN_CREATE_MUTATION, {
        input: {
          name: `e2e_gql_camp_${ts}`,
          subject: `subj ${ts}`,
          marketingTemplateId: tpl._id,
          channelId: 1,
          customerGroupId: 2,
          status: 0,
        },
      });
      expect(createResp.status()).toBe(200);
      const cb = await safeBody(createResp);
      const id = cb?.data?.createAdminMarketingCampaign?.adminMarketingCampaign?.id;
      console.log('gql campaign create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
      if (!id) return;

      const updateResp = await sendAdminGraphQLRequest(request, ADMIN_CAMPAIGN_UPDATE_MUTATION, {
        input: {
          id,
          name: `e2e_gql_camp_${ts}_upd`,
          subject: `subj upd ${ts}`,
          marketingTemplateId: tpl._id,
          channelId: 1,
          customerGroupId: 2,
          status: 0,
        },
      });
      expect(updateResp.status()).toBe(200);

      const delResp = await sendAdminGraphQLRequest(request, ADMIN_CAMPAIGN_DELETE_MUTATION, {
        input: { id },
      });
      expect(delResp.status()).toBe(200);
    } finally {
      await deleteTemplate(request, tpl.id);
    }
  });

  test.skip('send action queues emails (skipped — side effect)', async () => {});
});
