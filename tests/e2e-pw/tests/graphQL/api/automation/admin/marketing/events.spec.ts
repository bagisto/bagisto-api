import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_EVENT_QUERY,
  ADMIN_EVENT_CREATE_MUTATION,
  ADMIN_EVENT_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/events.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/marketing/events/999999999';

const NOT_FOUND = 'Marketing event not found.';

const EVENTS_BY_NAME_QUERY = `
  query adminMarketingEvents($first: Int, $name: String) {
    adminMarketingEvents(first: $first, name: $name) {
      totalCount
      edges { node { _id name description date } }
    }
  }
`;

const EVENT_DELETE_MUTATION = `
  mutation deleteAdminMarketingEvent($input: deleteAdminMarketingEventInput!) {
    deleteAdminMarketingEvent(input: $input) {
      clientMutationId
    }
  }
`;

function uniqueName(): string {
  return `e2e_gql_event_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
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

async function eventsNamed(request: APIRequestContext, name: string): Promise<any> {
  return dataOf(await gql(request, EVENTS_BY_NAME_QUERY, { first: 5, name }), 'adminMarketingEvents');
}

test.describe('admin marketing events graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, EVENT_DELETE_MUTATION, { input: { id } });
    }
  });

  async function createEvent(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const event = dataOf(await gql(request, ADMIN_EVENT_CREATE_MUTATION, { input }), 'createAdminMarketingEvent').adminMarketingEvent;
    created.push(event.id);
    return event;
  }

  test('listing filtered by name returns only the created event', async ({ request }) => {
    const name = uniqueName();
    const event = await createEvent(request, { name, description: 'gql e2e', date: '2027-12-31' });

    const connection = await eventsNamed(request, name);
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: event._id, name, description: 'gql e2e', date: '2027-12-31' });
  });

  test('listing with a name nobody uses returns no events', async ({ request }) => {
    const connection = await eventsNamed(request, uniqueName());
    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('detail unknown id reports the event as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_EVENT_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingEvent ?? null).toBeNull();
  });

  test('create missing date is rejected and nothing is stored', async ({ request }) => {
    const name = uniqueName();
    const body = await gql(request, ADMIN_EVENT_CREATE_MUTATION, { input: { name, description: 'no date' } });
    expect(firstError(body)).toBe('The date field is required.');
    expect(body.data?.createAdminMarketingEvent ?? null).toBeNull();
    expect((await eventsNamed(request, name)).totalCount).toBe(0);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const name = uniqueName();
    const event = await createEvent(request, { name, description: 'gql e2e', date: '2027-12-31' });
    expect(event.id).toBe(`/api/admin/marketing/events/${event._id}`);
    expect(event.name).toBe(name);

    const updatedName = `${name}_upd`;
    const updated = dataOf(
      await gql(request, ADMIN_EVENT_UPDATE_MUTATION, {
        input: { id: event.id, name: updatedName, description: 'upd', date: '2028-01-01' },
      }),
      'updateAdminMarketingEvent',
    ).adminMarketingEvent;
    expect(updated).toEqual({ id: event.id, _id: event._id, name: updatedName });

    const read = dataOf(await gql(request, ADMIN_EVENT_QUERY, { id: event.id }), 'adminMarketingEvent');
    expect(read).toEqual(expect.objectContaining({ _id: event._id, name: updatedName, description: 'upd', date: '2028-01-01' }));

    const deleted = dataOf(
      await gql(request, EVENT_DELETE_MUTATION, { input: { id: event.id, clientMutationId: 'e2e-delete-event' } }),
      'deleteAdminMarketingEvent',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-event');
    created = [];

    expect(firstError(await gql(request, ADMIN_EVENT_QUERY, { id: event.id }))).toBe(NOT_FOUND);
  });

  test('update unknown id reports the event as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_EVENT_UPDATE_MUTATION, {
      input: { id: UNKNOWN_IRI, name: uniqueName(), description: 'x', date: '2027-12-31' },
    });
    expect(firstError(body)).toBe(NOT_FOUND);
  });

  test('create empty payload is rejected with the name requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_EVENT_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The name field is required.');
    expect(body.data?.createAdminMarketingEvent ?? null).toBeNull();
  });
});
