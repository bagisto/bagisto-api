import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CAMPAIGN_QUERY,
  ADMIN_CAMPAIGN_CREATE_MUTATION,
  ADMIN_CAMPAIGN_UPDATE_MUTATION,
  ADMIN_TEMPLATE_CREATE_FOR_CAMPAIGN,
} from '../../../../graphql/Queries/admin/marketing/campaigns.queries';

test.describe.configure({ timeout: 120_000 });

const UNKNOWN_IRI = '/api/admin/marketing/campaigns/999999999';

const NOT_FOUND = 'Campaign not found.';

const DEFAULT_CHANNEL_ID = 1;

const GENERAL_CUSTOMER_GROUP_ID = 2;

const CAMPAIGNS_BY_NAME_QUERY = `
  query adminMarketingCampaigns($first: Int, $name: String) {
    adminMarketingCampaigns(first: $first, name: $name) {
      totalCount
      edges { node { _id name subject status } }
    }
  }
`;

const CAMPAIGN_DELETE_MUTATION = `
  mutation deleteAdminMarketingCampaign($input: deleteAdminMarketingCampaignInput!) {
    deleteAdminMarketingCampaign(input: $input) {
      clientMutationId
    }
  }
`;

const CAMPAIGN_SEND_MUTATION = `
  mutation createAdminMarketingCampaignSend($input: createAdminMarketingCampaignSendInput!) {
    createAdminMarketingCampaignSend(input: $input) {
      adminMarketingCampaignSend { queued message }
    }
  }
`;

const TEMPLATE_DELETE_MUTATION = `
  mutation deleteAdminMarketingTemplate($input: deleteAdminMarketingTemplateInput!) {
    deleteAdminMarketingTemplate(input: $input) {
      clientMutationId
    }
  }
`;

function uniqueName(prefix: string): string {
  return `${prefix}_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  return resp.json();
}

function dataOf(body: any, field: string): any {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data[field];
}

function firstError(body: any): string | undefined {
  return body.errors?.[0]?.message;
}

async function campaignsNamed(request: APIRequestContext, name: string): Promise<any> {
  return dataOf(await gql(request, CAMPAIGNS_BY_NAME_QUERY, { first: 5, name }), 'adminMarketingCampaigns');
}

test.describe('admin marketing campaigns graphql', () => {
  let template: { id: string; _id: number };
  let createdCampaigns: string[];

  test.beforeEach(async ({ request }) => {
    createdCampaigns = [];
    template = dataOf(
      await gql(request, ADMIN_TEMPLATE_CREATE_FOR_CAMPAIGN, {
        input: { name: uniqueName('e2e_gql_tpl_for_camp'), status: 'active', content: '<p>x</p>' },
      }),
      'createAdminMarketingTemplate',
    ).adminMarketingTemplate;
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdCampaigns) {
      await sendAdminGraphQLRequest(request, CAMPAIGN_DELETE_MUTATION, { input: { id } });
    }
    await sendAdminGraphQLRequest(request, TEMPLATE_DELETE_MUTATION, { input: { id: template.id } });
  });

  function campaignInput(name: string, status: number): Record<string, any> {
    return {
      name,
      subject: `subj ${name}`,
      marketingTemplateId: template._id,
      channelId: DEFAULT_CHANNEL_ID,
      customerGroupId: GENERAL_CUSTOMER_GROUP_ID,
      status,
    };
  }

  async function createCampaign(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const campaign = dataOf(await gql(request, ADMIN_CAMPAIGN_CREATE_MUTATION, { input }), 'createAdminMarketingCampaign').adminMarketingCampaign;
    createdCampaigns.push(campaign.id);
    return campaign;
  }

  test('listing filtered by name returns only the created campaign', async ({ request }) => {
    const name = uniqueName('e2e_gql_camp');
    const campaign = await createCampaign(request, campaignInput(name, 0));

    const connection = await campaignsNamed(request, name);
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: campaign._id, name, subject: `subj ${name}`, status: 0 });
  });

  test('detail unknown id reports the campaign as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CAMPAIGN_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingCampaign ?? null).toBeNull();
  });

  test('create empty payload is rejected with the name requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_CAMPAIGN_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The name field is required.');
    expect(body.data?.createAdminMarketingCampaign ?? null).toBeNull();
  });

  test('create + update + delete round trip', async ({ request }) => {
    const name = uniqueName('e2e_gql_camp');
    const campaign = await createCampaign(request, campaignInput(name, 0));
    expect(campaign).toEqual({ id: `/api/admin/marketing/campaigns/${campaign._id}`, _id: campaign._id, name });

    const updatedName = `${name}_upd`;
    const updated = dataOf(
      await gql(request, ADMIN_CAMPAIGN_UPDATE_MUTATION, {
        input: { id: campaign.id, ...campaignInput(updatedName, 0), subject: `subj upd ${name}` },
      }),
      'updateAdminMarketingCampaign',
    ).adminMarketingCampaign;
    expect(updated).toEqual({ id: campaign.id, _id: campaign._id, name: updatedName });

    const read = dataOf(await gql(request, ADMIN_CAMPAIGN_QUERY, { id: campaign.id }), 'adminMarketingCampaign');
    expect(read).toEqual({
      id: campaign.id,
      _id: campaign._id,
      name: updatedName,
      subject: `subj upd ${name}`,
      status: 0,
      marketingTemplate: { _id: template._id },
      channel: { _id: DEFAULT_CHANNEL_ID },
      customerGroup: { _id: GENERAL_CUSTOMER_GROUP_ID },
    });

    const deleted = dataOf(
      await gql(request, CAMPAIGN_DELETE_MUTATION, { input: { id: campaign.id, clientMutationId: 'e2e-delete-campaign' } }),
      'deleteAdminMarketingCampaign',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-campaign');
    createdCampaigns = [];

    expect(firstError(await gql(request, ADMIN_CAMPAIGN_QUERY, { id: campaign.id }))).toBe(NOT_FOUND);
  });

  test('send of an inactive campaign is refused without queueing mail', async ({ request }) => {
    const campaign = await createCampaign(request, campaignInput(uniqueName('e2e_gql_camp'), 0));

    const body = await gql(request, CAMPAIGN_SEND_MUTATION, { input: { campaignId: campaign._id } });
    expect(firstError(body)).toBe('Cannot send an inactive campaign. Enable it first.');
    expect(body.data?.createAdminMarketingCampaignSend ?? null).toBeNull();
  });
});
