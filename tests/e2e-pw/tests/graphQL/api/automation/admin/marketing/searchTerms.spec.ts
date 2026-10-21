import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SEARCH_TERM_QUERY,
  ADMIN_SEARCH_TERM_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/searchTerms.queries';
import { ADMIN_SEARCH_TERM_CREATE_COVERAGE_MUTATION } from '../../../../graphql/Queries/coverage/adminMarketing.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/marketing/search-terms/999999999';

const NOT_FOUND = 'Search term not found.';

const DEFAULT_CHANNEL_ID = 1;

const DEFAULT_LOCALE = 'en';

const TERMS_FILTER_QUERY = `
  query adminMarketingSearchTerms($first: Int, $term: String) {
    adminMarketingSearchTerms(first: $first, term: $term) {
      totalCount
      edges { node { _id term results uses redirectUrl } }
    }
  }
`;

const TERM_DELETE_MUTATION = `
  mutation deleteAdminMarketingSearchTerm($input: deleteAdminMarketingSearchTermInput!) {
    deleteAdminMarketingSearchTerm(input: $input) {
      clientMutationId
    }
  }
`;

const TERM_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingSearchTermMassDelete($input: createAdminMarketingSearchTermMassDeleteInput!) {
    createAdminMarketingSearchTermMassDelete(input: $input) {
      adminMarketingSearchTermMassDelete { deleted message }
    }
  }
`;

function uniqueTerm(): string {
  return `e2e-gql-term-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
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

async function termsMatching(request: APIRequestContext, term: string): Promise<any> {
  return dataOf(await gql(request, TERMS_FILTER_QUERY, { first: 5, term }), 'adminMarketingSearchTerms');
}

test.describe('admin marketing search terms graphql', () => {
  let created: string[];
  let term: string;
  let searchTerm: { id: string; _id: number };

  test.beforeEach(async ({ request }) => {
    created = [];
    term = uniqueTerm();
    searchTerm = dataOf(
      await gql(request, ADMIN_SEARCH_TERM_CREATE_COVERAGE_MUTATION, {
        input: { term, channelId: DEFAULT_CHANNEL_ID, locale: DEFAULT_LOCALE },
      }),
      'createAdminMarketingSearchTerm',
    ).adminMarketingSearchTerm;
    created.push(searchTerm.id);
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, TERM_DELETE_MUTATION, { input: { id } });
    }
  });

  test('listing filtered by term returns only the created term', async ({ request }) => {
    const connection = await termsMatching(request, term);
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: searchTerm._id, term, results: 0, uses: 0, redirectUrl: null });
  });

  test('listing with a term nobody searched returns no terms', async ({ request }) => {
    const connection = await termsMatching(request, uniqueTerm());
    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('detail unknown id reports the term as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_SEARCH_TERM_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingSearchTerm ?? null).toBeNull();
  });

  test('update unknown id reports the term as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_SEARCH_TERM_UPDATE_MUTATION, { input: { id: UNKNOWN_IRI, term: 'x' } });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.updateAdminMarketingSearchTerm ?? null).toBeNull();
  });

  test('update renames the created term and sets its redirect', async ({ request }) => {
    const renamed = uniqueTerm();
    const updated = dataOf(
      await gql(request, ADMIN_SEARCH_TERM_UPDATE_MUTATION, {
        input: { id: searchTerm.id, term: renamed, redirectUrl: 'https://example.com/e2e' },
      }),
      'updateAdminMarketingSearchTerm',
    ).adminMarketingSearchTerm;
    expect(updated).toEqual({ id: searchTerm.id, _id: searchTerm._id, term: renamed });

    const read = dataOf(await gql(request, ADMIN_SEARCH_TERM_QUERY, { id: searchTerm.id }), 'adminMarketingSearchTerm');
    expect(read).toEqual(expect.objectContaining({ _id: searchTerm._id, term: renamed, redirectUrl: 'https://example.com/e2e' }));
  });

  test('update without a term is rejected and the term is unchanged', async ({ request }) => {
    const body = await gql(request, ADMIN_SEARCH_TERM_UPDATE_MUTATION, { input: { id: searchTerm.id } });
    expect(firstError(body)).toBe('The term field is required.');

    const read = dataOf(await gql(request, ADMIN_SEARCH_TERM_QUERY, { id: searchTerm.id }), 'adminMarketingSearchTerm');
    expect(read.term).toBe(term);
  });

  test('delete unknown id reports the term as not found', async ({ request }) => {
    const body = await gql(request, TERM_DELETE_MUTATION, { input: { id: UNKNOWN_IRI } });
    expect(firstError(body)).toBe(NOT_FOUND);
  });

  test('delete removes the created term', async ({ request }) => {
    const deleted = dataOf(
      await gql(request, TERM_DELETE_MUTATION, { input: { id: searchTerm.id, clientMutationId: 'e2e-delete-term' } }),
      'deleteAdminMarketingSearchTerm',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-term');
    created = [];

    expect(firstError(await gql(request, ADMIN_SEARCH_TERM_QUERY, { id: searchTerm.id }))).toBe(NOT_FOUND);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const body = await gql(request, TERM_MASS_DELETE_MUTATION, { input: { indices: [] } });
    expect(firstError(body)).toBe('At least one ID must be provided in indices.');
    expect(body.data?.createAdminMarketingSearchTermMassDelete ?? null).toBeNull();
  });

  test('mass-delete removes the created term and ignores unknown ids', async ({ request }) => {
    const result = dataOf(
      await gql(request, TERM_MASS_DELETE_MUTATION, { input: { indices: [searchTerm._id, 999999999] } }),
      'createAdminMarketingSearchTermMassDelete',
    ).adminMarketingSearchTermMassDelete;
    expect(result).toEqual({ deleted: [searchTerm._id], message: 'Search terms deleted successfully.' });
    created = [];

    expect((await termsMatching(request, term)).totalCount).toBe(0);
  });
});
