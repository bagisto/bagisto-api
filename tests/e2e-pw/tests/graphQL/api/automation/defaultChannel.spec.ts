import { test, expect } from '@playwright/test';
import {
  GET_DEFAULT_CHANNEL,
  GET_DEFAULT_CHANNEL_MINIMAL,
} from '../../graphql/Queries/defaultChannel.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

const DEFAULT_CHANNEL_CODE = 'default';

test.describe('default channel graphql api tests', () => {
  test('should return the default channel collection', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();

    const edges = body.data.collectionDefaultChannels.edges;
    expect(edges).toHaveLength(1);
    expect(edges[0].node.code).toBe(DEFAULT_CHANNEL_CODE);
  });

  test('should return the default channel node with its configuration', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();

    const node = body.data.collectionDefaultChannels.edges[0].node;
    expect(node.code).toBe(DEFAULT_CHANNEL_CODE);
    expect(typeof node._id).toBe('number');
    expect(typeof node.name).toBe('string');
    expect(node.name.length).toBeGreaterThan(0);
    expect(typeof node.rootCategoryId).toBe('number');
    expect(typeof node.defaultLocaleId).toBe('number');
    expect(typeof node.baseCurrencyId).toBe('number');
    expect(typeof node.isMaintenanceOn).toBe('boolean');
  });

  test('should expose the minimal default channel shape', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL_MINIMAL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();

    const edges = body.data.collectionDefaultChannels.edges;
    expect(edges).toHaveLength(1);
    expect(edges[0].node.code).toBe(DEFAULT_CHANNEL_CODE);
    expect(Object.keys(edges[0].node).sort()).toEqual(['code', 'id', 'name']);
  });

  test('should return a totalcount field on the default channel connection', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_DEFAULT_CHANNEL);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();
    expect(typeof body.data.collectionDefaultChannels.totalCount).toBe('number');
  });
});
