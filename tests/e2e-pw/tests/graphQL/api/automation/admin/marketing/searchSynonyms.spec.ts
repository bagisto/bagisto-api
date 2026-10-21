import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SEARCH_SYNONYM_QUERY,
  ADMIN_SEARCH_SYNONYM_CREATE_MUTATION,
  ADMIN_SEARCH_SYNONYM_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/searchSynonyms.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/marketing/search-synonyms/999999999';

const NOT_FOUND = 'Search synonym not found.';

const SYNONYMS_BY_NAME_QUERY = `
  query adminMarketingSearchSynonyms($first: Int, $name: String) {
    adminMarketingSearchSynonyms(first: $first, name: $name) {
      totalCount
      edges { node { _id name terms } }
    }
  }
`;

const SYNONYM_DELETE_MUTATION = `
  mutation deleteAdminMarketingSearchSynonym($input: deleteAdminMarketingSearchSynonymInput!) {
    deleteAdminMarketingSearchSynonym(input: $input) {
      clientMutationId
    }
  }
`;

const SYNONYM_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingSearchSynonymMassDelete($input: createAdminMarketingSearchSynonymMassDeleteInput!) {
    createAdminMarketingSearchSynonymMassDelete(input: $input) {
      adminMarketingSearchSynonymMassDelete { deleted message }
    }
  }
`;

function uniqueName(): string {
  return `e2e_gql_syn_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
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

async function synonymsNamed(request: APIRequestContext, name: string): Promise<any> {
  return dataOf(await gql(request, SYNONYMS_BY_NAME_QUERY, { first: 5, name }), 'adminMarketingSearchSynonyms');
}

test.describe('admin marketing search synonyms graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, SYNONYM_DELETE_MUTATION, { input: { id } });
    }
  });

  async function createSynonym(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const synonym = dataOf(await gql(request, ADMIN_SEARCH_SYNONYM_CREATE_MUTATION, { input }), 'createAdminMarketingSearchSynonym').adminMarketingSearchSynonym;
    created.push(synonym.id);
    return synonym;
  }

  test('listing filtered by name returns only the created synonym group', async ({ request }) => {
    const name = uniqueName();
    const synonym = await createSynonym(request, { name, terms: 'shirt,tshirt,tee' });

    const connection = await synonymsNamed(request, name);
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: synonym._id, name, terms: 'shirt,tshirt,tee' });
  });

  test('listing with a name nobody uses returns no synonym groups', async ({ request }) => {
    const connection = await synonymsNamed(request, uniqueName());
    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('detail unknown id reports the synonym group as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_SEARCH_SYNONYM_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingSearchSynonym ?? null).toBeNull();
  });

  test('create empty payload is rejected with the name requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_SEARCH_SYNONYM_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The name field is required.');
    expect(body.data?.createAdminMarketingSearchSynonym ?? null).toBeNull();
  });

  test('create without terms is rejected and nothing is stored', async ({ request }) => {
    const name = uniqueName();
    const body = await gql(request, ADMIN_SEARCH_SYNONYM_CREATE_MUTATION, { input: { name } });
    expect(firstError(body)).toBe('The terms field is required.');
    expect((await synonymsNamed(request, name)).totalCount).toBe(0);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const name = uniqueName();
    const synonym = await createSynonym(request, { name, terms: 'shirt,tshirt,tee' });
    expect(synonym).toEqual({ id: `/api/admin/marketing/search-synonyms/${synonym._id}`, _id: synonym._id, name });

    const updated = dataOf(
      await gql(request, ADMIN_SEARCH_SYNONYM_UPDATE_MUTATION, { input: { id: synonym.id, name: `${name}_upd`, terms: 'pant,trousers' } }),
      'updateAdminMarketingSearchSynonym',
    ).adminMarketingSearchSynonym;
    expect(updated).toEqual({ id: synonym.id, _id: synonym._id, name: `${name}_upd` });

    const read = dataOf(await gql(request, ADMIN_SEARCH_SYNONYM_QUERY, { id: synonym.id }), 'adminMarketingSearchSynonym');
    expect(read).toEqual({ id: synonym.id, _id: synonym._id, name: `${name}_upd`, terms: 'pant,trousers' });

    const deleted = dataOf(
      await gql(request, SYNONYM_DELETE_MUTATION, { input: { id: synonym.id, clientMutationId: 'e2e-delete-synonym' } }),
      'deleteAdminMarketingSearchSynonym',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-synonym');
    created = [];

    expect(firstError(await gql(request, ADMIN_SEARCH_SYNONYM_QUERY, { id: synonym.id }))).toBe(NOT_FOUND);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const body = await gql(request, SYNONYM_MASS_DELETE_MUTATION, { input: { indices: [] } });
    expect(firstError(body)).toBe('The indices field is required and must be a non-empty array of search synonym IDs.');
    expect(body.data?.createAdminMarketingSearchSynonymMassDelete ?? null).toBeNull();
  });

  test('mass-delete removes the created synonym groups and ignores unknown ids', async ({ request }) => {
    const first = await createSynonym(request, { name: uniqueName(), terms: 'a,b' });
    const second = await createSynonym(request, { name: uniqueName(), terms: 'c,d' });

    const result = dataOf(
      await gql(request, SYNONYM_MASS_DELETE_MUTATION, { input: { indices: [first._id, second._id, 999999999] } }),
      'createAdminMarketingSearchSynonymMassDelete',
    ).adminMarketingSearchSynonymMassDelete;
    expect(result).toEqual({ deleted: [first._id, second._id], message: 'Search synonyms deleted successfully.' });
    created = [];

    expect(firstError(await gql(request, ADMIN_SEARCH_SYNONYM_QUERY, { id: second.id }))).toBe(NOT_FOUND);
  });
});
