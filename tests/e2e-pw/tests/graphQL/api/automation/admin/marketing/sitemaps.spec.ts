import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SITEMAP_QUERY,
  ADMIN_SITEMAP_CREATE_MUTATION,
  ADMIN_SITEMAP_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/sitemaps.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/marketing/sitemaps/999999999';

const NOT_FOUND = 'Sitemap not found.';

const DEFAULT_CHANNEL_ID = 1;

const SITEMAPS_BY_FILE_QUERY = `
  query adminMarketingSitemaps($first: Int, $file_name: String) {
    adminMarketingSitemaps(first: $first, file_name: $file_name) {
      totalCount
      edges { node { _id fileName path channels } }
    }
  }
`;

const SITEMAP_DELETE_MUTATION = `
  mutation deleteAdminMarketingSitemap($input: deleteAdminMarketingSitemapInput!) {
    deleteAdminMarketingSitemap(input: $input) {
      clientMutationId
    }
  }
`;

const SITEMAP_GENERATE_MUTATION = `
  mutation createAdminMarketingSitemapGenerate($input: createAdminMarketingSitemapGenerateInput!) {
    createAdminMarketingSitemapGenerate(input: $input) {
      adminMarketingSitemapGenerate { message }
    }
  }
`;

function uniqueStem(): string {
  return `e2e-gql-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
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

async function sitemapsNamed(request: APIRequestContext, fileName: string): Promise<any> {
  return dataOf(await gql(request, SITEMAPS_BY_FILE_QUERY, { first: 5, file_name: fileName }), 'adminMarketingSitemaps');
}

test.describe('admin marketing sitemaps graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, SITEMAP_DELETE_MUTATION, { input: { id } });
    }
  });

  async function createSitemap(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const sitemap = dataOf(await gql(request, ADMIN_SITEMAP_CREATE_MUTATION, { input }), 'createAdminMarketingSitemap').adminMarketingSitemap;
    created.push(sitemap.id);
    return sitemap;
  }

  test('listing filtered by file name returns only the created sitemap', async ({ request }) => {
    const fileName = `${uniqueStem()}.xml`;
    const sitemap = await createSitemap(request, { fileName, path: '/', channels: [DEFAULT_CHANNEL_ID] });

    const connection = await sitemapsNamed(request, fileName);
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: sitemap._id, fileName, path: '/', channels: [DEFAULT_CHANNEL_ID] });
  });

  test('listing with a file name nobody uses returns no sitemaps', async ({ request }) => {
    const connection = await sitemapsNamed(request, `${uniqueStem()}.xml`);
    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('detail unknown id reports the sitemap as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_SITEMAP_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingSitemap ?? null).toBeNull();
  });

  test('create empty payload is rejected with the file name requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_SITEMAP_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The file name field is required.');
    expect(body.data?.createAdminMarketingSitemap ?? null).toBeNull();
  });

  test('create without channels is rejected and nothing is stored', async ({ request }) => {
    const fileName = `${uniqueStem()}.xml`;
    const body = await gql(request, ADMIN_SITEMAP_CREATE_MUTATION, { input: { fileName, path: '/' } });
    expect(firstError(body)).toBe('The channels field is required.');
    expect((await sitemapsNamed(request, fileName)).totalCount).toBe(0);
  });

  test('create + update + delete round trip', async ({ request }) => {
    const stem = uniqueStem();
    const sitemap = await createSitemap(request, { fileName: `${stem}.xml`, path: '/', channels: [DEFAULT_CHANNEL_ID] });
    expect(sitemap).toEqual({
      id: `/api/admin/marketing/sitemaps/${sitemap._id}`,
      _id: sitemap._id,
      fileName: `${stem}.xml`,
      path: '/',
    });

    const updated = dataOf(
      await gql(request, ADMIN_SITEMAP_UPDATE_MUTATION, { input: { id: sitemap.id, fileName: `${stem}-upd.xml`, path: '/' } }),
      'updateAdminMarketingSitemap',
    ).adminMarketingSitemap;
    expect(updated).toEqual({ id: sitemap.id, _id: sitemap._id, fileName: `${stem}-upd.xml` });

    const read = dataOf(await gql(request, ADMIN_SITEMAP_QUERY, { id: sitemap.id }), 'adminMarketingSitemap');
    expect(read).toEqual(expect.objectContaining({ _id: sitemap._id, fileName: `${stem}-upd.xml`, path: '/', generatedAt: null }));

    const deleted = dataOf(
      await gql(request, SITEMAP_DELETE_MUTATION, { input: { id: sitemap.id, clientMutationId: 'e2e-delete-sitemap' } }),
      'deleteAdminMarketingSitemap',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-sitemap');
    created = [];

    expect(firstError(await gql(request, ADMIN_SITEMAP_QUERY, { id: sitemap.id }))).toBe(NOT_FOUND);
  });

  test('create invalid file name extension is rejected and nothing is stored', async ({ request }) => {
    const fileName = `${uniqueStem()}.txt`;
    const body = await gql(request, ADMIN_SITEMAP_CREATE_MUTATION, {
      input: { fileName, path: '/', channels: [DEFAULT_CHANNEL_ID] },
    });
    expect(firstError(body)).toBe('The file name field must end with one of the following: .xml.');
    expect(body.data?.createAdminMarketingSitemap ?? null).toBeNull();
    expect((await sitemapsNamed(request, fileName)).totalCount).toBe(0);
  });

  test('generate for an unknown sitemap reports it as not found', async ({ request }) => {
    const body = await gql(request, SITEMAP_GENERATE_MUTATION, { input: { sitemapId: 999999999 } });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.createAdminMarketingSitemapGenerate ?? null).toBeNull();
  });
});
