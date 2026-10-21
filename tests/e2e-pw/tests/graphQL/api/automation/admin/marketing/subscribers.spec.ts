import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../../graphql/helpers/graphqlClient';
import {
  ADMIN_SUBSCRIBER_QUERY,
  ADMIN_SUBSCRIBER_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/subscribers.queries';
import { CREATE_NEWSLETTER } from '../../../../graphql/Queries/newsletter.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/marketing/subscribers/999999999';

const NOT_FOUND = 'Newsletter subscriber not found.';

const SUBSCRIBERS_FILTER_QUERY = `
  query adminMarketingSubscribers($first: Int, $email: String, $is_subscribed: Int) {
    adminMarketingSubscribers(first: $first, email: $email, is_subscribed: $is_subscribed) {
      totalCount
      edges { node { id _id email isSubscribed customerId } }
    }
  }
`;

const SUBSCRIBER_DELETE_MUTATION = `
  mutation deleteAdminMarketingSubscriber($input: deleteAdminMarketingSubscriberInput!) {
    deleteAdminMarketingSubscriber(input: $input) {
      clientMutationId
    }
  }
`;

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

async function subscribers(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, SUBSCRIBERS_FILTER_QUERY, { first: 5, ...variables }), 'adminMarketingSubscribers');
}

test.describe('admin marketing newsletter subscribers graphql', () => {
  let email: string;
  let subscriber: { id: string; _id: number };

  test.beforeEach(async ({ request }) => {
    email = `e2e-gql-sub-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}@example.com`;

    const resp = await sendGraphQLRequest(request, CREATE_NEWSLETTER, { input: { customerEmail: email } });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.createNewsletter.newsletter).toEqual({
      success: true,
      message: 'You have successfully subscribed to our newsletter.',
    });

    const connection = await subscribers(request, { email });
    expect(connection.totalCount).toBe(1);
    subscriber = connection.edges[0].node;
  });

  test.afterEach(async ({ request }) => {
    await sendAdminGraphQLRequest(request, SUBSCRIBER_DELETE_MUTATION, { input: { id: subscriber.id } });
  });

  test('listing filtered by email returns only the subscribed guest', async ({ request }) => {
    const connection = await subscribers(request, { email });
    expect(connection.edges[0].node).toEqual({
      id: `/api/admin/marketing/subscribers/${subscriber._id}`,
      _id: subscriber._id,
      email,
      isSubscribed: true,
      customerId: null,
    });
  });

  test('listing is_subscribed filter keeps or drops the subscriber by its flag', async ({ request }) => {
    expect((await subscribers(request, { email, is_subscribed: 1 })).totalCount).toBe(1);
    expect((await subscribers(request, { email, is_subscribed: 0 })).totalCount).toBe(0);
  });

  test('detail unknown id reports the subscriber as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_SUBSCRIBER_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingSubscriber ?? null).toBeNull();
  });

  test('toggle missing is subscribed flag is rejected and the flag is unchanged', async ({ request }) => {
    const body = await gql(request, ADMIN_SUBSCRIBER_UPDATE_MUTATION, { input: { id: subscriber.id } });
    expect(firstError(body)).toBe('The is_subscribed field is required.');
    expect(body.data?.updateAdminMarketingSubscriber ?? null).toBeNull();

    const read = dataOf(await gql(request, ADMIN_SUBSCRIBER_QUERY, { id: subscriber.id }), 'adminMarketingSubscriber');
    expect(read.isSubscribed).toBe(true);
  });

  test('toggle unsubscribes the created subscriber and the detail reads it back', async ({ request }) => {
    const updated = dataOf(
      await gql(request, ADMIN_SUBSCRIBER_UPDATE_MUTATION, { input: { id: subscriber.id, isSubscribed: false } }),
      'updateAdminMarketingSubscriber',
    ).adminMarketingSubscriber;
    expect(updated).toEqual({ id: subscriber.id, _id: subscriber._id, isSubscribed: false });

    const read = dataOf(await gql(request, ADMIN_SUBSCRIBER_QUERY, { id: subscriber.id }), 'adminMarketingSubscriber');
    expect(read).toEqual({ id: subscriber.id, _id: subscriber._id, email, isSubscribed: false });
    expect((await subscribers(request, { email, is_subscribed: 0 })).totalCount).toBe(1);
  });

  test('delete removes the created subscriber', async ({ request }) => {
    const deleted = dataOf(
      await gql(request, SUBSCRIBER_DELETE_MUTATION, { input: { id: subscriber.id, clientMutationId: 'e2e-delete-subscriber' } }),
      'deleteAdminMarketingSubscriber',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-subscriber');

    expect(firstError(await gql(request, ADMIN_SUBSCRIBER_QUERY, { id: subscriber.id }))).toBe(NOT_FOUND);
    expect((await subscribers(request, { email })).totalCount).toBe(0);
  });
});
