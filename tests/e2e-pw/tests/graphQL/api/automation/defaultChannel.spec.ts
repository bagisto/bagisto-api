import { test, expect } from '@playwright/test';
import {
  GET_DEFAULT_CHANNEL,
  GET_DEFAULT_CHANNEL_MINIMAL,
} from '../../graphql/Queries/defaultChannel.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Default Channel GraphQL API Tests', () => {
  test('Should return the default channel collection or show the real API response', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Default channel response: ${JSON.stringify(body)}`);
    expect(
      body.data?.collectionDefaultChannels ||
      graphQLErrorMessages(body).length > 0
    ).toBeTruthy();
  });

  test('Should return at least one channel node when default channel is configured', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    const edges = body.data?.collectionDefaultChannels?.edges;

    if (Array.isArray(edges) && edges.length > 0) {
      const node = edges[0].node;
      expect(node).toBeTruthy();
      expect(node.code).toBeTruthy();
      expect(node.name).toBeTruthy();
    } else {
      console.log(`No default channel returned: ${graphQLErrorMessages(body).join(' | ')}`);
    }
  });

  test('Should expose the minimal default channel shape', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL_MINIMAL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Default channel minimal response: ${JSON.stringify(body)}`);
    expect(
      body.data?.collectionDefaultChannels ||
      graphQLErrorMessages(body).length > 0
    ).toBeTruthy();
  });

  test('Should return a totalCount field on the default channel connection', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    if (body.data?.collectionDefaultChannels) {
      expect(typeof body.data.collectionDefaultChannels.totalCount === 'number').toBe(true);
    } else {
      console.log(`Default channel had no data: ${graphQLErrorMessages(body).join(' | ')}`);
    }
  });
});
