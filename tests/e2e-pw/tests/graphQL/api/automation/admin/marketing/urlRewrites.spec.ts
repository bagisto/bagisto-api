import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_URL_REWRITE_QUERY,
  ADMIN_URL_REWRITE_CREATE_MUTATION,
  ADMIN_URL_REWRITE_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/urlRewrites.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/marketing/url-rewrites/999999999';

const NOT_FOUND = 'URL rewrite not found.';

const REWRITES_FILTER_QUERY = `
  query adminMarketingUrlRewrites($first: Int, $request_path: String, $entity_type: String) {
    adminMarketingUrlRewrites(first: $first, request_path: $request_path, entity_type: $entity_type) {
      totalCount
      edges { node { _id entityType requestPath targetPath redirectType locale } }
    }
  }
`;

const REWRITE_DELETE_MUTATION = `
  mutation deleteAdminMarketingUrlRewrite($input: deleteAdminMarketingUrlRewriteInput!) {
    deleteAdminMarketingUrlRewrite(input: $input) {
      clientMutationId
    }
  }
`;

const REWRITE_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingUrlRewriteMassDelete($input: createAdminMarketingUrlRewriteMassDeleteInput!) {
    createAdminMarketingUrlRewriteMassDelete(input: $input) {
      adminMarketingUrlRewriteMassDelete { deleted message }
    }
  }
`;

function uniqueStem(): string {
  return `e2e-gql-rw-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function rewriteInput(stem: string): Record<string, any> {
  return {
    entityType: 'product',
    requestPath: `${stem}-old`,
    targetPath: `${stem}-new`,
    redirectType: '301',
    locale: 'en',
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

async function rewrites(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, REWRITES_FILTER_QUERY, { first: 5, ...variables }), 'adminMarketingUrlRewrites');
}

test.describe('admin marketing url rewrites graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, REWRITE_DELETE_MUTATION, { input: { id } });
    }
  });

  async function createRewrite(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const rewrite = dataOf(await gql(request, ADMIN_URL_REWRITE_CREATE_MUTATION, { input }), 'createAdminMarketingUrlRewrite').adminMarketingUrlRewrite;
    created.push(rewrite.id);
    return rewrite;
  }

  test('listing filtered by request path returns only the created rewrite', async ({ request }) => {
    const stem = uniqueStem();
    const rewrite = await createRewrite(request, rewriteInput(stem));

    const connection = await rewrites(request, { request_path: stem });
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({
      _id: rewrite._id,
      entityType: 'product',
      requestPath: `${stem}-old`,
      targetPath: `${stem}-new`,
      redirectType: '301',
      locale: 'en',
    });
  });

  test('listing entity type filter keeps or drops the created rewrite by its type', async ({ request }) => {
    const stem = uniqueStem();
    await createRewrite(request, rewriteInput(stem));

    expect((await rewrites(request, { request_path: stem, entity_type: 'product' })).totalCount).toBe(1);
    expect((await rewrites(request, { request_path: stem, entity_type: 'category' })).totalCount).toBe(0);
  });

  test('detail unknown id reports the rewrite as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_URL_REWRITE_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingUrlRewrite ?? null).toBeNull();
  });

  test('create empty payload is rejected with the entity type requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_URL_REWRITE_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The entity type field is required.');
    expect(body.data?.createAdminMarketingUrlRewrite ?? null).toBeNull();
  });

  test('create + update + delete round trip', async ({ request }) => {
    const stem = uniqueStem();
    const rewrite = await createRewrite(request, rewriteInput(stem));
    expect(rewrite).toEqual({
      id: `/api/admin/marketing/url-rewrites/${rewrite._id}`,
      _id: rewrite._id,
      entityType: 'product',
      requestPath: `${stem}-old`,
    });

    const updated = dataOf(
      await gql(request, ADMIN_URL_REWRITE_UPDATE_MUTATION, { input: { id: rewrite.id, ...rewriteInput(stem), redirectType: '302' } }),
      'updateAdminMarketingUrlRewrite',
    ).adminMarketingUrlRewrite;
    expect(updated).toEqual({ id: rewrite.id, _id: rewrite._id, redirectType: '302' });

    const read = dataOf(await gql(request, ADMIN_URL_REWRITE_QUERY, { id: rewrite.id }), 'adminMarketingUrlRewrite');
    expect(read).toEqual({
      id: rewrite.id,
      _id: rewrite._id,
      entityType: 'product',
      requestPath: `${stem}-old`,
      targetPath: `${stem}-new`,
      redirectType: '302',
      locale: 'en',
    });

    const deleted = dataOf(
      await gql(request, REWRITE_DELETE_MUTATION, { input: { id: rewrite.id, clientMutationId: 'e2e-delete-rewrite' } }),
      'deleteAdminMarketingUrlRewrite',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-rewrite');
    created = [];

    expect(firstError(await gql(request, ADMIN_URL_REWRITE_QUERY, { id: rewrite.id }))).toBe(NOT_FOUND);
  });

  test('create invalid entity type is rejected and nothing is stored', async ({ request }) => {
    const stem = uniqueStem();
    const body = await gql(request, ADMIN_URL_REWRITE_CREATE_MUTATION, { input: { ...rewriteInput(stem), entityType: 'banana' } });
    expect(firstError(body)).toBe('The selected entity type is invalid.');
    expect(body.data?.createAdminMarketingUrlRewrite ?? null).toBeNull();
    expect((await rewrites(request, { request_path: stem })).totalCount).toBe(0);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const body = await gql(request, REWRITE_MASS_DELETE_MUTATION, { input: { indices: [] } });
    expect(firstError(body)).toBe('At least one URL rewrite id is required.');
    expect(body.data?.createAdminMarketingUrlRewriteMassDelete ?? null).toBeNull();
  });

  test('mass-delete removes the created rewrite and ignores unknown ids', async ({ request }) => {
    const stem = uniqueStem();
    const rewrite = await createRewrite(request, rewriteInput(stem));

    const result = dataOf(
      await gql(request, REWRITE_MASS_DELETE_MUTATION, { input: { indices: [rewrite._id, 999999999] } }),
      'createAdminMarketingUrlRewriteMassDelete',
    ).adminMarketingUrlRewriteMassDelete;
    expect(result).toEqual({ deleted: [rewrite._id], message: 'URL rewrites deleted successfully.' });
    created = [];

    expect((await rewrites(request, { request_path: stem })).totalCount).toBe(0);
  });
});
