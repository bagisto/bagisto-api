import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_ALL_CHANNELS_COMPLETE, GET_CHANNEL_COMPLETE } from '../../graphql/Queries/channel.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

const INVALID_CHANNEL_ID_MESSAGE = 'Invalid ID format. Expected IRI format like "/api/shop/channels/1" or numeric ID';

test.describe('channels graphql api - docs aligned', () => {
  test('should return the channels connection shape from the docs', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannels, { first: 10 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const channels = expectConnection(body, 'data.channels');
    expect(typeof channels.totalCount).toBe('number');
  });

  test('should return a single channel by the id the listing gives', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannels, { first: 1 });
    const body = await response.json();
    const channels = expectConnection(body, 'data.channels');
    expect(channels.edges.length).toBe(1);
    const listed = channels.edges[0].node;

    const singleResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannel, {
      id: listed.id,
    });
    expect(singleResponse.status()).toBe(200);

    const singleBody = await singleResponse.json();
    expectGraphQLSuccess(singleBody, 'data.channel');
    expect(singleBody.data.channel._id).toBe(listed._id);
    expect(singleBody.data.channel.code).toBe(listed.code);
  });

  test('should return the invalid id format message for a malformed channel id', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannel, {
      id: 'invalid-format-id',
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body)).toEqual([INVALID_CHANNEL_ID_MESSAGE]);
    expect(body.data.channel).toBeNull();
  });

  test('should cover complete channels docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_CHANNELS_COMPLETE, { first: 5, after: null });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const channels = expectConnection(body, 'data.channels');
    expect(typeof channels.totalCount).toBe('number');
  });

  test('should cover complete single channel docs query', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannels, { first: 1 });
    const listBody = await listResponse.json();
    const channels = expectConnection(listBody, 'data.channels');
    expect(channels.edges.length).toBe(1);
    const listed = channels.edges[0].node;

    const response = await sendGraphQLRequest(request, GET_CHANNEL_COMPLETE, { id: listed.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.channel');
    expect(body.data.channel._id).toBe(listed._id);
    expect(body.data.channel.code).toBe(listed.code);
  });
});
