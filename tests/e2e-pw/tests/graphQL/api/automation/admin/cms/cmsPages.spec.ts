import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CMS_PAGE_CREATE,
  ADMIN_CMS_PAGE_DETAIL,
  ADMIN_CMS_PAGE_UPDATE,
} from '../../../../graphql/Queries/admin/cms/cmsPages.queries';
import { UNSAFE_HTML, assertSanitisedHtml } from '../../../../graphql/assertions/adminSanitisedHtml.assertions';

test.describe.configure({ timeout: 60_000 });

const PAGE_IRI = (id: number) => `/api/admin/cms/pages/${id}`;
const UNKNOWN_IRI = PAGE_IRI(999999999);
const DEFAULT_CHANNEL_ID = 1;

const NOT_FOUND = 'CMS page not found.';
const DELETED = 'CMS page deleted successfully.';
const MASS_DELETED = 'CMS pages deleted successfully.';

const PAGES_LIST = `
  query adminCmsPages($first: Int, $url_key: String, $locale: String) {
    adminCmsPages(first: $first, url_key: $url_key, locale: $locale) {
      totalCount
      edges { node { _id urlKey pageTitle } }
    }
  }
`;

const PAGE_DELETE = `
  mutation deleteAdminCmsPage($id: ID!) {
    deleteAdminCmsPage(input: { id: $id }) { adminCmsPage { _id message } }
  }
`;

const PAGE_MASS_DELETE = `
  mutation createAdminCmsPageMassDelete($indices: Iterable!) {
    createAdminCmsPageMassDelete(input: { indices: $indices }) {
      adminCmsPageMassDelete { deleted message }
    }
  }
`;

interface CreatedPage {
  _id: number;
  id: string;
  urlKey: string;
  pageTitle: string;
}

