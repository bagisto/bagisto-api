import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CHANNEL_UPDATE } from '../../../../graphql/Queries/admin/settings/channels.queries';

test.describe.configure({ timeout: 60_000 });

const CHANNEL_IRI = (id: number) => `/api/admin/settings/channels/${id}`;
const UNKNOWN_IRI = CHANNEL_IRI(999999999);

const NOT_FOUND = 'Channel not found.';
const DELETED = 'Channel deleted successfully.';
const CODE_TAKEN = 'The code has already been taken.';
const NAME_REQUIRED = 'The name field is required.';

const CHANNELS_LIST = `
  query adminSettingsChannels($first: Int, $code: String) {
    adminSettingsChannels(first: $first, code: $code) {
      totalCount
      edges { node { _id code hostname name } }
    }
  }
`;

const CHANNEL_DETAIL = `
  query adminSettingsChannel($id: ID!) {
    adminSettingsChannel(id: $id) { id _id code hostname name description }
  }
`;

const CHANNEL_CREATE = `
  mutation createAdminSettingsChannel($input: createAdminSettingsChannelInput!) {
    createAdminSettingsChannel(input: $input) {
      adminSettingsChannel { id _id code hostname name description }
    }
  }
`;

const CHANNEL_DELETE = `
  mutation deleteAdminSettingsChannel($id: ID!) {
    deleteAdminSettingsChannel(input: { id: $id }) { adminSettingsChannel { _id code message } }
  }
`;

interface CreatedChannel {
  _id: number;
  id: string;
  code: string;
  name: string;
  hostname: string;
}

function uniqueCode(): string {
  return `e2e_gql_ch_${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

function channelInput(code: string, overrides: Record<string, any> = {}): Record<string, any> {
  return {
    code,
    name: `e2e gql channel ${code}`,
    description: 'e2e generated',
    hostname: `https://${code.replace(/_/g, '-')}.example.com`,
    locales: [1],
    currencies: [1],
    inventorySources: [1],
    defaultLocaleId: 1,
    baseCurrencyId: 1,
    rootCategoryId: 1,
    ...overrides,
  };
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

async function createChannel(request: APIRequestContext): Promise<CreatedChannel> {
  const input = channelInput(uniqueCode());
  const channel = dataOf(await gql(request, CHANNEL_CREATE, { input }), 'createAdminSettingsChannel').adminSettingsChannel;

  expect(channel).toEqual(expect.objectContaining({
    code: input.code,
    name: input.name,
    hostname: input.hostname,
    description: 'e2e generated',
  }));
  expect(channel.id).toBe(CHANNEL_IRI(channel._id));

  return { _id: channel._id, id: channel.id, code: input.code, name: input.name, hostname: input.hostname };
}

async function channelCount(request: APIRequestContext, code: string): Promise<number> {
  return dataOf(await gql(request, CHANNELS_LIST, { code, first: 5 }), 'adminSettingsChannels').totalCount;
}

test.describe('admin settings channels graphql api', () => {
  let created: CreatedChannel[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const channel of created) {
      await sendAdminGraphQLRequest(request, CHANNEL_DELETE, { id: channel.id });
    }
  });

  test('listing filtered by code returns only the created channel', async ({ request }) => {
    const channel = await createChannel(request);
    created.push(channel);

    const connection = dataOf(await gql(request, CHANNELS_LIST, { code: channel.code, first: 5 }), 'adminSettingsChannels');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({
      _id: channel._id,
      code: channel.code,
      hostname: channel.hostname,
      name: channel.name,
    });
  });

  test('detail returns the created channel by iri', async ({ request }) => {
    const channel = await createChannel(request);
    created.push(channel);

    const node = dataOf(await gql(request, CHANNEL_DETAIL, { id: channel.id }), 'adminSettingsChannel');
    expect(node).toEqual({
      id: channel.id,
      _id: channel._id,
      code: channel.code,
      hostname: channel.hostname,
      name: channel.name,
      description: 'e2e generated',
    });
  });

  test('detail of an unknown channel reports not found', async ({ request }) => {
    const body = await gql(request, CHANNEL_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsChannel ?? null).toBeNull();
  });

  test('create without a name is rejected and stores nothing', async ({ request }) => {
    const code = uniqueCode();
    const body = await gql(request, CHANNEL_CREATE, { input: channelInput(code, { name: null }) });

    expect(firstError(body)).toBe(NAME_REQUIRED);
    expect(body.data?.createAdminSettingsChannel ?? null).toBeNull();
    expect(await channelCount(request, code)).toBe(0);
  });

  test('create with a taken code is rejected', async ({ request }) => {
    const channel = await createChannel(request);
    created.push(channel);

    const body = await gql(request, CHANNEL_CREATE, {
      input: channelInput(channel.code, { hostname: `https://other-${channel.code.replace(/_/g, '-')}.example.com` }),
    });

    expect(firstError(body)).toBe(CODE_TAKEN);
    expect(body.data?.createAdminSettingsChannel ?? null).toBeNull();
    expect(await channelCount(request, channel.code)).toBe(1);
  });

  test('update renames the created channel and the detail reads the new name back', async ({ request }) => {
    const channel = await createChannel(request);
    created.push(channel);
    const name = `e2e gql channel renamed ${Date.now()}`;

    const updated = dataOf(await gql(request, ADMIN_CHANNEL_UPDATE, { id: channel.id, name }), 'updateAdminSettingsChannel').adminSettingsChannel;
    expect(updated).toEqual({ id: channel.id, _id: channel._id, name });

    const node = dataOf(await gql(request, CHANNEL_DETAIL, { id: channel.id }), 'adminSettingsChannel');
    expect(node).toEqual(expect.objectContaining({ _id: channel._id, code: channel.code, name, hostname: channel.hostname }));
  });

  test('update of an unknown channel reports not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CHANNEL_UPDATE, { id: UNKNOWN_IRI, name: 'e2e unknown channel' });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.updateAdminSettingsChannel ?? null).toBeNull();
  });

  test('delete removes the created channel', async ({ request }) => {
    const channel = await createChannel(request);

    const deleted = dataOf(await gql(request, CHANNEL_DELETE, { id: channel.id }), 'deleteAdminSettingsChannel').adminSettingsChannel;
    expect(deleted).toEqual({ _id: channel._id, code: channel.code, message: DELETED });

    const body = await gql(request, CHANNEL_DETAIL, { id: channel.id });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(await channelCount(request, channel.code)).toBe(0);
  });

  test('delete of an unknown channel reports not found', async ({ request }) => {
    const body = await gql(request, CHANNEL_DELETE, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.deleteAdminSettingsChannel ?? null).toBeNull();
  });
});
