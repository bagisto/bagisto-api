import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CUSTOMER_CREATE } from '../../../../graphql/Queries/admin/customers/customers.queries';

test.describe.configure({ timeout: 120_000 });

const CREATE_NOTE = `
  mutation createAdminCustomerNote($customerId: Int!, $note: String!, $customerNotified: Boolean) {
    createAdminCustomerNote(input: { customerId: $customerId, note: $note, customerNotified: $customerNotified }) {
      adminCustomerNote {
        _id
        customerId
        note
        customerNotified
        createdAt
      }
    }
  }
`;

const LIST_NOTES = `
  query adminCustomerNotes($customerId: Int!) {
    adminCustomerNotes(customerId: $customerId) {
      edges {
        node {
          _id
          customerId
          note
          customerNotified
        }
      }
    }
  }
`;

const DELETE_CUSTOMER = `
  mutation deleteAdminCustomer($id: ID!) {
    deleteAdminCustomer(input: { id: $id }) {
      adminCustomer {
        _id
      }
    }
  }
`;

let customerId: number;

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

function expectGraphQLError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(body.errors?.[0]?.message).toBe(message);
}

async function createCustomer(request: APIRequestContext): Promise<number> {
  const body = await gql(request, ADMIN_CUSTOMER_CREATE, {
    firstName: 'Note',
    lastName: 'Subject',
    email: `e2e_gql_note_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
    customerGroupId: 2,
    channelId: 1,
    sendPassword: false,
    password: 'e2epass123',
  });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.createAdminCustomer.adminCustomer._id;
}

async function listedNotes(request: APIRequestContext): Promise<any[]> {
  const body = await gql(request, LIST_NOTES, { customerId });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomerNotes.edges.map((edge: any) => edge.node);
}

test.describe('admin customer notes graphql api', () => {
  test.beforeEach(async ({ request }) => {
    customerId = await createCustomer(request);
  });

  test.afterEach(async ({ request }) => {
    await sendAdminGraphQLRequest(request, DELETE_CUSTOMER, { id: `/api/admin/customers/${customerId}` });
  });

  test('create note with only note text', async ({ request }) => {
    const text = `e2e gql note ${Date.now()}`;
    const body = await gql(request, CREATE_NOTE, { customerId, note: text });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const note = body.data.createAdminCustomerNote.adminCustomerNote;
    expect(note._id).toBeGreaterThan(0);
    expect(note.customerId).toBe(customerId);
    expect(note.note).toBe(text);
    expect(note.customerNotified).toBe(false);
    expect(typeof note.createdAt).toBe('string');

    expect(await listedNotes(request)).toEqual([
      { _id: note._id, customerId, note: text, customerNotified: false },
    ]);
  });

  test('create note with customer notified true', async ({ request }) => {
    const text = `notify ${Date.now()}`;
    const body = await gql(request, CREATE_NOTE, { customerId, note: text, customerNotified: true });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const note = body.data.createAdminCustomerNote.adminCustomerNote;
    expect(note.note).toBe(text);
    expect(note.customerNotified).toBe(true);

    expect(await listedNotes(request)).toEqual([
      { _id: note._id, customerId, note: text, customerNotified: true },
    ]);
  });

  test('create rejects an empty note', async ({ request }) => {
    const body = await gql(request, CREATE_NOTE, { customerId, note: '' });

    expectGraphQLError(body, 'createAdminCustomerNote', 'Note text is required.');
    expect(await listedNotes(request)).toEqual([]);
  });

  test('create for an unknown customer reports customer not found', async ({ request }) => {
    const body = await gql(request, CREATE_NOTE, { customerId: 99999999, note: 'orphan' });

    expectGraphQLError(body, 'createAdminCustomerNote', 'Customer not found.');
  });
});