function uniqueKey(): string {
  return `e2e-gql-page-${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
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

async function pageCount(request: APIRequestContext, urlKey: string): Promise<number> {
  return dataOf(await gql(request, PAGES_LIST, { first: 5, url_key: urlKey, locale: 'en' }), 'adminCmsPages').totalCount;
}

test.describe('admin cms pages graphql api', () => {
  let created: CreatedPage[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const page of created) {
      await sendAdminGraphQLRequest(request, PAGE_DELETE, { id: page.id });
    }
  });

  async function createPage(request: APIRequestContext, htmlContent = '<p>hello</p>'): Promise<CreatedPage> {
    const urlKey = uniqueKey();
    const pageTitle = `e2e gql page ${urlKey}`;
    const page = dataOf(
      await gql(request, ADMIN_CMS_PAGE_CREATE, { urlKey, pageTitle, htmlContent, channels: [DEFAULT_CHANNEL_ID] }),
      'createAdminCmsPage',
    ).adminCmsPage;
    expect(page).toEqual({ id: PAGE_IRI(page._id), _id: page._id, urlKey, pageTitle });

    const createdPage = { _id: page._id, id: page.id, urlKey, pageTitle };
    created.push(createdPage);

    return createdPage;
  }

  test('listing filtered by url key returns only the created page', async ({ request }) => {
    const page = await createPage(request);

    const connection = dataOf(await gql(request, PAGES_LIST, { first: 5, url_key: page.urlKey, locale: 'en' }), 'adminCmsPages');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: page._id, urlKey: page.urlKey, pageTitle: page.pageTitle });
  });

  test('detail returns the created page', async ({ request }) => {
    const page = await createPage(request);

    const node = dataOf(await gql(request, ADMIN_CMS_PAGE_DETAIL, { id: page.id }), 'adminCmsPage');
    expect(node).toEqual({ id: page.id, _id: page._id, urlKey: page.urlKey, pageTitle: page.pageTitle, htmlContent: '<p>hello</p>' });
  });

  test('detail of an unknown page reports not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CMS_PAGE_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminCmsPage ?? null).toBeNull();
  });

  test('update changes the en translation and delete removes the page', async ({ request }) => {
    const page = await createPage(request);
    const urlKey = uniqueKey();

    const updated = dataOf(
      await gql(request, ADMIN_CMS_PAGE_UPDATE, {
        id: page.id,
        en: { url_key: urlKey, page_title: 'e2e gql updated', html_content: '<p>updated</p>' },
        channels: [DEFAULT_CHANNEL_ID],
        locale: 'en',
      }),
      'updateAdminCmsPage',
    ).adminCmsPage;
    expect(updated).toEqual({ id: page.id, _id: page._id });

    const reread = dataOf(await gql(request, ADMIN_CMS_PAGE_DETAIL, { id: page.id }), 'adminCmsPage');
    expect(reread).toEqual(expect.objectContaining({ urlKey, pageTitle: 'e2e gql updated', htmlContent: '<p>updated</p>' }));

    const deleted = dataOf(await gql(request, PAGE_DELETE, { id: page.id }), 'deleteAdminCmsPage').adminCmsPage;
    expect(deleted).toEqual({ _id: page._id, message: DELETED });
    created = [];

    expect(firstError(await gql(request, ADMIN_CMS_PAGE_DETAIL, { id: page.id }))).toBe(NOT_FOUND);
  });

  test('create with no channels is rejected', async ({ request }) => {
    const urlKey = uniqueKey();

    const body = await gql(request, ADMIN_CMS_PAGE_CREATE, { urlKey, pageTitle: 'x', htmlContent: '<p>x</p>', channels: [] });
    expect(firstError(body)).toBe('The channels field is required.');
    expect(await pageCount(request, urlKey)).toBe(0);
  });

  test('create with a taken url key is rejected', async ({ request }) => {
    const page = await createPage(request);

    const body = await gql(request, ADMIN_CMS_PAGE_CREATE, {
      urlKey: page.urlKey,
      pageTitle: 'e2e gql twin',
      htmlContent: '<p>twin</p>',
      channels: [DEFAULT_CHANNEL_ID],
    });
    expect(firstError(body)).toBe('The url_key has already been taken.');
    expect(await pageCount(request, page.urlKey)).toBe(1);
  });

  test('mass-delete removes the freshly created page', async ({ request }) => {
    const page = await createPage(request);

    const result = dataOf(await gql(request, PAGE_MASS_DELETE, { indices: [page._id] }), 'createAdminCmsPageMassDelete').adminCmsPageMassDelete;
    expect(result).toEqual({ deleted: [page._id], message: MASS_DELETED });
    created = [];

    expect(firstError(await gql(request, ADMIN_CMS_PAGE_DETAIL, { id: page.id }))).toBe(NOT_FOUND);
  });

  test('mass-delete without indices is rejected', async ({ request }) => {
    expect(firstError(await gql(request, PAGE_MASS_DELETE, { indices: [] }))).toBe(
      'The indices field is required and must be a non-empty array.',
    );
  });

  test('should strip scripts and template code from page html on create and update', async ({ request }) => {
    const page = await createPage(request, UNSAFE_HTML);

    const createdNode = dataOf(await gql(request, ADMIN_CMS_PAGE_DETAIL, { id: page.id }), 'adminCmsPage');
    assertSanitisedHtml(createdNode.htmlContent);

    dataOf(
      await gql(request, ADMIN_CMS_PAGE_UPDATE, {
        id: page.id,
        en: { url_key: page.urlKey, page_title: 'e2e gql sanitised page updated', html_content: `${UNSAFE_HTML}<p>Updated</p>` },
        channels: [DEFAULT_CHANNEL_ID],
        locale: 'en',
      }),
      'updateAdminCmsPage',
    );

    const updatedNode = dataOf(await gql(request, ADMIN_CMS_PAGE_DETAIL, { id: page.id }), 'adminCmsPage');
    assertSanitisedHtml(updatedNode.htmlContent);
    expect(updatedNode.htmlContent).toContain('<p>Updated</p>');
  });
});
