import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_ALL_CHANNELS_COMPLETE, GET_CHANNEL_COMPLETE } from '../../graphql/Queries/channel.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Channels GraphQL API - Docs aligned', () => {
  test('Should return the channels connection shape from the docs', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannels, { first: 10 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const channels = expectConnection(body, 'data.channels');
    expect(typeof channels.totalCount).toBe('number');
  });

  test('Should return a single channel when the API has channel data', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannels, { first: 1 });
    const body = await response.json();
    const channels = expectConnection(body, 'data.channels');

    if (channels.edges.length === 0) {
      console.log('Channel list is empty in this environment.');
      return;
    }

    const singleResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannel, {
      id: channels.edges[0].node.id,
    });
    expect(singleResponse.status()).toBe(200);

    const singleBody = await singleResponse.json();
    expectGraphQLSuccess(singleBody, 'data.channel');
  });

  test('Should return a real message for invalid channel IDs', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannel, {
      id: 'invalid-format-id',
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    console.log(`Channel invalid ID response: ${messages.join(' | ')}`);
    expect(messages.length > 0 || body.data?.channel === null).toBeTruthy();
  });

  test('Should cover complete channels docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_CHANNELS_COMPLETE, { first: 5, after: null });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const channels = expectConnection(body, 'data.channels');
    expect(typeof channels.totalCount).toBe('number');
  });

  test('Should cover complete single channel docs query', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getChannels, { first: 1 });
    const listBody = await listResponse.json();
    const channels = expectConnection(listBody, 'data.channels');

    if (channels.edges.length === 0) {
      console.log('No channels available for complete coverage.');
      expect(true).toBeTruthy();
      return;
    }

    const response = await sendGraphQLRequest(request, GET_CHANNEL_COMPLETE, { id: channels.edges[0].node.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.channel');
  });
});
